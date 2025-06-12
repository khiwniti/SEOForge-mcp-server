# 🚀 SEOForge CLI

**AI-powered content generation, SEO analysis, and chatbot CLI tool**

Connect to the SEOForge Universal MCP Server for instant AI-powered content creation, SEO optimization, and intelligent chatbot interactions.

## 🌟 Features

- ✅ **AI Content Generation** - Create high-quality articles, blog posts, and marketing content
- ✅ **SEO Analysis** - Comprehensive content optimization with actionable recommendations  
- ✅ **AI Chatbot** - Intelligent conversational AI with website context awareness
- ✅ **Image Generation** - Professional AI-generated illustrations and graphics
- ✅ **Blog Creation** - Complete blog posts with integrated images
- ✅ **Multi-language Support** - English, Thai, Spanish, French, German, and more
- ✅ **Interactive Mode** - Guided CLI experience for easy usage

## 🚀 Quick Start

### Using npx (Recommended)

```bash
# Check server status
npx seo-forge status

# Generate content
npx seo-forge generate -t "AI Technology" -k "AI,tech,innovation"

# Analyze SEO
npx seo-forge analyze -c "Your content here" -k "seo,optimization"

# Chat with AI
npx seo-forge chat -m "What is SEO?"

# Generate image
npx seo-forge image -p "professional business illustration"

# Interactive mode
npx seo-forge interactive
```

### Global Installation

```bash
npm install -g seo-forge
seo-forge --help
```

## 📋 Commands

### Status Check
```bash
npx seo-forge status
# Check if the MCP server is online and view server information
```

### Content Generation
```bash
npx seo-forge generate -t "Your Topic" [options]

Options:
  -t, --topic <topic>      Content topic (required)
  -k, --keywords <list>    Keywords (comma-separated)
  -l, --language <lang>    Language (en, th, es, fr, de)
  --tone <tone>           Content tone (professional, casual, formal)
  --length <length>       Content length (short, medium, long)
  --type <type>           Content type (blog_post, article, marketing)
  -s, --server <url>      Custom server URL
```

**Example:**
```bash
npx seo-forge generate \
  -t "Digital Marketing Trends 2024" \
  -k "digital marketing,trends,2024,SEO" \
  -l "en" \
  --tone "professional" \
  --length "long"
```

### SEO Analysis
```bash
npx seo-forge analyze -c "Content to analyze" [options]

Options:
  -c, --content <text>     Content to analyze (required)
  -k, --keywords <list>    Target keywords (comma-separated)
  -l, --language <lang>    Content language
  -s, --server <url>       Custom server URL
```

**Example:**
```bash
npx seo-forge analyze \
  -c "Your website content here..." \
  -k "SEO,optimization,ranking" \
  -l "en"
```

### AI Chat
```bash
npx seo-forge chat -m "Your message" [options]

Options:
  -m, --message <text>     Message to send (required)
  -w, --website <url>      Website URL for context
  -s, --server <url>       Custom server URL
```

**Example:**
```bash
npx seo-forge chat \
  -m "How can I improve my website's SEO?" \
  -w "https://example.com"
```

### Image Generation
```bash
npx seo-forge image -p "Image prompt" [options]

Options:
  -p, --prompt <text>      Image description (required)
  --style <style>         Image style (professional, artistic, modern)
  --size <size>           Image size (1024x1024, 512x512)
  -s, --server <url>      Custom server URL
```

**Example:**
```bash
npx seo-forge image \
  -p "professional business team meeting" \
  --style "professional" \
  --size "1024x1024"
```

### Blog Post Creation
```bash
npx seo-forge blog -t "Blog topic" [options]

Options:
  -t, --topic <topic>      Blog topic (required)
  -k, --keywords <list>    Keywords (comma-separated)
  -l, --language <lang>    Language
  --images <count>        Number of images to generate
  -s, --server <url>      Custom server URL
```

**Example:**
```bash
npx seo-forge blog \
  -t "Complete Guide to WordPress SEO" \
  -k "WordPress,SEO,optimization,guide" \
  --images 3
```

### Interactive Mode
```bash
npx seo-forge interactive
# Guided CLI experience with menu-driven interface
```

## 🔧 Programmatic Usage

You can also use SEOForge in your Node.js applications:

```javascript
const { SEOForgeClient } = require('seo-forge');

const client = new SEOForgeClient();

// Generate content
const content = await client.generateContent('https://your-server.com', {
  topic: 'AI Technology',
  keywords: ['AI', 'technology'],
  language: 'en'
});

// Analyze SEO
const analysis = await client.analyzeSEO('https://your-server.com', {
  content: 'Your content here...',
  keywords: ['seo', 'optimization']
});

// Chat with AI
const response = await client.chat('https://your-server.com', {
  message: 'Hello, how can you help me?',
  website_url: 'https://example.com'
});
```

## 🌐 Server Information

**Default Server:** `https://seoforge-mcp-server.onrender.com`

