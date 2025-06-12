# 🚀 SEO Forge MCP Server - Usage Guide

## Quick Start with npm exec

The easiest way to use SEO Forge MCP Server is with `npm exec` or `npx`:

```bash
# Run directly without installation
npx seo-forge-mcp-server

# Or with npm exec
npm exec seo-forge-mcp-server

# With custom options
npx seo-forge-mcp-server --port 8080 --api-url https://your-api.com
```

## Installation Options

### 1. Direct Execution (Recommended)
```bash
# No installation required - runs latest version
npx seo-forge-mcp-server
```

### 2. Global Installation
```bash
# Install globally for system-wide access
npm install -g seo-forge-mcp-server

# Run from anywhere
seo-forge-mcp
# or
seoforge
```

### 3. Local Project Installation
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

## Configuration

### Environment Variables
Create a `.env` file or set environment variables:

```bash
# Required - Your SEO Forge API endpoint
API_BASE_URL=https://your-seo-forge-server.com

# Optional - AI API keys for enhanced features
GOOGLE_API_KEY=your_gemini_api_key
HUGGINGFACE_TOKEN=hf_your_token
REPLICATE_API_TOKEN=r8_your_token
TOGETHER_API_KEY=your_together_key

# Server settings
PORT=3000
HOST=0.0.0.0
LOG_LEVEL=info
```

### Command Line Options
```bash
# Basic usage
npx seo-forge-mcp-server

# Custom port
npx seo-forge-mcp-server --port 8080

# Custom host
npx seo-forge-mcp-server --host localhost

# Custom API URL
npx seo-forge-mcp-server --api-url https://your-api.com

# Multiple options
npx seo-forge-mcp-server --port 8080 --host localhost --api-url https://your-api.com

# Show help
npx seo-forge-mcp-server --help
```

## Claude Desktop Integration

### Step 1: Locate Configuration File

**macOS:**
```bash
~/Library/Application Support/Claude/claude_desktop_config.json
```

**Windows:**
```bash
%APPDATA%\Claude\claude_desktop_config.json
```

**Linux:**
```bash
~/.config/Claude/claude_desktop_config.json
```

### Step 2: Add MCP Server Configuration

Edit the configuration file:

```json
{
  "mcpServers": {
    "seo-forge": {
      "command": "npx",
      "args": ["seo-forge-mcp-server"],
      "env": {
        "API_BASE_URL": "https://your-seo-forge-server.com",
        "GOOGLE_API_KEY": "your_api_key_here",
        "LOG_LEVEL": "info"
      }
    }
  }
}
```

### Step 3: Restart Claude Desktop

After saving the configuration, restart Claude Desktop to load the MCP server.

## Available Tools

### 1. Content Generation
Generate SEO-optimized content with optional Flux images:

```typescript
generate_content({
  topic: "Digital Marketing Trends 2024",
  keywords: ["digital marketing", "SEO", "content marketing"],
  content_type: "blog_post",
  language: "en",
  tone: "professional",
  length: "long",
  include_images: true,
  image_count: 3,
  image_style: "professional"
})
```

### 2. Flux Image Generation
Create high-quality images using Flux AI models:

```typescript
generate_flux_image({
  prompt: "A modern office building at sunset with glass facades",
  model: "flux-dev",
  style: "professional",
  width: 1024,
  height: 1024,
  guidance_scale: 7.5,
  num_inference_steps: 20,
  enhance_prompt: true
})
```

### 3. Batch Image Generation
Generate multiple images simultaneously:

```typescript
generate_flux_batch({
  prompts: [
    "Professional business meeting",
    "Team collaboration workspace",
    "Digital marketing dashboard"
  ],
  model: "flux-schnell",
  style: "professional"
})
```

### 4. SEO Analysis
Analyze content for SEO optimization:

```typescript
analyze_seo({
  content: "Your blog post content here...",
  keywords: ["target keyword", "secondary keyword"],
  language: "en"
})
```

### 5. Keyword Research
Research keywords with search volume and difficulty:

```typescript
research_keywords({
  seed_keywords: ["digital marketing", "SEO"],
  language: "en",
  location: "global",
  limit: 50
})
```

### 6. Server Status
Get server status and capabilities:

```typescript
get_server_status()
get_flux_models()
```

## Usage Examples

### Example 1: Generate Thai Content with Images

