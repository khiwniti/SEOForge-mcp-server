# 📦 SEO Forge WordPress Plugin - Installation Guide

## 🚀 Quick Installation

### Method 1: WordPress Admin Dashboard (Recommended)
1. Download the `seo-forge-v1.2.0.zip` file
2. Go to your WordPress admin dashboard
3. Navigate to **Plugins > Add New**
4. Click **Upload Plugin**
5. Choose the downloaded zip file
6. Click **Install Now**
7. Click **Activate Plugin**

### Method 2: FTP Upload
1. Extract the `seo-forge-v1.2.0.zip` file
2. Upload the `seo-forge` folder to `/wp-content/plugins/`
3. Go to **Plugins** in your WordPress admin
4. Find "SEO Forge" and click **Activate**

## ⚙️ Configuration

### 1. Basic Setup
After activation, you'll see "SEO Forge" in your WordPress admin menu:

1. Go to **SEO Forge > Settings**
2. Configure your API endpoint (optional - works with defaults)
3. Set your preferred language
4. Configure image generation settings

### 2. API Configuration (Optional)
For enhanced features, you can configure API tokens:

```
MCP Server URL: https://your-server.com/universal-mcp
Google API Key: your_gemini_api_key (for AI content)
HuggingFace Token: hf_your_token (for premium image generation)
```

### 3. Flux Image Generation Setup
The plugin includes multiple image generation providers:

- **Pollinations AI**: Free, no setup required
- **HuggingFace**: Requires token for premium models
- **Replicate**: Requires API token
- **Together AI**: Requires API key

## 🎯 Features Overview

### ✅ AI Content Generation
- Generate SEO-optimized blog posts
- Create product descriptions
- Multi-language support (11 languages)
- Industry-specific content

### ✅ Flux Image Generation
- **flux-schnell**: Fast generation (4-8 steps)
- **flux-dev**: High quality (20-50 steps)
- **flux-pro**: Professional quality (25-50 steps)
- 9 professional styles
- Batch generation support

### ✅ SEO Analysis
- Comprehensive SEO scoring
- Keyword density analysis
- Meta tag optimization
- Content recommendations

### ✅ Keyword Research
- Search volume data
- Keyword difficulty analysis
- Related keywords suggestions
- Competition analysis

## 🇹🇭 Thai Language Support

SEO Forge includes enhanced Thai language capabilities:

- Thai content generation
- Thai keyword research
- Thai image generation with proper text rendering
- Cultural context awareness

## 🛠️ Usage Instructions

### Content Generation
1. Go to **Posts > Add New** or **Pages > Add New**
2. Look for the "SEO Forge" meta box
3. Enter your topic or keywords
4. Select content type and language
5. Click "Generate Content"
6. Review and edit the generated content

### Image Generation
1. In the SEO Forge meta box, enable "Include Images"
2. Select image style (professional, artistic, etc.)
3. Choose Flux model (schnell for speed, dev for quality)
4. The plugin will generate relevant images automatically

### SEO Analysis
1. Write or generate your content
2. Click "Analyze SEO" in the SEO Forge meta box
3. Review the SEO score and recommendations
4. Implement suggested improvements

### Keyword Research
1. Go to **SEO Forge > Keyword Research**
2. Enter your seed keywords
3. Select target language and location
4. Review search volume and difficulty data
5. Export keywords for content planning

## 🔧 Troubleshooting

### Common Issues

#### Plugin Not Activating
- Check PHP version (requires 7.4+)
- Ensure WordPress version 5.0+
- Check for plugin conflicts

#### API Connection Issues
- Verify internet connection
- Check API endpoint URL
- Ensure API tokens are correct

#### Image Generation Not Working
- Check if images directory is writable
- Verify API tokens (if using premium providers)
- Try different Flux models

#### Thai Language Issues
- Ensure UTF-8 encoding
- Check font support in your theme
- Verify language settings

### Debug Mode
Enable WordPress debug mode for detailed error logs:

```php
// Add to wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 📊 Performance Optimization

### Recommended Settings
- Use **flux-schnell** for bulk content generation
- Use **flux-dev** for final high-quality images
- Enable caching for better performance
- Optimize images after generation

### Server Requirements
- **PHP**: 7.4 or higher
- **Memory**: 256MB minimum (512MB recommended)
- **Storage**: 100MB free space
- **Internet**: Stable connection for API calls

## 🔐 Security Considerations

### API Key Security
- Store API keys securely
- Use environment variables when possible
- Rotate keys regularly
- Monitor API usage

### Content Moderation
- Review generated content before publishing
- Use appropriate content filters
- Follow platform guidelines
- Respect copyright and licensing

## 📈 Best Practices

### Content Generation
1. Provide specific, detailed prompts
2. Review and edit generated content
3. Add personal insights and expertise
4. Optimize for your target audience

### Image Generation
1. Use descriptive prompts
2. Select appropriate styles for your brand
3. Optimize image sizes for web
4. Add proper alt text for SEO

### SEO Optimization
1. Target specific keywords
2. Optimize meta titles and descriptions
3. Use proper heading structure
4. Monitor SEO scores regularly

## 🆕 What's New in v1.2.0

### 🎨 Flux Image Generation
- State-of-the-art Flux AI models
- Multiple provider fallbacks
- Enhanced prompt engineering
- Batch generation capabilities

### 🇹🇭 Enhanced Thai Support
- Improved Thai text rendering
- Cultural context awareness
- Thai keyword optimization
- Better font handling

### 🚀 Performance Improvements
- Faster generation times
- Better error handling
- Enhanced caching
- Optimized API calls

### 🔧 Technical Enhancements
- Multiple API provider support
- Comprehensive logging
- Better error messages
- Enhanced security

## 📞 Support

### Documentation
- Plugin documentation: Available in admin panel
- API documentation: Included with server
- Video tutorials: Coming soon

### Community Support
- WordPress.org support forums
- GitHub issues (for developers)
- Community Discord (coming soon)

### Premium Support
- Priority email support
- Custom integration assistance
- Advanced configuration help
- Performance optimization

## 🔄 Updates

### Automatic Updates
The plugin supports WordPress automatic updates:
1. Enable automatic updates in **Plugins** page
2. Updates will be downloaded and installed automatically
3. Always backup before major updates

### Manual Updates
1. Download the latest version
2. Deactivate the current plugin
3. Upload the new version
4. Activate the updated plugin
5. Check settings and configuration

## 📋 Changelog Summary

### v1.2.0 (Current)
- ✅ Flux image generation integration
- ✅ Enhanced Thai language support
- ✅ Multiple API provider support
- ✅ Improved performance and reliability

### v1.0.0
- ✅ Initial release
- ✅ Basic content generation
- ✅ SEO analysis tools
- ✅ Keyword research functionality

---

## 🎯 Quick Start Checklist

- [ ] Download and install the plugin
- [ ] Activate SEO Forge
- [ ] Configure basic settings
- [ ] Test content generation
- [ ] Test image generation
- [ ] Run SEO analysis
- [ ] Explore keyword research
- [ ] Customize for your needs

**Ready to boost your SEO with AI-powered content and images!** 🚀

For the latest updates and documentation, visit: [https://seoforge.dev](https://seoforge.dev)