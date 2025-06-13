#!/usr/bin/env python3
"""
SEOForge Production Deployment Tool
A comprehensive Python-based deployment system for EC2
"""

import os
import sys
import json
import time
import subprocess
import argparse
import logging
from pathlib import Path
from typing import Dict, List, Optional, Any
from dataclasses import dataclass
import boto3
from botocore.exceptions import ClientError, NoCredentialsError

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.StreamHandler(),
        logging.FileHandler('deployment.log')
    ]
)
logger = logging.getLogger(__name__)

@dataclass
class DeploymentConfig:
    """Deployment configuration"""
    domain: str
    email: str
    environment: str = "production"
    instance_type: str = "t3.large"
    region: str = "us-east-1"
    key_name: str = ""
    vpc_id: str = ""
    subnet_id: str = ""
    db_instance_class: str = "db.t3.small"
    redis_node_type: str = "cache.t3.micro"
    
class Colors:
    """ANSI color codes for terminal output"""
    RED = '\033[0;31m'
    GREEN = '\033[0;32m'
    YELLOW = '\033[1;33m'
    BLUE = '\033[0;34m'
    PURPLE = '\033[0;35m'
    CYAN = '\033[0;36m'
    WHITE = '\033[1;37m'
    NC = '\033[0m'  # No Color

class DeploymentError(Exception):
    """Custom deployment exception"""
    pass

