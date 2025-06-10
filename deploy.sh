#!/bin/bash

# Universal MCP Server Platform Deployment Script
# This script automates the deployment of the entire platform

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to check prerequisites
check_prerequisites() {
    print_status "Checking prerequisites..."
    
    if ! command_exists docker; then
        print_error "Docker is not installed. Please install Docker first."
        exit 1
    fi
    
    if ! command_exists docker-compose; then
        print_error "Docker Compose is not installed. Please install Docker Compose first."
        exit 1
    fi
    
    if ! command_exists git; then
        print_error "Git is not installed. Please install Git first."
        exit 1
    fi
    
    print_success "All prerequisites are installed."
}

# Function to setup environment
setup_environment() {
    print_status "Setting up environment..."
    
    if [ ! -f .env ]; then
        if [ -f .env.example ]; then
            cp .env.example .env
            print_warning "Created .env file from .env.example. Please edit it with your configuration."
            print_warning "You need to add your API keys and other configuration values."
            
            # Prompt user to edit .env
            read -p "Do you want to edit the .env file now? (y/n): " edit_env
            if [ "$edit_env" = "y" ] || [ "$edit_env" = "Y" ]; then
                ${EDITOR:-nano} .env
            fi
        else
            print_error ".env.example file not found. Cannot create .env file."
            exit 1
        fi
    else
        print_success ".env file already exists."
    fi
}

