# 🚀 SEO Forge MCP Server - Deployment Guide

## 📦 NPM Package Deployment

### Prerequisites
- Node.js 16+ installed
- npm account (for publishing)
- Git repository set up

### 1. Prepare for Publishing

```bash
# Clone the repository
git clone https://github.com/khiwniti/SEOForge-mcp-server.git
cd seo-forge-mcp-server

# Install dependencies
npm install

# Build the project
npm run build

# Run tests
npm test

# Lint code
npm run lint
```

### 2. Publish to NPM

```bash
# Login to npm (first time only)
npm login

# Publish the package
npm publish

# Or publish with tag
npm publish --tag beta
```

### 3. Verify Publication

```bash
# Check package info
npm info seo-forge-mcp-server

# Test installation
npx seo-forge-mcp-server --help
```

## 🌐 Public Usage

### Direct Execution (No Installation)

```bash
# Run latest version directly
npx seo-forge-mcp-server

# With custom options
npx seo-forge-mcp-server --port 8080 --api-url https://your-api.com

# Using npm exec
npm exec seo-forge-mcp-server

# With arguments
npm exec seo-forge-mcp-server -- --port 8080
```

### Global Installation

```bash
# Install globally
npm install -g seo-forge-mcp-server

# Run from anywhere
seo-forge-mcp
# or
seoforge

# Update to latest version
npm update -g seo-forge-mcp-server
```

### Local Project Installation

```bash
# Add to project
npm install seo-forge-mcp-server

# Use in package.json scripts
{
  "scripts": {
    "mcp-server": "seo-forge-mcp-server",
    "mcp-dev": "seo-forge-mcp-server --port 8080"
  }
}

# Run with npm
npm run mcp-server
```

## 🔧 Claude Desktop Integration

### Automatic Configuration

Create a setup script for users:

```bash
#!/bin/bash
# setup-claude.sh

# Detect OS and set config path
if [[ "$OSTYPE" == "darwin"* ]]; then
    CONFIG_PATH="$HOME/Library/Application Support/Claude/claude_desktop_config.json"
elif [[ "$OSTYPE" == "msys" || "$OSTYPE" == "win32" ]]; then
    CONFIG_PATH="$APPDATA/Claude/claude_desktop_config.json"
else
    CONFIG_PATH="$HOME/.config/Claude/claude_desktop_config.json"
fi

# Create config directory if it doesn't exist
mkdir -p "$(dirname "$CONFIG_PATH")"

# Add SEO Forge MCP server configuration
cat > "$CONFIG_PATH" << 'EOF'
{
  "mcpServers": {
    "seo-forge": {
      "command": "npx",
      "args": ["seo-forge-mcp-server"],
      "env": {
        "API_BASE_URL": "https://your-seo-forge-server.com",
        "LOG_LEVEL": "info"
      }
    }
  }
}
EOF

echo "Claude Desktop configuration updated!"
echo "Please restart Claude Desktop to load the MCP server."
```

### Manual Configuration

**macOS:**
```bash
# Edit configuration file
nano ~/Library/Application\ Support/Claude/claude_desktop_config.json
```

**Windows:**
```bash
# Edit configuration file
notepad %APPDATA%\Claude\claude_desktop_config.json
```

**Linux:**
```bash
# Edit configuration file
nano ~/.config/Claude/claude_desktop_config.json
```

Add this configuration:
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

## 🐳 Docker Deployment

### Dockerfile

```dockerfile
FROM node:18-alpine

# Set working directory
WORKDIR /app

# Install the MCP server globally
RUN npm install -g seo-forge-mcp-server

# Create non-root user
RUN addgroup -g 1001 -S nodejs
RUN adduser -S mcp -u 1001

# Switch to non-root user
USER mcp

# Expose port
EXPOSE 3000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD npx seo-forge-mcp-server --help || exit 1

# Start the server
CMD ["seo-forge-mcp-server"]
```

### Docker Compose

```yaml
version: '3.8'

services:
  seo-forge-mcp:
    build: .
    ports:
      - "3000:3000"
    environment:
      - API_BASE_URL=https://your-seo-forge-server.com
      - GOOGLE_API_KEY=${GOOGLE_API_KEY}
      - HUGGINGFACE_TOKEN=${HUGGINGFACE_TOKEN}
      - LOG_LEVEL=info
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "npx", "seo-forge-mcp-server", "--help"]
      interval: 30s
      timeout: 10s
      retries: 3
```

### Build and Run

```bash
# Build Docker image
docker build -t seo-forge-mcp-server .

# Run container
docker run -d \
  --name seo-forge-mcp \
  -p 3000:3000 \
  -e API_BASE_URL=https://your-api.com \
  seo-forge-mcp-server

# Using Docker Compose
docker-compose up -d
```

## ☁️ Cloud Deployment

### Heroku

