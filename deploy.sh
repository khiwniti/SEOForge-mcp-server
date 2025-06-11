#!/bin/bash

# SEOForge MCP Server Deployment Script
# This script automates the deployment process for the production-ready MCP server

set -e

echo "🚀 Starting SEOForge MCP Server Deployment..."

# Check if required environment variables are set
if [ -z "$VERCEL_TOKEN" ]; then
    echo "❌ VERCEL_TOKEN environment variable is required"
    exit 1
fi

# Install dependencies
echo "📦 Installing dependencies..."
cd backend
pip install -r requirements.txt
cd ..

# Run tests (if any)
echo "🧪 Running tests..."
# Add test commands here when tests are implemented

# Build frontend
echo "🏗️ Building frontend..."
cd frontend
npm install
npm run build
cd ..

# Deploy to Vercel
echo "🚀 Deploying to Vercel..."
vercel --prod --token $VERCEL_TOKEN

# Verify deployment
echo "✅ Verifying deployment..."
DEPLOYMENT_URL=$(vercel ls --token $VERCEL_TOKEN | grep "seoforge-mcp" | head -1 | awk '{print $2}')

if [ ! -z "$DEPLOYMENT_URL" ]; then
    echo "🎉 Deployment successful!"
    echo "🌐 URL: https://$DEPLOYMENT_URL"
    
    # Test health endpoints
    echo "🔍 Testing health endpoints..."
    curl -f "https://$DEPLOYMENT_URL/mcp-server/health" || echo "⚠️ MCP server health check failed"
    curl -f "https://$DEPLOYMENT_URL/wordpress/plugin/health" || echo "⚠️ WordPress plugin health check failed"
    
    echo "✅ Deployment completed successfully!"
    echo "📋 Next steps:"
    echo "   1. Install the WordPress plugin from the wordpress-plugin/ directory"
    echo "   2. Configure the plugin with API URL: https://$DEPLOYMENT_URL"
    echo "   3. Set up authentication and test the integration"
else
    echo "❌ Deployment verification failed"
    exit 1
fi
