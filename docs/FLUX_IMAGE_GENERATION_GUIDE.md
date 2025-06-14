# 🎨 Flux Image Generation Integration Guide

## Overview

This guide covers the enhanced Flux image generation capabilities integrated into the SEO Forge MCP Server. The implementation provides state-of-the-art AI image generation using multiple Flux models with fallback mechanisms and optimization features.

## 🚀 Features

### Flux Models Supported
- **FLUX.1-schnell**: Fast generation (4-8 steps) - Best for previews
- **FLUX.1-dev**: High quality (20-50 steps) - Best for final images  
- **FLUX.1-pro**: Professional quality (25-50 steps) - Best for commercial use

### Generation Methods
1. **Hugging Face Inference API** (Primary - requires token)
2. **Pollinations AI with Flux** (Free fallback)
3. **Replicate API** (Premium - requires token)
4. **Together AI** (Alternative - requires token)
5. **Enhanced Placeholder** (Final fallback)

### Key Features
- ✅ Multiple Flux model support
- ✅ AI-powered prompt enhancement
- ✅ Batch image generation
- ✅ Thai language support
- ✅ Multiple fallback methods
- ✅ Professional styling options
- ✅ Configurable parameters
- ✅ Real-time generation status

## 📋 API Endpoints

### 1. Single Image Generation
```http
POST /universal-mcp/generate-flux-image
```

**Request Body:**
```json
{
  "prompt": "A professional business meeting in a modern office",
  "negative_prompt": "blurry, low quality, distorted",
  "width": 1024,
  "height": 1024,
  "guidance_scale": 7.5,
  "num_inference_steps": 20,
  "seed": null,
  "model": "flux-schnell",
  "style": "professional",
  "enhance_prompt": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "Flux image generated successfully",
  "data": {
    "id": "uuid-string",
    "filename": "flux_uuid.png",
    "url": "/images/flux_uuid.png",
    "prompt": "Enhanced prompt text...",
    "width": 1024,
    "height": 1024,
    "model": "flux-schnell",
    "generation_method": "pollinations_flux",
    "seed": 123456,
    "generated_at": "2025-06-12T04:45:00Z",
    "file_size": 1048576
  }
}
```

### 2. Batch Image Generation
```http
POST /universal-mcp/generate-flux-batch
```

**Request Body:**
```json
{
  "prompts": [
    "A modern office building at sunset",
    "A team of professionals in a meeting",
    "A sleek product photography setup"
  ],
  "model": "flux-schnell",
  "style": "professional",
  "width": 1024,
  "height": 1024,
  "num_inference_steps": 8,
  "guidance_scale": 7.5,
  "enhance_prompt": true
}
```

### 3. Available Models
```http
GET /universal-mcp/flux-models
```

### 4. Prompt Enhancement
```http
POST /universal-mcp/enhance-flux-prompt
```

**Request Body:**
```json
{
  "prompt": "business meeting",
  "style": "professional"
}
```

## 🛠️ Configuration

### Environment Variables

```bash
# Optional API tokens for enhanced capabilities
HUGGINGFACE_TOKEN=hf_your_token_here
REPLICATE_API_TOKEN=r8_your_token_here
TOGETHER_API_KEY=your_together_key_here
GOOGLE_API_KEY=your_gemini_key_here
```

### Model Configuration

```python
# Default settings by model
FLUX_SETTINGS = {
    "flux-schnell": {
        "steps": 4,
        "guidance_scale": 7.5,
        "description": "Fast generation, good for previews"
    },
    "flux-dev": {
        "steps": 20,
        "guidance_scale": 7.5,
        "description": "High quality, slower generation"
    },
    "flux-pro": {
        "steps": 25,
        "guidance_scale": 7.5,
        "description": "Professional quality, commercial use"
    }
}
```

## 🎨 Style Options

### Available Styles
- **professional**: Clean, modern, business-appropriate imagery
- **artistic**: Creative, expressive, fine art style
- **photorealistic**: Realistic photography style
- **minimalist**: Simple, clean, minimal design
- **commercial**: Marketing and advertising style
- **cinematic**: Movie-like, dramatic lighting
- **illustration**: Digital illustration and vector art
- **fantasy**: Magical, fantastical, imaginative
- **modern**: Contemporary, trendy, stylish

### Style Enhancement Keywords
Each style automatically adds relevant keywords to improve generation:

```python
STYLE_ENHANCEMENTS = {
    'professional': 'professional photography, commercial quality, studio lighting, sharp focus, high resolution, clean composition, modern aesthetic',
    'artistic': 'artistic masterpiece, creative composition, dramatic lighting, vibrant colors, fine art quality, aesthetic beauty, visual impact',
    'photorealistic': 'photorealistic, ultra detailed, natural lighting, high definition, lifelike, realistic textures, professional photography'
    # ... more styles
}
```

## 🇹🇭 Thai Language Support

The system includes enhanced support for Thai keywords and content:

### Thai Keywords Testing
```javascript
const thaiPrompts = [
    'กระดาษมวนขายส่ง กรุงเทพฯ, professional business photography',
    'กระดาษมวน RAW ขายส่ง, product showcase, commercial photography',
    'บ้องแก้ว, artistic glass art, beautiful craftsmanship'
];
```

### Thai Text Rendering
- Supports Thai text in image generation
- Proper font handling for Thai characters
- Cultural context awareness in prompt enhancement

## 🔧 Implementation Details