```bash
# Create Heroku app
heroku create seo-forge-mcp-server

# Set environment variables
heroku config:set API_BASE_URL=https://your-api.com
heroku config:set GOOGLE_API_KEY=your_key

# Deploy
git push heroku main
```

### Railway

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login and deploy
railway login
railway init
railway up
```

### Vercel

```bash
# Install Vercel CLI
npm install -g vercel

# Deploy
vercel

# Set environment variables
vercel env add API_BASE_URL
```

## 🔄 CI/CD Pipeline

### GitHub Actions

```yaml
# .github/workflows/publish.yml
name: Publish to NPM

on:
  release:
    types: [published]

jobs:
  publish:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
          registry-url: 'https://registry.npmjs.org'
      
      - name: Install dependencies
        run: npm ci
      
      - name: Run tests
        run: npm test
      
      - name: Build
        run: npm run build
      
      - name: Publish to NPM
        run: npm publish
        env:
          NODE_AUTH_TOKEN: ${{ secrets.NPM_TOKEN }}
```

### Automated Testing

```yaml
# .github/workflows/test.yml
name: Test

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        node-version: [16, 18, 20]
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node.js ${{ matrix.node-version }}
        uses: actions/setup-node@v3
        with:
          node-version: ${{ matrix.node-version }}
      
      - name: Install dependencies
        run: npm ci
      
      - name: Run tests
        run: npm test
      
      - name: Run linting
        run: npm run lint
      
      - name: Test build
        run: npm run build
      
      - name: Test CLI
        run: npx . --help
```

## 📊 Monitoring and Analytics

### Usage Tracking

```typescript
// Add to your MCP server
import { Analytics } from './analytics';

const analytics = new Analytics({
  trackingId: 'your-tracking-id'
});

// Track tool usage
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  analytics.track('tool_used', {
    tool: request.params.name,
    timestamp: new Date().toISOString()
  });
  
  // ... rest of handler
});
```

### Health Monitoring

```bash
# Add health check endpoint
curl https://your-mcp-server.com/health

# Monitor with uptime services
# - UptimeRobot
# - Pingdom
# - StatusCake
```

## 🔐 Security Considerations

### API Key Management

```bash
# Use environment variables
export GOOGLE_API_KEY="your_key_here"

# Or use secrets management
# - AWS Secrets Manager
# - Azure Key Vault
# - HashiCorp Vault
```

### Rate Limiting

```typescript
// Add rate limiting
import { RateLimiterMemory } from 'rate-limiter-flexible';

const rateLimiter = new RateLimiterMemory({
  keyGenerator: (req) => req.ip,
  points: 100, // Number of requests
  duration: 60, // Per 60 seconds
});
```

### Input Validation

```typescript
// Validate all inputs
import { z } from 'zod';

const InputSchema = z.object({
  prompt: z.string().min(1).max(1000),
  model: z.enum(['flux-schnell', 'flux-dev', 'flux-pro'])
});
```

## 📈 Performance Optimization

### Caching

```typescript
// Add Redis caching
import Redis from 'ioredis';

const redis = new Redis(process.env.REDIS_URL);

// Cache responses
const cacheKey = `response:${JSON.stringify(params)}`;
const cached = await redis.get(cacheKey);
if (cached) {
  return JSON.parse(cached);
}
```

### Load Balancing

```nginx
# nginx.conf
upstream mcp_servers {
    server localhost:3000;
    server localhost:3001;
    server localhost:3002;
}

server {
    listen 80;
    location / {
        proxy_pass http://mcp_servers;
    }
}
```

## 📚 Documentation

### API Documentation

```bash
# Generate API docs
npm run docs

# Serve documentation
npm run docs:serve
```

### User Guides

- **Installation Guide**: Step-by-step setup
- **Usage Examples**: Common use cases
- **Troubleshooting**: Common issues and solutions
- **API Reference**: Complete tool documentation

## 🎯 Distribution Checklist

- [ ] Package builds successfully
- [ ] All tests pass
- [ ] Documentation is complete
- [ ] CLI works with npx
- [ ] Claude Desktop integration tested
- [ ] Environment variables documented
- [ ] Security review completed
- [ ] Performance benchmarks done
- [ ] CI/CD pipeline configured
- [ ] Monitoring set up

## 🚀 Launch Strategy

### 1. Soft Launch
- Publish to npm with beta tag
- Test with limited users
- Gather feedback and iterate

### 2. Public Release
- Publish stable version
- Announce on social media
- Submit to relevant directories
- Create demo videos

### 3. Community Building
- Create GitHub discussions
- Set up Discord/Slack
- Write blog posts
- Engage with MCP community

---

## Quick Commands

```bash
# Publish to npm
npm publish

# Test installation
npx seo-forge-mcp-server --help

# Update version
npm version patch
npm version minor
npm version major

# Check package info
npm info seo-forge-mcp-server
```

**Ready to deploy your MCP server to the world!** 🌍