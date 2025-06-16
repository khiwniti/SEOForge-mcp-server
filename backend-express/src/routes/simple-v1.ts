/**
 * Simplified V1 API Routes for SEOForge
 * Minimal dependencies for reliable Vercel deployment
 */

import { Router, Request, Response } from 'express';
import { body, validationResult } from 'express-validator';
import rateLimit from 'express-rate-limit';
import { SimpleContentGenerationService } from '../services/simple-content-generation';

const router = Router();

// Rate limiting
const contentGenerationRateLimit = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 50, // limit each IP to 50 requests per windowMs
  message: {
    success: false,
    error: {
      code: 'RATE_LIMIT_EXCEEDED',
      message: 'Too many content generation requests, please try again later.'
    }
  }
});

const imageGenerationRateLimit = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 100, // limit each IP to 100 requests per windowMs
  message: {
    success: false,
    error: {
      code: 'RATE_LIMIT_EXCEEDED',
      message: 'Too many image generation requests, please try again later.'
    }
  }
});

const generalApiRateLimit = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 200, // limit each IP to 200 requests per windowMs
  message: {
    success: false,
    error: {
      code: 'RATE_LIMIT_EXCEEDED',
      message: 'Too many API requests, please try again later.'
    }
  }
});

// Initialize content generation service
const contentService = new SimpleContentGenerationService();

// Async handler wrapper
const asyncHandler = (fn: Function) => (req: Request, res: Response, next: Function) => {
  Promise.resolve(fn(req, res, next)).catch(next);
};

// Content Generation API - Public Access
router.post('/content/generate',
  contentGenerationRateLimit,
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
  asyncHandler(async (req: Request, res: Response) => {
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
      const result = await contentService.generateContent({
        keyword,
        language,
        type,
        length,
        style,
        additional_keywords,
        target_audience,
        include_faq,
        include_images
      });

      const processingTime = Date.now() - startTime;

      return res.json({
        success: true,
        data: {
          ...result,
          performance: {
            ...result.metadata,
            generation_time_ms: processingTime,
            cache_hit: false
          },
          generated_at: new Date().toISOString()
        }
      });
      
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Unknown error';
      const processingTime = Date.now() - startTime;
      
      console.error('Content generation error:', {
        error: errorMessage,
        keyword,
        language,
        type,
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

// Image Generation API - Simplified
router.post('/images/generate',
  imageGenerationRateLimit,
  [
    body('prompt').isString().notEmpty().withMessage('Prompt is required')
      .isLength({ min: 10, max: 1000 }).withMessage('Prompt must be between 10 and 1000 characters'),
    body('style').optional().isString().withMessage('Style must be a string'),
    body('size').optional().isIn(['1024x1024', '1024x768', '768x1024']).withMessage('Invalid size'),
    body('quality').optional().isIn(['high', 'medium', 'low']).withMessage('Invalid quality')
  ],
  asyncHandler(async (req: Request, res: Response) => {
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

    const { prompt, style = 'photographic', size = '1024x1024', quality = 'high' } = req.body;

    // For now, return a placeholder response since image generation requires additional setup
    const processingTime = Date.now() - startTime;

    return res.json({
      success: true,
      data: {
        image_url: 'https://via.placeholder.com/1024x1024?text=Image+Generation+Coming+Soon',
        prompt,
        style,
        size,
        quality,
        model_used: 'placeholder',
        performance: {
          generation_time_ms: processingTime,
          model: 'placeholder',
          cache_hit: false
        },
        generated_at: new Date().toISOString(),
        note: 'Image generation feature coming soon'
      }
    });
  })
);

// API capabilities endpoint
router.get('/capabilities', generalApiRateLimit, asyncHandler(async (req: Request, res: Response) => {
  res.json({
    success: true,
    capabilities: {
      content_generation: {
        available: true,
        supported_languages: ['en', 'th'],
        supported_types: ['blog_post', 'article', 'guide', 'tutorial'],
        supported_lengths: ['short', 'medium', 'long'],
        supported_styles: ['informative', 'conversational', 'professional', 'casual', 'technical'],
        features: {
          seo_optimization: true,
          keyword_analysis: true,
          content_caching: true,
          multi_language_support: true
        }
      },
      image_generation: {
        available: false,
        note: 'Coming soon'
      },
      rate_limits: {
        content_generation: {
          requests_per_hour: 50,
          burst_limit: 10
        },
        image_generation: {
          requests_per_hour: 100,
          burst_limit: 5
        }
      }
    },
    api_version: '1.0.0',
    timestamp: new Date().toISOString()
  });
}));

// Health check endpoint
router.get('/health', (req: Request, res: Response) => {
  res.json({
    success: true,
    status: 'healthy',
    timestamp: new Date().toISOString(),
    uptime: process.uptime(),
    memory: process.memoryUsage(),
    version: '1.0.0'
  });
});

export { router as simpleV1Routes };
