/**
 * API v1 routes for SEO-Forge WordPress plugin compatibility
 * Implements the exact API specification from API_REQUIREMENTS.md
 */

import { Router, Request, Response } from 'express';
import { body, validationResult } from 'express-validator';
import { asyncHandler, createError } from '../middleware/error-handler.js';
import { authMiddleware, AuthenticatedRequest } from '../middleware/auth.js';
import { contentGenerationRateLimit, imageGenerationRateLimit, generalApiRateLimit } from '../middleware/rate-limit.js';
import { MCPServiceManager } from '../services/mcp-service-manager.js';

export const v1Routes = Router();

// Content Generation API - Enhanced with intelligent features
v1Routes.post('/content/generate',
  contentGenerationRateLimit,
  authMiddleware,
  [
    body('keyword').isString().notEmpty().withMessage('Keyword is required')
      .isLength({ min: 2, max: 200 }).withMessage('Keyword must be between 2 and 200 characters'),
    body('language').isIn(['en', 'th']).withMessage('Language must be "en" or "th"'),
    body('type').optional().isString().withMessage('Type must be a string')
      .isIn(['blog_post', 'article', 'guide', 'tutorial']).withMessage('Invalid content type'),
    body('length').optional().isIn(['short', 'medium', 'long']).withMessage('Length must be short, medium, or long'),
    body('style').optional().isString().withMessage('Style must be a string')
      .isIn(['informative', 'conversational', 'professional', 'casual', 'technical']).withMessage('Invalid style'),
    body('additional_keywords').optional().isArray().withMessage('Additional keywords must be an array'),
    body('target_audience').optional().isString().withMessage('Target audience must be a string'),
    body('include_faq').optional().isBoolean().withMessage('Include FAQ must be a boolean'),
    body('include_images').optional().isBoolean().withMessage('Include images must be a boolean')
  ],
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const startTime = Date.now();

    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        error: {
          code: 'VALIDATION_ERROR',
          message: 'Request validation failed',
          details: errors.array(),
          timestamp: new Date().toISOString()
        }
      });
    }

    const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
    if (!mcpManager) {
      return res.status(503).json({
        success: false,
        error: {
          code: 'SERVICE_UNAVAILABLE',
          message: 'Content generation service is temporarily unavailable',
          timestamp: new Date().toISOString()
        }
      });
    }

    const {
      keyword,
      language,
      type = 'blog_post',
      length = 'long',
      style = 'informative',
      additional_keywords = [],
      target_audience = 'general',
      include_faq = false,
      include_images = false
    } = req.body;

    try {
      // Prepare enhanced keyword list
      const allKeywords = [keyword, ...additional_keywords].filter(k => k && k.trim().length > 0);

      // Determine optimal word count based on content type and length
      const wordCountMap = {
        short: { blog_post: 500, article: 400, guide: 600, tutorial: 500 },
        medium: { blog_post: 1000, article: 800, guide: 1200, tutorial: 1000 },
        long: { blog_post: 2000, article: 1500, guide: 2500, tutorial: 2000 }
      };

      const minWordCount = wordCountMap[length]?.[type] || wordCountMap[length]?.blog_post || 1000;

      // Enhanced content generation request
      const result = await mcpManager.executeTool({
        tool: 'generate_content',
        arguments: {
          type: 'blog',
          topic: keyword,
          keywords: allKeywords,
          language,
          tone: style,
          length,
          target_audience,
          content_type: type,
          seo_requirements: {
            target_keyword: keyword,
            secondary_keywords: additional_keywords,
            keyword_density: '1-2%',
            meta_description_length: language === 'th' ? 120 : 160,
            title_length: language === 'th' ? 50 : 60,
            min_word_count: minWordCount,
            include_faq,
            include_images,
            readability_target: 'grade_8'
          },
          enhancement_options: {
            add_statistics: true,
            add_examples: true,
            add_actionable_tips: true,
            optimize_for_featured_snippets: true,
            include_schema_markup: true
          }
        },
        context: {
          user: req.user,
          api_version: 'v1',
          plugin_compatibility: true,
          request_id: `req_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
          generation_timestamp: new Date().toISOString()
        }
      });

      if (!result.success) {
        const errorCode = this.determineErrorCode(result.error);
        return res.status(500).json({
          success: false,
          error: {
            code: errorCode,
            message: result.error || 'Content generation failed',
            details: result.error,
            timestamp: new Date().toISOString(),
            request_id: result.context?.request_id
          }
        });
      }

      // Format response with enhanced data
      const content = result.result;
      const wordCount = content.content ? content.content.replace(/<[^>]*>/g, '').split(/\s+/).length : 0;
      const processingTime = Date.now() - startTime;

      // Generate additional SEO insights
      const seoInsights = {
        keyword_density: this.calculateKeywordDensity(content.content, allKeywords),
        readability_score: content.metadata?.readability_score || 0,
        seo_score: content.seo_score || 0,
        suggestions: content.suggestions || [],
        schema_markup: content.schema_suggestions || null,
        internal_linking: content.internal_linking_suggestions || []
      };

      return res.json({
        success: true,
        data: {
          title: content.title || `${keyword} - Complete Guide`,
          content: content.content || content.html || '',
          excerpt: content.excerpt || content.summary || '',
          meta_description: content.meta_description || content.description || '',
          word_count: wordCount,
          language,
          keyword,
          additional_keywords,
          target_audience,
          content_type: type,
          style,
          length,
          seo_insights,
          performance: {
            generation_time_ms: processingTime,
            ai_model: content.metadata?.ai_model || 'gemini-2.0-flash-exp',
            template_used: content.metadata?.template_used || 'default',
            cache_hit: false
          },
          generated_at: new Date().toISOString(),
          request_id: result.context?.request_id
        }
      });

    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Unknown error';
      const processingTime = Date.now() - startTime;

      // Log error for monitoring
      console.error('Content generation error:', {
        error: errorMessage,
        keyword,
        language,
        type,
        user_id: req.user?.id,
        processing_time: processingTime,
        timestamp: new Date().toISOString()
      });

      return res.status(500).json({
        success: false,
        error: {
          code: 'GENERATION_FAILED',
          message: 'Content generation failed',
          details: process.env.NODE_ENV === 'development' ? errorMessage : 'Internal server error',
          timestamp: new Date().toISOString(),
          processing_time_ms: processingTime
        }
      });
    }
  })
);

// Helper function to determine error codes
function determineErrorCode(error: string): string {
  if (error?.includes('rate limit')) return 'RATE_LIMIT_EXCEEDED';
  if (error?.includes('API key')) return 'INVALID_API_KEY';
  if (error?.includes('quota')) return 'QUOTA_EXCEEDED';
  if (error?.includes('timeout')) return 'REQUEST_TIMEOUT';
  if (error?.includes('safety')) return 'CONTENT_BLOCKED';
  return 'GENERATION_FAILED';
}

// Helper function to calculate keyword density
function calculateKeywordDensity(content: string, keywords: string[]): Record<string, number> {
  const text = content.replace(/<[^>]*>/g, '').toLowerCase();
  const words = text.split(/\s+/).length;
  const density: Record<string, number> = {};

  keywords.forEach(keyword => {
    const regex = new RegExp(keyword.toLowerCase(), 'gi');
    const matches = text.match(regex) || [];
    density[keyword] = words > 0 ? (matches.length / words) * 100 : 0;
  });

  return density;
}

// Image Generation API - Enhanced with intelligent features
v1Routes.post('/images/generate',
  imageGenerationRateLimit,
  authMiddleware,
  [
    body('prompt').isString().notEmpty().withMessage('Prompt is required')
      .isLength({ min: 10, max: 1000 }).withMessage('Prompt must be between 10 and 1000 characters'),
    body('style').optional().isString().withMessage('Style must be a string')
      .isIn(['photographic', 'illustration', 'digital_art', 'minimalist', 'realistic', 'artistic', 'cartoon', 'sketch']).withMessage('Invalid style'),
    body('size').optional().isIn(['1024x1024', '1024x768', '768x1024', '512x512', '1536x1024', '1024x1536']).withMessage('Invalid size'),
    body('quality').optional().isIn(['high', 'medium', 'low']).withMessage('Invalid quality'),
    body('negative_prompt').optional().isString().withMessage('Negative prompt must be a string'),
    body('seed').optional().isInt({ min: 0 }).withMessage('Seed must be a positive integer'),
    body('steps').optional().isInt({ min: 1, max: 50 }).withMessage('Steps must be between 1 and 50'),
    body('guidance_scale').optional().isFloat({ min: 1, max: 20 }).withMessage('Guidance scale must be between 1 and 20'),
    body('model_preference').optional().isIn(['flux', 'dalle', 'midjourney', 'stable-diffusion']).withMessage('Invalid model preference')
  ],
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const startTime = Date.now();

    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        error: {
          code: 'VALIDATION_ERROR',
          message: 'Request validation failed',
          details: errors.array(),
          timestamp: new Date().toISOString()
        }
      });
    }

    const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
    if (!mcpManager) {
      return res.status(503).json({
        success: false,
        error: {
          code: 'SERVICE_UNAVAILABLE',
          message: 'Image generation service is temporarily unavailable',
          timestamp: new Date().toISOString()
        }
      });
    }

    const {
      prompt,
      style = 'photographic',
      size = '1024x1024',
      quality = 'high',
      negative_prompt = '',
      seed,
      steps = 20,
      guidance_scale = 7.5,
      model_preference = 'flux'
    } = req.body;

    try {
      // Enhance prompt with style-specific improvements
      const enhancedPrompt = enhanceImagePrompt(prompt, style, quality);

      // Prepare advanced generation parameters
      const result = await mcpManager.executeTool({
        tool: 'generate_image',
        arguments: {
          prompt: enhancedPrompt,
          negative_prompt: negative_prompt || getDefaultNegativePrompt(style),
          style,
          size,
          quality,
          model: model_preference,
          advanced_params: {
            seed,
            steps,
            guidance_scale,
            sampler: 'DPM++ 2M Karras',
            cfg_scale: guidance_scale
          },
          optimization: {
            enhance_details: quality === 'high',
            upscale: quality === 'high' && size.includes('1024'),
            face_enhancement: style === 'photographic',
            color_correction: true
          }
        },
        context: {
          user: req.user,
          api_version: 'v1',
          plugin_compatibility: true,
          request_id: `img_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
          generation_timestamp: new Date().toISOString()
        }
      });

      if (!result.success) {
        const errorCode = determineImageErrorCode(result.error);
        return res.status(500).json({
          success: false,
          error: {
            code: errorCode,
            message: result.error || 'Image generation failed',
            details: result.error,
            timestamp: new Date().toISOString(),
            request_id: result.context?.request_id
          }
        });
      }

      // Format response with enhanced data
      const imageData = result.result;
      const processingTime = Date.now() - startTime;

      return res.json({
        success: true,
        data: {
          image_url: imageData.url || imageData.image_url || '',
          thumbnail_url: imageData.thumbnail_url || '',
          prompt: enhancedPrompt,
          original_prompt: prompt,
          negative_prompt: negative_prompt || getDefaultNegativePrompt(style),
          style,
          size,
          quality,
          model_used: imageData.model_used || model_preference,
          generation_params: {
            seed: imageData.seed || seed,
            steps: imageData.steps || steps,
            guidance_scale: imageData.guidance_scale || guidance_scale,
            sampler: imageData.sampler || 'DPM++ 2M Karras'
          },
          performance: {
            generation_time_ms: processingTime,
            model: imageData.model_used || model_preference,
            cache_hit: false
          },
          metadata: {
            dimensions: size,
            file_size: imageData.file_size || null,
            format: imageData.format || 'PNG',
            color_space: imageData.color_space || 'sRGB'
          },
          generated_at: new Date().toISOString(),
          request_id: result.context?.request_id
        }
      });

    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Unknown error';
      const processingTime = Date.now() - startTime;

      // Log error for monitoring
      console.error('Image generation error:', {
        error: errorMessage,
        prompt: prompt.substring(0, 100) + '...',
        style,
        size,
        quality,
        user_id: req.user?.id,
        processing_time: processingTime,
        timestamp: new Date().toISOString()
      });

      return res.status(500).json({
        success: false,
        error: {
          code: 'GENERATION_FAILED',
          message: 'Image generation failed',
          details: process.env.NODE_ENV === 'development' ? errorMessage : 'Internal server error',
          timestamp: new Date().toISOString(),
          processing_time_ms: processingTime
        }
      });
    }
  })
);

