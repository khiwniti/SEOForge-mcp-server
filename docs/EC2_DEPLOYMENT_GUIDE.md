# SEOForge Backend EC2 Deployment Guide

## 🚀 Production-Ready EC2 Deployment

### Prerequisites
- AWS Account with appropriate permissions
- Domain name (optional but recommended)
- SSL certificate (Let's Encrypt or AWS Certificate Manager)

## 📋 Infrastructure Setup

### 1. EC2 Instance Configuration

**Recommended Instance Type**: `t3.large` (2 vCPU, 8GB RAM)
- **OS**: Ubuntu 22.04 LTS
- **Storage**: 20GB GP3 SSD (minimum)
- **Security Group**: Custom (see below)

### 2. Security Groups

#### Application Load Balancer Security Group
```
Inbound Rules:
- HTTP (80) from 0.0.0.0/0
- HTTPS (443) from 0.0.0.0/0

Outbound Rules:
- All traffic to 0.0.0.0/0
```

#### EC2 Security Group
```
Inbound Rules:
- HTTP (8083) from ALB Security Group
- SSH (22) from your IP address
- Custom (9090) from ALB Security Group (metrics)

Outbound Rules:
- All traffic to 0.0.0.0/0
```

#### RDS Security Group
```
Inbound Rules:
- PostgreSQL (5432) from EC2 Security Group

Outbound Rules:
- None required
```

#### ElastiCache Security Group
```
Inbound Rules:
- Redis (6379) from EC2 Security Group

Outbound Rules:
- None required
```

### 3. RDS PostgreSQL Setup

**Recommended Configuration**:
- **Instance Class**: db.t3.small (2 vCPU, 2GB RAM)
- **Engine**: PostgreSQL 15.x
- **Storage**: 20GB GP3 SSD
- **Multi-AZ**: Yes (for production)
- **Backup Retention**: 7 days
- **Encryption**: Enabled

### 4. ElastiCache Redis Setup

**Recommended Configuration**:
- **Node Type**: cache.t3.micro (1 vCPU, 0.5GB RAM)
- **Engine**: Redis 7.x
- **Encryption**: In-transit and at-rest
- **Backup**: Enabled

## 🔧 Server Setup

### 1. Initial Server Configuration

```bash
#!/bin/bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y \
    docker.io \
    docker-compose \
    nginx \
    certbot \
    python3-certbot-nginx \
    htop \
    curl \
    git \
    unzip

# Start and enable Docker
sudo systemctl start docker
sudo systemctl enable docker

# Add user to docker group
sudo usermod -aG docker $USER

# Install AWS CLI
curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
unzip awscliv2.zip
sudo ./aws/install

# Install CloudWatch agent
wget https://s3.amazonaws.com/amazoncloudwatch-agent/ubuntu/amd64/latest/amazon-cloudwatch-agent.deb
sudo dpkg -i -E ./amazon-cloudwatch-agent.deb
```

### 2. Application Deployment

```bash
#!/bin/bash
# Clone repository
git clone https://github.com/your-org/seoforge-mcp-server.git
cd seoforge-mcp-server

# Create production environment file
cp production.env .env

# Edit environment variables
nano .env
```

### 3. Environment Variables (.env)

```bash
# Application
ENVIRONMENT=production
DEBUG=false
HOST=0.0.0.0
PORT=8083
WORKERS=4

# Security
SECRET_KEY=your-super-secure-secret-key-here
JWT_SECRET=your-jwt-secret-key-here
CORS_ORIGINS=https://yourdomain.com,https://www.yourdomain.com

# Database (Replace with your RDS endpoint)
DATABASE_URL=postgresql://username:password@your-rds-endpoint:5432/seoforge
DB_POOL_SIZE=20
DB_MAX_OVERFLOW=30

# Redis (Replace with your ElastiCache endpoint)
REDIS_URL=redis://your-elasticache-endpoint:6379
REDIS_PASSWORD=your-redis-password

# AI Providers
OPENAI_API_KEY=your-openai-api-key
ANTHROPIC_API_KEY=your-anthropic-api-key
GOOGLE_AI_API_KEY=your-google-ai-api-key

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
```

## 🐳 Docker Deployment

### 1. Production Docker Compose

```yaml
version: '3.8'

services:
  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile
    ports:
      - "8083:8083"
    environment:
      - ENVIRONMENT=production
    env_file:
      - .env
    volumes:
      - ./logs:/app/logs
      - /var/log/seoforge:/var/log/seoforge
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:8083/health"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./nginx/ssl:/etc/nginx/ssl
      - /etc/letsencrypt:/etc/letsencrypt
    depends_on:
      - backend
    restart: unless-stopped
```

### 2. Nginx Configuration

```nginx
upstream backend {
    server backend:8083;
}

server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    # Security headers
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";

    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;

    location / {
        limit_req zone=api burst=20 nodelay;
        
        proxy_pass http://backend;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }

    location /health {
        proxy_pass http://backend/health;
        access_log off;
    }
}
```

## 📊 Monitoring Setup

### 1. CloudWatch Configuration

```json
{
    "agent": {
        "metrics_collection_interval": 60,
        "run_as_user": "cwagent"
    },
    "logs": {
        "logs_collected": {
            "files": {
                "collect_list": [
                    {
                        "file_path": "/var/log/seoforge/app.log",
                        "log_group_name": "seoforge-application",
                        "log_stream_name": "{instance_id}"
                    }
                ]
            }
        }
    },
    "metrics": {
        "namespace": "SEOForge/Application",
        "metrics_collected": {
            "cpu": {
                "measurement": ["cpu_usage_idle", "cpu_usage_iowait", "cpu_usage_user", "cpu_usage_system"],
                "metrics_collection_interval": 60
            },
            "disk": {
                "measurement": ["used_percent"],
                "metrics_collection_interval": 60,
                "resources": ["*"]
            },
            "mem": {
                "measurement": ["mem_used_percent"],
                "metrics_collection_interval": 60
            }
        }
    }
}
```

## 🚀 Deployment Commands

```bash
# 1. Deploy application
docker-compose -f docker-compose.prod.yml up -d

# 2. Setup SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# 3. Setup log rotation
sudo tee /etc/logrotate.d/seoforge << EOF
/var/log/seoforge/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 root root
    postrotate
        docker-compose -f /path/to/docker-compose.prod.yml restart backend
    endscript
}
EOF

# 4. Setup CloudWatch agent
sudo /opt/aws/amazon-cloudwatch-agent/bin/amazon-cloudwatch-agent-ctl \
    -a fetch-config -m ec2 -c file:/opt/aws/amazon-cloudwatch-agent/etc/amazon-cloudwatch-agent.json -s

# 5. Setup auto-start
sudo systemctl enable docker
```

## 💰 Cost Estimation (Monthly)

| Service | Configuration | Cost (USD) |
|---------|--------------|------------|
| EC2 t3.large | 2 vCPU, 8GB RAM | ~$60 |
| RDS db.t3.small | 2 vCPU, 2GB RAM | ~$25 |
| ElastiCache t3.micro | 1 vCPU, 0.5GB RAM | ~$15 |
| Application Load Balancer | Standard | ~$20 |
| Data Transfer | 100GB/month | ~$9 |
| CloudWatch | Logs + Metrics | ~$5 |
| **Total** | | **~$134/month** |

## 🔒 Security Checklist

- ✅ Security groups properly configured
- ✅ SSL/TLS encryption enabled
- ✅ Database encryption at rest
- ✅ Redis encryption in transit
- ✅ Rate limiting configured
- ✅ Security headers implemented
- ✅ Regular security updates
- ✅ CloudWatch monitoring enabled
- ✅ Backup strategy implemented
- ✅ Access logging enabled

## 📈 Scaling Considerations

### Horizontal Scaling
- Use Auto Scaling Groups
- Multiple AZ deployment
- Load balancer health checks

### Vertical Scaling
- Monitor CPU/Memory usage
- Upgrade instance types as needed
- Database read replicas

### Performance Optimization
- Enable CloudFront CDN
- Implement application caching
- Database query optimization
- Connection pooling
