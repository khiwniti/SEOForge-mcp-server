# SEOForge Express Backend - Deployment Guide

## Overview

This guide covers deploying the SEOForge Express backend to Vercel. The backend has been cleaned up and optimized for serverless deployment.

## Architecture

```
SEOForge MCP Server
├── backend-express/          # Express.js backend (Primary)
│   ├── src/                  # TypeScript source code
│   ├── api/                  # Vercel API routes
│   ├── package.json          # Node.js dependencies
│   ├── vercel.json           # Vercel configuration
│   └── deploy-vercel.sh      # Deployment script
├── frontend/                 # React frontend
├── cloudflare-worker.js      # Cloudflare Workers (Alternative)
└── package.json              # Root package.json (Cloudflare)
```

## Quick Deployment

### 1. Prerequisites

- Node.js 18+ installed
- Vercel CLI installed (`npm install -g vercel`)
- Vercel account

### 2. Deploy Express Backend

```bash
cd backend-express
./deploy-vercel.sh
```

### 3. Environment Variables

The backend is pre-configured with Google Gemini 2.5 Pro for enhanced accuracy. Set these additional variables in your Vercel dashboard if needed:

```env
NODE_ENV=production
GOOGLE_API_KEY=AIzaSyDTITCw_UcgzUufrsCFuxp9HXri6Y0XrDo  # Pre-configured
OPENAI_API_KEY=your_openai_api_key  # Optional fallback
ANTHROPIC_API_KEY=your_anthropic_api_key  # Optional fallback
CORS_ORIGINS=https://your-frontend-domain.com
```

**Note**: Google Gemini 2.5 Pro is prioritized for all AI tasks due to superior accuracy and performance.

## Manual Deployment

### Step 1: Prepare Backend

```bash
cd backend-express
npm install
npm run build
```

### Step 2: Deploy to Vercel

```bash
vercel --prod
```

### Step 3: Configure Domain (Optional)

```bash
vercel domains add your-domain.com
vercel alias your-deployment-url.vercel.app your-domain.com
```

## API Endpoints

Once deployed, your backend will have these endpoints:

- `GET /` - API information
- `GET /health` - Health check
- `POST /auth/*` - Authentication
- `POST /mcp/*` - MCP server operations
- `POST /api/*` - General API endpoints

## Testing Deployment

```bash
# Test health endpoint
curl https://your-backend.vercel.app/health

# Test API info
curl https://your-backend.vercel.app/
```

## Troubleshooting

### Common Issues

1. **Build Errors**: Ensure all TypeScript files compile correctly
2. **Environment Variables**: Check Vercel dashboard for missing env vars
3. **CORS Issues**: Update CORS_ORIGINS environment variable

### Logs

View deployment logs:
```bash
vercel logs your-deployment-url.vercel.app
```

## Performance Optimization

- ✅ Serverless functions with 30s timeout
- ✅ Automatic scaling
- ✅ Global CDN distribution
- ✅ Optimized build process

## Security Features

- ✅ Helmet.js security headers
- ✅ Rate limiting
- ✅ CORS configuration
- ✅ Input validation
- ✅ JWT authentication

## Next Steps

1. Deploy frontend to Vercel/Netlify
2. Configure custom domain
3. Set up monitoring and alerts
4. Configure CI/CD pipeline

## Support

For issues or questions:
- Check the logs: `vercel logs`
- Review environment variables
- Ensure all dependencies are installed
- Verify TypeScript compilation