// Helper functions for image generation
function enhanceImagePrompt(prompt: string, style: string, quality: string): string {
  let enhanced = prompt;

  // Add style-specific enhancements
  const styleEnhancements = {
    'photographic': ', professional photography, high resolution, sharp focus, detailed',
    'illustration': ', digital illustration, vibrant colors, detailed artwork',
    'digital_art': ', digital art, concept art, detailed, artistic',
    'minimalist': ', minimalist design, clean, simple, elegant',
    'realistic': ', photorealistic, highly detailed, realistic lighting',
    'artistic': ', artistic style, creative, expressive, detailed',
    'cartoon': ', cartoon style, colorful, fun, animated',
    'sketch': ', pencil sketch, hand-drawn, artistic, detailed line work'
  };

  if (styleEnhancements[style]) {
    enhanced += styleEnhancements[style];
  }

  // Add quality enhancements
  if (quality === 'high') {
    enhanced += ', 4K, ultra high quality, masterpiece';
  } else if (quality === 'medium') {
    enhanced += ', high quality, detailed';
  }

  return enhanced;
}

function getDefaultNegativePrompt(style: string): string {
  const baseNegative = 'blurry, low quality, distorted, deformed, ugly, bad anatomy, bad proportions';

  const styleNegatives = {
    'photographic': ', cartoon, anime, painting, drawing, sketch',
    'illustration': ', photograph, realistic, 3d render',
    'digital_art': ', photograph, low resolution, pixelated',
    'minimalist': ', cluttered, busy, complex, detailed background',
    'realistic': ', cartoon, anime, stylized, abstract',
    'artistic': ', photograph, realistic, plain',
    'cartoon': ', realistic, photograph, dark, scary',
    'sketch': ', colored, painted, photograph, digital'
  };

  return baseNegative + (styleNegatives[style] || '');
}

