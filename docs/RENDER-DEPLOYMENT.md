# 🚀 Render.com Deployment Guide

## 🌟 Why Render.com?

Render.com is the **perfect choice** for our Universal MCP Server because:

- ✅ **Native Python support** - No configuration needed
- ✅ **Free tier** with 750 hours/month
- ✅ **Automatic HTTPS** and SSL certificates
- ✅ **Git-based deployment** - Deploy from GitHub automatically
- ✅ **Environment variables** management
- ✅ **99.9% uptime** SLA
- ✅ **Global CDN** included
- ✅ **Zero cold starts** (unlike serverless)
- ✅ **Simple pricing** - No surprises

## 🚀 One-Click Deployment

### Method 1: Deploy from GitHub (Recommended)

1. **Fork the Repository** (if you haven't already)
   - Go to: https://github.com/khiwniti/SEOForge-mcp-server
   - Click "Fork" to create your own copy

2. **Connect to Render.com**
   - Go to: https://render.com
   - Sign up/Login with GitHub
   - Click "New +" → "Web Service"
   - Connect your GitHub account
   - Select the `SEOForge-mcp-server` repository

3. **Configure Deployment**
   ```
   Name: universal-mcp-server
   Environment: Python 3
   Build Command: pip install -r requirements.txt
   Start Command: python main.py
   Plan: Free (or Starter for production)
   ```

4. **Set Environment Variables**
   - Click "Environment" tab
   - Add: `GOOGLE_API_KEY` = `your-google-gemini-api-key`
   - Add: `PORT` = `10000` (auto-set by Render)
   - Add: `HOST` = `0.0.0.0` (auto-set by Render)

5. **Deploy**
   - Click "Create Web Service"
   - Wait 2-3 minutes for deployment
   - Your API will be live at: `https://universal-mcp-server-xxxx.onrender.com`

### Method 2: Manual Upload

1. **Download the Code**
   - Download this repository as ZIP
   - Extract to your computer

2. **Create Render Service**
   - Go to https://render.com
   - Click "New +" → "Web Service"
   - Choose "Deploy an existing image" → "Public Git repository"
   - Enter: `https://github.com/khiwniti/SEOForge-mcp-server`

3. **Configure and Deploy** (same as Method 1)

## 🔧 Configuration

### Required Environment Variables

| Variable | Value | Description |
|----------|-------|-------------|
| `GOOGLE_API_KEY` | Your API key | Google Gemini API key |
| `PORT` | 10000 | Auto-set by Render |
| `HOST` | 0.0.0.0 | Auto-set by Render |

### Optional Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `DEBUG` | false | Enable debug logging |
| `CORS_ORIGINS` | * | Allowed CORS origins |

## 🎯 Your Live API

Once deployed, your API will be available at:
```
https://universal-mcp-server-xxxx.onrender.com
```

### Test Your Deployment

```bash
# Health check
curl https://universal-mcp-server-xxxx.onrender.com/

# Content generation
curl -X POST https://universal-mcp-server-xxxx.onrender.com/universal-mcp/generate-content \
  -H "Content-Type: application/json" \
  -d '{
    "topic": "AI Technology",
    "keywords": ["AI", "technology"],
    "language": "en"
  }'

# Chatbot test
curl -X POST https://universal-mcp-server-xxxx.onrender.com/universal-mcp/chatbot \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Hello, what can you help me with?",
    "website_url": "https://example.com"
  }'
```

## 📦 Update WordPress Plugin

After deployment, update your WordPress plugin:

1. **Go to WordPress Admin** → Universal MCP → Settings
2. **Update Server URL** to: `https://universal-mcp-server-xxxx.onrender.com`
3. **Test Connection** - Should show "Online ✅"
4. **Save Settings**

## 🤖 Update Chatbot Widget

Update your chatbot integration:

```html
<script src="https://universal-mcp-server-xxxx.onrender.com/static/chatbot-widget.js"></script>
<script>
  UMCPChatbot.init({
    serverUrl: 'https://universal-mcp-server-xxxx.onrender.com',
    companyName: 'Your Company'
  });
</script>
```

## 🔄 Automatic Updates

### Git-based Deployment Benefits:
- **Auto-deploy** when you push to GitHub
- **Rollback** to previous versions easily
- **Branch deployments** for testing
- **Build logs** for debugging

### Enable Auto-Deploy:
1. Go to Render Dashboard → Your Service
2. Click "Settings" tab
3. Enable "Auto-Deploy" from GitHub
4. Choose branch (usually `main`)

## 📊 Monitoring & Logs

### View Logs:
1. Go to Render Dashboard → Your Service
2. Click "Logs" tab
3. See real-time application logs

### Monitor Performance:
1. Click "Metrics" tab to see:
   - CPU usage
   - Memory usage
   - Response times
   - Request volume

### Health Checks:
- Render automatically monitors `/` endpoint
- Service restarts if health check fails
- Email notifications for downtime

## 💰 Pricing

### Free Tier:
- ✅ **750 hours/month** (enough for 24/7 operation)
- ✅ **512MB RAM**
- ✅ **0.1 CPU**
- ✅ **Automatic HTTPS**
- ✅ **Custom domains**
- ⚠️ **Sleeps after 15 minutes** of inactivity

### Starter Plan ($7/month):
- ✅ **Always on** - No sleeping
- ✅ **1GB RAM**
- ✅ **0.5 CPU**
- ✅ **Priority support**
- ✅ **Better performance**

### Pro Plan ($25/month):
- ✅ **4GB RAM**
- ✅ **2 CPU**
- ✅ **Horizontal scaling**
- ✅ **Advanced metrics**

## 🔒 Security Features

### Automatic Security:
- ✅ **HTTPS/SSL** certificates (auto-renewed)
- ✅ **DDoS protection**
- ✅ **Environment variable** encryption
- ✅ **Private networking** between services
- ✅ **Security headers** included

### Best Practices:
- Keep `GOOGLE_API_KEY` in environment variables
- Use HTTPS URLs only
- Monitor logs for suspicious activity
- Enable notifications for service issues

## 🚀 Performance Optimization

### For Free Tier:
```python
# Add to main.py to prevent sleeping
import threading
import time
import requests

def keep_alive():
    while True:
        try:
            requests.get("https://your-service.onrender.com/")
            time.sleep(840)  # 14 minutes
        except:
            pass

# Start keep-alive thread
threading.Thread(target=keep_alive, daemon=True).start()
```

### For Production:
- Upgrade to Starter plan ($7/month)
- Enable horizontal scaling
- Use Redis for caching (separate service)
- Monitor performance metrics

## 🔧 Troubleshooting

### Common Issues:

#### 1. Service Won't Start
- Check build logs in Render dashboard
- Verify `requirements.txt` is correct
- Ensure `main.py` is in root directory

#### 2. API Key Errors
- Verify `GOOGLE_API_KEY` is set correctly
- Check API key has Gemini access enabled
- Test API key with direct Google API call

#### 3. CORS Errors
- CORS is already configured in the code
- Check browser console for specific errors
- Verify request format

#### 4. Slow Response Times
- Free tier has limited resources
- Consider upgrading to Starter plan
- Check Google API response times

#### 5. Service Sleeping (Free Tier)
- Implement keep-alive function above
- Or upgrade to paid plan
- Use external monitoring service

## 🎯 Custom Domain (Optional)

### Add Your Domain:
1. Go to Render Dashboard → Your Service
2. Click "Settings" tab
3. Scroll to "Custom Domains"
4. Add your domain: `api.yourdomain.com`
5. Update DNS records as instructed
6. SSL certificate auto-generated

### Update Configurations:
- WordPress plugin: Use your custom domain
- Chatbot widgets: Update server URL
- API documentation: Update examples

## 📈 Scaling Considerations

### Traffic Growth:
- **Free tier**: ~1000 requests/hour
- **Starter plan**: ~10,000 requests/hour  
- **Pro plan**: ~100,000 requests/hour
- **Horizontal scaling**: Multiple instances

### Database Needs:
- Current setup: In-memory (resets on restart)
- For persistence: Add PostgreSQL service
- For caching: Add Redis service
- For files: Use Render Disks

## 🎉 Success Checklist

After deployment, verify:

- [ ] ✅ API health check returns 200
- [ ] ✅ Content generation works
- [ ] ✅ Image generation works  
- [ ] ✅ SEO analysis works
- [ ] ✅ Chatbot responds correctly
- [ ] ✅ WordPress plugin connects
- [ ] ✅ Chatbot widget loads
- [ ] ✅ HTTPS certificate active
- [ ] ✅ Logs show no errors

## 🔗 Useful Links

- **Render Dashboard**: https://dashboard.render.com
- **Render Documentation**: https://render.com/docs
- **Python on Render**: https://render.com/docs/deploy-python
- **Environment Variables**: https://render.com/docs/environment-variables
- **Custom Domains**: https://render.com/docs/custom-domains

---

## 🎊 Congratulations!

Your Universal MCP Server is now running on **enterprise-grade infrastructure** with:

- ✅ **99.9% uptime** guarantee
- ✅ **Automatic scaling** and load balancing
- ✅ **Global CDN** for fast responses worldwide
- ✅ **Automatic HTTPS** and security
- ✅ **Git-based deployment** for easy updates
- ✅ **Professional monitoring** and logging

**Your AI-powered WordPress plugin and chatbot are now production-ready!** 🚀