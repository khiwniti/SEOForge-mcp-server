#!/bin/bash

# Universal MCP Server - Cloudflare Workers Deployment Script

echo "🌐 Universal MCP Server - Cloudflare Workers Deployment"
echo "======================================================="

# Check if wrangler is installed
if ! command -v wrangler &> /dev/null; then
    echo "❌ Wrangler CLI not found. Installing..."
    npm install -g wrangler
fi

# Check if user is logged in
echo "🔐 Checking Cloudflare authentication..."
if ! wrangler whoami &> /dev/null; then
    echo "🔑 Please login to Cloudflare..."
    wrangler login
fi

# Check if Google API key is set
echo "🔑 Checking environment variables..."
if ! wrangler secret list | grep -q "GOOGLE_API_KEY"; then
    echo "⚠️  Google API key not found."
    echo "📝 Please set your Google Gemini API key:"
    wrangler secret put GOOGLE_API_KEY
fi

# Deploy to Cloudflare Workers
echo "🚀 Deploying to Cloudflare Workers..."
wrangler deploy

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Deployment successful!"
    echo ""
    echo "🎉 Your Universal MCP Server is now live on Cloudflare Workers!"
    echo ""
    echo "📋 Next Steps:"
    echo "1. Copy your Worker URL from the output above"
    echo "2. Update WordPress plugin: Admin → Universal MCP → Settings"
    echo "3. Update chatbot widgets with new server URL"
    echo "4. Test your API endpoints"
    echo ""
    echo "🔗 API Endpoints:"
    echo "   GET  / - Health check"
    echo "   POST /universal-mcp/generate-content - Content generation"
    echo "   POST /universal-mcp/generate-image - AI image generation"
    echo "   POST /universal-mcp/analyze-seo - SEO analysis"
    echo "   POST /universal-mcp/chatbot - AI chatbot"
    echo "   GET  /static/chatbot-widget.js - Chatbot widget"
    echo ""
    echo "📊 Monitor your deployment:"
    echo "   https://dash.cloudflare.com → Workers → universal-mcp-server"
    echo ""
    echo "🎯 Your API is now running on Cloudflare's global edge network!"
    echo "   - 99.99% uptime guarantee"
    echo "   - Sub-50ms response times globally"
    echo "   - Automatic DDoS protection"
    echo "   - Built-in scaling"
else
    echo "❌ Deployment failed. Please check the error messages above."
    exit 1
fi