function determineImageErrorCode(error: string): string {
  if (error?.includes('NSFW') || error?.includes('safety')) return 'CONTENT_BLOCKED';
  if (error?.includes('rate limit')) return 'RATE_LIMIT_EXCEEDED';
  if (error?.includes('quota')) return 'QUOTA_EXCEEDED';
  if (error?.includes('timeout')) return 'REQUEST_TIMEOUT';
  if (error?.includes('model')) return 'MODEL_UNAVAILABLE';
  return 'GENERATION_FAILED';
}

// Health check endpoint for API v1
v1Routes.get('/health', generalApiRateLimit, asyncHandler(async (req: Request, res: Response) => {
  const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
  const serviceStatus = await mcpManager.getServiceStatus();

  res.json({
    success: true,
    status: 'healthy',
    version: '1.0.0',
    services: serviceStatus,
    timestamp: new Date().toISOString()
  });
}));

// API capabilities endpoint - Enhanced with detailed feature information
v1Routes.get('/capabilities', generalApiRateLimit, asyncHandler(async (req: Request, res: Response) => {
  const mcpManager = req.app.locals.mcpManager as MCPServiceManager;

  // Get real-time service status
  const serviceStatus = mcpManager ? await mcpManager.getServiceStatus() : {};

  res.json({
    success: true,
    capabilities: {
      content_generation: {
        available: serviceStatus.content || false,
        supported_languages: ['en', 'th'],
        supported_types: ['blog_post', 'article', 'guide', 'tutorial'],
        supported_lengths: ['short', 'medium', 'long'],
        supported_styles: ['informative', 'conversational', 'professional', 'casual', 'technical'],
        features: {
          seo_optimization: true,
          keyword_analysis: true,
          readability_scoring: true,
          content_templates: true,
          multi_language_support: true,
          faq_generation: true,
          schema_markup: true,
          internal_linking_suggestions: true,
          performance_analytics: true,
          content_caching: true
        },
        word_count_ranges: {
          short: { min: 400, max: 800 },
          medium: { min: 800, max: 1500 },
          long: { min: 1500, max: 3000 }
        },
        advanced_options: [
          'target_audience_specification',
          'additional_keywords',
          'faq_inclusion',
          'image_suggestions',
          'statistical_data_inclusion',
          'actionable_tips',
          'featured_snippet_optimization'
        ]
      },
      image_generation: {
        available: serviceStatus.image || false,
        supported_styles: [
          'photographic', 'illustration', 'digital_art', 'minimalist',
          'realistic', 'artistic', 'cartoon', 'sketch'
        ],
        supported_sizes: [
          '512x512', '1024x1024', '1024x768', '768x1024',
          '1536x1024', '1024x1536'
        ],
        supported_qualities: ['high', 'medium', 'low'],
        models: ['flux', 'dalle', 'stable-diffusion', 'midjourney'],
        features: {
          negative_prompts: true,
          seed_control: true,
          step_control: true,
          guidance_scale_control: true,
          style_enhancement: true,
          quality_optimization: true,
          face_enhancement: true,
          upscaling: true,
          batch_generation: false,
          inpainting: false,
          outpainting: false
        },
        advanced_params: {
          steps: { min: 1, max: 50, default: 20 },
          guidance_scale: { min: 1, max: 20, default: 7.5 },
          seed: { min: 0, max: 4294967295, random: true }
        }
      },
      seo_analysis: {
        available: serviceStatus.seo || false,
        features: [
          'keyword_density_analysis',
          'readability_scoring',
          'meta_description_optimization',
          'title_optimization',
          'content_structure_analysis',
          'competitor_analysis',
          'schema_markup_suggestions',
          'internal_linking_analysis'
        ]
      },
      authentication: {
        methods: ['jwt_token', 'api_key'],
        token_expiry: '24 hours',
        rate_limiting: 'per_api_key'
      },
      rate_limits: {
        content_generation: {
          requests_per_hour: 50,
          burst_limit: 10,
          cooldown_period: '1 minute'
        },
        image_generation: {
          requests_per_hour: 100,
          burst_limit: 5,
          cooldown_period: '30 seconds'
        },
        general_api: {
          requests_per_hour: 200,
          burst_limit: 20
        }
      },
      performance: {
        average_content_generation_time: '15-30 seconds',
        average_image_generation_time: '10-20 seconds',
        cache_hit_rate: '85%',
        uptime: '99.9%'
      }
    },
    service_status: serviceStatus,
    api_version: '1.0.0',
    last_updated: new Date().toISOString(),
    timestamp: new Date().toISOString()
  });
}));

