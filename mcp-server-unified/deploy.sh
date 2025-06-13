#!/bin/bash

# SEO Forge MCP Server Deployment Script
# Deploys the unified MCP server to Vercel

set -e

echo "🚀 Starting SEO Forge MCP Server Deployment..."

# Check if Vercel CLI is installed
if ! command -v vercel &> /dev/null; then
    echo "❌ Vercel CLI not found. Installing..."
    npm install -g vercel
fi

# Check if user is logged in to Vercel
if ! vercel whoami &> /dev/null; then
    echo "🔐 Please login to Vercel..."
    vercel login
fi

# Build the project
echo "🔨 Building project..."
npm run build

# Deploy to Vercel
echo "🚀 Deploying to Vercel..."
vercel --prod

# Get deployment URL
DEPLOYMENT_URL=$(vercel --prod 2>&1 | grep -o 'https://[^[:space:]]*')

echo "✅ Deployment completed successfully!"
echo "🌐 Your MCP server is available at: $DEPLOYMENT_URL"
echo ""
echo "📋 Next steps:"
echo "1. Set up environment variables in Vercel dashboard"
echo "2. Test the deployment: curl $DEPLOYMENT_URL/health"
echo "3. Access the client interface: $DEPLOYMENT_URL/client"
echo "4. Configure your WordPress plugin with the server URL"
echo ""
echo "🔧 Required environment variables:"
echo "- GOOGLE_API_KEY"
echo "- OPENAI_API_KEY (optional)"
echo "- ANTHROPIC_API_KEY (optional)"
echo "- REPLICATE_API_TOKEN (optional)"
echo "- JWT_SECRET"
echo "- DEFAULT_ADMIN_EMAIL"
echo "- DEFAULT_ADMIN_PASSWORD"
echo ""
echo "📚 Documentation: $DEPLOYMENT_URL/client/docs"
echo "🏥 Health check: $DEPLOYMENT_URL/health"
