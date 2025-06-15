# SEOForge MCP Server Integration Guide

## Overview

SEOForge Express Backend now uses **Model Context Protocol (MCP)** for all AI operations, with **Google Gemini 2.5 Pro** as the primary AI model for enhanced accuracy and performance.

## MCP Architecture

```
SEOForge Express Backend
├── MCP Service Manager          # Central coordinator
├── Content Generation Service   # Blog, product, meta content
├── SEO Analysis Service        # SEO scoring and optimization
├── Keyword Research Service    # Keyword analysis and suggestions
├── Image Generation Service    # AI-powered image creation
├── Thai Language Service       # Thai language optimization
├── WordPress Service          # WordPress integration
└── Authentication Service     # User management
```

## AI Model Priority

### Primary: Google Gemini 2.5 Pro
- **API Key**: `AIzaSyDTITCw_UcgzUufrsCFuxp9HXri6Y0XrDo`
- **Model**: `gemini-2.0-flash-exp`
- **Use Cases**: All content generation, SEO analysis, keyword research
- **Benefits**: Superior accuracy, multilingual support, advanced reasoning

### Fallback Models
1. **Claude 3 Sonnet** - Long-form content
2. **GPT-4** - General purpose tasks

## MCP Tools Available

### 1. Content Generation
```typescript
{
  tool: "generate_content",
  arguments: {
    type: "blog" | "product" | "category" | "meta",
    topic: string,
    keywords: string[],
    language?: string,
    tone?: string,
    length?: "short" | "medium" | "long"
  }
}
```

### 2. SEO Analysis
```typescript
{
  tool: "analyze_seo",
  arguments: {
    content: string,
    target_keywords: string[],
    url?: string
  }
}
```

### 3. Keyword Research
```typescript
{
  tool: "research_keywords",
  arguments: {
    seed_keyword: string,
    language?: string,
    country?: string,
    limit?: number
  }
}
```

### 4. Image Generation
```typescript
{
  tool: "generate_image",
  arguments: {
    prompt: string,
    style?: string,
    size?: string,
    quality?: string
  }
}
```

### 5. Thai Language Optimization
```typescript
{
  tool: "optimize_thai",
  arguments: {
    content: string,
    keywords: string[],
    tone?: string
  }
}
```

### 6. WordPress Integration
```typescript
{
  tool: "wordpress_publish",
  arguments: {
    title: string,
    content: string,
    status?: "draft" | "publish",
    categories?: string[],
    tags?: string[]
  }
}
```

## API Endpoints

### MCP Tool Execution
```http
POST /mcp/execute
Content-Type: application/json
Authorization: Bearer <token>

{
  "tool": "generate_content",
  "arguments": {
    "type": "blog",
    "topic": "SEO Best Practices 2024",
    "keywords": ["SEO", "optimization", "ranking"],
    "length": "medium"
  }
}
```

### Response Format
```json
{
  "success": true,
  "result": {
    "content": "Generated content...",
    "seo_score": 85,
    "suggestions": ["Add more keywords", "Improve readability"],
    "metadata": {
      "word_count": 1250,
      "keyword_density": {"SEO": 2.1, "optimization": 1.8},
      "readability_score": 78
    }
  },
  "tool": "generate_content",
  "executionTime": 3500,
  "timestamp": "2024-06-15T04:30:00Z"
}
```

## Gemini 2.5 Pro Configuration

### Generation Settings
```typescript
{
  temperature: 0.7,        // Balanced creativity
  topK: 40,               // Token selection diversity
  topP: 0.95,             // Nucleus sampling
  maxOutputTokens: 8192,  // Extended output length
  candidateCount: 1       // Single response
}
```

### Safety Settings
- Harassment: BLOCK_MEDIUM_AND_ABOVE
- Hate Speech: BLOCK_MEDIUM_AND_ABOVE
- Sexually Explicit: BLOCK_MEDIUM_AND_ABOVE
- Dangerous Content: BLOCK_MEDIUM_AND_ABOVE

## Usage Examples

### Generate Blog Post
```bash
curl -X POST https://your-backend.vercel.app/mcp/execute \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-token" \
  -d '{
    "tool": "generate_content",
    "arguments": {
      "type": "blog",
      "topic": "WordPress SEO Guide",
      "keywords": ["WordPress", "SEO", "optimization"],
      "language": "en",
      "tone": "professional",
      "length": "long"
    }
  }'
```

### Analyze SEO
```bash
curl -X POST https://your-backend.vercel.app/mcp/execute \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-token" \
  -d '{
    "tool": "analyze_seo",
    "arguments": {
      "content": "Your content here...",
      "target_keywords": ["SEO", "WordPress", "optimization"]
    }
  }'
```

### Research Keywords
```bash
curl -X POST https://your-backend.vercel.app/mcp/execute \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-token" \
  -d '{
    "tool": "research_keywords",
    "arguments": {
      "seed_keyword": "digital marketing",
      "language": "en",
      "limit": 20
    }
  }'
```

## Performance Optimizations

### Caching
- Redis-based caching for frequent requests
- 15-minute cache for keyword research
- 5-minute cache for SEO analysis
- 1-hour cache for generated content

### Rate Limiting
- 1000 requests per 15 minutes per IP
- Burst protection for API endpoints
- Priority queuing for authenticated users

### Error Handling
- Automatic fallback to secondary AI models
- Retry logic with exponential backoff
- Comprehensive error logging and monitoring

## Monitoring & Analytics

### Metrics Tracked
- Tool execution times
- Success/failure rates
- AI model performance
- Cache hit rates
- User engagement

### Logging
- Structured JSON logging
- Error tracking with stack traces
- Performance monitoring
- Security event logging

## Security Features

### Authentication
- JWT-based authentication
- API key validation
- Role-based access control
- Session management

### Data Protection
- Input sanitization
- Output validation
- Rate limiting
- CORS protection
- Helmet.js security headers

## Deployment Considerations

### Environment Variables
```env
GOOGLE_API_KEY=AIzaSyDTITCw_UcgzUufrsCFuxp9HXri6Y0XrDo
NODE_ENV=production
REDIS_URL=redis://localhost:6379
JWT_SECRET=your-secret-key
```

### Scaling
- Serverless functions auto-scale
- Redis cluster for high availability
- CDN for static assets
- Load balancing for multiple regions

## Troubleshooting

### Common Issues
1. **API Key Errors**: Verify Gemini API key is valid
2. **Rate Limits**: Check API quotas and limits
3. **Timeout Errors**: Increase function timeout settings
4. **Cache Issues**: Clear Redis cache if needed

### Debug Mode
```env
LOG_LEVEL=debug
NODE_ENV=development
```

## Future Enhancements

- Multi-model ensemble for improved accuracy
- Real-time collaboration features
- Advanced analytics dashboard
- Custom model fine-tuning
- Voice-to-text content generation

## Support

For technical support or questions:
- Check logs: `vercel logs your-deployment-url`
- Review environment variables
- Verify API key permissions
- Test with debug mode enabled