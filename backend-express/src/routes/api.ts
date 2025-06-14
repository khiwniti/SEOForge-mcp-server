/**
 * Legacy API routes for backward compatibility
 * These routes proxy to MCP tools to maintain compatibility with existing clients
 */

import { Router, Request, Response } from 'express';
import { body, validationResult } from 'express-validator';
import { asyncHandler, createError } from '../middleware/error-handler.js';
import { authMiddleware, optionalAuth, AuthenticatedRequest } from '../middleware/auth.js';
import { MCPServiceManager } from '../services/mcp-service-manager.js';

export const apiRoutes = Router();

// Blog Generator API (legacy compatibility)
apiRoutes.post('/blog-generator/generate',
  optionalAuth,
  [
    body('topic').isString().notEmpty().withMessage('Topic is required'),
    body('keywords').optional().isArray().withMessage('Keywords must be an array'),
    body('language').optional().isString().withMessage('Language must be a string'),
    body('tone').optional().isString().withMessage('Tone must be a string'),
    body('length').optional().isIn(['short', 'medium', 'long']).withMessage('Length must be short, medium, or long')
  ],
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      throw createError('Validation failed: ' + errors.array().map(e => e.msg).join(', '), 400);
    }

    const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
    const { topic, keywords, language, tone, length } = req.body;

    const result = await mcpManager.executeTool({
      tool: 'generate_content',
      arguments: {
        type: 'blog',
        topic,
        keywords: keywords || [],
        language: language || 'en',
        tone: tone || 'professional',
        length: length || 'medium'
      },
      context: {
        user: req.user,
        legacy_api: 'blog-generator'
      }
    });

    res.json(result);
  })
);

// SEO Analyzer API (legacy compatibility)
apiRoutes.post('/seo-analyzer/analyze',
  optionalAuth,
  [
    body('url').optional().isURL().withMessage('URL must be valid'),
    body('content').optional().isString().withMessage('Content must be a string'),
    body('keywords').optional().isArray().withMessage('Keywords must be an array')
  ],
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      throw createError('Validation failed: ' + errors.array().map(e => e.msg).join(', '), 400);
    }

    const { url, content, keywords } = req.body;

    if (!url && !content) {
      throw createError('Either URL or content is required', 400);
    }

    const mcpManager = req.app.locals.mcpManager as MCPServiceManager;

    const result = await mcpManager.executeTool({
      tool: 'analyze_seo',
      arguments: {
        url,
        content,
        keywords: keywords || []
      },
      context: {
        user: req.user,
        legacy_api: 'seo-analyzer'
      }
    });

    res.json(result);
  })
);

// Image Generation API (legacy compatibility)
apiRoutes.post('/flux-image-gen/generate',
  authMiddleware,
  [
    body('prompt').isString().notEmpty().withMessage('Prompt is required'),
    body('style').optional().isString().withMessage('Style must be a string'),
    body('size').optional().isIn(['512x512', '1024x1024', '1024x768']).withMessage('Invalid size'),
    body('model').optional().isIn(['flux', 'dalle', 'midjourney']).withMessage('Invalid model')
  ],
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      throw createError('Validation failed: ' + errors.array().map(e => e.msg).join(', '), 400);
    }

    const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
    const { prompt, style, size, model } = req.body;

    const result = await mcpManager.executeTool({
      tool: 'generate_image',
      arguments: {
        prompt,
        style: style || 'realistic',
        size: size || '1024x1024',
        model: model || 'flux'
      },
      context: {
        user: req.user,
        legacy_api: 'flux-image-gen'
      }
    });

    res.json(result);
  })
);

// WordPress Manager API (legacy compatibility)
apiRoutes.post('/wordpress-manager/sync',
  authMiddleware,
  [
    body('site_url').isURL().withMessage('Site URL must be valid'),
    body('action').isIn(['create', 'update', 'delete']).withMessage('Invalid action'),
    body('content_type').isIn(['post', 'page', 'product']).withMessage('Invalid content type'),
    body('content').isObject().withMessage('Content must be an object'),
    body('auth_token').optional().isString().withMessage('Auth token must be a string')
  ],
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      throw createError('Validation failed: ' + errors.array().map(e => e.msg).join(', '), 400);
    }

    const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
    const { site_url, action, content_type, content, auth_token } = req.body;

    const result = await mcpManager.executeTool({
      tool: 'wordpress_sync',
      arguments: {
        site_url,
        action,
        content_type,
        content,
        auth_token
      },
      context: {
        user: req.user,
        legacy_api: 'wordpress-manager'
      }
    });

    res.json(result);
  })
);

// Thai Language API
apiRoutes.post('/thai-language/translate',
  optionalAuth,
  [
    body('text').isString().notEmpty().withMessage('Text is required'),
    body('source_language').optional().isString().withMessage('Source language must be a string'),
    body('target_language').optional().isString().withMessage('Target language must be a string'),
    body('context').optional().isString().withMessage('Context must be a string'),
    body('cultural_adaptation').optional().isBoolean().withMessage('Cultural adaptation must be boolean')
  ],
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      throw createError('Validation failed: ' + errors.array().map(e => e.msg).join(', '), 400);
    }

    const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
    const { text, source_language, target_language, context, cultural_adaptation } = req.body;

    const result = await mcpManager.executeTool({
      tool: 'translate_thai',
      arguments: {
        text,
        source_language: source_language || 'en',
        target_language: target_language || 'th',
        context,
        cultural_adaptation: cultural_adaptation !== false
      },
      context: {
        user: req.user,
        legacy_api: 'thai-language'
      }
    });

    res.json(result);
  })
);

// Keyword Research API
apiRoutes.post('/keyword-research/analyze',
  optionalAuth,
  [
    body('seed_keywords').isArray().notEmpty().withMessage('Seed keywords are required'),
    body('market').optional().isString().withMessage('Market must be a string'),
    body('language').optional().isString().withMessage('Language must be a string'),
    body('industry').optional().isString().withMessage('Industry must be a string'),
    body('competition_level').optional().isIn(['low', 'medium', 'high']).withMessage('Invalid competition level')
  ],
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      throw createError('Validation failed: ' + errors.array().map(e => e.msg).join(', '), 400);
    }

    const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
    const { seed_keywords, market, language, industry, competition_level } = req.body;

    const result = await mcpManager.executeTool({
      tool: 'research_keywords',
      arguments: {
        seed_keywords,
        market: market || 'global',
        language: language || 'en',
        industry,
        competition_level: competition_level || 'medium'
      },
      context: {
        user: req.user,
        legacy_api: 'keyword-research'
      }
    });

    res.json(result);
  })
);

// Universal MCP endpoint (for any tool)
apiRoutes.post('/universal-mcp/execute',
  authMiddleware,
  [
    body('tool').isString().notEmpty().withMessage('Tool name is required'),
    body('arguments').isObject().withMessage('Arguments must be an object')
  ],
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      throw createError('Validation failed: ' + errors.array().map(e => e.msg).join(', '), 400);
    }

    const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
    const { tool, arguments: args, context } = req.body;

    const result = await mcpManager.executeTool({
      tool,
      arguments: args,
      context: {
        ...context,
        user: req.user,
        legacy_api: 'universal-mcp'
      }
    });

    res.json(result);
  })
);