# SEOForge MCP Server - Production Ready WordPress Integration

This project implements a production-ready WordPress plugin with an integrated MCP (Model Context Protocol) server for AI-powered SEO content generation and analysis. The system is designed to work seamlessly between WordPress sites and the MCP server deployed on Vercel.

## 🚀 Project Structure

```
wordpress-plugin-with-mcp-server/
├── backend/
│   ├── api/
│   │   ├── mcp-server.py          # MCP Protocol Server Implementation
│   │   ├── wordpress.py           # WordPress Authentication & Rate Limiting
│   │   └── wordpress_plugin.py    # WordPress Plugin API Endpoints
│   ├── main.py                    # Main FastAPI application
│   └── requirements.txt           # Python dependencies
├── frontend/                      # React frontend application
├── wordpress-plugin/              # WordPress Plugin Files
│   ├── seoforge-mcp.php          # Main plugin file
│   └── assets/
│       ├── js/
│       │   ├── admin.js          # Admin interface JavaScript
│       │   └── frontend.js       # Frontend enhancements
│       └── css/
│           └── admin.css         # Admin interface styles
├── vercel.json                    # Vercel deployment configuration
└── README.md                      # This file
```

## MCP Server Features

The MCP server provides the following capabilities with **bilingual support (English/Thai)**:

### Tools
1. **content_generation** - Generate SEO-optimized content for various industries
   - English: "Generate SEO-optimized content"
   - Thai: "สร้างเนื้อหาที่เหมาะสมกับ SEO"
2. **seo_analysis** - Analyze SEO performance of content or URLs
   - English: "Analyze SEO performance"
   - Thai: "วิเคราะห์ประสิทธิภาพ SEO"
3. **keyword_research** - Research keywords for SEO optimization
   - English: "Research keywords for SEO"
   - Thai: "วิจัยคำค้นหาสำหรับ SEO"

### Prompts
1. **blog_post** - Generate blog post prompts for specific topics and industries
   - English: "Generate blog post prompts"
   - Thai: "สร้างคำแนะนำสำหรับบทความบล็อก"

### Resources
1. **industry_data** - Access comprehensive data about specific industries
   - English: "Access industry data"
   - Thai: "เข้าถึงข้อมูลอุตสาหกรรม"

## MCP Protocol Implementation

The server implements the MCP protocol with the following endpoints:

- `POST /mcp-server` - Main MCP protocol endpoint
- `GET /mcp-server/health` - Health check endpoint

### Supported MCP Methods

- `initialize` - Initialize the MCP connection
- `tools/list` - List available tools
- `tools/call` - Execute a tool
- `prompts/list` - List available prompts
- `prompts/get` - Get a prompt
- `resources/list` - List available resources
- `resources/read` - Read a resource

## Usage Examples

### Tool Usage

#### Content Generation (English)
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "tools/call",
  "params": {
    "name": "content_generation",
    "arguments": {
      "topic": "Digital Marketing Strategies",
      "content_type": "blog_post",
      "keywords": ["SEO", "content marketing", "digital strategy"],
      "industry": "technology",
      "language": "en"
    }
  }
}
```

#### Content Generation (Thai)
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "tools/call",
  "params": {
    "name": "content_generation",
    "arguments": {
      "topic": "กลยุทธ์การตลาดดิจิทัล",
      "content_type": "blog_post",
      "keywords": ["SEO", "การตลาดเนื้อหา", "กลยุทธ์ดิจิทัล"],
      "industry": "เทคโนโลยี",
      "language": "th"
    }
  }
}
```

#### SEO Analysis (English)
```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "tools/call",
  "params": {
    "name": "seo_analysis",
    "arguments": {
      "url": "https://example.com/blog-post",
      "content": "Your content to analyze...",
      "language": "en"
    }
  }
}
```

#### SEO Analysis (Thai)
```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "tools/call",
  "params": {
    "name": "seo_analysis",
    "arguments": {
      "url": "https://example.com/blog-post",
      "content": "เนื้อหาของคุณที่ต้องการวิเคราะห์...",
      "language": "th"
    }
  }
}
```

#### Keyword Research (English)
```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "method": "tools/call",
  "params": {
    "name": "keyword_research",
    "arguments": {
      "seed_keyword": "digital marketing",
      "industry": "technology",
      "language": "en"
    }
  }
}
```

#### Keyword Research (Thai)
```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "method": "tools/call",
  "params": {
    "name": "keyword_research",
    "arguments": {
      "seed_keyword": "การตลาดดิจิทัล",
      "industry": "เทคโนโลยี",
      "language": "th"
    }
  }
}
```

### Prompt Usage

#### Blog Post Prompt (English)
```json
{
  "jsonrpc": "2.0",
  "id": 4,
  "method": "prompts/get",
  "params": {
    "name": "blog_post",
    "arguments": {
      "topic": "AI in Marketing",
      "industry": "technology",
      "language": "en"
    }
  }
}
```

