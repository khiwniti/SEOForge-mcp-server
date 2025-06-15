#!/bin/bash

# SEOForge Express Backend - Vercel Deployment Script

set -e

echo "🚀 Deploying SEOForge Express Backend to Vercel..."

# Check if we're in the correct directory
if [ ! -f "package.json" ]; then
    echo "❌ Error: package.json not found. Please run this script from the backend-express directory."
    exit 1
fi

# Install dependencies
echo "📦 Installing dependencies..."
npm install

# Build the project
echo "🔨 Building project..."
npm run build

# Check if Vercel CLI is installed
if ! command -v vercel &> /dev/null; then
    echo "📥 Installing Vercel CLI..."
    npm install -g vercel
fi

# Deploy to Vercel
echo "🌐 Deploying to Vercel..."
vercel --prod

echo "✅ Deployment completed!"
echo ""
echo "🔗 Your backend is now available at the URL provided by Vercel"
echo "📝 Don't forget to set your environment variables in Vercel dashboard:"
echo "   - GOOGLE_API_KEY"
echo "   - OPENAI_API_KEY"
echo "   - ANTHROPIC_API_KEY"
echo "   - NODE_ENV=production"
echo ""
echo "🎉 SEOForge Express Backend deployment successful!"