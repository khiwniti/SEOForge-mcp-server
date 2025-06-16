#!/bin/bash

# SEOForge Express Backend Vercel Deployment Script
echo "Starting SEOForge Express Backend Deployment..."

# Install dependencies
echo "Installing dependencies..."
npm install --no-optional

# Build the project
echo "Building the project..."
npm run build

echo "Deployment preparation completed!"

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