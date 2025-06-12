# 🚀 Universal MCP Server - Production Ready

## 🌐 Live Production Server
**API Base URL:** `https://seo-forge-mcp-server-645x.vercel.app`

## 📦 Quick Start

### WordPress Plugin Installation
1. Download: `universal-mcp-plugin-production.zip`
2. Upload to WordPress: Admin → Plugins → Add New → Upload Plugin
3. Activate the plugin
4. Configure: Admin → Universal MCP → Settings
5. Server URL is pre-configured to production: `https://seo-forge-mcp-server-645x.vercel.app`

### Chatbot Widget Integration
Add to any website with 2 lines of code:

```html
<script src="https://seo-forge-mcp-server-645x.vercel.app/static/chatbot-widget.js"></script>
<script>
  UMCPChatbot.init({
    serverUrl: 'https://seo-forge-mcp-server-645x.vercel.app',
    companyName: 'Your Company'
  });
</script>
```

## 🔧 Available API Endpoints

### Content Generation
```bash
curl -X POST https://seo-forge-mcp-server-645x.vercel.app/universal-mcp/generate-content \
  -H "Content-Type: application/json" \
  -d '{
    "content_type": "blog_post",
    "topic": "AI Technology",
    "keywords": ["AI", "technology", "innovation"],
    "language": "en",
    "tone": "professional",
    "length": "medium"
  }'
```

### AI Image Generation
```bash
curl -X POST https://seo-forge-mcp-server-645x.vercel.app/universal-mcp/generate-image \
  -H "Content-Type: application/json" \
  -d '{
    "prompt": "Professional business meeting",
    "style": "professional",
    "size": "1024x1024"
  }'
```

### SEO Analysis
```bash
curl -X POST https://seo-forge-mcp-server-645x.vercel.app/universal-mcp/analyze-seo \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Your content here...",
    "keywords": ["SEO", "optimization"],
    "language": "en"
  }'
```

### AI Chatbot
```bash
curl -X POST https://seo-forge-mcp-server-645x.vercel.app/universal-mcp/chatbot \
  -H "Content-Type: application/json" \
  -d '{
    "message": "What products do you sell?",
    "website_url": "https://your-website.com"
  }'
```

### Blog with Images
```bash
curl -X POST https://seo-forge-mcp-server-645x.vercel.app/universal-mcp/generate-blog-with-images \
  -H "Content-Type: application/json" \
  -d '{
    "topic": "Digital Marketing Trends",
    "keywords": ["digital marketing", "trends"],
    "include_images": true,
    "image_count": 2
  }'
```

## 🎯 Features

### ✅ WordPress Plugin
- **Content Generator**: AI-powered blog posts and articles
- **Image Generator**: Real AI images via Pollinations API
- **SEO Analyzer**: Content optimization recommendations
- **Admin Dashboard**: Complete management interface
- **Real-time Testing**: Connection and API testing tools

### ✅ AI Chatbot System
- **Facebook Messenger UI**: Pixel-perfect design
- **Website Intelligence**: Analyzes your site for context
- **Natural Conversations**: Google Gemini 1.5 Flash powered
- **Product Recommendations**: Smart suggestions
- **Multi-language Support**: EN, TH, ES, FR, DE

### ✅ Universal API
- **Content Generation**: 11+ industries supported
- **Real AI Images**: Not placeholders - actual AI generation
- **SEO Optimization**: Advanced analysis and recommendations
- **Website Analysis**: Automatic content extraction
- **Multi-format Support**: JSON responses, error handling

## 🌍 Multi-Language Support
- **English** (en) - Default
- **Thai** (th) - ไทย
- **Spanish** (es) - Español
- **French** (fr) - Français
- **German** (de) - Deutsch

## 🏢 Industry Support
- General Business
- E-commerce
- Healthcare
- Finance & Banking
- Technology
- Education
- Real Estate
- Automotive
- Travel & Tourism
- Food & Restaurant
- Legal Services

## 🔒 Security Features
- Input sanitization and validation
- Rate limiting protection
- CORS headers configured
- Error handling with fallbacks
- Secure API communication

## 📱 Integration Examples

### WordPress Site
```php
// The plugin handles everything automatically
// Just install and activate!
```

### React/Vue/Angular
```javascript
// Load the widget script
import 'https://seo-forge-mcp-server-645x.vercel.app/static/chatbot-widget.js';

// Initialize
UMCPChatbot.init({
  serverUrl: 'https://seo-forge-mcp-server-645x.vercel.app',
  companyName: 'Your Company',
  primaryColor: '#your-brand-color'
});
```

### Plain HTML
```html
<!DOCTYPE html>
<html>
<head>
    <title>My Website</title>
</head>
<body>
    <h1>Welcome!</h1>
    
    <!-- Chatbot Widget -->
    <script src="https://seo-forge-mcp-server-645x.vercel.app/static/chatbot-widget.js"></script>
    <script>
        UMCPChatbot.init({
            serverUrl: 'https://seo-forge-mcp-server-645x.vercel.app',
            companyName: 'My Company'
        });
    </script>
</body>
</html>
```

## 🎨 Customization Options

### Chatbot Widget
```javascript
UMCPChatbot.init({
  serverUrl: 'https://seo-forge-mcp-server-645x.vercel.app',
  websiteUrl: 'https://your-website.com',
  companyName: 'Your Company Name',
  primaryColor: '#667eea',           // Brand color
  position: 'bottom-right',          // bottom-left, top-right, top-left
  autoOpen: false,                   // Auto-open after delay
  autoOpenDelay: 5000,              // Delay in milliseconds
  showNotifications: true            // Show notification badges
});
```

### WordPress Plugin
- Configure server URL in Admin → Universal MCP → Settings
- Set default industry and language preferences
- Enable/disable caching for performance
- Debug mode for troubleshooting

## 📊 Performance
- **Response Time**: < 3 seconds for content generation
- **Image Generation**: < 5 seconds for AI images
- **Chatbot**: < 2 seconds for responses
- **Uptime**: 99.9% (Vercel infrastructure)
- **Scalability**: Serverless auto-scaling

## 🆘 Support & Troubleshooting

### Common Issues

#### WordPress Plugin Not Connecting
1. Check server URL: `https://seo-forge-mcp-server-645x.vercel.app`
2. Test connection in plugin settings
3. Check WordPress error logs
4. Verify internet connectivity

#### Chatbot Not Loading
1. Check console for JavaScript errors
2. Verify script URL is correct
3. Ensure CORS is not blocking requests
4. Test API endpoints directly

#### API Errors
1. Check request format (JSON)
2. Verify required parameters
3. Check rate limiting
4. Review error messages

### Getting Help
- Check the documentation in this repository
- Test API endpoints with curl commands above
- Review WordPress plugin logs
- Contact support with specific error messages

## 🎉 Success Stories

This Universal MCP Server provides:
- **Real AI Image Generation** (not placeholders)
- **Intelligent Chatbot** with website context
- **Professional WordPress Integration**
- **Multi-language & Multi-industry Support**
- **Production-ready Reliability**

Perfect for businesses wanting to add AI-powered content generation and customer service to their websites!

## 🔗 Quick Links
- **WordPress Plugin**: `universal-mcp-plugin-production.zip`
- **API Documentation**: See curl examples above
- **Chatbot Demo**: `chatbot-integration-example.html`
- **Live API**: `https://seo-forge-mcp-server-645x.vercel.app`

---

**Ready to transform your website with AI? Start with the WordPress plugin or chatbot widget today!** 🚀