```typescript
generate_content({
  topic: "กระดาษมวนขายส่ง กรุงเทพฯ",
  keywords: ["กระดาษมวน", "ขายส่ง", "กรุงเทพ"],
  content_type: "product_description",
  language: "th",
  tone: "professional",
  include_images: true,
  image_count: 2,
  image_style: "commercial"
})
```

### Example 2: Create Professional Images

```typescript
generate_flux_image({
  prompt: "Professional Thai business team in modern Bangkok office",
  model: "flux-dev",
  style: "professional",
  width: 1920,
  height: 1080,
  guidance_scale: 7.5,
  num_inference_steps: 25,
  enhance_prompt: true
})
```

### Example 3: SEO Analysis for Thai Content

```typescript
analyze_seo({
  content: "เนื้อหาบล็อกภาษาไทยของคุณ...",
  keywords: ["กระดาษมวน", "ขายส่ง"],
  language: "th"
})
```

## Troubleshooting

### Common Issues

#### 1. Server Won't Start
```bash
# Check Node.js version (requires 16+)
node --version

# Clear npm cache
npm cache clean --force

# Try with debug logging
LOG_LEVEL=debug npx seo-forge-mcp-server
```

#### 2. API Connection Issues
```bash
# Test API endpoint
curl https://your-seo-forge-server.com/universal-mcp/status

# Check environment variables
echo $API_BASE_URL

# Verify API URL format
npx seo-forge-mcp-server --api-url https://your-correct-api.com
```

#### 3. Claude Integration Issues
1. Verify configuration file location
2. Check JSON syntax
3. Restart Claude Desktop
4. Check Claude Desktop logs

#### 4. Permission Issues
```bash
# On macOS/Linux, ensure executable permissions
chmod +x ~/.npm/_npx/*/node_modules/seo-forge-mcp-server/dist/index.js
```

### Debug Mode
```bash
# Enable detailed logging
LOG_LEVEL=debug npx seo-forge-mcp-server

# Test with verbose npm output
npm exec --loglevel verbose seo-forge-mcp-server
```

## Performance Tips

### 1. Model Selection
- **flux-schnell**: Fast generation (5-15 seconds) - Use for previews
- **flux-dev**: High quality (30-90 seconds) - Use for final images
- **flux-pro**: Professional quality (45-120 seconds) - Use for commercial work

### 2. Optimization
- Use batch generation for multiple images
- Enable prompt enhancement for better results
- Configure appropriate timeout values
- Cache frequently used content

### 3. Resource Management
- Monitor API usage and costs
- Use appropriate image dimensions
- Optimize generation parameters

## Security Best Practices

### 1. API Key Management
```bash
# Use environment variables
export GOOGLE_API_KEY="your_key_here"

# Or use .env file (never commit to git)
echo "GOOGLE_API_KEY=your_key_here" >> .env
```

### 2. Network Security
- Use HTTPS for all API endpoints
- Implement rate limiting
- Monitor API usage

### 3. Access Control
- Restrict server access to trusted networks
- Use strong API keys
- Regularly rotate credentials

## Advanced Configuration

### Custom API Endpoint
```bash
# Point to your own SEO Forge server
npx seo-forge-mcp-server --api-url https://your-custom-api.com
```

### Multiple Instances
```bash
# Run multiple instances on different ports
npx seo-forge-mcp-server --port 3001 &
npx seo-forge-mcp-server --port 3002 &
```

### Docker Deployment
```dockerfile
FROM node:18-alpine
WORKDIR /app
RUN npm install -g seo-forge-mcp-server
EXPOSE 3000
CMD ["seo-forge-mcp-server"]
```

## Support and Resources

### Documentation
- **GitHub Repository**: https://github.com/khiwniti/SEOForge-mcp-server
- **Issue Tracker**: https://github.com/khiwniti/SEOForge-mcp-server/issues
- **NPM Package**: https://www.npmjs.com/package/seo-forge-mcp-server

### Getting Help
1. Check this usage guide
2. Review troubleshooting section
3. Search existing GitHub issues
4. Create a new issue with detailed information

### Contributing
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

---

## Quick Reference

```bash
# Install and run
npx seo-forge-mcp-server

# With options
npx seo-forge-mcp-server --port 8080 --api-url https://your-api.com

# Global install
npm install -g seo-forge-mcp-server

# Help
npx seo-forge-mcp-server --help

# Debug mode
LOG_LEVEL=debug npx seo-forge-mcp-server
```

**Ready to supercharge your AI workflows with SEO and image generation!** 🚀