### File Structure
```
backend/
├── app/
│   ├── services/
│   │   └── flux_image_generator.py    # Main Flux generator
│   └── apis/
│       └── flux_image_gen/
│           └── __init__.py            # API endpoints
├── main.py                            # Enhanced with Flux endpoints
└── requirements.txt                   # Updated dependencies
```

### Key Classes

#### FluxImageGenerator
```python
class FluxImageGenerator:
    def __init__(self):
        self.device = "cuda" if torch.cuda.is_available() else "cpu"
        self.available_models = {...}
        self.hf_endpoints = {...}
        
    async def generate_image(self, prompt, **kwargs):
        # Main generation method with fallbacks
        
    async def generate_multiple_images(self, prompts, **kwargs):
        # Batch generation with concurrency
        
    async def _enhance_prompt_for_flux(self, prompt, style):
        # AI-powered prompt enhancement
```

### Fallback Mechanism
1. **Primary**: Hugging Face Inference API (if token available)
2. **Secondary**: Pollinations AI with Flux (free, reliable)
3. **Tertiary**: Replicate API (if token available)
4. **Quaternary**: Together AI (if token available)
5. **Final**: Enhanced placeholder generation

## 📊 Performance Optimization

### Generation Times
- **flux-schnell**: 5-15 seconds
- **flux-dev**: 30-90 seconds
- **flux-pro**: 45-120 seconds

### Batch Processing
- Concurrent generation for multiple images
- Maximum 10 images per batch request
- Automatic load balancing across providers

### Caching Strategy
- Generated images cached locally
- Unique filenames with UUID
- Automatic cleanup of old files

## 🧪 Testing

### Test Interface
Access the comprehensive test interface at:
```
http://your-domain/flux_image_test.html
```

### Test Features
- Single image generation with all parameters
- Batch generation testing
- Thai keywords testing
- Model comparison
- Style comparison
- Performance monitoring

### Example Test Cases

#### Professional Business Images
```json
{
  "prompt": "A modern corporate office with glass walls and professional lighting",
  "style": "professional",
  "model": "flux-dev",
  "steps": 20
}
```

#### Thai E-commerce Products
```json
{
  "prompt": "กระดาษมวนขายส่ง กรุงเทพฯ, professional product photography, clean background",
  "style": "commercial",
  "model": "flux-schnell",
  "steps": 8
}
```

## 🚨 Error Handling

### Common Issues and Solutions

#### 1. API Token Issues
```json
{
  "success": false,
  "error": "HuggingFace API error: 401",
  "fallback_used": "pollinations_flux"
}
```

#### 2. Generation Timeout
```json
{
  "success": false,
  "error": "Generation timeout after 60 seconds",
  "suggestion": "Try flux-schnell model for faster generation"
}
```

#### 3. Invalid Parameters
```json
{
  "success": false,
  "error": "Width must be between 256 and 2048 pixels"
}
```

### Monitoring and Logging
- Detailed logging for each generation attempt
- Performance metrics tracking
- Error rate monitoring
- Fallback usage statistics

## 🔐 Security Considerations

### API Token Management
- Store tokens in environment variables
- Never expose tokens in client-side code
- Rotate tokens regularly
- Monitor token usage

### Content Filtering
- Automatic content moderation
- Inappropriate prompt detection
- Safe content guidelines
- Legal compliance checks

## 📈 Scaling and Production

### Production Deployment
1. Set up proper API tokens for all providers
2. Configure load balancing
3. Set up monitoring and alerting
4. Implement rate limiting
5. Configure image storage and CDN

### Performance Monitoring
```python
# Example monitoring metrics
METRICS = {
    "generation_time": "Average time per image",
    "success_rate": "Percentage of successful generations",
    "fallback_usage": "Which providers are being used",
    "error_rate": "Percentage of failed generations"
}
```

## 🔄 Updates and Maintenance

### Regular Updates
- Monitor Flux model updates
- Update API endpoints as needed
- Refresh fallback mechanisms
- Update style enhancements

### Version History
- **v1.0.0**: Initial Flux integration
- **v1.1.0**: Added batch generation
- **v1.2.0**: Enhanced Thai language support
- **v1.3.0**: Added multiple provider fallbacks

## 📞 Support and Troubleshooting

### Common Commands
```bash
# Test the Flux generator
curl -X POST "http://localhost:8083/universal-mcp/generate-flux-image" \
  -H "Content-Type: application/json" \
  -d '{"prompt": "test image", "model": "flux-schnell"}'

# Check available models
curl "http://localhost:8083/universal-mcp/flux-models"

# Check system status
curl "http://localhost:8083/universal-mcp/status"
```

### Debug Mode
Enable detailed logging:
```python
import logging
logging.getLogger("flux_image_generator").setLevel(logging.DEBUG)
```

## 🎯 Best Practices

### Prompt Writing
1. Be specific and descriptive
2. Include style keywords
3. Mention lighting and composition
4. Specify quality requirements
5. Use negative prompts effectively

### Model Selection
- **flux-schnell**: Quick previews, testing, batch generation
- **flux-dev**: Final high-quality images, detailed work
- **flux-pro**: Commercial use, professional projects

### Performance Tips
1. Use appropriate step counts for each model
2. Batch similar requests together
3. Cache frequently used images
4. Monitor generation times
5. Use fallback providers effectively

---

## 📝 Conclusion

The Flux image generation integration provides a robust, scalable solution for high-quality AI image generation with multiple fallback mechanisms and comprehensive Thai language support. The system is designed for production use with proper error handling, monitoring, and optimization features.

For additional support or feature requests, please refer to the main repository documentation or contact the development team.