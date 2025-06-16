# SEOForge MCP Server - Clean Vercel Deployment Guide

## 🎯 Overview
This guide provides step-by-step instructions for deploying the SEOForge MCP Server to Vercel with a clean, optimized configuration.

## 📋 Prerequisites
- Vercel account
- GitHub repository access
- Node.js 18+ locally (for testing)

## 🚀 Deployment Steps

### 1. Repository Preparation
The repository has been cleaned and optimized for Vercel deployment:

✅ **Cleaned Structure:**
- Only `backend-express/` is used for deployment
- Unnecessary directories are ignored via `.vercelignore`
- Build artifacts are properly configured

✅ **Configuration Files:**
- `vercel.json` - Optimized for Node.js deployment
- `package.json` - Updated with correct scripts
- `.vercelignore` - Excludes unnecessary files

### 2. Vercel Configuration

#### Current `vercel.json` Setup:
```json
{
  "version": 2,
  "name": "seoforge-mcp-server",
  "builds": [
    {
      "src": "backend-express/package.json",
      "use": "@vercel/node",
      "config": {
        "maxLambdaSize": "50mb"
      }
    }
  ],
  "routes": [
    {
      "src": "/(.*)",
      "dest": "backend-express/dist/server.js"
    }
  ]
}
```

#### Environment Variables Required:
```bash
NODE_ENV=production
PORT=3000
HOST=0.0.0.0
LOG_LEVEL=info
CORS_ORIGINS=*
CORS_CREDENTIALS=true
JWT_SECRET=your-jwt-secret-here
VALID_API_KEYS=test-api-key
OPENAI_API_KEY=your-openai-key
GOOGLE_API_KEY=your-google-key
ANTHROPIC_API_KEY=your-anthropic-key
REPLICATE_API_TOKEN=your-replicate-token
TOGETHER_API_KEY=your-together-key
ALLOW_REGISTRATION=true
ENABLE_SWAGGER_DOCS=true
ENABLE_METRICS=true
```

### 3. Deploy to Vercel

#### Option A: GitHub Integration (Recommended)
1. **Connect Repository:**
   ```bash
   # Push changes to GitHub
   git add .
   git commit -m "Clean Vercel deployment configuration"
   git push origin main
   ```

2. **Vercel Dashboard:**
   - Go to [vercel.com](https://vercel.com)
   - Click "New Project"
   - Import from GitHub: `khiwniti/SEOForge-mcp-server`
   - Vercel will auto-detect the configuration

3. **Environment Variables:**
   - In Vercel dashboard, go to Project Settings > Environment Variables
   - Add all required environment variables listed above

#### Option B: Vercel CLI
```bash
# Install Vercel CLI
npm i -g vercel

# Login to Vercel
vercel login

# Deploy
vercel --prod
```

### 4. API Endpoints Available

Once deployed, the following endpoints will be available:

#### Health Check:
- `GET /health` - Basic health check
- `GET /mcp/status` - MCP service status
- `GET /universal-mcp/status` - Universal MCP status

#### Content Generation:
- `POST /api/blog-generator/generate` - Blog generation
- `POST /universal-mcp/generate-content` - Universal content generation
- `POST /universal-mcp/generate-blog-with-images` - Blog with images

#### SEO Analysis:
- `POST /api/seo-analyzer/analyze` - SEO analysis
- `POST /universal-mcp/analyze-seo` - Universal SEO analysis

#### Image Generation:
- `POST /api/flux-image-gen/generate` - Image generation
- `POST /universal-mcp/generate-image` - Universal image generation
- `POST /universal-mcp/generate-flux-image` - Flux image generation

#### MCP Tools:
- `GET /mcp/tools` - List available tools
- `POST /mcp/tools/execute` - Execute MCP tools
- `POST /mcp/protocol` - MCP protocol handler

### 5. Testing Deployment

#### Test Health Endpoints:
```bash
# Replace YOUR_VERCEL_URL with your actual Vercel URL
curl https://YOUR_VERCEL_URL.vercel.app/health
curl https://YOUR_VERCEL_URL.vercel.app/mcp/status
```

#### Test Content Generation:
```bash
curl -X POST https://YOUR_VERCEL_URL.vercel.app/api/blog-generator/generate \
  -H "Content-Type: application/json" \
  -d '{
    "topic": "AI Technology",
    "keywords": ["artificial intelligence", "machine learning"],
    "length": "medium",
    "tone": "professional",
    "language": "en"
  }'
```

### 6. WordPress Plugin Integration

Update your WordPress plugin to use the new Vercel URL:

```php
// In seo-forge-complete.php
private function generate_seo_forge_content($topic, $keywords, $length, $type = 'blog') {
    $api_base = 'https://YOUR_VERCEL_URL.vercel.app'; // Update this URL
    // ... rest of the function
}
```

### 7. Monitoring & Debugging

#### Vercel Dashboard:
- **Functions:** Monitor function execution and logs
- **Analytics:** Track API usage and performance
- **Deployments:** View deployment history and status

#### Logs Access:
```bash
# View real-time logs
vercel logs YOUR_PROJECT_NAME --follow
```

#### Common Issues & Solutions:

1. **Build Failures:**
   ```bash
   # Check build logs in Vercel dashboard
   # Ensure all dependencies are in package.json
   ```

2. **Environment Variables:**
   ```bash
   # Verify all required env vars are set in Vercel dashboard
   # Check for typos in variable names
   ```

3. **Function Timeouts:**
   ```bash
   # Increase maxDuration in vercel.json if needed
   # Optimize API response times
   ```

### 8. Performance Optimization

#### Current Optimizations:
- ✅ TypeScript compilation to JavaScript
- ✅ Tree-shaking and dead code elimination
- ✅ Gzip compression enabled
- ✅ Request/response caching
- ✅ Rate limiting configured

#### Additional Recommendations:
- Set up CDN for static assets
- Implement Redis caching for API responses
- Monitor function cold starts
- Optimize bundle size

### 9. Security Configuration

#### Current Security Features:
- ✅ Helmet.js for security headers
- ✅ CORS configuration
- ✅ Rate limiting
- ✅ Input validation
- ✅ JWT authentication

#### Environment Security:
- Use Vercel environment variables for secrets
- Never commit API keys to repository
- Rotate secrets regularly

### 10. Scaling Considerations

#### Vercel Limits:
- **Function Duration:** 30 seconds (configurable)
- **Function Size:** 50MB (configured)
- **Concurrent Executions:** Based on plan
- **Bandwidth:** Based on plan

#### Scaling Strategies:
- Implement caching for expensive operations
- Use background jobs for long-running tasks
- Consider upgrading Vercel plan for higher limits

## 🎉 Success Checklist

- [ ] Repository cleaned and optimized
- [ ] Vercel configuration updated
- [ ] Environment variables configured
- [ ] Deployment successful
- [ ] Health endpoints responding
- [ ] API endpoints functional
- [ ] WordPress plugin updated with new URL
- [ ] Monitoring and logging configured

## 📞 Support

If you encounter issues:

1. **Check Vercel Logs:** Dashboard > Functions > View Function Logs
2. **Verify Environment Variables:** Dashboard > Settings > Environment Variables
3. **Test Locally:** Run `npm run dev` in `backend-express/`
4. **Check API Responses:** Use curl or Postman to test endpoints

## 🔄 Continuous Deployment

The repository is configured for automatic deployment:
- Push to `main` branch triggers production deployment
- Pull requests create preview deployments
- Environment variables are inherited from production

---

**Your SEOForge MCP Server is now ready for production use on Vercel!** 🚀