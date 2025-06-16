# SEOForge Public MCP Server API Guide

## 🌐 Public Access - No Authentication Required!

This is a **public API** that provides AI-powered content generation and SEO analysis services. No API keys or authentication tokens are required to use these endpoints.

## 🚀 Base URL
```
https://your-deployment-url.vercel.app
```

## 📋 Available Endpoints

### 1. **Content Generation** 
Generate SEO-optimized content in English or Thai

**Endpoint:** `POST /api/v1/content/generate`

**Request Body:**
```json
{
  "keyword": "artificial intelligence",
  "language": "en",
  "type": "blog_post",
  "length": "long",
  "style": "informative",
  "additional_keywords": ["machine learning", "AI technology"],
  "target_audience": "general",
  "include_faq": true,
  "include_images": false
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "title": "Artificial Intelligence - Complete Guide",
    "content": "<h1>Artificial Intelligence...</h1>",
    "excerpt": "Learn about artificial intelligence...",
    "meta_description": "Comprehensive guide to artificial intelligence...",
    "word_count": 1500,
    "language": "en",
    "seo_insights": {
      "keyword_density": {"artificial intelligence": 2.1},
      "readability_score": 85,
      "seo_score": 92
    },
    "performance": {
      "generation_time_ms": 1500,
      "ai_model": "gemini-2.0-flash-exp"
    }
  }
}
```

### 2. **Image Generation**
Generate AI-powered images with advanced parameters

**Endpoint:** `POST /api/v1/images/generate`

**Request Body:**
```json
{
  "prompt": "A futuristic AI robot in a modern office",
  "style": "photographic",
  "size": "1024x1024",
  "quality": "high",
  "negative_prompt": "blurry, low quality",
  "steps": 20,
  "guidance_scale": 7.5
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "image_url": "https://generated-image-url.com/image.png",
    "prompt": "A futuristic AI robot in a modern office, professional photography...",
    "style": "photographic",
    "size": "1024x1024",
    "model_used": "flux",
    "generation_params": {
      "steps": 20,
      "guidance_scale": 7.5
    },
    "performance": {
      "generation_time_ms": 1200
    }
  }
}
```

### 3. **Content Analysis**
Analyze content for SEO optimization

**Endpoint:** `POST /api/v1/content/analyze`

**Request Body:**
```json
{
  "content": "<h1>Your content here...</h1>",
  "keywords": ["target keyword", "secondary keyword"],
  "language": "en"
}
```

### 4. **API Capabilities**
Get information about available features

**Endpoint:** `GET /api/v1/capabilities`

**Response:**
```json
{
  "success": true,
  "capabilities": {
    "content_generation": {
      "available": true,
      "supported_languages": ["en", "th"],
      "supported_types": ["blog_post", "article", "guide", "tutorial"],
      "features": {
        "seo_optimization": true,
        "keyword_analysis": true,
        "content_templates": true
      }
    },
    "image_generation": {
      "available": true,
      "supported_styles": ["photographic", "illustration", "digital_art"],
      "models": ["flux", "dalle", "stable-diffusion"]
    }
  }
}
```

### 5. **Performance Metrics**
Get real-time API performance data

**Endpoint:** `GET /api/v1/metrics`

### 6. **Health Check**
Check API status

**Endpoint:** `GET /health`

## 🎯 Supported Parameters

### Content Generation
- **Languages:** `en` (English), `th` (Thai)
- **Types:** `blog_post`, `article`, `guide`, `tutorial`
- **Lengths:** `short` (400-800 words), `medium` (800-1500 words), `long` (1500-3000 words)
- **Styles:** `informative`, `conversational`, `professional`, `casual`, `technical`

### Image Generation
- **Styles:** `photographic`, `illustration`, `digital_art`, `minimalist`, `realistic`, `artistic`, `cartoon`, `sketch`
- **Sizes:** `512x512`, `1024x1024`, `1024x768`, `768x1024`, `1536x1024`, `1024x1536`
- **Quality:** `high`, `medium`, `low`
- **Models:** `flux`, `dalle`, `stable-diffusion`, `midjourney`

## 🔧 Rate Limits
- **Content Generation:** 50 requests per hour
- **Image Generation:** 100 requests per hour
- **General API:** 200 requests per hour

## 💡 Example Usage

### cURL Examples

**Generate Content:**
```bash
curl -X POST https://your-deployment-url.vercel.app/api/v1/content/generate \
  -H "Content-Type: application/json" \
  -d '{
    "keyword": "sustainable energy",
    "language": "en",
    "length": "medium",
    "style": "informative"
  }'
```

**Generate Image:**
```bash
curl -X POST https://your-deployment-url.vercel.app/api/v1/images/generate \
  -H "Content-Type: application/json" \
  -d '{
    "prompt": "Solar panels on a modern house",
    "style": "photographic",
    "quality": "high"
  }'
```

### JavaScript/Node.js Example
```javascript
const response = await fetch('https://your-deployment-url.vercel.app/api/v1/content/generate', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    keyword: 'machine learning',
    language: 'en',
    length: 'long',
    style: 'professional'
  })
});

const data = await response.json();
console.log(data);
```

### Python Example
```python
import requests

response = requests.post(
    'https://your-deployment-url.vercel.app/api/v1/content/generate',
    json={
        'keyword': 'artificial intelligence',
        'language': 'en',
        'length': 'medium',
        'style': 'informative'
    }
)

data = response.json()
print(data)
```

## 🌟 Features

✅ **No Authentication Required** - Public access for everyone
✅ **Multi-language Support** - English and Thai content generation
✅ **Advanced AI Models** - Google Gemini 2.0 Flash, FLUX, and more
✅ **SEO Optimization** - Built-in keyword analysis and optimization
✅ **Performance Monitoring** - Real-time metrics and analytics
✅ **WordPress Compatible** - Direct integration with WordPress plugins
✅ **Intelligent Caching** - Faster responses for repeated requests
✅ **Error Recovery** - Automatic fallback to alternative AI models

## 🚀 Getting Started

1. **No setup required!** Just start making requests to the API endpoints
2. **Test the API** with the `/health` endpoint first
3. **Check capabilities** with `/api/v1/capabilities`
4. **Generate your first content** with `/api/v1/content/generate`

## 📞 Support

This is a public MCP server designed for open access. For issues or questions, please refer to the GitHub repository or documentation.

**Happy content generating! 🎉**
