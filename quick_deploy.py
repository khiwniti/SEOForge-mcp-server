#!/usr/bin/env python3
"""
Quick SEOForge Deployment Script
Simple Python script for rapid deployment to existing infrastructure
"""

import os
import sys
import json
import time
import subprocess
import argparse
from pathlib import Path
from typing import Dict, Optional
import boto3
from botocore.exceptions import ClientError

class QuickDeployer:
    """Simple deployment class for existing infrastructure"""
    
    def __init__(self, instance_ip: str, key_file: str, domain: str):
        self.instance_ip = instance_ip
        self.key_file = key_file
        self.domain = domain
        self.ssh_user = "ec2-user"  # or "ubuntu" for Ubuntu instances
    
    def log(self, message: str, level: str = "INFO"):
        """Simple logging"""
        timestamp = time.strftime("%Y-%m-%d %H:%M:%S")
        colors = {
            "INFO": "\033[0;34m",
            "SUCCESS": "\033[0;32m", 
            "WARNING": "\033[1;33m",
            "ERROR": "\033[0;31m"
        }
        color = colors.get(level, "\033[0m")
        print(f"{color}[{level}] {timestamp} - {message}\033[0m")
    
    def run_ssh_command(self, command: str, check: bool = True) -> str:
        """Run command on remote server via SSH"""
        ssh_command = [
            "ssh", "-i", self.key_file,
            "-o", "StrictHostKeyChecking=no",
            "-o", "UserKnownHostsFile=/dev/null",
            f"{self.ssh_user}@{self.instance_ip}",
            command
        ]
        
        self.log(f"Running SSH command: {command}")
        
        try:
            result = subprocess.run(
                ssh_command,
                capture_output=True,
                text=True,
                check=check
            )
            return result.stdout.strip()
        except subprocess.CalledProcessError as e:
            self.log(f"SSH command failed: {e.stderr}", "ERROR")
            if check:
                raise
            return ""
    
    def copy_file(self, local_path: str, remote_path: str):
        """Copy file to remote server"""
        scp_command = [
            "scp", "-i", self.key_file,
            "-o", "StrictHostKeyChecking=no",
            "-o", "UserKnownHostsFile=/dev/null",
            local_path,
            f"{self.ssh_user}@{self.instance_ip}:{remote_path}"
        ]
        
        self.log(f"Copying {local_path} to {remote_path}")
        subprocess.run(scp_command, check=True)
    
    def install_dependencies(self):
        """Install required dependencies on the server"""
        self.log("Installing dependencies...")
        
        commands = [
            "sudo yum update -y",
            "sudo yum install -y docker git htop curl",
            "sudo systemctl start docker",
            "sudo systemctl enable docker",
            "sudo usermod -aG docker ec2-user",
            # Install Docker Compose
            'sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose',
            "sudo chmod +x /usr/local/bin/docker-compose",
            # Install Nginx
            "sudo amazon-linux-extras install nginx1 -y",
            "sudo systemctl enable nginx"
        ]
        
        for command in commands:
            try:
                self.run_ssh_command(command)
            except subprocess.CalledProcessError as e:
                self.log(f"Warning: Command failed: {command}", "WARNING")
    
    def setup_application(self):
        """Setup the SEOForge application"""
        self.log("Setting up application...")
        
        # Create application directory
        self.run_ssh_command("sudo mkdir -p /opt/seoforge")
        self.run_ssh_command("sudo chown ec2-user:ec2-user /opt/seoforge")
        
        # Clone repository (or copy files)
        repo_url = "https://github.com/your-org/seoforge-mcp-server.git"
        self.run_ssh_command(f"git clone {repo_url} /opt/seoforge || (cd /opt/seoforge && git pull)")
        
        # Create environment file
        env_content = self.create_env_file()
        with open("/tmp/seoforge.env", "w") as f:
            f.write(env_content)
        
        self.copy_file("/tmp/seoforge.env", "/opt/seoforge/.env")
        os.remove("/tmp/seoforge.env")
        
        self.log("Application setup completed", "SUCCESS")
    
    def create_env_file(self) -> str:
        """Create environment file content"""
        # Get environment variables or use defaults
        db_url = os.getenv("DATABASE_URL", "postgresql://user:pass@localhost:5432/seoforge")
        redis_url = os.getenv("REDIS_URL", "redis://localhost:6379")
        openai_key = os.getenv("OPENAI_API_KEY", "your-openai-key")
        anthropic_key = os.getenv("ANTHROPIC_API_KEY", "your-anthropic-key")
        google_key = os.getenv("GOOGLE_AI_API_KEY", "your-google-key")
        
        return f"""# SEOForge Production Environment
ENVIRONMENT=production
DEBUG=false
HOST=0.0.0.0
PORT=8083
WORKERS=4

# Database
DATABASE_URL={db_url}
DB_POOL_SIZE=20
DB_MAX_OVERFLOW=30

# Redis
REDIS_URL={redis_url}

# Security
SECRET_KEY={os.urandom(32).hex()}
JWT_SECRET={os.urandom(32).hex()}
CORS_ORIGINS=https://{self.domain},https://www.{self.domain}

# AI Providers
OPENAI_API_KEY={openai_key}
ANTHROPIC_API_KEY={anthropic_key}
GOOGLE_AI_API_KEY={google_key}

# Rate Limiting
RATE_LIMIT_ENABLED=true
RATE_LIMIT_REQUESTS=100
RATE_LIMIT_WINDOW=3600

# Logging
LOG_LEVEL=INFO
LOG_FORMAT=json
LOG_FILE=/var/log/seoforge/app.log

# Monitoring
ENABLE_METRICS=true
ENABLE_TRACING=true
"""
    
    def setup_nginx(self):
        """Setup Nginx reverse proxy"""
        self.log("Setting up Nginx...")
        
        nginx_config = f"""
server {{
    listen 80;
    server_name {self.domain} www.{self.domain};
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}}

server {{
    listen 443 ssl http2;
    server_name {self.domain} www.{self.domain};
    
    # SSL configuration (you'll need to add certificates)
    # ssl_certificate /etc/ssl/certs/your-cert.pem;
    # ssl_certificate_key /etc/ssl/private/your-key.pem;
    
    # Security headers
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    
    # Proxy to backend
    location / {{
        proxy_pass http://localhost:8083;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }}
    
    # Health check
    location /health {{
        proxy_pass http://localhost:8083/health;
        access_log off;
    }}
}}
"""
        
        # Write nginx config
        with open("/tmp/seoforge.conf", "w") as f:
            f.write(nginx_config)
        
        self.copy_file("/tmp/seoforge.conf", "/tmp/seoforge.conf")
        self.run_ssh_command("sudo mv /tmp/seoforge.conf /etc/nginx/conf.d/")
        self.run_ssh_command("sudo nginx -t")  # Test configuration
        
        os.remove("/tmp/seoforge.conf")
        
        self.log("Nginx setup completed", "SUCCESS")
    
    def start_services(self):
        """Start all services"""
        self.log("Starting services...")
        
        # Create log directory
        self.run_ssh_command("sudo mkdir -p /var/log/seoforge")
        self.run_ssh_command("sudo chown ec2-user:ec2-user /var/log/seoforge")
        
        # Start application with Docker Compose
        self.run_ssh_command("cd /opt/seoforge && docker-compose -f docker-compose.prod.yml up -d")
        
        # Start Nginx
        self.run_ssh_command("sudo systemctl start nginx")
        
        self.log("Services started", "SUCCESS")
    
    def health_check(self):
        """Perform health check"""
        self.log("Performing health check...")
        
        # Wait for services to start
        time.sleep(30)
        
        # Check if backend is responding
        try:
            result = self.run_ssh_command("curl -f http://localhost:8083/health", check=False)
            if "healthy" in result.lower():
                self.log("Backend health check passed", "SUCCESS")
            else:
                self.log("Backend health check failed", "ERROR")
                return False
        except:
            self.log("Backend health check failed", "ERROR")
            return False
        
        # Check if Nginx is responding
        try:
            result = self.run_ssh_command("curl -f http://localhost/health", check=False)
            if result:
                self.log("Nginx health check passed", "SUCCESS")
            else:
                self.log("Nginx health check failed", "ERROR")
                return False
        except:
            self.log("Nginx health check failed", "ERROR")
            return False
        
        return True
    
    def deploy(self):
        """Main deployment method"""
        try:
            self.log("Starting SEOForge deployment...")
            
            # Check SSH connectivity
            self.run_ssh_command("echo 'SSH connection successful'")
            self.log("SSH connection verified", "SUCCESS")
            
            # Run deployment steps
            self.install_dependencies()
            self.setup_application()
            self.setup_nginx()
            self.start_services()
            
            if self.health_check():
                self.log("Deployment completed successfully!", "SUCCESS")
                self.print_summary()
            else:
                self.log("Deployment completed but health checks failed", "WARNING")
                
        except Exception as e:
            self.log(f"Deployment failed: {str(e)}", "ERROR")
            raise
    
    def print_summary(self):
        """Print deployment summary"""
        print("\n" + "="*50)
        print("🎉 DEPLOYMENT SUCCESSFUL!")
        print("="*50)
        print(f"Domain: {self.domain}")
        print(f"Server IP: {self.instance_ip}")
        print(f"Application URL: http://{self.instance_ip}:8083")
        print(f"Health Check: http://{self.instance_ip}/health")
        print("\n📋 Next Steps:")
        print("1. Configure SSL certificate for HTTPS")
        print("2. Update DNS records to point to this server")
        print("3. Update API keys in /opt/seoforge/.env")
        print("4. Set up monitoring and backups")
        print("5. Configure firewall rules")
        print("\n🔧 Useful Commands:")
        print(f"  SSH: ssh -i {self.key_file} {self.ssh_user}@{self.instance_ip}")
        print("  Logs: docker-compose -f /opt/seoforge/docker-compose.prod.yml logs -f")
        print("  Restart: docker-compose -f /opt/seoforge/docker-compose.prod.yml restart")

def main():
    """Main function"""
    parser = argparse.ArgumentParser(description='Quick SEOForge Deployment')
    parser.add_argument('--ip', required=True, help='EC2 instance IP address')
    parser.add_argument('--key', required=True, help='SSH private key file path')
    parser.add_argument('--domain', required=True, help='Domain name')
    parser.add_argument('--user', default='ec2-user', help='SSH user (default: ec2-user)')
    
    args = parser.parse_args()
    
    # Validate inputs
    if not os.path.exists(args.key):
        print(f"Error: SSH key file not found: {args.key}")
        sys.exit(1)
    
    # Set SSH user
    deployer = QuickDeployer(args.ip, args.key, args.domain)
    deployer.ssh_user = args.user
    
    # Run deployment
    deployer.deploy()

if __name__ == '__main__':
    main()