// Performance metrics endpoint
v1Routes.get('/metrics', generalApiRateLimit, authMiddleware, asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
  const mcpManager = req.app.locals.mcpManager as MCPServiceManager;

  if (!mcpManager) {
    return res.status(503).json({
      success: false,
      error: {
        code: 'SERVICE_UNAVAILABLE',
        message: 'Metrics service is temporarily unavailable'
      }
    });
  }

  try {
    // Get performance metrics from content generation service
    const contentService = (mcpManager as any).services?.content;
    const performanceMetrics = contentService?.getPerformanceMetrics?.() || {};

    // Get cache statistics
    const cacheStats = await mcpManager.getCacheStats();

    // Get service status
    const serviceStatus = await mcpManager.getServiceStatus();

    res.json({
      success: true,
      metrics: {
        performance: performanceMetrics,
        cache: cacheStats,
        services: serviceStatus,
        system: {
          uptime: process.uptime(),
          memory_usage: process.memoryUsage(),
          node_version: process.version,
          platform: process.platform
        },
        api_health: {
          total_requests: performanceMetrics.totalRequests || 0,
          success_rate: performanceMetrics.successRate || 0,
          average_response_time: performanceMetrics.averageGenerationTime || 0,
          cache_hit_rate: performanceMetrics.cacheHitRate || 0
        }
      },
      timestamp: new Date().toISOString()
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: {
        code: 'METRICS_ERROR',
        message: 'Failed to retrieve performance metrics',
        details: error instanceof Error ? error.message : 'Unknown error'
      }
    });
  }
}));