class SEOForgeDeployer:
    """Main deployment class"""
    
    def __init__(self, config: DeploymentConfig):
        self.config = config
        self.ec2_client = None
        self.rds_client = None
        self.elasticache_client = None
        self.elbv2_client = None
        self.route53_client = None
        self.instance_id = None
        self.db_endpoint = None
        self.redis_endpoint = None
        
        # Initialize AWS clients
        self._init_aws_clients()
    
    def _init_aws_clients(self):
        """Initialize AWS service clients"""
        try:
            session = boto3.Session(region_name=self.config.region)
            self.ec2_client = session.client('ec2')
            self.rds_client = session.client('rds')
            self.elasticache_client = session.client('elasticache')
            self.elbv2_client = session.client('elbv2')
            self.route53_client = session.client('route53')
            
            # Test credentials
            self.ec2_client.describe_regions()
            self.log_success("AWS credentials validated")
            
        except NoCredentialsError:
            raise DeploymentError("AWS credentials not found. Please configure AWS CLI.")
        except Exception as e:
            raise DeploymentError(f"Failed to initialize AWS clients: {e}")
    
    def log_info(self, message: str):
        """Log info message with color"""
        print(f"{Colors.BLUE}[INFO]{Colors.NC} {message}")
        logger.info(message)
    
    def log_success(self, message: str):
        """Log success message with color"""
        print(f"{Colors.GREEN}[SUCCESS]{Colors.NC} {message}")
        logger.info(message)
    
    def log_warning(self, message: str):
        """Log warning message with color"""
        print(f"{Colors.YELLOW}[WARNING]{Colors.NC} {message}")
        logger.warning(message)
    
    def log_error(self, message: str):
        """Log error message with color"""
        print(f"{Colors.RED}[ERROR]{Colors.NC} {message}")
        logger.error(message)
    
    def run_command(self, command: str, check: bool = True) -> subprocess.CompletedProcess:
        """Run shell command with logging"""
        self.log_info(f"Running: {command}")
        try:
            result = subprocess.run(
                command,
                shell=True,
                capture_output=True,
                text=True,
                check=check
            )
            if result.stdout:
                logger.debug(f"STDOUT: {result.stdout}")
            if result.stderr:
                logger.debug(f"STDERR: {result.stderr}")
            return result
        except subprocess.CalledProcessError as e:
            self.log_error(f"Command failed: {e}")
            if e.stdout:
                self.log_error(f"STDOUT: {e.stdout}")
            if e.stderr:
                self.log_error(f"STDERR: {e.stderr}")
            raise DeploymentError(f"Command failed: {command}")
    
    def create_security_groups(self) -> Dict[str, str]:
        """Create security groups for the deployment"""
        self.log_info("Creating security groups...")
        
        security_groups = {}
        
        try:
            # ALB Security Group
            alb_sg = self.ec2_client.create_security_group(
                GroupName=f'seoforge-alb-{int(time.time())}',
                Description='SEOForge Application Load Balancer Security Group',
                VpcId=self.config.vpc_id
            )
            alb_sg_id = alb_sg['GroupId']
            security_groups['alb'] = alb_sg_id
            
            # Add rules to ALB security group
            self.ec2_client.authorize_security_group_ingress(
                GroupId=alb_sg_id,
                IpPermissions=[
                    {
                        'IpProtocol': 'tcp',
                        'FromPort': 80,
                        'ToPort': 80,
                        'IpRanges': [{'CidrIp': '0.0.0.0/0'}]
                    },
                    {
                        'IpProtocol': 'tcp',
                        'FromPort': 443,
                        'ToPort': 443,
                        'IpRanges': [{'CidrIp': '0.0.0.0/0'}]
                    }
                ]
            )
            
            # EC2 Security Group
            ec2_sg = self.ec2_client.create_security_group(
                GroupName=f'seoforge-ec2-{int(time.time())}',
                Description='SEOForge EC2 Instance Security Group',
                VpcId=self.config.vpc_id
            )
            ec2_sg_id = ec2_sg['GroupId']
            security_groups['ec2'] = ec2_sg_id
            
            # Add rules to EC2 security group
            self.ec2_client.authorize_security_group_ingress(
                GroupId=ec2_sg_id,
                IpPermissions=[
                    {
                        'IpProtocol': 'tcp',
                        'FromPort': 22,
                        'ToPort': 22,
                        'IpRanges': [{'CidrIp': '0.0.0.0/0'}]  # Restrict this in production
                    },
                    {
                        'IpProtocol': 'tcp',
                        'FromPort': 8083,
                        'ToPort': 8083,
                        'UserIdGroupPairs': [{'GroupId': alb_sg_id}]
                    },
                    {
                        'IpProtocol': 'tcp',
                        'FromPort': 9090,
                        'ToPort': 9090,
                        'UserIdGroupPairs': [{'GroupId': alb_sg_id}]
                    }
                ]
            )
            
            # RDS Security Group
            rds_sg = self.ec2_client.create_security_group(
                GroupName=f'seoforge-rds-{int(time.time())}',
                Description='SEOForge RDS Security Group',
                VpcId=self.config.vpc_id
            )
            rds_sg_id = rds_sg['GroupId']
            security_groups['rds'] = rds_sg_id
            
            # Add rules to RDS security group
            self.ec2_client.authorize_security_group_ingress(
                GroupId=rds_sg_id,
                IpPermissions=[
                    {
                        'IpProtocol': 'tcp',
                        'FromPort': 5432,
                        'ToPort': 5432,
                        'UserIdGroupPairs': [{'GroupId': ec2_sg_id}]
                    }
                ]
            )
            
            # ElastiCache Security Group
            cache_sg = self.ec2_client.create_security_group(
                GroupName=f'seoforge-cache-{int(time.time())}',
                Description='SEOForge ElastiCache Security Group',
                VpcId=self.config.vpc_id
            )
            cache_sg_id = cache_sg['GroupId']
            security_groups['cache'] = cache_sg_id
            
            # Add rules to ElastiCache security group
            self.ec2_client.authorize_security_group_ingress(
                GroupId=cache_sg_id,
                IpPermissions=[
                    {
                        'IpProtocol': 'tcp',
                        'FromPort': 6379,
                        'ToPort': 6379,
                        'UserIdGroupPairs': [{'GroupId': ec2_sg_id}]
                    }
                ]
            )
            
            self.log_success("Security groups created successfully")
            return security_groups
            
        except ClientError as e:
            raise DeploymentError(f"Failed to create security groups: {e}")
    
    def create_rds_instance(self, security_group_id: str) -> str:
        """Create RDS PostgreSQL instance"""
        self.log_info("Creating RDS PostgreSQL instance...")
        
        db_instance_id = f"seoforge-db-{int(time.time())}"
        
        try:
            response = self.rds_client.create_db_instance(
                DBInstanceIdentifier=db_instance_id,
                DBInstanceClass=self.config.db_instance_class,
                Engine='postgres',
                EngineVersion='15.4',
                MasterUsername='seoforge',
                MasterUserPassword='SecurePassword123!',  # Change this!
                AllocatedStorage=20,
                StorageType='gp3',
                StorageEncrypted=True,
                VpcSecurityGroupIds=[security_group_id],
                DBSubnetGroupName='default',  # Use your subnet group
                BackupRetentionPeriod=7,
                MultiAZ=True,
                PubliclyAccessible=False,
                DeletionProtection=True
            )
            
            # Wait for instance to be available
            self.log_info("Waiting for RDS instance to be available...")
            waiter = self.rds_client.get_waiter('db_instance_available')
            waiter.wait(
                DBInstanceIdentifier=db_instance_id,
                WaiterConfig={'Delay': 30, 'MaxAttempts': 40}
            )
            
            # Get endpoint
            response = self.rds_client.describe_db_instances(
                DBInstanceIdentifier=db_instance_id
            )
            endpoint = response['DBInstances'][0]['Endpoint']['Address']
            
            self.log_success(f"RDS instance created: {endpoint}")
            return endpoint
            
        except ClientError as e:
            raise DeploymentError(f"Failed to create RDS instance: {e}")
    
    def create_elasticache_cluster(self, security_group_id: str) -> str:
        """Create ElastiCache Redis cluster"""
        self.log_info("Creating ElastiCache Redis cluster...")
        
        cluster_id = f"seoforge-redis-{int(time.time())}"
        
        try:
            response = self.elasticache_client.create_cache_cluster(
                CacheClusterId=cluster_id,
                CacheNodeType=self.config.redis_node_type,
                Engine='redis',
                EngineVersion='7.0',
                NumCacheNodes=1,
                SecurityGroupIds=[security_group_id],
                CacheSubnetGroupName='default'  # Use your subnet group
            )
            
            # Wait for cluster to be available
            self.log_info("Waiting for Redis cluster to be available...")
            waiter = self.elasticache_client.get_waiter('cache_cluster_available')
            waiter.wait(
                CacheClusterId=cluster_id,
                WaiterConfig={'Delay': 30, 'MaxAttempts': 20}
            )
            
            # Get endpoint
            response = self.elasticache_client.describe_cache_clusters(
                CacheClusterId=cluster_id,
                ShowCacheNodeInfo=True
            )
            endpoint = response['CacheClusters'][0]['CacheNodes'][0]['Endpoint']['Address']
            
            self.log_success(f"Redis cluster created: {endpoint}")
            return endpoint
            
        except ClientError as e:
            raise DeploymentError(f"Failed to create Redis cluster: {e}")
    
    def create_ec2_instance(self, security_group_id: str) -> str:
        """Create EC2 instance"""
        self.log_info("Creating EC2 instance...")
        
        # User data script for initial setup
        user_data = """#!/bin/bash
        yum update -y
        yum install -y docker git
        systemctl start docker
        systemctl enable docker
        usermod -aG docker ec2-user
        
        # Install Docker Compose
        curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
        chmod +x /usr/local/bin/docker-compose
        
        # Install CloudWatch agent
        wget https://s3.amazonaws.com/amazoncloudwatch-agent/amazon_linux/amd64/latest/amazon-cloudwatch-agent.rpm
        rpm -U ./amazon-cloudwatch-agent.rpm
        """
        
        try:
            response = self.ec2_client.run_instances(
                ImageId='ami-0c02fb55956c7d316',  # Amazon Linux 2 AMI
                MinCount=1,
                MaxCount=1,
                InstanceType=self.config.instance_type,
                KeyName=self.config.key_name,
                SecurityGroupIds=[security_group_id],
                SubnetId=self.config.subnet_id,
                UserData=user_data,
                TagSpecifications=[
                    {
                        'ResourceType': 'instance',
                        'Tags': [
                            {'Key': 'Name', 'Value': 'SEOForge-Production'},
                            {'Key': 'Environment', 'Value': self.config.environment}
                        ]
                    }
                ]
            )
            
            instance_id = response['Instances'][0]['InstanceId']
            
            # Wait for instance to be running
            self.log_info("Waiting for EC2 instance to be running...")
            waiter = self.ec2_client.get_waiter('instance_running')
            waiter.wait(InstanceIds=[instance_id])
            
            # Get public IP
            response = self.ec2_client.describe_instances(InstanceIds=[instance_id])
            public_ip = response['Reservations'][0]['Instances'][0]['PublicIpAddress']
            
            self.log_success(f"EC2 instance created: {instance_id} ({public_ip})")
            return instance_id
            
        except ClientError as e:
            raise DeploymentError(f"Failed to create EC2 instance: {e}")
    
    def deploy_application(self, instance_id: str):
        """Deploy application to EC2 instance"""
        self.log_info("Deploying application...")
        
        # Get instance public IP
        response = self.ec2_client.describe_instances(InstanceIds=[instance_id])
        public_ip = response['Reservations'][0]['Instances'][0]['PublicIpAddress']
        
        # Create deployment script
        deploy_script = f"""
        #!/bin/bash
        set -e
        
        # Clone repository
        git clone https://github.com/your-org/seoforge-mcp-server.git /opt/seoforge
        cd /opt/seoforge
        
        # Create environment file
        cat > .env << EOF
ENVIRONMENT=production
DEBUG=false
HOST=0.0.0.0
PORT=8083
DATABASE_URL=postgresql://seoforge:SecurePassword123!@{self.db_endpoint}:5432/seoforge
REDIS_URL=redis://{self.redis_endpoint}:6379
CORS_ORIGINS=https://{self.config.domain}
SECRET_KEY=$(openssl rand -base64 32)
JWT_SECRET=$(openssl rand -base64 32)
OPENAI_API_KEY=your-openai-key
ANTHROPIC_API_KEY=your-anthropic-key
GOOGLE_AI_API_KEY=your-google-key
EOF
        
        # Start services
        docker-compose -f docker-compose.prod.yml up -d
        """
        
        # Save script to file
        with open('deploy_script.sh', 'w') as f:
            f.write(deploy_script)
        
        # Copy and execute script on instance
        self.run_command(f"scp -i {self.config.key_name}.pem deploy_script.sh ec2-user@{public_ip}:/tmp/")
        self.run_command(f"ssh -i {self.config.key_name}.pem ec2-user@{public_ip} 'chmod +x /tmp/deploy_script.sh && sudo /tmp/deploy_script.sh'")
        
        self.log_success("Application deployed successfully")
    
    def deploy(self):
        """Main deployment method"""
        try:
            self.log_info(f"Starting deployment for {self.config.domain}")
            
            # Create security groups
            security_groups = self.create_security_groups()
            
            # Create RDS instance
            self.db_endpoint = self.create_rds_instance(security_groups['rds'])
            
            # Create Redis cluster
            self.redis_endpoint = self.create_elasticache_cluster(security_groups['cache'])
            
            # Create EC2 instance
            self.instance_id = self.create_ec2_instance(security_groups['ec2'])
            
            # Deploy application
            self.deploy_application(self.instance_id)
            
            self.log_success("Deployment completed successfully!")
            self.print_deployment_info()
            
        except Exception as e:
            self.log_error(f"Deployment failed: {e}")
            raise

    def print_deployment_info(self):
        """Print deployment information"""
        print(f"\n{Colors.GREEN}=== DEPLOYMENT SUCCESSFUL ==={Colors.NC}")
        print(f"Domain: {self.config.domain}")
        print(f"Instance ID: {self.instance_id}")
        print(f"Database Endpoint: {self.db_endpoint}")
        print(f"Redis Endpoint: {self.redis_endpoint}")
        print(f"\n{Colors.YELLOW}Next Steps:{Colors.NC}")
        print("1. Update DNS records to point to the EC2 instance")
        print("2. Configure SSL certificate")
        print("3. Update API keys in environment variables")
        print("4. Test the application")

def main():
    """Main function"""
    parser = argparse.ArgumentParser(description='SEOForge Production Deployment Tool')
    parser.add_argument('--domain', required=True, help='Domain name for the application')
    parser.add_argument('--email', required=True, help='Email for SSL certificate')
    parser.add_argument('--key-name', required=True, help='EC2 Key Pair name')
    parser.add_argument('--vpc-id', required=True, help='VPC ID')
    parser.add_argument('--subnet-id', required=True, help='Subnet ID')
    parser.add_argument('--region', default='us-east-1', help='AWS region')
    parser.add_argument('--instance-type', default='t3.large', help='EC2 instance type')
    
    args = parser.parse_args()
    
    config = DeploymentConfig(
        domain=args.domain,
        email=args.email,
        key_name=args.key_name,
        vpc_id=args.vpc_id,
        subnet_id=args.subnet_id,
        region=args.region,
        instance_type=args.instance_type
    )
    
    deployer = SEOForgeDeployer(config)
    deployer.deploy()

if __name__ == '__main__':
    main()
