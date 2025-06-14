# Vercel Deployment Guide for SEO Forge MCP Server

This guide will help you deploy the SEO Forge Express MCP server to Vercel for production use.

## 🚀 Quick Deployment

### Prerequisites
- GitHub account
- Vercel account (free tier available)
- API keys for AI services (OpenAI, Replicate, etc.)

### Step 1: Prepare Repository
1. **Fork** this repository to your GitHub account
2. **Clone** your fork locally (optional)
3. **Ensure** the latest changes are pushed to your main branch

### Step 2: Connect to Vercel
1. **Visit** [vercel.com](https://vercel.com) and sign in
2. **Click** "New Project"
3. **Import** your forked repository
4. **Select** the repository from the list

### Step 3: Configure Build Settings
Vercel should automatically detect the configuration from `vercel.json`, but verify:

- **Framework Preset**: None (we use custom configuration)
- **Build Command**: `cd backend-express && npm install && npm run build`
- **Output Directory**: `backend-express/dist`
- **Install Command**: `npm install`

### Step 4: Set Environment Variables
In the Vercel dashboard, add these environment variables:

#### Required Variables
```bash
NODE_ENV=production
PORT=3000
HOST=0.0.0.0
LOG_LEVEL=info
CORS_ORIGINS=*
CORS_CREDENTIALS=true
```

#### Authentication
```bash
JWT_SECRET=your-secure-jwt-secret-here
VALID_API_KEYS=your-api-key-1,your-api-key-2
```

#### AI Service API Keys
```bash
OPENAI_API_KEY=sk-your-openai-api-key
GOOGLE_API_KEY=your-google-api-key
ANTHROPIC_API_KEY=your-anthropic-api-key
REPLICATE_API_TOKEN=r8_your-replicate-token
TOGETHER_API_KEY=your-together-api-key
```

#### Optional Settings
```bash
ALLOW_REGISTRATION=true
ENABLE_SWAGGER_DOCS=true
ENABLE_METRICS=true
```

### Step 5: Deploy
1. **Click** "Deploy"
2. **Wait** for the build to complete (usually 2-3 minutes)
3. **Get** your deployment URL (e.g., `https://your-app.vercel.app`)

## 🔧 Configuration Details

### Environment Variables Reference

#### Core Server Settings
| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `NODE_ENV` | Environment mode | `production` | Yes |
| `PORT` | Server port | `3000` | No |
| `HOST` | Server host | `0.0.0.0` | No |
| `LOG_LEVEL` | Logging level | `info` | No |

#### CORS Configuration
| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `CORS_ORIGINS` | Allowed origins | `*` | No |
| `CORS_CREDENTIALS` | Allow credentials | `true` | No |

#### Authentication
| Variable | Description | Example | Required |
|----------|-------------|---------|----------|
| `JWT_SECRET` | JWT signing secret | `your-secret-key` | Yes |
| `VALID_API_KEYS` | Comma-separated API keys | `key1,key2,key3` | Yes |

#### AI Services
| Variable | Description | Format | Required |
|----------|-------------|--------|----------|
| `OPENAI_API_KEY` | OpenAI API key | `sk-...` | Yes |
| `GOOGLE_API_KEY` | Google API key | `AIza...` | No |
| `ANTHROPIC_API_KEY` | Anthropic API key | `sk-ant-...` | No |
| `REPLICATE_API_TOKEN` | Replicate token | `r8_...` | Yes |
| `TOGETHER_API_KEY` | Together AI key | `...` | No |

### Getting API Keys

#### OpenAI API Key (Required)
1. Visit [OpenAI Platform](https://platform.openai.com/api-keys)
2. Sign up or log in
3. Create a new API key
4. Copy and add to Vercel environment variables

#### Replicate Token (Required for Images)
1. Visit [Replicate](https://replicate.com/account/api-tokens)
2. Sign up or log in
3. Create a new API token
4. Copy and add to Vercel environment variables

#### Google API Key (Optional)
1. Visit [Google Cloud Console](https://console.cloud.google.com/)
2. Create a project or select existing
3. Enable required APIs
4. Create credentials (API key)
5. Copy and add to Vercel environment variables

#### Together AI Key (Optional)
1. Visit [Together AI](https://api.together.xyz/settings/api-keys)
2. Sign up or log in
3. Create a new API key
4. Copy and add to Vercel environment variables

## 🧪 Testing Your Deployment

### 1. Health Check
Test the basic health endpoint:
```bash
curl https://your-app.vercel.app/health
```

Expected response:
```json
{
  "status": "healthy",
  "timestamp": "2024-06-14T...",
  "uptime": "...",
  "version": "1.0.0",
  "services": {
    "mcp": "operational",
    "cache": "operational"
  }
}
```

### 2. API Endpoints
Test the main API endpoints:

#### Content Generation
```bash
curl -X POST https://your-app.vercel.app/api/blog-generator/generate \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your-api-key" \
  -d '{
    "topic": "WordPress SEO Tips",
    "keywords": ["SEO", "WordPress", "optimization"],
    "language": "en",
    "tone": "professional"
  }'
```

#### SEO Analysis
```bash
curl -X POST https://your-app.vercel.app/api/seo-analyzer/analyze \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your-api-key" \
  -d '{
    "content": "Your content here...",
    "keywords": ["SEO", "analysis"]
  }'
```

#### Image Generation
```bash
curl -X POST https://your-app.vercel.app/api/flux-image-gen/generate \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your-api-key" \
  -d '{
    "prompt": "Professional WordPress website design",
    "model": "flux",
    "style": "professional",
    "size": "1024x1024"
  }'
```

### 3. MCP Tools
Test the MCP tools endpoint:
```bash
curl https://your-app.vercel.app/mcp/tools \
  -H "X-API-Key: your-api-key"
```

## 🔧 WordPress Plugin Configuration

After deploying to Vercel, update your WordPress plugin settings:

### Plugin Settings
1. **Go to** WordPress Admin → SEO Forge → Settings
2. **Update** API URL to: `https://your-app.vercel.app`
3. **Set** API Key to one of your `VALID_API_KEYS`
4. **Click** "Test Connection"
5. **Verify** successful connection

### Example Configuration
```
API URL: https://seoforge-mcp-server.vercel.app
API Key: your-secure-api-key
```

## 🐛 Troubleshooting

### Common Issues

#### Build Failures
**Error**: "Build failed" or timeout
**Solutions**:
- Check that `backend-express/package.json` exists
- Verify TypeScript compilation works locally
- Check build logs for specific errors
- Ensure all dependencies are properly listed

#### Environment Variable Issues
**Error**: "Missing required environment variables"
**Solutions**:
- Verify all required variables are set in Vercel dashboard
- Check variable names match exactly (case-sensitive)
- Ensure no extra spaces in variable values
- Redeploy after adding missing variables

#### API Key Authentication Failures
**Error**: "Invalid API key" or 403 errors
**Solutions**:
- Verify `VALID_API_KEYS` contains your plugin API key
- Check that API key is passed in `X-API-Key` header
- Ensure no extra characters or spaces in API key
- Test with curl to verify authentication

#### Function Timeout Issues
**Error**: "Function execution timeout"
**Solutions**:
- Check that AI service API keys are valid
- Verify external API services are responding
- Consider upgrading to Vercel Pro for longer timeouts
- Optimize requests to reduce processing time

### Debug Mode

Enable debug logging by setting:
```bash
LOG_LEVEL=debug
```

Then check Vercel function logs for detailed information.

### Vercel Logs

View logs in Vercel dashboard:
1. **Go to** your project dashboard
2. **Click** "Functions" tab
3. **View** real-time logs
4. **Filter** by error level or time range

## 📊 Performance Optimization

### Vercel Configuration
The `vercel.json` file is optimized for:
- **Fast cold starts**: Minimal bundle size
- **Efficient routing**: Direct function routing
- **Proper headers**: CORS and security headers
- **Timeout handling**: 30-second function timeout

### Performance Tips
1. **Use environment variables** for configuration
2. **Enable caching** where appropriate
3. **Monitor function execution time** in Vercel dashboard
4. **Optimize AI API calls** to reduce latency
5. **Consider upgrading** to Vercel Pro for better performance

## 🔒 Security Considerations

### API Key Security
- **Use strong, unique API keys** for production
- **Rotate keys regularly** for security
- **Monitor usage** for unusual activity
- **Limit key permissions** where possible

### Environment Variables
- **Never commit** API keys to version control
- **Use Vercel's secure** environment variable storage
- **Separate** development and production keys
- **Audit access** to your Vercel project

### CORS Configuration
- **Restrict origins** in production if possible
- **Use specific domains** instead of `*` when feasible
- **Monitor requests** for unusual patterns
- **Implement rate limiting** if needed

## 📈 Monitoring and Maintenance

### Vercel Analytics
Enable Vercel Analytics for:
- **Function performance** monitoring
- **Error rate** tracking
- **Usage patterns** analysis
- **Cost optimization** insights

### Health Monitoring
Set up monitoring for:
- **Health endpoint** availability
- **API response times** 
- **Error rates** and patterns
- **Function execution** metrics

### Regular Maintenance
- **Update dependencies** regularly
- **Monitor API usage** and costs
- **Review logs** for errors or issues
- **Test functionality** after updates

## 🆘 Support

### Vercel Support
- **Documentation**: [vercel.com/docs](https://vercel.com/docs)
- **Community**: [github.com/vercel/vercel](https://github.com/vercel/vercel)
- **Support**: Available for Pro and Enterprise plans

### Project Support
- **GitHub Issues**: For bug reports and feature requests
- **Documentation**: Check README.md and other guides
- **Community**: WordPress and developer forums

---

**Deployment Complete!** Your SEO Forge MCP server is now running on Vercel and ready to power your WordPress plugin.