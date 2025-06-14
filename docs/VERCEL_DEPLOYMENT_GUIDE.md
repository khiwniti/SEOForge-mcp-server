# 🚀 Vercel Deployment Guide - Universal MCP Server Platform

## 📋 **Prerequisites**

- Node.js 18+ installed
- Git configured for Windows with CRLF handling
- Vercel account (free tier available)
- API keys for OpenAI, Anthropic, Google AI

## 🔧 **Step 1: Fix Git Line Endings (Windows)**

```powershell
# Configure Git for Windows CRLF handling
git config --global core.autocrlf true
git config --global core.safecrlf false
git config --global core.eol crlf

# Apply to current repository
git add .gitattributes
git commit -m "Configure line endings for Windows"
```

## 🌐 **Step 2: Deploy to Vercel**

### **Option A: Automated PowerShell Script (Recommended)**

```powershell
# Make script executable and run
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
.\deploy-vercel.ps1 deploy

# For production deployment
.\deploy-vercel.ps1 deploy -Production
```

### **Option B: Manual Deployment**

```bash
# Install Vercel CLI
npm install -g vercel

# Login to Vercel
vercel login

# Deploy (first time will prompt for configuration)
vercel

# Deploy to production
vercel --prod
```

## ⚙️ **Step 3: Configure Environment Variables**

In your Vercel dashboard, add these environment variables:

### **Required Variables**
```env
OPENAI_API_KEY=sk-your-openai-key
ANTHROPIC_API_KEY=your-anthropic-key
GOOGLE_AI_API_KEY=your-google-ai-key
MCP_API_KEY=your-secure-mcp-key
JWT_SECRET=your-jwt-secret-key
```

### **Database Variables**
```env
# Option 1: Vercel Postgres (Recommended)
POSTGRES_URL=your-vercel-postgres-url
POSTGRES_PRISMA_URL=your-prisma-url
POSTGRES_URL_NON_POOLING=your-non-pooling-url

# Option 2: External Database
DATABASE_URL=postgresql://user:pass@host:port/db
REDIS_URL=redis://user:pass@host:port
```

### **Optional Variables**
```env
ENVIRONMENT=production
CORS_ORIGINS=https://your-domain.vercel.app
RATE_LIMIT=100
CACHE_DURATION=3600
DEBUG_MODE=false
```

## 🗄️ **Step 4: Database Setup**

### **Option A: Vercel Postgres (Recommended)**

1. Go to your Vercel project dashboard
2. Navigate to "Storage" tab
3. Create a new Postgres database
4. Copy the connection strings to environment variables
5. Run database migrations:

```sql
-- Create tables (run in Vercel Postgres dashboard)
CREATE TABLE IF NOT EXISTS umcp_requests (
    id SERIAL PRIMARY KEY,
    user_id INTEGER,
    tool_name VARCHAR(100) NOT NULL,
    parameters TEXT NOT NULL,
    response TEXT NOT NULL,
    industry VARCHAR(50) DEFAULT 'general',
    execution_time FLOAT DEFAULT 0,
    success BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS umcp_industry_templates (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    industry VARCHAR(50) NOT NULL,
    template_data TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_requests_tool_name ON umcp_requests(tool_name);
CREATE INDEX IF NOT EXISTS idx_requests_industry ON umcp_requests(industry);
CREATE INDEX IF NOT EXISTS idx_templates_industry ON umcp_industry_templates(industry);
```

### **Option B: External Database**

Use any PostgreSQL provider (AWS RDS, DigitalOcean, etc.) and add the connection string to `DATABASE_URL`.

## 🔗 **Step 5: Custom Domain (Optional)**

1. In Vercel dashboard, go to "Domains"
2. Add your custom domain
3. Configure DNS records as instructed
4. Update CORS_ORIGINS environment variable

## 📱 **Step 6: WordPress Plugin Configuration**

1. Download the WordPress plugin from `wordpress-plugin/` folder
2. Install on your WordPress site
3. Configure plugin settings:
   - **MCP Server URL**: `https://your-domain.vercel.app/api`
   - **API Key**: Your MCP_API_KEY value
   - **Default Industry**: Choose your industry
   - **Default Language**: en, th, or dual

## 🧪 **Step 7: Testing Deployment**

