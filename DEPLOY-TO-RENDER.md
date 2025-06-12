# 🚀 Deploy to Render.com - Quick Start Guide

## 🎯 One-Click Deployment

### Step 1: Deploy to Render.com
[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://render.com/deploy?repo=https://github.com/khiwniti/SEOForge-mcp-server)

**OR manually:**

1. Go to [render.com](https://render.com)
2. Click "New +" → "Web Service"
3. Connect GitHub and select this repository
4. Use these settings:
   ```
   Name: universal-mcp-server
   Environment: Python 3
   Build Command: pip install -r requirements.txt
   Start Command: python main.py
   Plan: Free (or Starter for production)
   ```

### Step 2: Set Environment Variables
In Render dashboard, add:
- `GOOGLE_API_KEY` = Your Google Gemini API key

### Step 3: Deploy
Click "Create Web Service" and wait 2-3 minutes.

## 🎉 Your API is Live!

Your Universal MCP Server will be available at:
```
https://universal-mcp-server-xxxx.onrender.com
```

## 🔧 Quick Test

```bash
# Test your deployment
curl https://universal-mcp-server-xxxx.onrender.com/

# Should return:
{
  "status": "active",
  "message": "Universal MCP Server is running",
  "version": "3.0.0"
}
```

## 📦 WordPress Plugin Setup

1. Download: `universal-mcp-plugin-render.zip`
2. Install in WordPress: Admin → Plugins → Add New → Upload
3. Activate plugin
4. Go to: Admin → Universal MCP → Settings
5. Server URL should auto-detect your Render deployment
6. Test connection ✅

## 🤖 Chatbot Integration

Add to any website:
```html
<script src="https://universal-mcp-server-xxxx.onrender.com/static/chatbot-widget.js"></script>
<script>
  UMCPChatbot.init({
    serverUrl: 'https://universal-mcp-server-xxxx.onrender.com',
    companyName: 'Your Company'
  });
</script>
```

## 🎯 Available Endpoints

- `GET /` - Health check
- `POST /universal-mcp/generate-content` - AI content generation
- `POST /universal-mcp/generate-image` - AI image generation  
- `POST /universal-mcp/analyze-seo` - SEO analysis
- `POST /universal-mcp/chatbot` - AI chatbot responses
- `POST /universal-mcp/generate-blog-with-images` - Complete blog posts
- `GET /static/chatbot-widget.js` - Chatbot widget script

## 💰 Pricing

### Free Tier (Perfect for Testing):
- ✅ 750 hours/month (24/7 for 31 days)
- ✅ 512MB RAM, 0.1 CPU
- ✅ Automatic HTTPS
- ⚠️ Sleeps after 15 minutes of inactivity

### Starter Plan ($7/month - Recommended):
- ✅ Always on (no sleeping)
- ✅ 1GB RAM, 0.5 CPU  
- ✅ Better performance
- ✅ Priority support

## 🔒 Security Features

✅ **Automatic HTTPS** with SSL certificates
✅ **DDoS protection** included
✅ **Environment variables** encrypted
✅ **Private networking** between services
✅ **Security headers** automatically added

## 📊 Why Render.com?

| Feature | Render.com | Vercel | Railway |
|---------|------------|--------|---------|
| **Python Support** | ✅ Native | ❌ Serverless only | ✅ Native |
| **Always On** | ✅ Yes | ❌ Cold starts | ✅ Yes |
| **Free Tier** | ✅ 750h/month | ✅ 100GB-hours | ✅ $5 credit |
| **Automatic HTTPS** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Git Deployment** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Custom Domains** | ✅ Free | ✅ Free | ✅ Free |
| **Database** | ✅ PostgreSQL | ❌ External only | ✅ PostgreSQL |
| **Uptime SLA** | ✅ 99.9% | ✅ 99.9% | ❌ No SLA |

## 🚀 Next Steps

1. **Deploy** using the button above
2. **Test** your API endpoints
3. **Install** WordPress plugin
4. **Add** chatbot to your websites
5. **Monitor** performance in Render dashboard

## 🆘 Need Help?

- 📖 **Full Guide**: See `RENDER-DEPLOYMENT.md`
- 🐛 **Issues**: Check GitHub Issues
- 💬 **Support**: Render.com documentation
- 📧 **Contact**: Create GitHub issue

---

**Your AI-powered Universal MCP Server will be live in under 5 minutes!** 🎉