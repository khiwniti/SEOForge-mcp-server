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

// Content Generation API - Exact specification from API_REQUIREMENTS.md
v1Routes.post('/content/generate',
  contentGenerationRateLimit,
  authMiddleware,
  [
    body('keyword').isString().notEmpty().withMessage('Keyword is required'),
    body('language').isIn(['en', 'th']).withMessage('Language must be "en" or "th"'),
    body('type').optional().isString().withMessage('Type must be a string'),
    body('length').optional().isIn(['short', 'medium', 'long']).withMessage('Length must be short, medium, or long'),
    body('style').optional().isString().withMessage('Style must be a string')
  ],
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        error: {
          code: 'INVALID_KEYWORD',
          message: 'Validation failed: ' + errors.array().map(e => e.msg).join(', '),
          details: errors.array()
        }
      });
    }

    const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
    const { keyword, language, type = 'blog_post', length = 'long', style = 'informative' } = req.body;

    try {
      const result = await mcpManager.executeTool({
        tool: 'generate_content',
        arguments: {
          type: 'blog',
          topic: keyword,
          keywords: [keyword],
          language,
          tone: style,
          length,
          seo_requirements: {
            target_keyword: keyword,
            keyword_density: '1-2%',
            meta_description_length: language === 'th' ? 100 : 160,
            title_length: 60,
            min_word_count: length === 'long' ? 1500 : length === 'medium' ? 800 : 500
          }
        },
        context: {
          user: req.user,
          api_version: 'v1',
          plugin_compatibility: true
        }
      });

      if (!result.success) {
        return res.status(500).json({
          success: false,
          error: {
            code: 'GENERATION_FAILED',
            message: result.error || 'Content generation failed',
            details: result.error
          }
        });
      }

      // Format response according to API requirements
      const content = result.result;
      const wordCount = content.content ? content.content.replace(/<[^>]*>/g, '').split(/\s+/).length : 0;

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
          generated_at: new Date().toISOString()
        }
      });

    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Unknown error';
      
      return res.status(500).json({
        success: false,
        error: {
          code: 'GENERATION_FAILED',
          message: 'Content generation failed',
          details: errorMessage
        }
      });
    }
  })
);

// Image Generation API - Exact specification from API_REQUIREMENTS.md
v1Routes.post('/images/generate',
  imageGenerationRateLimit,
  authMiddleware,
  [
    body('prompt').isString().notEmpty().withMessage('Prompt is required'),
    body('style').optional().isString().withMessage('Style must be a string'),
    body('size').optional().isIn(['1024x1024', '1024x768', '768x1024']).withMessage('Invalid size'),
    body('quality').optional().isIn(['high', 'medium', 'low']).withMessage('Invalid quality')
  ],
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({
        success: false,
        error: {
          code: 'INVALID_PROMPT',
          message: 'Validation failed: ' + errors.array().map(e => e.msg).join(', '),
          details: errors.array()
        }
      });
    }

    const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
    const { prompt, style = 'photographic', size = '1024x1024', quality = 'high' } = req.body;

    try {
      const result = await mcpManager.executeTool({
        tool: 'generate_image',
        arguments: {
          prompt,
          style,
          size,
          quality,
          model: 'flux' // Use FLUX as primary model
        },
        context: {
          user: req.user,
          api_version: 'v1',
          plugin_compatibility: true
        }
      });

      if (!result.success) {
        return res.status(500).json({
          success: false,
          error: {
            code: 'GENERATION_FAILED',
            message: result.error || 'Image generation failed',
            details: result.error
          }
        });
      }

      // Format response according to API requirements
      const imageData = result.result;

      return res.json({
        success: true,
        data: {
          image_url: imageData.url || imageData.image_url || '',
          prompt,
          style,
          size,
          generated_at: new Date().toISOString()
        }
      });

    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Unknown error';
      
      return res.status(500).json({
        success: false,
        error: {
          code: 'GENERATION_FAILED',
          message: 'Image generation failed',
          details: errorMessage
        }
      });
    }
  })
);

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

// API capabilities endpoint
v1Routes.get('/capabilities', generalApiRateLimit, asyncHandler(async (req: Request, res: Response) => {
  res.json({
    success: true,
    capabilities: {
      content_generation: {
        supported_languages: ['en', 'th'],
        supported_types: ['blog_post', 'article', 'guide'],
        supported_lengths: ['short', 'medium', 'long'],
        supported_styles: ['informative', 'conversational', 'professional', 'casual']
      },
      image_generation: {
        supported_styles: ['photographic', 'illustration', 'digital_art', 'minimalist'],
        supported_sizes: ['1024x1024', '1024x768', '768x1024'],
        supported_qualities: ['high', 'medium', 'low'],
        models: ['flux', 'dalle', 'midjourney']
      },
      rate_limits: {
        content_generation: '50 requests per hour',
        image_generation: '100 requests per hour'
      }
    },
    timestamp: new Date().toISOString()
  });
}));