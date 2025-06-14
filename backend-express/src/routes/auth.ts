/**
 * Authentication routes
 */

import { Router, Request, Response } from 'express';
import { body, validationResult } from 'express-validator';
import jwt from 'jsonwebtoken';
import { asyncHandler, createError } from '../middleware/error-handler.js';
import { MCPServiceManager } from '../services/mcp-service-manager.js';

export const authRoutes = Router();

// Login endpoint
authRoutes.post('/login',
  [
    body('email').isEmail().withMessage('Valid email is required'),
    body('password').isLength({ min: 6 }).withMessage('Password must be at least 6 characters')
  ],
  asyncHandler(async (req: Request, res: Response) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      throw createError('Validation failed: ' + errors.array().map(e => e.msg).join(', '), 400);
    }

    const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
    const { email, password } = req.body;

    const authResult = await mcpManager.authenticate({ email, password });

    if (authResult.success) {
      const jwtSecret = process.env.JWT_SECRET;
      if (!jwtSecret) {
        throw createError('JWT secret not configured', 500);
      }

      const token = jwt.sign(
        {
          id: authResult.user.id,
          email: authResult.user.email,
          role: authResult.user.role
        },
        jwtSecret,
        { expiresIn: '24h' }
      );

      res.json({
        success: true,
        user: authResult.user,
        token,
        expiresIn: '24h',
        timestamp: new Date().toISOString()
      });
    } else {
      throw createError(authResult.error || 'Authentication failed', 401);
    }
  })
);

// Register endpoint
authRoutes.post('/register',
  [
    body('email').isEmail().withMessage('Valid email is required'),
    body('password').isLength({ min: 6 }).withMessage('Password must be at least 6 characters'),
    body('role').optional().isIn(['user', 'admin', 'api']).withMessage('Invalid role')
  ],
  asyncHandler(async (req: Request, res: Response) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      throw createError('Validation failed: ' + errors.array().map(e => e.msg).join(', '), 400);
    }

    const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
    const { email, password, role = 'user' } = req.body;

    // For now, only allow registration in development
    if (process.env.NODE_ENV === 'production' && !process.env.ALLOW_REGISTRATION) {
      throw createError('Registration is disabled in production', 403);
    }

    const authResult = await mcpManager.authenticate({ 
      action: 'register',
      email, 
      password, 
      role 
    });

    if (authResult.success) {
      res.json({
        success: true,
        user: authResult.user,
        message: 'User registered successfully',
        timestamp: new Date().toISOString()
      });
    } else {
      throw createError(authResult.error || 'Registration failed', 400);
    }
  })
);

// Verify token endpoint
authRoutes.post('/verify',
  [
    body('token').isString().notEmpty().withMessage('Token is required')
  ],
  asyncHandler(async (req: Request, res: Response) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      throw createError('Validation failed: ' + errors.array().map(e => e.msg).join(', '), 400);
    }

    const { token } = req.body;
    const jwtSecret = process.env.JWT_SECRET;

    if (!jwtSecret) {
      throw createError('JWT secret not configured', 500);
    }

    try {
      const decoded = jwt.verify(token, jwtSecret) as any;
      
      res.json({
        success: true,
        valid: true,
        user: {
          id: decoded.id,
          email: decoded.email,
          role: decoded.role
        },
        expiresAt: new Date(decoded.exp * 1000).toISOString(),
        timestamp: new Date().toISOString()
      });
    } catch (jwtError) {
      res.json({
        success: false,
        valid: false,
        error: 'Invalid or expired token',
        timestamp: new Date().toISOString()
      });
    }
  })
);

// Refresh token endpoint
authRoutes.post('/refresh',
  [
    body('token').isString().notEmpty().withMessage('Token is required')
  ],
  asyncHandler(async (req: Request, res: Response) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      throw createError('Validation failed: ' + errors.array().map(e => e.msg).join(', '), 400);
    }

    const { token } = req.body;
    const jwtSecret = process.env.JWT_SECRET;

    if (!jwtSecret) {
      throw createError('JWT secret not configured', 500);
    }

    try {
      const decoded = jwt.verify(token, jwtSecret, { ignoreExpiration: true }) as any;
      
      // Check if token is not too old (max 7 days)
      const tokenAge = Date.now() - (decoded.iat * 1000);
      const maxAge = 7 * 24 * 60 * 60 * 1000; // 7 days
      
      if (tokenAge > maxAge) {
        throw createError('Token too old for refresh', 401);
      }

      const newToken = jwt.sign(
        {
          id: decoded.id,
          email: decoded.email,
          role: decoded.role
        },
        jwtSecret,
        { expiresIn: '24h' }
      );

      res.json({
        success: true,
        token: newToken,
        expiresIn: '24h',
        timestamp: new Date().toISOString()
      });
    } catch (jwtError) {
      throw createError('Invalid token for refresh', 401);
    }
  })
);