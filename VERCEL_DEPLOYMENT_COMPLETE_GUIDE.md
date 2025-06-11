# SEOForge MCP Platform - Complete Vercel Deployment Guide

## 🚀 Quick Deployment

### Option 1: Automated Deployment Script

```bash
# Clone the repository
git clone https://github.com/khiwniti/SEOForge-mcp-server.git
cd SEOForge-mcp-server

# Run the deployment script
./deploy-to-vercel.sh
```

### Option 2: Manual Deployment

```bash
# Install Vercel CLI
npm install -g vercel

# Deploy from project root
vercel --prod
```

## 📁 Project Structure

```
SEOForge-mcp-server/
├── backend/                 # Python FastAPI backend
│   ├── main.py             # Main application file
│   ├── requirements.txt    # Python dependencies
│   └── ...
├── frontend/               # React frontend
│   ├── src/               # Source files
│   ├── public/            # Static assets
│   ├── package.json       # Node.js dependencies
│   ├── vite.config.ts     # Vite configuration
│   └── dist/              # Built files (generated)
├── vercel.json            # Vercel configuration
└── deploy-to-vercel.sh    # Deployment script
```

## ⚙️ Configuration Files

### 1. Main Vercel Configuration (`vercel.json`)

```json
{
  "version": 2,
  "name": "seoforge-mcp-platform",
  "builds": [
    {
      "src": "backend/main.py",
      "use": "@vercel/python",
      "config": {
        "maxLambdaSize": "15mb",
        "runtime": "python3.11"
      }
    },
    {
      "src": "frontend/package.json",
      "use": "@vercel/static-build",
      "config": {
        "buildCommand": "cd frontend && npm ci && npm run build",
        "distDir": "frontend/dist"
      }
    }
  ],
  "routes": [
    {
      "src": "/mcp-server(.*)",
      "dest": "backend/main.py"
    },
    {
      "src": "/wordpress/(.*)",
      "dest": "backend/main.py"
    },
    {
      "src": "/health",
      "dest": "backend/main.py"
    },
    {
      "src": "/(.*)",
      "dest": "frontend/dist/index.html"
    }
  ]
}
```

### 2. Frontend Configuration (`frontend/vite.config.ts`)

- Configured for production builds
- API URL automatically set for Vercel deployment
- Optimized bundle splitting
- CORS proxy configuration for development

### 3. Backend Configuration (`backend/main.py`)

- FastAPI application with CORS enabled
- MCP server integration
- WordPress plugin API
- Health check endpoints

## 🌐 Deployment URLs

After deployment, your application will be available at:

- **Main Application**: `https://seoforge-mcp-platform.vercel.app`
- **API Documentation**: `https://seoforge-mcp-platform.vercel.app/docs`
- **Health Check**: `https://seoforge-mcp-platform.vercel.app/health`
- **MCP Server**: `https://seoforge-mcp-platform.vercel.app/mcp-server`
- **WordPress API**: `https://seoforge-mcp-platform.vercel.app/wordpress/plugin`

## 🔧 Environment Variables

### Required Environment Variables

Set these in your Vercel dashboard:

```bash
# WordPress Integration
WORDPRESS_SECRET_KEY=your-secret-key-here
ALLOWED_WORDPRESS_DOMAINS=yourdomain.com,playground.wordpress.net

# Optional Redis Configuration
REDIS_HOST=your-redis-host
REDIS_PORT=6379
REDIS_PASSWORD=your-redis-password

# Frontend Configuration
VITE_API_URL=https://seoforge-mcp-platform.vercel.app
NODE_ENV=production
```

### Setting Environment Variables

