# 🚀 SEOForge MCP Server - WordPress Integration Guide

## 🎯 Overview

SEOForge MCP Server is now fully integrated with **Google Gemini AI** and ready for seamless WordPress plugin integration. This guide provides complete setup instructions for both development and production environments.

## ✨ Features

### 🤖 AI-Powered Content Generation
- **Blog Generator**: Creates SEO-optimized blog posts using Google Gemini 1.5 Flash
- **SEO Analyzer**: Provides comprehensive content analysis and actionable recommendations
- **Meta Tag Optimization**: Generates optimized titles and descriptions
- **Keyword Density Analysis**: Analyzes keyword usage and provides optimization suggestions

### 🔧 WordPress Integration
- **Universal MCP Plugin**: Compatible with any WordPress site
- **Real-time API Communication**: Direct integration with SEOForge MCP Server
- **Admin Dashboard**: Easy-to-use interface for content generation and analysis
- **CORS Enabled**: Full cross-origin support for WordPress environments

## 🛠️ Quick Setup

### 1. Server Configuration

The SEOForge MCP Server is configured to run on **port 8083** with the following endpoints:

```
Health Check: GET http://localhost:8083/
Blog Generator: POST http://localhost:8083/routes/blog-generator/generate
SEO Analyzer: POST http://localhost:8083/routes/seo-analyzer/analyze
```

### 2. WordPress Plugin Installation

1. **Download Plugin**: Use the updated `seoforge-mcp-plugin.zip` file
2. **Install in WordPress**: Upload via Admin → Plugins → Add New → Upload Plugin
3. **Activate Plugin**: Enable the SEOForge MCP plugin
4. **Configure Settings**: Set API URL to your server endpoint

### 3. API Configuration

The plugin is pre-configured with:
- **Default API URL**: `http://localhost:8083` (development)
- **Production URL**: Update to your deployed server URL
- **CORS Support**: Enabled for all origins
- **Authentication**: Optional API key support

## 🧪 Testing & Validation

### Integration Test Suite

Run the comprehensive test suite to verify functionality:

```bash
cd /workspace/seoforge-production
python test_integration.py
```

**Expected Results:**
```
🚀 SEOForge MCP Server Integration Test
==================================================
✅ Server: ONLINE
✅ Blog Generator: PASS
✅ SEO Analyzer: PASS

🎉 ALL TESTS PASSED! SEOForge MCP Server is ready for WordPress integration.
```

### Manual API Testing

#### Blog Generator Test
```bash
curl -X POST "http://localhost:8083/routes/blog-generator/generate" \
  -H "Content-Type: application/json" \
  -d '{
    "topic": "WordPress SEO Best Practices",
    "keywords": ["WordPress", "SEO", "optimization"]
  }'
```

#### SEO Analyzer Test
```bash
curl -X POST "http://localhost:8083/routes/seo-analyzer/analyze" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Your content here...",
    "keywords": ["keyword1", "keyword2"],
    "current_meta_title": "Current Title",
    "current_meta_description": "Current Description"
  }'
```

## 🔑 Google Gemini API Integration

### API Configuration
- **Provider**: Google Gemini AI
- **Model**: gemini-1.5-flash
- **API Key**: Configured and tested
- **Fallback**: Mock content generation when API unavailable

### Key Features
- **High-Quality Content**: Advanced AI content generation
- **SEO Optimization**: Built-in SEO best practices
- **Fast Response**: Optimized for real-time WordPress integration
- **Error Handling**: Comprehensive error management and fallbacks

## 🌐 Production Deployment

### Server Requirements
- **Python 3.8+**: Required for FastAPI and dependencies
- **Port Access**: Ensure port 8083 is accessible
- **HTTPS**: Recommended for production environments
- **CORS**: Configured for WordPress domain access

### WordPress Plugin Configuration

Update the plugin configuration for production:

```php
// In wordpress-plugin/seoforge-mcp.php
$this->api_url = get_option('seoforge_mcp_api_url', 'https://your-production-server.com');
```

### Environment Variables

Set the following environment variables for production:

```bash
GEMINI_API_KEY=your_gemini_api_key_here
ENVIRONMENT=production
PORT=8083
```

## 📊 API Response Examples

### Blog Generator Response
```json
{
  "generated_text": "# WordPress SEO Best Practices\n\nWordPress is a powerful platform..."
}
```

### SEO Analyzer Response
```json
{
  "overall_seo_score": 85.0,
  "keyword_density_results": [
    {
      "keyword": "WordPress",
      "count": 5,
      "density": 2.5
    }
  ],
  "readability_scores": {
    "flesch_reading_ease": 65.2,
    "flesch_kincaid_grade": 8.1
  },
  "meta_tag_suggestions": {
    "suggested_title": "Ultimate WordPress SEO Guide 2024",
    "suggested_description": "Master WordPress SEO with proven strategies..."
  },
  "actionable_recommendations": [
    "Optimize keyword density for better search rankings",
    "Improve content readability for better user engagement"
  ]
}
```

## 🔧 Troubleshooting

### Common Issues

1. **Server Not Responding**
   - Check if server is running on port 8083
   - Verify firewall settings
   - Test with `curl http://localhost:8083/`

2. **CORS Errors**
   - Ensure CORS is enabled in server configuration
   - Check WordPress site URL in CORS settings

3. **API Key Issues**
   - Verify Google Gemini API key is valid
   - Check API quota and usage limits

4. **Plugin Not Connecting**
   - Verify API URL in WordPress plugin settings
   - Check network connectivity between WordPress and server

### Debug Mode

Enable debug logging in the WordPress plugin:

```php
define('SEOFORGE_DEBUG', true);
```

## 🚀 Next Steps

1. **Deploy to Production**: Set up server on your preferred hosting platform
2. **Configure WordPress**: Install and configure the plugin on your WordPress site
3. **Test Integration**: Verify all features work correctly
4. **Monitor Performance**: Set up logging and monitoring for production use

## 📞 Support

For issues or questions:
- **GitHub Issues**: [SEOForge MCP Server Repository](https://github.com/khiwniti/SEOForge-mcp-server)
- **Documentation**: Check this guide and code comments
- **Testing**: Use the provided integration test suite

---

**✅ Status**: Fully tested and ready for WordPress integration with Google Gemini AI
**🔗 Repository**: https://github.com/khiwniti/SEOForge-mcp-server
**🤖 AI Provider**: Google Gemini 1.5 Flash
**🌐 WordPress Compatible**: Yes, with universal MCP plugin