The SEOForge Universal MCP Server is hosted on Render.com with:
- ✅ 99.9% uptime guarantee
- ✅ Global CDN for fast responses
- ✅ Automatic scaling
- ✅ Enterprise-grade security

### Custom Server

You can use your own MCP server instance:

```bash
npx seo-forge status --server "https://your-server.com"
npx seo-forge generate -t "Topic" --server "https://your-server.com"
```

## 📊 Output Examples

### Content Generation Output
```
✅ Content generated successfully!

📝 Generated Content:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Title: The Future of AI Technology: Trends and Innovations

The artificial intelligence landscape is rapidly evolving...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 Stats: 847 words, 4 min read
🏷️ Keywords: AI, technology, innovation, future
📄 Meta: Explore the latest trends in AI technology and discover...
```

### SEO Analysis Output
```
✅ SEO analysis completed!

📈 SEO Analysis Results:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
SEO Score: 85/100
Word Count: 1,247
Readability: 78/100

🏷️ Keyword Analysis:
   SEO: 2.4%
   optimization: 1.8%
   ranking: 1.2%

💡 Recommendations:
   1. Add more internal links to related content
   2. Optimize meta description length
   3. Include more semantic keywords
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

## 🔧 Configuration

### Environment Variables

```bash
export SEOFORGE_SERVER_URL="https://your-server.com"
export SEOFORGE_API_KEY="your-api-key"  # If required
```

### Config File

Create `~/.seoforge.json`:

```json
{
  "serverUrl": "https://your-server.com",
  "apiKey": "your-api-key",
  "defaultLanguage": "en",
  "defaultTone": "professional"
}
```

## 🚀 Advanced Usage

### Batch Processing

```bash
# Generate multiple content pieces
for topic in "AI" "SEO" "Marketing"; do
  npx seo-forge generate -t "$topic" -k "$topic,guide,2024"
done

# Analyze multiple files
find . -name "*.txt" -exec npx seo-forge analyze -c "{}" \;
```

### Pipeline Integration

```bash
# Use in CI/CD pipelines
npx seo-forge status || exit 1
npx seo-forge generate -t "Release Notes" > release-content.md
```

### Custom Scripts

```javascript
#!/usr/bin/env node
const { generateContent, analyzeSEO } = require('seo-forge');

async function createOptimizedContent(topic, keywords) {
  // Generate content
  const content = await generateContent({
    topic,
    keywords,
    language: 'en'
  });
  
  // Analyze and optimize
  const analysis = await analyzeSEO({
    content: content.content.body,
    keywords
  });
  
  console.log('Content:', content.content.title);
  console.log('SEO Score:', analysis.analysis.seo_score);
}

createOptimizedContent('AI Technology', ['AI', 'tech']);
```

## 🆘 Troubleshooting

### Common Issues

**Server Connection Failed**
```bash
# Check server status
npx seo-forge status

# Try with different server
npx seo-forge status --server "https://backup-server.com"
```

**Timeout Errors**
```bash
# The server might be processing a large request
# Wait a moment and try again, or use shorter content
```

**Permission Errors**
```bash
# Use npx instead of global installation
npx seo-forge --help

# Or install globally with proper permissions
sudo npm install -g seo-forge
```

### Debug Mode

```bash
DEBUG=seo-forge* npx seo-forge generate -t "Test"
```

## 📚 API Reference

### SEOForgeClient Class

```javascript
const client = new SEOForgeClient('https://server-url.com');

// Methods
await client.getStatus()
await client.generateContent(serverUrl, options)
await client.analyzeSEO(serverUrl, options)
await client.chat(serverUrl, options)
await client.generateImage(serverUrl, options)
await client.generateBlog(serverUrl, options)
await client.testAllEndpoints(serverUrl)
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add amazing feature'`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

## 📄 License

MIT License - see [LICENSE](LICENSE) file for details.

## 🔗 Links

- **GitHub Repository:** https://github.com/khiwniti/SEOForge-mcp-server
- **Live API Server:** https://seoforge-mcp-server.onrender.com
- **Documentation:** Available in repository
- **Issues:** https://github.com/khiwniti/SEOForge-mcp-server/issues

## 🎉 Examples Gallery

### Marketing Content
```bash
npx seo-forge generate \
  -t "Email Marketing Best Practices" \
  -k "email,marketing,conversion,engagement" \
  --tone "professional" \
  --length "long"
```

### Technical Documentation
```bash
npx seo-forge generate \
  -t "API Integration Guide" \
  -k "API,integration,development,guide" \
  --tone "technical" \
  --type "documentation"
```

### Social Media Content
```bash
npx seo-forge generate \
  -t "Social Media Trends 2024" \
  -k "social media,trends,engagement" \
  --tone "casual" \
  --length "medium"
```

---

**Made with ❤️ by the SEOForge Team**

*Empowering content creators with AI-powered tools for better SEO and engagement.*