1. Go to [Vercel Dashboard](https://vercel.com/dashboard)
2. Select your project
3. Go to **Settings** > **Environment Variables**
4. Add the required variables

## 🧪 Testing Deployment

### 1. Automated Testing

```bash
# Test all APIs
python test_all_apis.py --url https://seoforge-mcp-platform.vercel.app

# Test bilingual features
python test_bilingual_features.py --url https://seoforge-mcp-platform.vercel.app
```

### 2. Manual Testing

#### Backend API Tests:
```bash
# Health check
curl https://seoforge-mcp-platform.vercel.app/health

# API documentation
curl https://seoforge-mcp-platform.vercel.app/docs

# MCP server test
curl -X POST https://seoforge-mcp-platform.vercel.app/mcp-server \
  -H "Content-Type: application/json" \
  -H "X-WordPress-Key: test-key" \
  -H "X-WordPress-Site: https://test-site.com" \
  -H "X-WordPress-Nonce: test-nonce" \
  -H "X-WordPress-Timestamp: $(date +%s)" \
  -d '{"jsonrpc":"2.0","method":"initialize","params":{},"id":1}'
```

#### Frontend Tests:
- Visit `https://seoforge-mcp-platform.vercel.app`
- Check that the React application loads
- Test navigation and functionality

## 🔄 Continuous Deployment

### GitHub Integration

1. Connect your GitHub repository to Vercel
2. Enable automatic deployments
3. Set up branch protection rules

### Deployment Triggers

- **Production**: Pushes to `main` branch
- **Preview**: Pull requests and feature branches
- **Manual**: Using Vercel CLI or dashboard

## 📊 Monitoring & Analytics

### Vercel Analytics

1. Enable Vercel Analytics in project settings
2. Monitor performance metrics
3. Track user engagement

### Error Monitoring

1. Check Vercel function logs
2. Monitor error rates
3. Set up alerts for critical issues

## 🛠 Troubleshooting

### Common Issues

#### 1. Build Failures

**Frontend Build Issues:**
```bash
# Check Node.js version
node --version  # Should be 18+

# Clear cache and reinstall
rm -rf frontend/node_modules frontend/package-lock.json
cd frontend && npm install
```

**Backend Build Issues:**
```bash
# Check Python version
python --version  # Should be 3.11+

# Install dependencies
cd backend && pip install -r requirements.txt
```

#### 2. Runtime Errors

**CORS Issues:**
- Check CORS configuration in `backend/main.py`
- Verify allowed origins include your domain

**Environment Variables:**
- Ensure all required variables are set in Vercel dashboard
- Check variable names match exactly

#### 3. Performance Issues

**Cold Starts:**
- Vercel functions may have cold start delays
- Consider upgrading to Pro plan for better performance

**Bundle Size:**
- Check frontend bundle size
- Optimize imports and dependencies

### Debug Commands

```bash
# Check deployment status
vercel ls

# View deployment logs
vercel logs

# Check function logs
vercel logs --follow

# Local development
vercel dev
```

## 🔐 Security Considerations

### Production Security

1. **Environment Variables**: Never commit secrets to repository
2. **CORS Configuration**: Restrict origins in production
3. **Rate Limiting**: Implement proper rate limiting
4. **Authentication**: Use secure authentication methods

### WordPress Integration Security

1. **Secret Keys**: Use strong, unique secret keys
2. **Domain Validation**: Restrict allowed WordPress domains
3. **Nonce Validation**: Implement proper nonce validation
4. **HTTPS Only**: Ensure all communication uses HTTPS

## 📈 Performance Optimization

### Frontend Optimization

1. **Code Splitting**: Implemented in Vite configuration
2. **Asset Optimization**: Automatic by Vercel
3. **Caching**: Configured for static assets
4. **Compression**: Automatic gzip/brotli compression

### Backend Optimization

1. **Function Size**: Optimized for Vercel limits
2. **Cold Start**: Minimized import overhead
3. **Response Caching**: Implement where appropriate
4. **Database Connections**: Use connection pooling

## 🚀 Advanced Configuration

### Custom Domains

1. Add custom domain in Vercel dashboard
2. Configure DNS records
3. SSL certificates are automatic

### Edge Functions

Consider using Vercel Edge Functions for:
- Authentication middleware
- Request routing
- Response transformation

### Database Integration

For production use, consider:
- Vercel Postgres
- PlanetScale
- Supabase
- MongoDB Atlas

## 📞 Support & Resources

### Documentation
- [Vercel Documentation](https://vercel.com/docs)
- [FastAPI Documentation](https://fastapi.tiangolo.com/)
- [React Documentation](https://react.dev/)
- [Vite Documentation](https://vitejs.dev/)

### Community
- [Vercel Discord](https://vercel.com/discord)
- [GitHub Issues](https://github.com/khiwniti/SEOForge-mcp-server/issues)
- [GitHub Discussions](https://github.com/khiwniti/SEOForge-mcp-server/discussions)

## 🎉 Conclusion

Your SEOForge MCP Platform is now deployed to Vercel with:

- ✅ **Full-stack deployment** (React frontend + FastAPI backend)
- ✅ **Automatic HTTPS** and global CDN
- ✅ **Serverless functions** for optimal performance
- ✅ **WordPress integration** ready for production
- ✅ **Bilingual support** (Thai/English)
- ✅ **Comprehensive testing** suite included
- ✅ **Production-ready** configuration

The platform is ready for real-world usage with WordPress sites and MCP clients!