### **Test API Endpoints**
```bash
# Test health endpoint
curl https://your-domain.vercel.app/health

# Test MCP server status
curl https://your-domain.vercel.app/api/mcp-server/status

# Test content generation
curl -X POST https://your-domain.vercel.app/api/mcp-server/execute-tool \
  -H "Content-Type: application/json" \
  -d '{
    "tool_name": "content_generation",
    "parameters": {
      "topic": "AI in business",
      "content_type": "blog_post"
    },
    "industry": "technology"
  }'
```

### **Test Frontend**
- Visit `https://your-domain.vercel.app`
- Navigate to MCP Dashboard
- Test tool execution
- Verify analytics display

## 📊 **Step 8: Monitoring & Analytics**

### **Vercel Analytics**
1. Enable Vercel Analytics in project settings
2. Monitor performance and usage
3. Set up alerts for errors

### **Custom Monitoring**
- Check `/health` endpoint regularly
- Monitor API response times
- Track tool usage in WordPress admin

## 🔧 **Troubleshooting**

### **Common Issues**

#### **1. Line Ending Issues**
```powershell
# Fix CRLF warnings
git config core.autocrlf true
git add . --renormalize
git commit -m "Normalize line endings"
```

#### **2. Build Failures**
```bash
# Clear cache and rebuild
vercel --force
```

#### **3. Environment Variable Issues**
- Verify all required variables are set in Vercel dashboard
- Check variable names match exactly
- Redeploy after adding variables

#### **4. Database Connection Issues**
- Verify DATABASE_URL format
- Check firewall settings for external databases
- Test connection from Vercel Functions

#### **5. CORS Issues**
```env
# Update CORS origins
CORS_ORIGINS=https://your-domain.vercel.app,https://www.your-domain.com
```

## 🚀 **Performance Optimization**

### **Vercel Configuration**
```json
{
  "functions": {
    "backend/api/index.py": {
      "maxDuration": 30
    }
  },
  "regions": ["iad1"]
}
```

### **Caching Strategy**
- Enable Redis for caching (Upstash recommended)
- Set appropriate cache durations
- Use CDN for static assets

### **Database Optimization**
- Use connection pooling
- Implement read replicas for scaling
- Regular database maintenance

## 📈 **Scaling Considerations**

### **Vercel Limits**
- **Hobby Plan**: 100GB bandwidth, 100 deployments/day
- **Pro Plan**: 1TB bandwidth, unlimited deployments
- **Enterprise**: Custom limits

### **Database Scaling**
- Monitor connection limits
- Implement connection pooling
- Consider read replicas

### **API Rate Limiting**
- Implement per-user rate limiting
- Use Redis for distributed rate limiting
- Monitor API usage patterns

## 🔐 **Security Best Practices**

1. **Environment Variables**: Never commit API keys
2. **CORS**: Restrict to your domains only
3. **Rate Limiting**: Implement proper limits
4. **Input Validation**: Validate all inputs
5. **HTTPS**: Always use HTTPS in production
6. **API Keys**: Rotate keys regularly

## 📞 **Support & Resources**

- **Vercel Documentation**: https://vercel.com/docs
- **Project Repository**: Your GitHub repository
- **Issue Tracking**: GitHub Issues
- **Community Support**: Discord/Slack channels

## ✅ **Deployment Checklist**

- [ ] Git line endings configured
- [ ] Vercel CLI installed and authenticated
- [ ] Environment variables configured
- [ ] Database setup and migrated
- [ ] Custom domain configured (optional)
- [ ] WordPress plugin installed and configured
- [ ] API endpoints tested
- [ ] Frontend functionality verified
- [ ] Monitoring and analytics enabled
- [ ] Performance optimized
- [ ] Security measures implemented

## 🎉 **Success!**

Your Universal MCP Server Platform is now deployed on Vercel and ready for production use! 

**Access Points:**
- **Frontend**: https://your-domain.vercel.app
- **API Documentation**: https://your-domain.vercel.app/docs
- **Health Check**: https://your-domain.vercel.app/health
- **WordPress Plugin**: Configure with your Vercel URL

The platform now supports enterprise-scale content generation and SEO optimization across any industry with global accessibility! 🌍
