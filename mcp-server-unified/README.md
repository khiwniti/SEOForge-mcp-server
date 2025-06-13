# 🚀 SEO Forge MCP Server - Unified Platform

A comprehensive Model Context Protocol (MCP) server that consolidates all SEO Forge backend functionality into a single, deployable platform optimized for Vercel.

## ✨ Features

### 🎯 Content Generation
- Multi-model AI content generation (GPT-4, Claude, Gemini)
- SEO-optimized blog posts, product descriptions, meta content
- Real-time content analysis and optimization
- Cannabis industry specialization

### 📊 SEO Analysis
- Comprehensive website SEO analysis
- Competitor analysis and insights
- Technical SEO recommendations
- Performance scoring and optimization tips

### 🎨 Image Generation
- AI-powered image generation (Flux, DALL-E, Midjourney)
- Cannabis product photography
- Multiple styles and formats
- Professional quality outputs

### 🌏 Thai Language Support
- Professional Thai translation services
- Cultural adaptation and localization
- Cannabis terminology expertise
- Thai market optimization

### 🔍 Keyword Research
- Advanced keyword research and analysis
- Cannabis industry keyword database
- Thai market keyword optimization
- Competition analysis

### 📝 WordPress Integration
- Seamless WordPress content synchronization
- WooCommerce product management
- Bulk content optimization
- Real-time updates

## 🚀 Quick Start

### 1. Clone and Setup

```bash
git clone <repository-url>
cd mcp-server-unified
npm install
```

### 2. Environment Configuration

```bash
cp .env.example .env
# Edit .env with your API keys
```

### 3. Local Development

```bash
npm run dev
```

### 4. Deploy to Vercel

```bash
npm run deploy
```

## 🔧 Configuration

### Required Environment Variables

```env
# AI API Keys (at least one required)
GOOGLE_API_KEY=your_google_api_key
OPENAI_API_KEY=your_openai_api_key
ANTHROPIC_API_KEY=your_anthropic_api_key
REPLICATE_API_TOKEN=your_replicate_token

# Authentication
JWT_SECRET=your_secure_jwt_secret
DEFAULT_ADMIN_EMAIL=admin@yourdomain.com
DEFAULT_ADMIN_PASSWORD=secure_password
```

### Optional Environment Variables

```env
# Database (for production scaling)
REDIS_URL=redis://localhost:6379
DATABASE_URL=postgresql://user:pass@host:5432/db

# Rate Limiting
RATE_LIMIT_REQUESTS=1000
RATE_LIMIT_WINDOW=3600
```

## 📡 API Endpoints

### Health Check
```
GET /health
```

### MCP Server
```
POST /mcp/tools/list
POST /mcp/tools/execute
```

### Authentication
```
POST /mcp/auth/login
POST /mcp/auth/register
```

### Client Interface
```
GET /client
```

## 🛠️ Available Tools

### Content Generation
```json
{
  "tool": "generate_content",
  "arguments": {
    "type": "blog|product|category|meta",
    "topic": "Your content topic",
    "keywords": ["keyword1", "keyword2"],
    "language": "en|th",
    "tone": "professional|casual|persuasive",
    "length": "short|medium|long"
  }
}
```

### SEO Analysis
```json
{
  "tool": "analyze_seo",
  "arguments": {
    "url": "https://example.com",
    "keywords": ["target", "keywords"],
    "competitors": ["https://competitor1.com"]
  }
}
```

### Image Generation
```json
{
  "tool": "generate_image",
  "arguments": {
    "prompt": "Image description",
    "style": "realistic|artistic|minimalist",
    "size": "1024x1024|1024x768|512x512",
    "model": "flux|dalle|midjourney"
  }
}
```

### Thai Translation
```json
{
  "tool": "translate_thai",
  "arguments": {
    "text": "Text to translate",
    "source_language": "en",
    "target_language": "th",
    "cultural_adaptation": true
  }
}
```

### Keyword Research
```json
{
  "tool": "research_keywords",
  "arguments": {
    "seed_keywords": ["cannabis", "bong"],
    "market": "thailand|global",
    "industry": "cannabis",
    "competition_level": "low|medium|high"
  }
}
```

### WordPress Sync
```json
{
  "tool": "wordpress_sync",
  "arguments": {
    "site_url": "https://yoursite.com",
    "action": "create|update|delete",
    "content_type": "post|page|product",
    "content": {...},
    "auth_token": "wp_auth_token"
  }
}
```

## 🔐 Authentication

### API Key Authentication
```bash
curl -H "X-API-Key: your_api_key" \
     -H "Content-Type: application/json" \
     -d '{"tool": "generate_content", "arguments": {...}}' \
     https://your-deployment.vercel.app/mcp/tools/execute
```

### JWT Token Authentication
```bash
curl -H "Authorization: Bearer your_jwt_token" \
     -H "Content-Type: application/json" \
     -d '{"tool": "analyze_seo", "arguments": {...}}' \
     https://your-deployment.vercel.app/mcp/tools/execute
```

## 🚀 Deployment

### Vercel Deployment

1. **Install Vercel CLI**
```bash
npm install -g vercel
```

2. **Login to Vercel**
```bash
vercel login
```

3. **Deploy**
```bash
vercel --prod
```

4. **Set Environment Variables**
```bash
vercel env add GOOGLE_API_KEY
vercel env add OPENAI_API_KEY
# ... add other required variables
```

### Environment Variables in Vercel Dashboard

1. Go to your Vercel project dashboard
2. Navigate to Settings → Environment Variables
3. Add all required environment variables
4. Redeploy the project

## 🔧 WordPress Plugin Integration

### Plugin Setup

1. Install the SEO Forge WordPress plugin
2. Configure the MCP server URL in plugin settings
3. Add authentication credentials
4. Test the connection

### Example WordPress Integration

```php
// WordPress plugin code
$mcp_client = new SEOForge_MCP_Client();
$result = $mcp_client->generate_content([
    'type' => 'product',
    'topic' => 'Glass Water Pipe',
    'keywords' => ['glass bong', 'water pipe'],
    'language' => 'en'
]);
```

## 📊 Monitoring and Analytics

### Health Monitoring
```bash
curl https://your-deployment.vercel.app/health
```

### Usage Analytics
- Request counts and response times
- Error rates and success metrics
- Cache hit rates and performance
- API usage by tool and user

## 🛡️ Security Features

- JWT-based authentication
- API key management
- Rate limiting per IP/user
- CORS protection
- Input validation and sanitization
- Secure environment variable handling

## 🔄 Caching Strategy

- **Content Generation**: 2 hours TTL
- **SEO Analysis**: 1 hour TTL
- **Keyword Research**: 24 hours TTL
- **Translations**: 24 hours TTL
- **Images**: 7 days TTL

## 📈 Performance Optimization

- Serverless function optimization
- Intelligent caching layers
- Request deduplication
- Lazy service initialization
- Memory-efficient processing

## 🐛 Troubleshooting

### Common Issues

1. **API Key Errors**
   - Verify environment variables are set
   - Check API key validity and quotas

2. **Rate Limiting**
   - Implement proper retry logic
   - Monitor usage patterns

3. **Timeout Issues**
   - Increase Vercel function timeout
   - Optimize AI model requests

### Debug Mode

```env
LOG_LEVEL=debug
NODE_ENV=development
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

MIT License - see LICENSE file for details

## 🆘 Support

- Documentation: [Link to docs]
- Issues: [GitHub Issues]
- Email: support@seoforge.dev

---

Built with ❤️ for the cannabis industry and SEO professionals worldwide.