// Content analysis endpoint
v1Routes.post('/content/analyze',
  generalApiRateLimit,
  authMiddleware,
  [
    body('content').isString().notEmpty().withMessage('Content is required'),
    body('keywords').optional().isArray().withMessage('Keywords must be an array'),
    body('language').optional().isIn(['en', 'th']).withMessage('Language must be "en" or "th"')
  ],
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        error: {
          code: 'VALIDATION_ERROR',
          message: 'Request validation failed',
          details: errors.array()
        }
      });
    }

    const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
    const { content, keywords = [], language = 'en' } = req.body;

    try {
      const result = await mcpManager.executeTool({
        tool: 'analyze_seo',
        arguments: {
          content,
          keywords,
          language,
          analysis_type: 'comprehensive'
        },
        context: {
          user: req.user,
          api_version: 'v1'
        }
      });

      if (!result.success) {
        return res.status(500).json({
          success: false,
          error: {
            code: 'ANALYSIS_FAILED',
            message: result.error || 'Content analysis failed'
          }
        });
      }

      res.json({
        success: true,
        data: result.result,
        timestamp: new Date().toISOString()
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        error: {
          code: 'ANALYSIS_ERROR',
          message: 'Content analysis failed',
          details: error instanceof Error ? error.message : 'Unknown error'
        }
      });
    }
  })
);