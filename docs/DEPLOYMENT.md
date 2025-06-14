# 🚀 Universal MCP Server - Production Deployment Guide

## 📋 Overview

This guide will help you deploy the Universal MCP Server to Vercel for production use and install the WordPress plugin.

## 🌐 Vercel Deployment

### Prerequisites
- Vercel account
- GitHub repository access
- Google API key for Gemini

### Step 1: Environment Variables

In your Vercel dashboard, add these environment variables:

```bash
GOOGLE_API_KEY=your_google_gemini_api_key_here
ENVIRONMENT=production
CORS_ORIGINS=*
```

### Step 2: Deploy to Vercel

#### Option A: Deploy via Vercel Dashboard
1. Go to [vercel.com](https://vercel.com)
2. Click "New Project"
3. Import from GitHub: `khiwniti/SEOForge-mcp-server`
4. Configure environment variables
5. Deploy

#### Option B: Deploy via Vercel CLI
```bash
# Install Vercel CLI
npm i -g vercel

# Clone repository
git clone https://github.com/khiwniti/SEOForge-mcp-server.git
cd SEOForge-mcp-server

# Deploy
vercel --prod
```

### Step 3: Configure Domain (Optional)
- Add custom domain in Vercel dashboard
- Update DNS settings
- SSL certificate will be automatically provisioned

## 🔌 WordPress Plugin Installation

### Step 1: Download Plugin
Download the WordPress plugin from the `wordpress-plugin` directory.

### Step 2: Install Plugin

#### Option A: Upload via WordPress Admin
1. Go to WordPress Admin → Plugins → Add New
2. Click "Upload Plugin"
3. Upload the plugin ZIP file
4. Activate the plugin

#### Option B: FTP Upload
1. Upload the `universal-mcp-plugin` folder to `/wp-content/plugins/`
2. Activate via WordPress Admin → Plugins

### Step 3: Configure Plugin
1. Go to WordPress Admin → Universal MCP → Settings
2. Set MCP Server URL: `https://seo-forge-mcp-server-645x.vercel.app`
3. Set API Key (if required)
4. Test connection
5. Configure default settings

## ⚙️ Configuration

### Server Configuration
The server is configured via `vercel.json`:

```json
{
  "version": 2,
  "name": "seoforge-mcp-server",
  "builds": [
    {
      "src": "main.py",
      "use": "@vercel/python",
      "config": {
        "maxLambdaSize": "50mb",
        "runtime": "python3.9"
      }
    }
  ],
  "routes": [
    {
      "src": "/universal-mcp/(.*)",
      "dest": "/main.py"
    }
  ]
}
```

### WordPress Plugin Configuration
Configure these settings in WordPress Admin:

- **Server URL**: Your Vercel deployment URL
- **API Key**: Optional authentication key
- **Default Industry**: Choose your primary industry
- **Default Language**: Set your preferred language
- **Cache Enabled**: Enable for better performance
- **Debug Mode**: Enable for troubleshooting

## 🧪 Testing

### Test API Endpoints
```bash
# Health check
curl https://seo-forge-mcp-server-645x.vercel.app/

# Content generation
curl -X POST https://seo-forge-mcp-server-645x.vercel.app/universal-mcp/generate-content \
  -H "Content-Type: application/json" \
  -d '{"topic": "Test", "keywords": ["test"], "language": "en"}'

# Image generation
curl -X POST https://seo-forge-mcp-server-645x.vercel.app/universal-mcp/generate-image \
  -H "Content-Type: application/json" \
  -d '{"prompt": "Test image", "style": "professional"}'

# SEO analysis
curl -X POST https://seo-forge-mcp-server-645x.vercel.app/universal-mcp/analyze-seo \
  -H "Content-Type: application/json" \
  -d '{"content": "Test content", "keywords": ["test"]}'

# Chatbot
curl -X POST https://seo-forge-mcp-server-645x.vercel.app/universal-mcp/chatbot \
  -H "Content-Type: application/json" \
  -d '{"message": "Hello", "website_url": "https://example.com"}'
```

### Test WordPress Plugin
1. Go to Universal MCP → Dashboard
2. Click "Test Connection"
3. Try generating content
4. Test image generation
5. Run SEO analysis

## 🔧 Features

### ✅ API Endpoints
- `/` - Health check and server status
- `/universal-mcp/generate-content` - Content generation
- `/universal-mcp/generate-blog-with-images` - Blog with AI images
- `/universal-mcp/generate-image` - AI image generation
- `/universal-mcp/analyze-seo` - SEO analysis
- `/universal-mcp/analyze-website` - Website analysis
- `/universal-mcp/status` - Server capabilities

### ✅ WordPress Plugin Features
- **Content Generator**: AI-powered content creation
- **Image Generator**: Real AI image generation via Pollinations
- **SEO Analyzer**: Content optimization analysis
- **Multi-language Support**: EN, TH, ES, FR, DE
- **Industry Specialization**: 11+ industries supported
- **WordPress Integration**: Direct post creation
- **Real-time Testing**: Connection and API testing

### ✅ AI Capabilities
- **Google Gemini 1.5 Flash**: Advanced content generation
- **Pollinations AI**: Real image generation (not placeholders)
- **Multi-provider Fallback**: Pollinations → Unsplash → Placeholder
- **Style Support**: Professional, Artistic, Minimalist, Commercial
- **Size Options**: Square, Portrait, Landscape formats

## 🛡️ Security

### Environment Variables
- Store sensitive data in Vercel environment variables
- Never commit API keys to repository
- Use HTTPS for all communications

### WordPress Security
- Nonce verification for all AJAX requests
- Input sanitization and validation
- Capability checks for admin functions
- Secure API communication

## 📊 Monitoring

### Vercel Analytics
- Monitor function execution times
- Track error rates
- View usage statistics

### WordPress Logs
- Enable debug mode for detailed logging
- Monitor plugin performance
- Track API usage

## 🔄 Updates

### Server Updates
1. Update code in GitHub repository
2. Vercel will automatically redeploy
3. Test all endpoints after deployment

### Plugin Updates
1. Update plugin files
2. Increment version number
3. Test in staging environment
4. Deploy to production

## 🆘 Troubleshooting

### Common Issues

#### Connection Failed
- Check server URL in WordPress settings
- Verify Vercel deployment is active
- Test API endpoints directly

#### Image Generation Not Working
- Verify Pollinations AI is accessible
- Check network connectivity
- Review server logs

#### SEO Analysis Errors
- Ensure content is provided
- Check keyword format (comma-separated)
- Verify language parameter

### Debug Mode
Enable debug mode in WordPress plugin settings to get detailed error logs.

### Support
- Check server logs in Vercel dashboard
- Enable WordPress debug logging
- Review browser console for JavaScript errors

## 🎯 Production Checklist

### Before Going Live
- [ ] Test all API endpoints
- [ ] Verify WordPress plugin functionality
- [ ] Configure environment variables
- [ ] Set up monitoring
- [ ] Test error handling
- [ ] Verify CORS settings
- [ ] Check SSL certificate
- [ ] Test mobile responsiveness

### Performance Optimization
- [ ] Enable caching in WordPress plugin
- [ ] Configure CDN for images
- [ ] Monitor function execution times
- [ ] Optimize image sizes
- [ ] Set appropriate cache headers

## 📈 Scaling

### Vercel Limits
- Function execution: 30 seconds max
- Memory: 1024MB default
- Bandwidth: Based on plan

### WordPress Optimization
- Use caching plugins
- Optimize database queries
- Implement image optimization
- Monitor plugin performance

## 🎉 Success!

Your Universal MCP Server is now deployed and ready for production use! The system provides:

- **Real AI Image Generation** via Pollinations AI
- **Advanced Content Creation** with Google Gemini
- **Comprehensive SEO Analysis**
- **Multi-language Support**
- **Universal Industry Compatibility**
- **Professional WordPress Integration**

Enjoy your new AI-powered content creation platform! 🚀