#!/bin/bash

# SEOForge Production Deployment Script
# This script automates the deployment process for EC2

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DOMAIN=${DOMAIN:-"yourdomain.com"}
EMAIL=${EMAIL:-"admin@yourdomain.com"}
BACKUP_DIR="/opt/seoforge/backups"
LOG_DIR="/var/log/seoforge"
APP_DIR="/opt/seoforge"

# Functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

check_requirements() {
    log_info "Checking system requirements..."
    
    # Check if running as root
    if [[ $EUID -eq 0 ]]; then
        log_error "This script should not be run as root"
        exit 1
    fi
    
    # Check if Docker is installed
    if ! command -v docker &> /dev/null; then
        log_error "Docker is not installed"
        exit 1
    fi
    
    # Check if Docker Compose is installed
    if ! command -v docker-compose &> /dev/null; then
        log_error "Docker Compose is not installed"
        exit 1
    fi
    
    # Check if user is in docker group
    if ! groups $USER | grep &>/dev/null '\bdocker\b'; then
        log_error "User $USER is not in docker group"
        exit 1
    fi
    
    log_success "System requirements check passed"
}

setup_directories() {
    log_info "Setting up directories..."
    
    sudo mkdir -p $APP_DIR
    sudo mkdir -p $BACKUP_DIR
    sudo mkdir -p $LOG_DIR
    sudo mkdir -p /etc/nginx/ssl
    
    # Set permissions
    sudo chown -R $USER:$USER $APP_DIR
    sudo chown -R $USER:$USER $LOG_DIR
    
    log_success "Directories created successfully"
}

install_dependencies() {
    log_info "Installing system dependencies..."
    
    # Update package list
    sudo apt update
    
    # Install required packages
    sudo apt install -y \
        nginx \
        certbot \
        python3-certbot-nginx \
        htop \
        curl \
        git \
        unzip \
        logrotate \
        fail2ban
    
    log_success "Dependencies installed successfully"
}

setup_ssl() {
    log_info "Setting up SSL certificate..."
    
    # Stop nginx if running
    sudo systemctl stop nginx 2>/dev/null || true
    
    # Get SSL certificate
    sudo certbot certonly \
        --standalone \
        --non-interactive \
        --agree-tos \
        --email $EMAIL \
        -d $DOMAIN \
        -d www.$DOMAIN
    
    # Setup auto-renewal
    echo "0 12 * * * /usr/bin/certbot renew --quiet" | sudo crontab -
    
    log_success "SSL certificate configured"
}

deploy_application() {
    log_info "Deploying application..."
    
    # Navigate to app directory
    cd $APP_DIR
    
    # Pull latest code (if git repo exists)
    if [ -d ".git" ]; then
        git pull origin main
    else
        log_warning "Not a git repository, skipping git pull"
    fi
    
    # Create production environment file if it doesn't exist
    if [ ! -f ".env" ]; then
        log_warning "Creating default .env file - please update with your values"
        cp production.env .env
    fi
    
    # Build and start services
    docker-compose -f docker-compose.prod.yml build --no-cache
    docker-compose -f docker-compose.prod.yml up -d
    
    log_success "Application deployed successfully"
}

setup_monitoring() {
    log_info "Setting up monitoring..."
    
    # Install CloudWatch agent
    if ! command -v amazon-cloudwatch-agent-ctl &> /dev/null; then
        wget https://s3.amazonaws.com/amazoncloudwatch-agent/ubuntu/amd64/latest/amazon-cloudwatch-agent.deb
        sudo dpkg -i -E ./amazon-cloudwatch-agent.deb
        rm amazon-cloudwatch-agent.deb
    fi
    
    # Setup log rotation
    sudo tee /etc/logrotate.d/seoforge << EOF
$LOG_DIR/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 $USER $USER
    postrotate
        docker-compose -f $APP_DIR/docker-compose.prod.yml restart backend
    endscript
}
EOF
    
    log_success "Monitoring configured"
}

