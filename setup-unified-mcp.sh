#!/bin/bash

# SEO Forge Unified MCP Server Setup Script
# Automates the complete setup and deployment process

set -e

echo "🚀 SEO Forge Unified MCP Server Setup"
echo "======================================"
echo ""

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

# Check prerequisites
print_status "Checking prerequisites..."

# Check Node.js
if ! command -v node &> /dev/null; then
    print_error "Node.js is not installed. Please install Node.js 18+ and try again."
    exit 1
fi

NODE_VERSION=$(node --version | cut -d'v' -f2 | cut -d'.' -f1)
if [ "$NODE_VERSION" -lt 18 ]; then
    print_error "Node.js version 18+ is required. Current version: $(node --version)"
    exit 1
fi

print_success "Node.js $(node --version) is installed"

# Check npm
if ! command -v npm &> /dev/null; then
    print_error "npm is not installed. Please install npm and try again."
    exit 1
fi

print_success "npm $(npm --version) is installed"

# Install dependencies
print_status "Installing dependencies..."
cd mcp-server-unified
npm install

if [ $? -eq 0 ]; then
    print_success "Dependencies installed successfully"
else
    print_error "Failed to install dependencies"
    exit 1
fi

# Setup environment file
print_status "Setting up environment configuration..."

if [ ! -f .env ]; then
    cp .env.example .env
    print_success "Environment file created from template"
    print_warning "Please edit .env file with your API keys before deployment"
else
    print_warning ".env file already exists, skipping creation"
fi

# Build the project
print_status "Building the project..."
npm run build

if [ $? -eq 0 ]; then
    print_success "Project built successfully"
else
    print_error "Build failed"
    exit 1
fi

# Test locally (optional)
echo ""
read -p "Do you want to test the server locally first? (y/n): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_status "Starting local development server..."
    print_warning "Server will start on http://localhost:3000"
    print_warning "Press Ctrl+C to stop the server and continue with deployment"
    npm run dev &
    SERVER_PID=$!
    
    # Wait for user to stop the server
    read -p "Press Enter when you're ready to continue with deployment..."
    kill $SERVER_PID 2>/dev/null || true
    print_success "Local server stopped"
fi

# Check Vercel CLI
print_status "Checking Vercel CLI..."

if ! command -v vercel &> /dev/null; then
    print_warning "Vercel CLI not found. Installing..."
    npm install -g vercel
    
    if [ $? -eq 0 ]; then
        print_success "Vercel CLI installed successfully"
    else
        print_error "Failed to install Vercel CLI"
        exit 1
    fi
else
    print_success "Vercel CLI is already installed"
fi

# Login to Vercel
print_status "Checking Vercel authentication..."

if ! vercel whoami &> /dev/null; then
    print_warning "Not logged in to Vercel. Please login..."
    vercel login
    
    if [ $? -eq 0 ]; then
        print_success "Successfully logged in to Vercel"
    else
        print_error "Failed to login to Vercel"
        exit 1
    fi
else
    VERCEL_USER=$(vercel whoami)
    print_success "Already logged in to Vercel as: $VERCEL_USER"
fi

# Deploy to Vercel
echo ""
read -p "Do you want to deploy to Vercel now? (y/n): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_status "Deploying to Vercel..."
    
    # Deploy
    DEPLOYMENT_OUTPUT=$(vercel --prod 2>&1)
    
    if [ $? -eq 0 ]; then
        print_success "Deployment completed successfully!"
        
        # Extract deployment URL
        DEPLOYMENT_URL=$(echo "$DEPLOYMENT_OUTPUT" | grep -o 'https://[^[:space:]]*' | head -1)
        
        if [ -n "$DEPLOYMENT_URL" ]; then
            echo ""
            echo "🎉 Your SEO Forge MCP Server is live!"
            echo "=================================="
            echo "🌐 Server URL: $DEPLOYMENT_URL"
            echo "🏥 Health Check: $DEPLOYMENT_URL/health"
            echo "💻 Client Interface: $DEPLOYMENT_URL/client"
            echo "📚 Documentation: $DEPLOYMENT_URL/client/docs"
            echo ""
        fi
    else
        print_error "Deployment failed"
        echo "$DEPLOYMENT_OUTPUT"
        exit 1
    fi
else
    print_warning "Skipping deployment. You can deploy later with: vercel --prod"
fi

# Environment variables setup
echo ""
print_status "Environment Variables Setup"
echo "============================"
echo ""
print_warning "Don't forget to set up your environment variables in Vercel:"
echo ""
echo "Required variables:"
echo "- GOOGLE_API_KEY (for Gemini AI)"
echo "- OPENAI_API_KEY (for GPT-4, optional)"
echo "- ANTHROPIC_API_KEY (for Claude, optional)"
echo "- REPLICATE_API_TOKEN (for Flux/image generation, optional)"
echo "- JWT_SECRET (for authentication)"
echo "- DEFAULT_ADMIN_EMAIL (admin user email)"
echo "- DEFAULT_ADMIN_PASSWORD (admin user password)"
echo ""
echo "Optional variables:"
echo "- REDIS_URL (for production caching)"
echo "- DATABASE_URL (for production data storage)"
echo ""
echo "You can set these in the Vercel dashboard or using the CLI:"
echo "vercel env add GOOGLE_API_KEY"
echo ""

# WordPress integration guide
echo ""
print_status "WordPress Integration"
echo "===================="
echo ""
echo "To integrate with WordPress:"
echo "1. Install the SEO Forge WordPress plugin"
echo "2. Configure the MCP server URL in plugin settings"
echo "3. Add your API key for authentication"
echo "4. Test the connection"
echo ""

# Final instructions
echo ""
print_success "Setup completed successfully! 🎉"
echo ""
echo "Next steps:"
echo "1. Set up environment variables in Vercel dashboard"
echo "2. Test your deployment"
echo "3. Configure WordPress plugin (if applicable)"
echo "4. Start using the MCP server!"
echo ""
echo "Need help? Check the documentation or contact support."
echo ""

# Cleanup
cd ..

print_success "All done! Your SEO Forge MCP Server is ready to use."