# Function to validate environment variables
validate_environment() {
    print_status "Validating environment variables..."
    
    source .env
    
    required_vars=(
        "OPENAI_API_KEY"
        "MCP_API_KEY"
        "JWT_SECRET"
        "DATABASE_URL"
        "REDIS_URL"
    )
    
    missing_vars=()
    
    for var in "${required_vars[@]}"; do
        if [ -z "${!var}" ]; then
            missing_vars+=("$var")
        fi
    done
    
    if [ ${#missing_vars[@]} -ne 0 ]; then
        print_error "Missing required environment variables:"
        for var in "${missing_vars[@]}"; do
            echo "  - $var"
        done
        print_error "Please update your .env file with the required values."
        exit 1
    fi
    
    print_success "Environment variables validated."
}

# Function to build Docker images
build_images() {
    print_status "Building Docker images..."
    
    # Build MCP Server
    print_status "Building MCP Server image..."
    docker build -t universal-mcp-server:latest ./mcp-server
    
    # Build Backend
    print_status "Building Backend API image..."
    docker build -t universal-mcp-backend:latest ./backend
    
    # Build Frontend
    print_status "Building Frontend image..."
    docker build -t universal-mcp-frontend:latest ./frontend
    
    print_success "All Docker images built successfully."
}

# Function to start services
start_services() {
    print_status "Starting services with Docker Compose..."
    
    # Create necessary directories
    mkdir -p logs
    mkdir -p database
    mkdir -p monitoring/grafana/dashboards
    mkdir -p monitoring/grafana/datasources
    mkdir -p nginx/ssl
    
    # Start services
    docker-compose up -d
    
    print_success "Services started successfully."
}

# Function to wait for services to be ready
wait_for_services() {
    print_status "Waiting for services to be ready..."
    
    # Wait for database
    print_status "Waiting for database..."
    until docker-compose exec -T postgres pg_isready -U postgres; do
        sleep 2
    done
    
    # Wait for Redis
    print_status "Waiting for Redis..."
    until docker-compose exec -T redis redis-cli ping; do
        sleep 2
    done
    
    # Wait for MCP Server
    print_status "Waiting for MCP Server..."
    until curl -f http://localhost:3000/health >/dev/null 2>&1; do
        sleep 5
    done
    
    # Wait for Backend API
    print_status "Waiting for Backend API..."
    until curl -f http://localhost:8000/health >/dev/null 2>&1; do
        sleep 5
    done
    
    # Wait for Frontend
    print_status "Waiting for Frontend..."
    until curl -f http://localhost:3001/health >/dev/null 2>&1; do
        sleep 5
    done
    
    print_success "All services are ready."
}

# Function to run database migrations
run_migrations() {
    print_status "Running database migrations..."
    
    # Create database schema if needed
    docker-compose exec -T postgres psql -U postgres -d universal_mcp -c "
        CREATE TABLE IF NOT EXISTS umcp_requests (
            id SERIAL PRIMARY KEY,
            user_id INTEGER,
            tool_name VARCHAR(100) NOT NULL,
            parameters TEXT NOT NULL,
            response TEXT NOT NULL,
            industry VARCHAR(50) DEFAULT 'general',
            execution_time FLOAT DEFAULT 0,
            success BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS umcp_industry_templates (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            industry VARCHAR(50) NOT NULL,
            template_data TEXT NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE INDEX IF NOT EXISTS idx_requests_tool_name ON umcp_requests(tool_name);
        CREATE INDEX IF NOT EXISTS idx_requests_industry ON umcp_requests(industry);
        CREATE INDEX IF NOT EXISTS idx_templates_industry ON umcp_industry_templates(industry);
    "
    
    print_success "Database migrations completed."
}

# Function to display deployment summary
show_deployment_summary() {
    print_success "🚀 Universal MCP Server Platform deployed successfully!"
    echo
    echo "📊 Access your platform:"
    echo "  • Frontend Dashboard: http://localhost:3001"
    echo "  • Backend API: http://localhost:8000"
    echo "  • API Documentation: http://localhost:8000/docs"
    echo "  • MCP Server: http://localhost:3000"
    echo "  • Grafana Monitoring: http://localhost:3002 (admin/admin)"
    echo "  • Prometheus Metrics: http://localhost:9090"
    echo
    echo "🔧 Useful commands:"
    echo "  • View logs: docker-compose logs -f [service-name]"
    echo "  • Stop services: docker-compose down"
    echo "  • Restart services: docker-compose restart"
    echo "  • Update services: docker-compose pull && docker-compose up -d"
    echo
    echo "📝 Next steps:"
    echo "  1. Install the WordPress plugin from ./wordpress-plugin/"
    echo "  2. Configure the plugin with your MCP server URL (http://localhost:3000)"
    echo "  3. Add your MCP API key in the WordPress plugin settings"
    echo "  4. Start generating content and optimizing SEO!"
    echo
    print_warning "Remember to:"
    echo "  • Backup your .env file"
    echo "  • Set up SSL certificates for production"
    echo "  • Configure proper firewall rules"
    echo "  • Monitor the logs for any issues"
}

# Function to handle cleanup on exit
cleanup() {
    if [ $? -ne 0 ]; then
        print_error "Deployment failed. Cleaning up..."
        docker-compose down
    fi
}

# Main deployment function
main() {
    echo "🌟 Universal MCP Server Platform Deployment"
    echo "=========================================="
    echo
    
    # Set up cleanup trap
    trap cleanup EXIT
    
    # Run deployment steps
    check_prerequisites
    setup_environment
    validate_environment
    build_images
    start_services
    wait_for_services
    run_migrations
    
    # Show summary
    show_deployment_summary
    
    # Remove cleanup trap on successful completion
    trap - EXIT
}

# Parse command line arguments
case "${1:-deploy}" in
    "deploy")
        main
        ;;
    "stop")
        print_status "Stopping all services..."
        docker-compose down
        print_success "All services stopped."
        ;;
    "restart")
        print_status "Restarting all services..."
        docker-compose restart
        print_success "All services restarted."
        ;;
    "logs")
        docker-compose logs -f "${2:-}"
        ;;
    "status")
        docker-compose ps
        ;;
    "update")
        print_status "Updating services..."
        docker-compose pull
        docker-compose up -d
        print_success "Services updated."
        ;;
    "clean")
        print_warning "This will remove all containers, images, and volumes. Are you sure? (y/N)"
        read -r response
        if [ "$response" = "y" ] || [ "$response" = "Y" ]; then
            docker-compose down -v --rmi all
            print_success "Cleanup completed."
        else
            print_status "Cleanup cancelled."
        fi
        ;;
    "help")
        echo "Usage: $0 [command]"
        echo
        echo "Commands:"
        echo "  deploy    Deploy the entire platform (default)"
        echo "  stop      Stop all services"
        echo "  restart   Restart all services"
        echo "  logs      View logs (optionally specify service name)"
        echo "  status    Show service status"
        echo "  update    Update and restart services"
        echo "  clean     Remove all containers, images, and volumes"
        echo "  help      Show this help message"
        ;;
    *)
        print_error "Unknown command: $1"
        echo "Use '$0 help' for usage information."
        exit 1
        ;;
esac