setup_firewall() {
    log_info "Configuring firewall..."
    
    # Install and configure UFW
    sudo ufw --force reset
    sudo ufw default deny incoming
    sudo ufw default allow outgoing
    
    # Allow SSH
    sudo ufw allow ssh
    
    # Allow HTTP and HTTPS
    sudo ufw allow 80/tcp
    sudo ufw allow 443/tcp
    
    # Allow monitoring (from internal networks only)
    sudo ufw allow from 10.0.0.0/8 to any port 9090
    sudo ufw allow from 172.16.0.0/12 to any port 9090
    sudo ufw allow from 192.168.0.0/16 to any port 9090
    
    # Enable firewall
    sudo ufw --force enable
    
    log_success "Firewall configured"
}

setup_fail2ban() {
    log_info "Configuring Fail2Ban..."
    
    # Create custom jail for nginx
    sudo tee /etc/fail2ban/jail.d/nginx.conf << EOF
[nginx-http-auth]
enabled = true
filter = nginx-http-auth
port = http,https
logpath = /var/log/nginx/error.log

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
port = http,https
logpath = /var/log/nginx/error.log
maxretry = 10
findtime = 600
bantime = 7200
EOF
    
    # Restart fail2ban
    sudo systemctl restart fail2ban
    sudo systemctl enable fail2ban
    
    log_success "Fail2Ban configured"
}

health_check() {
    log_info "Performing health check..."
    
    # Wait for services to start
    sleep 30
    
    # Check if backend is responding
    if curl -f http://localhost:8083/health > /dev/null 2>&1; then
        log_success "Backend health check passed"
    else
        log_error "Backend health check failed"
        return 1
    fi
    
    # Check if nginx is responding
    if curl -f http://localhost/health > /dev/null 2>&1; then
        log_success "Nginx health check passed"
    else
        log_error "Nginx health check failed"
        return 1
    fi
    
    log_success "All health checks passed"
}

create_backup() {
    log_info "Creating backup..."
    
    BACKUP_FILE="$BACKUP_DIR/seoforge-backup-$(date +%Y%m%d-%H%M%S).tar.gz"
    
    # Create backup of application and configuration
    tar -czf $BACKUP_FILE \
        -C $APP_DIR . \
        --exclude='.git' \
        --exclude='logs' \
        --exclude='node_modules'
    
    # Keep only last 7 backups
    find $BACKUP_DIR -name "seoforge-backup-*.tar.gz" -type f -mtime +7 -delete
    
    log_success "Backup created: $BACKUP_FILE"
}

main() {
    log_info "Starting SEOForge production deployment..."
    
    # Check if domain is provided
    if [ "$DOMAIN" = "yourdomain.com" ]; then
        log_error "Please set DOMAIN environment variable"
        exit 1
    fi
    
    # Run deployment steps
    check_requirements
    setup_directories
    install_dependencies
    setup_ssl
    deploy_application
    setup_monitoring
    setup_firewall
    setup_fail2ban
    health_check
    create_backup
    
    log_success "Deployment completed successfully!"
    log_info "Your application is now running at https://$DOMAIN"
    log_info "Monitoring dashboard: https://$DOMAIN:3000"
    log_info "Prometheus metrics: https://$DOMAIN:9090"
    
    # Display important information
    echo ""
    echo "=== IMPORTANT INFORMATION ==="
    echo "1. Update your .env file with production values"
    echo "2. Configure your DNS to point to this server"
    echo "3. Update security groups to allow traffic"
    echo "4. Set up database backups"
    echo "5. Configure monitoring alerts"
    echo ""
    echo "=== USEFUL COMMANDS ==="
    echo "View logs: docker-compose -f $APP_DIR/docker-compose.prod.yml logs -f"
    echo "Restart services: docker-compose -f $APP_DIR/docker-compose.prod.yml restart"
    echo "Update application: cd $APP_DIR && git pull && docker-compose -f docker-compose.prod.yml up -d --build"
    echo ""
}

# Run main function
main "$@"