#### Blog Post Prompt (Thai)
```json
{
  "jsonrpc": "2.0",
  "id": 4,
  "method": "prompts/get",
  "params": {
    "name": "blog_post",
    "arguments": {
      "topic": "AI ในการตลาด",
      "industry": "เทคโนโลยี",
      "language": "th"
    }
  }
}
```

### Resource Usage

#### Industry Data (English)
```json
{
  "jsonrpc": "2.0",
  "id": 5,
  "method": "resources/read",
  "params": {
    "uri": "industry://data/technology",
    "language": "en"
  }
}
```

#### Industry Data (Thai)
```json
{
  "jsonrpc": "2.0",
  "id": 5,
  "method": "resources/read",
  "params": {
    "uri": "industry://data/technology",
    "language": "th"
  }
}
```

## 🛠 Installation & Deployment

### 1. Deploy MCP Server to Vercel

#### Prerequisites
- Vercel account
- GitHub repository
- Redis database (Upstash recommended)

#### Step 1: Environment Variables
Set up the following environment variables in your Vercel dashboard:

```bash
REDIS_HOST=your-redis-host
REDIS_PORT=6379
REDIS_PASSWORD=your-redis-password
WORDPRESS_SECRET_KEY=your-secret-key-for-jwt
ALLOWED_WORDPRESS_DOMAINS=yourdomain.com,anotherdomain.com
```

#### Step 2: Deploy to Vercel
```bash
# Clone the repository
git clone https://github.com/khiwniti/SEOForge-mcp-server.git
cd SEOForge-mcp-server

# Deploy to Vercel
vercel --prod
```

#### Step 3: Configure Domain
After deployment, note your Vercel domain (e.g., `https://your-app.vercel.app`)

### 2. Install WordPress Plugin

#### Method 1: Manual Installation
1. Download the `wordpress-plugin` folder
2. Upload it to your WordPress `/wp-content/plugins/` directory
3. Rename the folder to `seoforge-mcp`
4. Activate the plugin in WordPress admin

#### Method 2: ZIP Installation
1. Create a ZIP file of the `wordpress-plugin` folder
2. Upload via WordPress admin → Plugins → Add New → Upload Plugin

### 3. Configure WordPress Plugin

1. Go to **SEOForge MCP → Settings** in WordPress admin
2. Set the **API URL** to your Vercel deployment URL
3. Generate and set an **API Key** for authentication
4. Choose your **Default Language** (English/Thai)
5. Configure other settings as needed

### 4. Local Development Setup

#### Backend Development
```bash
cd backend
pip install -r requirements.txt

# Set environment variables
export REDIS_HOST=localhost
export REDIS_PORT=6379
export WORDPRESS_SECRET_KEY=your-dev-secret

# Run the server
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

#### Frontend Development
```bash
cd frontend
npm install
npm start
```

#### WordPress Development
1. Set up a local WordPress installation
2. Copy the `wordpress-plugin` folder to `wp-content/plugins/`
3. Point the plugin to your local backend: `http://localhost:8000`

## 🔧 WordPress Plugin Features

### Admin Interface
- **Content Generator**: AI-powered content creation with SEO optimization
- **SEO Analysis**: Real-time analysis of posts and pages
- **Keyword Research**: Intelligent keyword suggestions
- **Dashboard**: Centralized control panel for all SEO tools

### Frontend Enhancements
- **Reading Time Calculator**: Automatic reading time estimation
- **Progress Bar**: Visual reading progress indicator
- **Table of Contents**: Auto-generated TOC for long content
- **Social Sharing**: Enhanced social media sharing buttons

### API Integration
- **REST API Endpoints**: `/wp-json/seoforge-mcp/v1/`
- **AJAX Handlers**: Real-time content generation and analysis
- **Webhook Support**: Automatic content optimization triggers

## 🔐 Security Features

### Authentication
- **WordPress Nonce Verification**: Secure request validation
- **JWT Token Authentication**: Stateless authentication for API calls
- **Rate Limiting**: 100 requests per hour per WordPress site
- **Domain Whitelisting**: Restrict access to authorized domains

### Data Protection
- **Encrypted Communication**: HTTPS-only API communication
- **Input Sanitization**: All user inputs are sanitized and validated
- **CORS Protection**: Configured for WordPress domain access only
- **Redis Session Management**: Secure session storage and tracking

## 🧪 Testing

### Test WordPress Plugin Locally

1. **Set up WordPress Playground**:
```bash
# Using WordPress Playground (recommended for testing)
npx @wp-playground/cli start --wp=6.4 --php=8.2
```

2. **Install and Test Plugin**:
   - Upload the plugin to the playground
   - Configure API settings
   - Test content generation and SEO analysis

### Test MCP Server Endpoints

