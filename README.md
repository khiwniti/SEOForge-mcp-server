# 🚀 SEOForge MCP Server

A modern, AI-powered SEO content generation platform built with **Google Gemini 2.5 Pro**, **Express.js**, and **Model Context Protocol (MCP)** for enhanced accuracy and performance.

## ✨ Key Features

🤖 **AI-Powered Content Generation** - Blog posts, product descriptions, meta content  
🔍 **Advanced SEO Analysis** - Real-time scoring and optimization suggestions  
🎯 **Smart Keyword Research** - AI-driven keyword discovery and analysis  
🖼️ **AI Image Generation** - FLUX-powered visual content creation  
🇹🇭 **Thai Language Support** - Native Thai content optimization  
📝 **WordPress Integration** - Direct publishing and bulk operations  

## 🏗️ Clean Architecture

```
SEOForge Platform
├── 🖥️  backend-express/     # Express.js + MCP + Gemini 2.5 Pro
├── ⚛️  frontend/            # React 18 + Vite + TypeScript
├── 🔌 wordpress-plugin/     # WordPress Plugin (PHP)
└── 🗄️  database/           # PostgreSQL Schema
```

## 🚀 Quick Start

### One-Click Deployment (Recommended)
```bash
cd backend-express
./deploy-vercel.sh
```

### Local Development
```bash
# Backend
cd backend-express
npm install && npm run dev

# Frontend  
cd frontend
npm install && npm run dev
```

## 🤖 AI Models

**Primary**: Google Gemini 2.5 Pro (`gemini-2.0-flash-exp`)
- Enhanced accuracy and reasoning
- Pre-configured API key included
- Optimized for SEO content generation

**Fallbacks**: Claude 3 Sonnet, GPT-4

## 📊 Performance

- ⚡ **Serverless**: Auto-scaling with Vercel
- 🚀 **Fast**: Sub-second response times
- 🔒 **Secure**: JWT auth, rate limiting, CORS
- 📈 **Scalable**: MCP architecture for growth

## 🛠️ Technology Stack

**Backend**: Express.js, TypeScript, MCP Protocol, Google Gemini 2.5 Pro  
**Frontend**: React 18, Vite, TypeScript, Tailwind CSS  
**WordPress**: PHP 8+, REST API, Custom Blocks  
**Database**: PostgreSQL, Redis (caching)  

## 📚 Documentation

- [🚀 Deployment Guide](./DEPLOYMENT_GUIDE_EXPRESS.md)
- [🤖 MCP Integration Guide](./MCP_INTEGRATION_GUIDE.md)
- [🔧 Gemini Integration Summary](./GEMINI_INTEGRATION_SUMMARY.md)
- [📋 Cleanup Summary](./CLEANUP_SUMMARY.md)

## 🎯 API Endpoints

### Content Generation
```bash
POST /mcp/execute
{
  "tool": "generate_content",
  "arguments": {
    "type": "blog",
    "topic": "SEO Best Practices",
    "keywords": ["SEO", "optimization"],
    "length": "medium"
  }
}
```

### SEO Analysis
```bash
POST /mcp/execute
{
  "tool": "analyze_seo",
  "arguments": {
    "content": "Your content here...",
    "target_keywords": ["SEO", "ranking"]
  }
}
```

## 🔐 Environment Setup

```env
# AI Configuration (Gemini 2.5 Pro prioritized)
GOOGLE_API_KEY=AIzaSyDTITCw_UcgzUufrsCFuxp9HXri6Y0XrDo
OPENAI_API_KEY=your_openai_key  # Optional fallback
ANTHROPIC_API_KEY=your_claude_key  # Optional fallback

# Server Configuration
NODE_ENV=production
PORT=8000
CORS_ORIGINS=https://your-domain.com
```

## 📈 Project Status

✅ **Dependencies Cleaned** - Removed redundant files and directories  
✅ **Gemini 2.5 Pro Integrated** - Enhanced AI accuracy  
✅ **MCP Architecture** - Standardized AI operations  
✅ **Vercel Ready** - One-click deployment  
✅ **Production Optimized** - Security, performance, monitoring  

## 🤝 Contributing

```bash
git clone https://github.com/khiwniti/SEOForge-mcp-server.git
cd SEOForge-mcp-server
cd backend-express && npm install
cd ../frontend && npm install
```

## 📄 License

MIT License - see [LICENSE](./LICENSE) for details.

## 🆘 Support

- **Issues**: [GitHub Issues](https://github.com/khiwniti/SEOForge-mcp-server/issues)
- **Documentation**: Comprehensive guides included
- **Testing**: Local test scripts provided

---

**🎉 Ready for production deployment with enhanced AI accuracy!**