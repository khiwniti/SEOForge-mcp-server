# 🎨 SEO Forge MCP Server

[![npm version](https://badge.fury.io/js/seo-forge-mcp-server.svg)](https://badge.fury.io/js/seo-forge-mcp-server)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Node.js CI](https://github.com/khiwniti/SEOForge-mcp-server/workflows/Node.js%20CI/badge.svg)](https://github.com/khiwniti/SEOForge-mcp-server/actions)

**Universal SEO MCP Server with Flux Image Generation** - AI-powered content generation, SEO analysis, and state-of-the-art image creation for Claude and other MCP-compatible clients.

## 🚀 Quick Start

### Install and Run with npm exec (Recommended)

```bash
# Run directly without installation
npx seo-forge-mcp-server

# Or with npm exec
npm exec seo-forge-mcp-server

# With custom options
npx seo-forge-mcp-server --port 8080 --host localhost
```

### Global Installation

```bash
# Install globally
npm install -g seo-forge-mcp-server

# Run from anywhere
seo-forge-mcp
# or
seoforge
```

### Local Installation

```bash
# Install in your project
npm install seo-forge-mcp-server

# Run with npx
npx seo-forge-mcp-server

# Or add to package.json scripts
{
  "scripts": {
    "mcp-server": "seo-forge-mcp-server"
  }
}
```

## 🌟 Features

### 🎨 Flux Image Generation
- **Multiple Flux Models**: flux-schnell (fast), flux-dev (high quality), flux-pro (professional)
- **AI Prompt Enhancement**: Automatically improve prompts for better results
- **Batch Generation**: Create multiple images simultaneously
- **9 Professional Styles**: Professional, Artistic, Photorealistic, Minimalist, etc.
- **Configurable Parameters**: Full control over generation settings

### 🤖 AI Content Generation
- **SEO-Optimized Content**: Blog posts, product descriptions, landing pages
- **11 Languages**: Including enhanced Thai language support
- **Industry-Specific**: Tailored content for different sectors
- **Multiple Tones**: Professional, casual, friendly, authoritative
- **Integrated Images**: Generate content with relevant Flux images

### 🔍 Advanced SEO Tools
- **Comprehensive Analysis**: SEO scoring with actionable recommendations
- **Keyword Research**: Search volume, difficulty, and competition data
- **Multi-Language Support**: SEO analysis in multiple languages
- **Real-time Optimization**: Live SEO scoring and suggestions

### 🇹🇭 Enhanced Thai Support
- **Native Thai Processing**: Cultural context awareness
- **Thai SEO Optimization**: Local keyword research and analysis
- **Thai Image Generation**: Proper text rendering and cultural relevance

## 📋 Available Tools

### Content Generation
```typescript
generate_content({
  topic?: string,
  keywords?: string[],
  content_type?: 'blog_post' | 'product_description' | 'landing_page' | 'how_to_guide' | 'news_article',
  language?: 'en' | 'th' | 'es' | 'fr' | 'de' | 'it' | 'pt' | 'ru' | 'ja' | 'ko' | 'zh',
  tone?: 'professional' | 'casual' | 'friendly' | 'authoritative' | 'conversational',
  length?: 'short' | 'medium' | 'long',
  industry?: string,
  include_images?: boolean,
  image_count?: number,
  image_style?: string
})
```

### Flux Image Generation
```typescript
generate_flux_image({
  prompt: string,
  negative_prompt?: string,
  width?: number,        // 256-2048
  height?: number,       // 256-2048
  guidance_scale?: number, // 1.0-20.0
  num_inference_steps?: number, // 1-50
  seed?: number,
  model?: 'flux-schnell' | 'flux-dev' | 'flux-pro',
  style?: 'professional' | 'artistic' | 'photorealistic' | 'minimalist' | 'commercial' | 'cinematic',
  enhance_prompt?: boolean
})
```

### Batch Image Generation
```typescript
generate_flux_batch({
  prompts: string[],     // Max 10 prompts
  model?: 'flux-schnell' | 'flux-dev' | 'flux-pro',
  style?: string,
  width?: number,
  height?: number
})
```

### SEO Analysis
```typescript
analyze_seo({
  content: string,
  keywords?: string[],
  language?: 'en' | 'th' | 'es' | 'fr' | 'de',
  url?: string
})
```

### Keyword Research
```typescript
research_keywords({
  seed_keywords: string[],
  language?: 'en' | 'th' | 'es' | 'fr' | 'de',
  location?: string,
  limit?: number         // 1-100
})
```

### Server Status
```typescript
get_server_status()    // Get server status and capabilities
get_flux_models()      // Get available Flux models info
```

## ⚙️ Configuration

### Environment Variables

```bash
# Server Configuration
PORT=3000                    # Server port
HOST=0.0.0.0                # Server host
API_BASE_URL=https://your-seo-forge-server.com  # Your SEO Forge API endpoint

# AI API Keys (Optional - for enhanced features)
GOOGLE_API_KEY=your_gemini_key
HUGGINGFACE_TOKEN=hf_your_token
REPLICATE_API_TOKEN=r8_your_token
TOGETHER_API_KEY=your_together_key

# Logging
LOG_LEVEL=info              # debug, info, warn, error
```

### Command Line Options

```bash
# Basic usage
npx seo-forge-mcp-server

# Custom port and host
npx seo-forge-mcp-server --port 8080 --host localhost

# Custom API URL
npx seo-forge-mcp-server --api-url https://your-api.com

# Show help
npx seo-forge-mcp-server --help
```

## 🔧 Claude Desktop Integration

Add to your Claude Desktop configuration:

### macOS
Edit `~/Library/Application Support/Claude/claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "seo-forge": {
      "command": "npx",
      "args": ["seo-forge-mcp-server"],
      "env": {
        "API_BASE_URL": "https://your-seo-forge-server.com",
        "GOOGLE_API_KEY": "your_api_key_here"
      }
    }
  }
}
```

### Windows
Edit `%APPDATA%\Claude\claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "seo-forge": {
      "command": "npx",
      "args": ["seo-forge-mcp-server"],
      "env": {
        "API_BASE_URL": "https://your-seo-forge-server.com"
      }
    }
  }
}
```

## 🎯 Usage Examples

### Generate SEO Blog Post with Images

```typescript
// In Claude or MCP client
generate_content({
  topic: "Digital Marketing Trends 2024",
  keywords: ["digital marketing", "SEO trends", "content marketing"],
  content_type: "blog_post",
  language: "en",
  tone: "professional",
  length: "long",
  include_images: true,
  image_count: 3,
  image_style: "professional"
})
```

### Create High-Quality Flux Images

```typescript
generate_flux_image({
  prompt: "A modern office building at sunset with glass facades reflecting the sky",
  model: "flux-dev",
  style: "professional",
  width: 1024,
  height: 1024,
  guidance_scale: 7.5,
  num_inference_steps: 20,
  enhance_prompt: true
})
```

### Thai Content Generation

```typescript
generate_content({
  topic: "กระดาษมวนขายส่ง กรุงเทพฯ",
  keywords: ["กระดาษมวน", "ขายส่ง", "กรุงเทพ"],
  content_type: "product_description",
  language: "th",
  tone: "professional",
  include_images: true,
  image_style: "commercial"
})
```

### Batch Image Generation

```typescript
generate_flux_batch({
  prompts: [
    "Professional business meeting in modern office",
    "Team collaboration in creative workspace",
    "Digital marketing dashboard on computer screen"
  ],
  model: "flux-schnell",
  style: "professional"
})
```

### SEO Analysis

```typescript
analyze_seo({
  content: "Your blog post content here...",
  keywords: ["target keyword", "secondary keyword"],
  language: "en"
})
```

## 🚀 Development

### Setup Development Environment

```bash
# Clone the repository
git clone https://github.com/khiwniti/SEOForge-mcp-server.git
cd seo-forge-mcp-server

# Install dependencies
npm install

# Set up environment variables
cp .env.example .env
# Edit .env with your configuration

# Build the project
npm run build

# Run in development mode
npm run dev

# Run tests
npm test

# Lint code
npm run lint

# Format code
npm run format
```

### Project Structure

```
seo-forge-mcp-server/
├── src/
│   ├── index.ts          # Main MCP server implementation
│   ├── types/            # TypeScript type definitions
│   ├── utils/            # Utility functions
│   └── tests/            # Test files
├── dist/                 # Compiled JavaScript output
├── package.json          # NPM package configuration
├── tsconfig.json         # TypeScript configuration
├── .eslintrc.js         # ESLint configuration
├── .prettierrc          # Prettier configuration
└── README.md            # This file
```

### Building and Publishing

```bash
# Build for production
npm run build

# Test the build
npm run start

# Publish to npm (maintainers only)
npm publish
```

## 📊 Performance

### Benchmarks
- **Content Generation**: ~2-5 seconds
- **Flux Image Generation**: 
  - flux-schnell: 5-15 seconds
  - flux-dev: 30-90 seconds
  - flux-pro: 45-120 seconds
- **SEO Analysis**: ~1-3 seconds
- **Keyword Research**: ~2-5 seconds

### Optimization Tips
- Use `flux-schnell` for quick previews
- Use `flux-dev` for final high-quality images
- Enable prompt enhancement for better results
- Use batch generation for multiple images
- Configure appropriate timeout values

## 🔐 Security

### API Security
- All API communications use HTTPS
- API keys are stored securely in environment variables
- Input validation using Zod schemas
- Rate limiting and timeout protection

### Best Practices
- Never commit API keys to version control
- Use environment variables for sensitive configuration
- Regularly rotate API keys
- Monitor API usage and costs

## 🐛 Troubleshooting

### Common Issues

#### Server Won't Start
```bash
# Check Node.js version (requires 16+)
node --version

# Check npm version
npm --version

# Clear npm cache
npm cache clean --force

# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install
```

#### API Connection Issues
```bash
# Test API endpoint
curl https://your-seo-forge-server.com/universal-mcp/status

# Check environment variables
echo $API_BASE_URL

# Enable debug logging
LOG_LEVEL=debug npx seo-forge-mcp-server
```

#### Claude Integration Issues
1. Verify Claude Desktop configuration file location
2. Check JSON syntax in configuration
3. Restart Claude Desktop after configuration changes
4. Check Claude Desktop logs for errors

### Debug Mode

```bash
# Enable detailed logging
LOG_LEVEL=debug npx seo-forge-mcp-server

# Test specific tools
npx seo-forge-mcp-server --help
```

## 📞 Support

### Getting Help
- **GitHub Issues**: [Report bugs and request features](https://github.com/khiwniti/SEOForge-mcp-server/issues)
- **Documentation**: Comprehensive guides in this README
- **Examples**: Check the examples section above

### Contributing
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

### Reporting Issues
When reporting issues, please include:
- Node.js and npm versions
- Operating system
- Complete error messages
- Steps to reproduce
- Configuration (without sensitive data)

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🏆 Credits

### Development Team
- **Core Development**: SEO Forge Team
- **MCP Integration**: Advanced AI Systems
- **Thai Localization**: Native Thai Language Experts

### Technologies Used
- **MCP SDK**: Model Context Protocol implementation
- **Flux Models**: Black Forest Labs
- **AI APIs**: Google Gemini, Hugging Face, Replicate, Together AI
- **TypeScript**: Type-safe development
- **Node.js**: Runtime environment

## 🔮 Roadmap

### Upcoming Features
- **Enhanced MCP Tools**: More specialized SEO and content tools
- **Custom Model Support**: Integration with custom AI models
- **Advanced Analytics**: Detailed performance metrics
- **Multi-tenant Support**: Support for multiple API configurations
- **Plugin System**: Extensible architecture for custom tools

### Version History
- **v1.2.0**: Flux image generation integration, enhanced Thai support
- **v1.1.0**: MCP server implementation, basic tools
- **v1.0.0**: Initial release

---

## 🎯 Quick Commands Reference

```bash
# Install and run
npx seo-forge-mcp-server

# With options
npx seo-forge-mcp-server --port 8080 --host localhost

# Global install
npm install -g seo-forge-mcp-server

# Development
git clone https://github.com/khiwniti/SEOForge-mcp-server.git
cd seo-forge-mcp-server
npm install
npm run dev

# Help
npx seo-forge-mcp-server --help
```

**Transform your AI workflows with powerful SEO and image generation capabilities!** 🚀

---

*For the latest updates and documentation, visit [https://github.com/khiwniti/SEOForge-mcp-server](https://github.com/khiwniti/SEOForge-mcp-server)*