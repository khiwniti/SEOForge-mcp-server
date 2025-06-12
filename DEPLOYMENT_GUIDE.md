# SEOForge MCP Platform - Complete Deployment Guide

## 🚀 Overview

This guide will help you deploy the SEOForge MCP Platform to Vercel and integrate it with WordPress. The platform consists of:

- **Frontend**: React-based dashboard for managing SEO operations
- **Backend**: FastAPI-based MCP server with WordPress integration
- **WordPress Plugin**: Seamless integration with WordPress sites

## 📋 Prerequisites

1. **Vercel Account**: Sign up at [vercel.com](https://vercel.com)
2. **GitHub Account**: For repository management
3. **WordPress Site**: For plugin installation
4. **Node.js**: Version 18 or higher
5. **Python**: Version 3.11 or higher

## 🔧 Step 1: Prepare for Deployment

### 1.1 Install Dependencies

```bash
# Install Vercel CLI globally
npm install -g vercel

# Install frontend dependencies
cd frontend
yarn install

# Install backend dependencies
cd ../backend
pip install -r requirements.txt
```

### 1.2 Environment Configuration

Create environment variables in Vercel dashboard:

```env
# Production API URL
VITE_API_URL=https://seoforge-mcp-platform.vercel.app

# Optional: Add your API keys
OPENAI_API_KEY=your_openai_key_here
ANTHROPIC_API_KEY=your_anthropic_key_here
```

## 🚀 Step 2: Deploy to Vercel

### 2.1 Automated Deployment

Run the deployment script:

```bash
chmod +x deploy.sh
./deploy.sh
```

### 2.2 Manual Deployment

If you prefer manual deployment:

```bash
# Build frontend
cd frontend
yarn build

# Deploy to Vercel
cd ..
vercel --prod
```

### 2.3 Verify Deployment

After deployment, test the endpoints:

```bash
# Test the deployment
python test_all_apis.py --url https://seoforge-mcp-platform.vercel.app
python test_bilingual_features.py --url https://seoforge-mcp-platform.vercel.app
```

## 🔌 Step 3: WordPress Integration

### 3.1 Install WordPress Plugin

1. Download the plugin from `wordpress-plugin/seoforge-mcp.php`
2. Upload to your WordPress site's `/wp-content/plugins/` directory
3. Activate the plugin in WordPress admin

### 3.2 Configure Plugin Settings

1. Go to **WordPress Admin > SEOForge MCP > Settings**
2. Set the API URL to: `https://seoforge-mcp-platform.vercel.app`
3. Configure your site key (optional for enhanced security)
4. Test the connection

### 3.3 Plugin Features

The plugin provides:

- **Content Generation**: AI-powered blog post creation
- **SEO Analysis**: Real-time content optimization
- **Keyword Research**: Intelligent keyword suggestions
- **Bilingual Support**: English and Thai content generation

## 🧪 Step 4: Testing WordPress Integration

### 4.1 Test with WordPress Playground

Use the provided test script:

```bash
python test_wordpress_playground.py
```

This will:
- Set up a temporary WordPress environment
- Install the plugin
- Test all MCP functionalities
- Generate a comprehensive report

### 4.2 Manual Testing

1. **Create New Post**: Use the SEOForge MCP meta box
2. **Generate Content**: Click "Generate Content" button
3. **Analyze SEO**: Use the SEO analysis feature
4. **Research Keywords**: Test keyword research functionality

## 🔒 Step 5: Security Configuration

### 5.1 WordPress Security Headers

The plugin automatically adds security headers:

```php
X-WordPress-Site: your-site-url
X-WordPress-Key: your-site-key
X-WordPress-Nonce: generated-nonce
X-WordPress-Timestamp: current-timestamp
```

### 5.2 Rate Limiting

The MCP server includes built-in rate limiting:
- 10 requests per minute per IP
- Configurable in backend settings

### 5.3 CORS Configuration

CORS is properly configured for WordPress integration:
- Allows WordPress admin origins
- Supports preflight requests
- Validates request headers

## 📊 Step 6: Monitoring and Maintenance

### 6.1 Health Checks

Monitor your deployment with these endpoints:

- **General Health**: `https://seoforge-mcp-platform.vercel.app/health`
- **MCP Server**: `https://seoforge-mcp-platform.vercel.app/mcp-server/health`
- **WordPress Plugin**: `https://seoforge-mcp-platform.vercel.app/wordpress/plugin/health`

### 6.2 Logs and Debugging

- Check Vercel function logs in the dashboard
- WordPress plugin logs are available in WP admin
- Use the test scripts for regular health checks

### 6.3 Performance Optimization

- Frontend is optimized with code splitting
- Backend uses FastAPI for high performance
- CDN delivery through Vercel Edge Network

## 🌐 Step 7: Custom Domain (Optional)

### 7.1 Add Custom Domain

1. Go to Vercel dashboard
2. Select your project
3. Go to Settings > Domains
4. Add your custom domain

### 7.2 Update WordPress Plugin

After adding custom domain:

1. Update API URL in WordPress plugin settings
2. Test the connection
3. Update any hardcoded references

## 🔧 Troubleshooting

### Common Issues

1. **Build Failures**
   - Check Node.js version (requires 18+)
   - Verify all dependencies are installed
   - Check for TypeScript errors

2. **API Connection Issues**
   - Verify CORS settings
   - Check WordPress site URL configuration
   - Validate security headers

3. **Plugin Not Working**
   - Ensure WordPress version compatibility
   - Check PHP error logs
   - Verify plugin activation

### Debug Mode

Enable debug mode in WordPress:

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 📞 Support

For issues and support:

1. Check the troubleshooting section
2. Review the test scripts output
3. Check Vercel function logs
4. Review WordPress error logs

## 🎉 Success!

Your SEOForge MCP Platform is now deployed and ready to use! The platform provides:

- ✅ Full-stack deployment on Vercel
- ✅ WordPress plugin integration
- ✅ Bilingual content support (English/Thai)
- ✅ Real-time SEO analysis
- ✅ AI-powered content generation
- ✅ Comprehensive security features
- ✅ Performance optimization
- ✅ Health monitoring

## 📈 Next Steps

1. **Content Creation**: Start generating SEO-optimized content
2. **SEO Analysis**: Analyze existing content for optimization
3. **Keyword Research**: Discover new keyword opportunities
4. **Performance Monitoring**: Track your SEO improvements
5. **Scale Up**: Add more WordPress sites to your MCP platform

---

**Happy SEO Optimizing! 🚀**