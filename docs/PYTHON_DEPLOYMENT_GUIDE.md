# SEOForge Python Deployment Guide

## 🐍 Python-Based Deployment Options

I've created three Python deployment tools for different scenarios:

### 1. **Full Infrastructure Deployment** (`deploy.py`)
- Creates complete AWS infrastructure from scratch
- Includes RDS, ElastiCache, EC2, Load Balancer
- Best for new deployments

### 2. **Enhanced Deployment** (`deploy_enhanced.py`)
- Advanced features with monitoring and rollback
- Configuration-driven deployment
- Progress tracking and health checks
- Best for production environments

### 3. **Quick Deployment** (`quick_deploy.py`)
- Deploy to existing EC2 instance
- Simple and fast
- Best for development/testing

## 🚀 Quick Start (Recommended)

### Prerequisites

```bash
# Install Python dependencies
pip install -r deployment-requirements.txt

# Configure AWS credentials
aws configure

# Ensure you have:
# - EC2 instance running
# - SSH key pair
# - Domain name (optional)
```

### Option 1: Quick Deployment to Existing EC2

```bash
# Make script executable
chmod +x quick_deploy.py

# Deploy to existing instance
python quick_deploy.py \
    --ip YOUR_EC2_IP \
    --key path/to/your-key.pem \
    --domain yourdomain.com

# Example:
python quick_deploy.py \
    --ip 54.123.45.67 \
    --key ~/.ssh/my-key.pem \
    --domain seoforge.example.com
```

### Option 2: Full Infrastructure Deployment

```bash
# Configure and run full deployment
python deploy.py \
    --domain yourdomain.com \
    --email admin@yourdomain.com \
    --key-name your-key-pair \
    --vpc-id vpc-xxxxxxxxx \
    --subnet-id subnet-xxxxxxxxx \
    --region us-east-1
```

### Option 3: Enhanced Deployment with Config File

```bash
# First run creates config template
python deploy_enhanced.py deploy

# Edit the generated config file
nano deployment_config.yaml

# Run deployment
python deploy_enhanced.py deploy --config deployment_config.yaml
```

## 📋 Step-by-Step Deployment

### Step 1: Prepare Environment

```bash
# Clone the repository
git clone https://github.com/your-org/seoforge-mcp-server.git
cd seoforge-mcp-server

# Install deployment dependencies
pip install -r deployment-requirements.txt

# Set environment variables
export AWS_PROFILE=your-profile
export OPENAI_API_KEY=your-openai-key
export ANTHROPIC_API_KEY=your-anthropic-key
export GOOGLE_AI_API_KEY=your-google-key
export DATABASE_URL=postgresql://user:pass@host:5432/db
export REDIS_URL=redis://host:6379
```

### Step 2: Choose Deployment Method

#### For Existing EC2 Instance:
```bash
python quick_deploy.py \
    --ip 54.123.45.67 \
    --key ~/.ssh/my-key.pem \
    --domain seoforge.example.com \
    --user ec2-user
```

#### For New Infrastructure:
```bash
python deploy.py \
    --domain seoforge.example.com \
    --email admin@example.com \
    --key-name my-aws-key \
    --vpc-id vpc-12345678 \
    --subnet-id subnet-12345678
```

### Step 3: Post-Deployment Configuration

```bash
# SSH into your instance
ssh -i your-key.pem ec2-user@your-instance-ip

# Update environment variables
sudo nano /opt/seoforge/.env

# Restart services
cd /opt/seoforge
docker-compose -f docker-compose.prod.yml restart

# Check logs
docker-compose -f docker-compose.prod.yml logs -f
```

## 🔧 Configuration Options

### Environment Variables

```bash
# Required
export OPENAI_API_KEY="sk-..."
export ANTHROPIC_API_KEY="sk-ant-..."
export GOOGLE_AI_API_KEY="AIza..."

# Database (if using external)
export DATABASE_URL="postgresql://user:pass@host:5432/seoforge"
export REDIS_URL="redis://host:6379"

# Optional
export DOMAIN="yourdomain.com"
export EMAIL="admin@yourdomain.com"
export AWS_REGION="us-east-1"
```

### Deployment Config (deployment_config.yaml)

