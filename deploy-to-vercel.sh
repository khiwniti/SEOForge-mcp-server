#!/bin/bash

# SEOForge MCP Platform - Vercel Deployment Script
# This script deploys both frontend and backend to Vercel

set -e

echo "🚀 Starting SEOForge MCP Platform deployment to Vercel..."

# Check if Vercel CLI is installed
if ! command -v vercel &> /dev/null; then
    echo "📦 Installing Vercel CLI..."
    npm install -g vercel
fi

# Set environment variables for deployment
export NODE_ENV=production
export VITE_API_URL="https://seoforge-mcp-platform.vercel.app"

echo "🔧 Setting up environment..."

# Clean previous builds
echo "🧹 Cleaning previous builds..."
rm -rf frontend/dist
rm -rf backend/__pycache__
rm -rf backend/.vercel

# Install frontend dependencies
echo "📦 Installing frontend dependencies..."
cd frontend
npm ci
echo "✅ Frontend dependencies installed"

# Build frontend
echo "🏗️  Building frontend..."
npm run build
echo "✅ Frontend built successfully"

# Go back to root
cd ..

# Install backend dependencies
echo "📦 Installing backend dependencies..."
cd backend
pip install -r requirements.txt
echo "✅ Backend dependencies installed"

# Go back to root
cd ..

echo "🚀 Deploying to Vercel..."

# Deploy to Vercel
vercel --prod --yes

echo "✅ Deployment completed!"
echo ""
echo "🌐 Your application should be available at:"
echo "   https://seoforge-mcp-platform.vercel.app"
echo ""
echo "📋 Next steps:"
echo "   1. Test the deployment with the provided test scripts"
echo "   2. Configure environment variables in Vercel dashboard if needed"
echo "   3. Update WordPress plugin settings with the new URL"
echo ""
echo "🧪 Test the deployment:"
echo "   python test_all_apis.py --url https://seoforge-mcp-platform.vercel.app"
echo "   python test_bilingual_features.py --url https://seoforge-mcp-platform.vercel.app"