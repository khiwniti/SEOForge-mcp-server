# 🔄 Migration Guide: Backend to Unified MCP Server

This guide explains how to migrate from the existing Python FastAPI backend to the new unified MCP server architecture for easy Vercel deployment.

## 🎯 What Changed

### Before (Multiple Components)
- **Python FastAPI Backend** - Separate backend server
- **Node.js MCP Server** - Separate MCP protocol server  
- **React Frontend** - Dashboard interface
- **Complex Deployment** - Multiple services to deploy

### After (Unified MCP Server)
- **Single Node.js/TypeScript Server** - All functionality in one place
- **MCP Protocol Native** - Built from ground up for MCP
- **Vercel Optimized** - Serverless functions for easy deployment
- **Simple Deployment** - One command deployment

## 🚀 Migration Steps

### 1. Install Dependencies

```bash
cd mcp-server-unified
npm install
```

### 2. Configure Environment

```bash
cp .env.example .env
# Edit .env with your API keys
```

### 3. Test Locally

```bash
npm run dev
# Test at http://localhost:3000
```

### 4. Deploy to Vercel

```bash
npm run deploy
# or
vercel --prod
```

## 🔧 API Mapping

### Content Generation
**Before:**
```
POST /api/blog-generator/generate
```

**After:**
```
POST /mcp/tools/execute
{
  "tool": "generate_content",
  "arguments": {
    "type": "blog",
    "topic": "...",
    "keywords": [...]
  }
}
```

### SEO Analysis
**Before:**
```
POST /api/seo-analyzer/analyze
```

**After:**
```
POST /mcp/tools/execute
{
  "tool": "analyze_seo",
  "arguments": {
    "url": "...",
    "keywords": [...]
  }
}
```

### Image Generation
**Before:**
```
POST /api/image-generator/generate
```

**After:**
```
POST /mcp/tools/execute
{
  "tool": "generate_image",
  "arguments": {
    "prompt": "...",
    "style": "realistic"
  }
}
```

## 🔐 Authentication Changes

### Before (FastAPI)
```python
headers = {
    "Authorization": f"Bearer {token}",
    "X-WordPress-Key": wp_key
}
```

### After (MCP Server)
```javascript
headers = {
    "Authorization": `Bearer ${token}`,
    "X-API-Key": api_key,
    "Content-Type": "application/json"
}
```

## 📝 WordPress Plugin Updates

### Update Plugin Configuration

```php
// Old configuration
$api_base_url = 'https://your-backend.vercel.app/api';

// New configuration  
$mcp_server_url = 'https://your-deployment.vercel.app/mcp';
```

### Update API Calls

```php
// Old API call
$response = wp_remote_post($api_base_url . '/blog-generator/generate', [
    'body' => json_encode($data),
    'headers' => ['Content-Type' => 'application/json']
]);

// New MCP call
$response = wp_remote_post($mcp_server_url . '/tools/execute', [
    'body' => json_encode([
        'tool' => 'generate_content',
        'arguments' => $data
    ]),
    'headers' => [
        'Content-Type' => 'application/json',
        'X-API-Key' => $api_key
    ]
]);
```

## 🌐 Frontend Updates

### Update API Service

```typescript
// Old service
class BackendService {
    async generateContent(data: any) {
        return fetch('/api/blog-generator/generate', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }
}

// New MCP service
class MCPService {
    async executeTool(tool: string, arguments: any) {
        return fetch('/mcp/tools/execute', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-API-Key': this.apiKey
            },
            body: JSON.stringify({ tool, arguments })
        });
    }
    
    async generateContent(data: any) {
        return this.executeTools('generate_content', data);
    }
}
```

## 🔄 Data Migration

### No Database Migration Required
- The unified MCP server uses in-memory caching by default
- Optional Redis/PostgreSQL for production scaling
- All data is processed in real-time

### Configuration Migration

1. **Export current settings** from old backend
2. **Set environment variables** in Vercel
3. **Test all functionality** with new server
4. **Update client applications** to use new endpoints

## ✅ Verification Checklist

### Deployment Verification
- [ ] Unified MCP server deployed to Vercel
- [ ] Health check endpoint responding: `/health`
- [ ] Client interface accessible: `/client`
- [ ] All tools listed: `/mcp/tools/list`

### Functionality Testing
- [ ] Content generation working
- [ ] SEO analysis functional
- [ ] Image generation operational
- [ ] Thai translation working
- [ ] Keyword research functional
- [ ] WordPress integration tested

### Performance Testing
- [ ] Response times under 5 seconds
- [ ] Rate limiting working
- [ ] Caching operational
- [ ] Error handling proper

## 🚨 Rollback Plan

If issues occur during migration:

1. **Keep old backend running** during transition
2. **Use feature flags** to switch between old/new
3. **Monitor error rates** and performance
4. **Gradual migration** of client applications

### Emergency Rollback

```bash
# Revert vercel.json to point to old backend
git checkout HEAD~1 vercel.json
vercel --prod
```

## 🎯 Benefits of Migration

### For Developers
- **Single Codebase** - Easier maintenance
- **TypeScript** - Better type safety
- **Modern Architecture** - Future-proof design
- **Better Testing** - Unified test suite

### For Deployment
- **Vercel Optimized** - Serverless functions
- **Auto Scaling** - Handles traffic spikes
- **Global CDN** - Faster response times
- **Zero Config** - Deploy with one command

### For Users
- **Faster Response** - Optimized performance
- **Better Reliability** - Improved error handling
- **Enhanced Features** - New capabilities
- **Consistent API** - Unified interface

## 📞 Support

If you encounter issues during migration:

1. **Check logs** in Vercel dashboard
2. **Test locally** with `npm run dev`
3. **Verify environment variables** are set
4. **Review API documentation** at `/client/docs`

## 🎉 Post-Migration

After successful migration:

1. **Update documentation** with new endpoints
2. **Train team** on new MCP architecture
3. **Monitor performance** and optimize
4. **Plan future enhancements** using MCP capabilities

---

The unified MCP server provides a more robust, scalable, and maintainable architecture while preserving all existing functionality. The migration ensures your SEO Forge platform is ready for future growth and development.