```yaml
application:
  name: seoforge
  domain: yourdomain.com
  email: admin@yourdomain.com
  environment: production

aws:
  region: us-east-1
  key_name: your-key-pair
  vpc_id: vpc-xxxxxxxxx
  subnet_ids:
    - subnet-xxxxxxxxx
    - subnet-yyyyyyyyy

infrastructure:
  ec2:
    instance_type: t3.large
    ami_id: ami-0c02fb55956c7d316
  rds:
    instance_class: db.t3.small
    allocated_storage: 20
  elasticache:
    node_type: cache.t3.micro

security:
  allowed_ssh_cidrs:
    - 0.0.0.0/0  # Restrict this!
  enable_waf: false

monitoring:
  enable_cloudwatch: true
  enable_prometheus: true
  alert_email: alerts@yourdomain.com
```

## 🔍 Troubleshooting

### Common Issues

#### SSH Connection Failed
```bash
# Check key permissions
chmod 600 your-key.pem

# Test SSH connection
ssh -i your-key.pem ec2-user@your-ip

# Check security groups allow SSH (port 22)
```

#### Docker Permission Denied
```bash
# SSH into instance and fix docker permissions
ssh -i your-key.pem ec2-user@your-ip
sudo usermod -aG docker ec2-user
# Logout and login again
```

#### Application Not Starting
```bash
# Check logs
docker-compose -f /opt/seoforge/docker-compose.prod.yml logs

# Check environment variables
cat /opt/seoforge/.env

# Restart services
docker-compose -f /opt/seoforge/docker-compose.prod.yml restart
```

#### Health Check Failed
```bash
# Check if backend is running
curl http://localhost:8083/health

# Check if all containers are up
docker ps

# Check application logs
docker logs seoforge_backend_1
```

### Debug Mode

```bash
# Run deployment with debug logging
python quick_deploy.py \
    --ip YOUR_IP \
    --key YOUR_KEY \
    --domain YOUR_DOMAIN \
    --debug

# Check deployment logs
tail -f deployment.log
```

## 📊 Monitoring Deployment

### Health Checks

```bash
# Backend health
curl http://your-ip:8083/health

# Full application health
curl http://your-ip/health

# API endpoint test
curl -X POST http://your-ip/api/blog-generator/generate \
  -H "Content-Type: application/json" \
  -d '{"topic": "test", "keywords": ["test"]}'
```

### Service Status

```bash
# Check Docker containers
docker ps

# Check Nginx status
sudo systemctl status nginx

# Check application logs
docker-compose -f /opt/seoforge/docker-compose.prod.yml logs -f backend
```

## 🔄 Updates and Maintenance

### Update Application

```bash
# SSH into instance
ssh -i your-key.pem ec2-user@your-ip

# Pull latest code
cd /opt/seoforge
git pull origin main

# Rebuild and restart
docker-compose -f docker-compose.prod.yml up -d --build
```

### Backup

```bash
# Create backup script
python -c "
import boto3
import datetime

# Backup database
# Backup application files
# Upload to S3
"
```

### Rollback

```bash
# Using enhanced deployer
python deploy_enhanced.py rollback

# Manual rollback
cd /opt/seoforge
git checkout previous-commit
docker-compose -f docker-compose.prod.yml up -d --build
```

## 🎯 Production Checklist

- [ ] SSL certificate configured
- [ ] DNS records updated
- [ ] API keys configured
- [ ] Database secured
- [ ] Monitoring enabled
- [ ] Backups configured
- [ ] Security groups restricted
- [ ] Health checks passing
- [ ] Load testing completed
- [ ] Documentation updated

## 💡 Tips and Best Practices

1. **Use Environment Variables**: Never hardcode secrets
2. **Test Locally First**: Use docker-compose.yml for local testing
3. **Monitor Resources**: Set up CloudWatch alarms
4. **Regular Backups**: Automate database and file backups
5. **Security Updates**: Keep system packages updated
6. **Log Rotation**: Configure log rotation to prevent disk full
7. **Health Checks**: Implement comprehensive health monitoring
8. **Graceful Shutdown**: Handle SIGTERM signals properly

## 🆘 Support

If you encounter issues:

1. Check the deployment logs: `tail -f deployment.log`
2. Verify AWS credentials: `aws sts get-caller-identity`
3. Test SSH connectivity: `ssh -i key.pem user@ip`
4. Check application logs: `docker-compose logs`
5. Review security groups and network settings

For additional help, check the troubleshooting section or create an issue in the repository.