```bash
# Health check
curl https://your-domain.vercel.app/mcp-server/health

# WordPress plugin health check
curl https://your-domain.vercel.app/wordpress/plugin/health

# Test MCP protocol
curl -X POST https://your-domain.vercel.app/mcp-server \
  -H "Content-Type: application/json" \
  -H "X-WordPress-Key: your-api-key" \
  -H "X-WordPress-Site: https://yoursite.com" \
  -H "X-WordPress-Nonce: generated-nonce" \
  -H "X-WordPress-Timestamp: $(date +%s)" \
  -d '{"jsonrpc":"2.0","method":"initialize","params":{},"id":1}'
```

## 🚀 Production Deployment Checklist

### Pre-Deployment
- [ ] Set up Redis database (Upstash recommended)
- [ ] Configure environment variables in Vercel
- [ ] Test all API endpoints locally
- [ ] Verify WordPress plugin functionality

### Deployment
- [ ] Deploy to Vercel using `vercel --prod`
- [ ] Verify all routes are working
- [ ] Test CORS configuration
- [ ] Validate rate limiting

### Post-Deployment
- [ ] Install WordPress plugin on target sites
- [ ] Configure API URL and authentication
- [ ] Test content generation and SEO analysis
- [ ] Monitor error logs and performance
- [ ] Set up monitoring and alerts

## 🔄 MCP Client Integration

### Claude Desktop Configuration

Add to your Claude Desktop configuration:

```json
{
  "mcpServers": {
    "seo-forge": {
      "command": "curl",
      "args": [
        "-X", "POST",
        "-H", "Content-Type: application/json",
        "-H", "X-WordPress-Key: your-api-key",
        "-H", "X-WordPress-Site: https://yoursite.com",
        "-H", "X-WordPress-Nonce: generated-nonce",
        "-H", "X-WordPress-Timestamp: $(date +%s)",
        "-d", "@-",
        "https://your-domain.vercel.app/mcp-server"
      ]
    }
  }
}
```

### Other MCP Clients
Configure your MCP client to connect to:
```
https://your-domain.vercel.app/mcp-server
```

## API Documentation

### Health Check
```
GET /mcp-server/health
```

Returns server status and information.

### MCP Protocol Endpoint
```
POST /mcp-server
```

Accepts MCP protocol JSON-RPC requests and returns appropriate responses.

## Error Handling

The server implements proper error handling for:
- Invalid JSON-RPC requests
- Unknown methods
- Missing required parameters
- Tool execution errors

All errors are returned in JSON-RPC error format:

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "error": {
    "code": -32603,
    "message": "Error description"
  }
}
```

## 📊 Monitoring & Analytics

### Performance Monitoring
- Monitor API response times
- Track rate limiting effectiveness
- Monitor Redis connection health
- Track WordPress plugin usage

### Error Handling
- Comprehensive error logging
- Graceful fallbacks for API failures
- User-friendly error messages
- Automatic retry mechanisms

## 🔧 Troubleshooting

### Common Issues

#### 1. Authentication Errors
```bash
# Check if nonce is properly generated
# Verify WordPress site URL matches configuration
# Ensure API key is correctly set
```

#### 2. CORS Issues
```bash
# Verify domain is in ALLOWED_WORDPRESS_DOMAINS
# Check if HTTPS is being used
# Validate request headers
```

#### 3. Rate Limiting
```bash
# Check current rate limit status
# Implement exponential backoff
# Consider upgrading rate limits for high-traffic sites
```

### Debug Mode
Enable debug mode in WordPress:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 🚀 Advanced Configuration

### Custom Content Types
Extend the plugin to support custom post types:
```php
add_filter('seoforge_mcp_post_types', function($post_types) {
    $post_types[] = 'your_custom_type';
    return $post_types;
});
```

### Custom Industries
Add custom industries for content generation:
```php
add_filter('seoforge_mcp_industries', function($industries) {
    $industries['custom_industry'] = 'Custom Industry Name';
    return $industries;
});
```

## 📈 Scaling Considerations

### High Traffic Sites
- Implement Redis clustering
- Use CDN for static assets
- Consider multiple Vercel regions
- Implement request queuing

### Enterprise Features
- Multi-site management
- Advanced analytics
- Custom AI model integration
- White-label solutions

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards for PHP
- Use ESLint for JavaScript
- Write comprehensive tests
- Update documentation

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation**: [GitHub Wiki](https://github.com/khiwniti/SEOForge-mcp-server/wiki)
- **Issues**: [GitHub Issues](https://github.com/khiwniti/SEOForge-mcp-server/issues)
- **Discussions**: [GitHub Discussions](https://github.com/khiwniti/SEOForge-mcp-server/discussions)

## 🎯 Roadmap

- [ ] Multi-language content generation
- [ ] Advanced SEO scoring algorithms
- [ ] Integration with popular SEO tools
- [ ] Mobile app for content management
- [ ] AI-powered content optimization
- [ ] Real-time collaboration features
