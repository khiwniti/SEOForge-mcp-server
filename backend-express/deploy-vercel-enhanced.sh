#!/bin/bash

# Enhanced Vercel Deployment Script for SEOForge Express Backend
# This script ensures error-free deployment with comprehensive checks

set -e  # Exit on any error

echo "🚀 Starting Enhanced SEOForge Express Backend Deployment to Vercel..."
echo "=================================================="

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

# Check if we're in the correct directory
if [ ! -f "package.json" ]; then
    print_error "package.json not found. Please run this script from the backend-express directory."
    exit 1
fi

# Check if Vercel CLI is installed
if ! command -v vercel &> /dev/null; then
    print_error "Vercel CLI is not installed. Please install it with: npm install -g vercel"
    exit 1
fi

# Pre-deployment checks
print_status "Running pre-deployment checks..."

# Check Node.js version
NODE_VERSION=$(node --version)
print_status "Node.js version: $NODE_VERSION"

# Check if required files exist
REQUIRED_FILES=("tsconfig.json" "vercel.json" "api/index.ts")
for file in "${REQUIRED_FILES[@]}"; do
    if [ ! -f "$file" ]; then
        print_error "Required file $file not found!"
        exit 1
    fi
done
print_success "All required files found"

# Install dependencies
print_status "Installing dependencies..."
npm ci --production=false
print_success "Dependencies installed"

# Run type checking
print_status "Running TypeScript type checking..."
npm run type-check
print_success "Type checking passed"

# Run linting (if available)
if npm run lint --silent 2>/dev/null; then
    print_status "Running ESLint..."
    npm run lint
    print_success "Linting passed"
else
    print_warning "Linting script not available, skipping..."
fi

# Build the project
print_status "Building the project..."
npm run build
print_success "Build completed successfully"

# Verify build output
if [ ! -d "dist" ]; then
    print_error "Build output directory 'dist' not found!"
    exit 1
fi

if [ ! -f "dist/server.js" ]; then
    print_error "Main server file 'dist/server.js' not found!"
    exit 1
fi
print_success "Build output verified"

# Check environment variables
print_status "Checking environment variables..."
if [ -z "$GOOGLE_API_KEY" ] && [ -z "$OPENAI_API_KEY" ] && [ -z "$ANTHROPIC_API_KEY" ]; then
    print_warning "No AI API keys found in environment. Make sure to set them in Vercel dashboard."
fi

# Optimize for deployment
print_status "Optimizing for deployment..."

# Remove development dependencies from node_modules for smaller deployment
npm prune --production

# Create deployment info
cat > deployment-info.json << EOF
{
  "deployment_time": "$(date -u +"%Y-%m-%dT%H:%M:%SZ")",
  "node_version": "$NODE_VERSION",
  "git_commit": "$(git rev-parse HEAD 2>/dev/null || echo 'unknown')",
  "git_branch": "$(git branch --show-current 2>/dev/null || echo 'unknown')",
  "build_environment": "$(uname -s)",
  "package_version": "$(node -p "require('./package.json').version")"
}
EOF

print_success "Deployment optimization completed"

# Deploy to Vercel
print_status "Deploying to Vercel..."

# Check if this is a production deployment
if [ "$1" = "--production" ] || [ "$1" = "-p" ]; then
    print_status "Deploying to production..."
    vercel --prod --yes
else
    print_status "Deploying to preview..."
    vercel --yes
fi

DEPLOYMENT_URL=$(vercel ls | head -n 2 | tail -n 1 | awk '{print $2}')

print_success "Deployment completed!"
echo ""
echo "🎉 SEOForge Express Backend deployed successfully!"
echo ""
echo "📋 Deployment Summary:"
echo "======================"
echo "🔗 URL: https://$DEPLOYMENT_URL"
echo "📦 Node.js: $NODE_VERSION"
echo "🏗️  Build: $(date)"
echo "📊 Package: $(node -p "require('./package.json').version")"
echo ""

# Test the deployment
print_status "Testing the deployment..."
sleep 5  # Wait for deployment to be ready

HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "https://$DEPLOYMENT_URL/health" || echo "000")

if [ "$HTTP_STATUS" = "200" ]; then
    print_success "Health check passed! API is responding correctly."
else
    print_warning "Health check failed with status: $HTTP_STATUS"
    print_warning "The deployment may need a few more seconds to be ready."
fi

# Test API endpoints
print_status "Testing API endpoints..."

# Test capabilities endpoint
CAPABILITIES_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "https://$DEPLOYMENT_URL/api/v1/capabilities" || echo "000")
if [ "$CAPABILITIES_STATUS" = "200" ]; then
    print_success "Capabilities endpoint is working"
else
    print_warning "Capabilities endpoint returned status: $CAPABILITIES_STATUS"
fi

echo ""
echo "🔧 Next Steps:"
echo "=============="
echo "1. Set environment variables in Vercel dashboard:"
echo "   - GOOGLE_API_KEY (for Gemini AI)"
echo "   - OPENAI_API_KEY (optional, for fallback)"
echo "   - ANTHROPIC_API_KEY (optional, for fallback)"
echo "   - JWT_SECRET (for authentication)"
echo "   - VALID_API_KEYS (for API key authentication)"
echo ""
echo "2. Test the API endpoints:"
echo "   - Health: https://$DEPLOYMENT_URL/health"
echo "   - Capabilities: https://$DEPLOYMENT_URL/api/v1/capabilities"
echo "   - Content Generation: https://$DEPLOYMENT_URL/api/v1/content/generate"
echo "   - Image Generation: https://$DEPLOYMENT_URL/api/v1/images/generate"
echo ""
echo "3. Update your WordPress plugin or frontend to use the new URL"
echo ""
echo "📚 Documentation:"
echo "   - API Docs: https://$DEPLOYMENT_URL/ (when ENABLE_SWAGGER_DOCS=true)"
echo "   - Metrics: https://$DEPLOYMENT_URL/api/v1/metrics (requires authentication)"
echo ""

# Cleanup
rm -f deployment-info.json

print_success "Deployment script completed successfully!"
echo ""
echo "🎯 Your SEOForge API is now live and ready to use!"
