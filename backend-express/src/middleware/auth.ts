/**
 * Authentication middleware
 */

import { Request, Response, NextFunction } from 'express';
import jwt from 'jsonwebtoken';
import { createError } from './error-handler.js';

export interface AuthenticatedRequest extends Request {
  user?: {
    id: string;
    email: string;
    role: string;
  };
}

export const authMiddleware = async (
  req: AuthenticatedRequest,
  res: Response,
  next: NextFunction
): Promise<void> => {
  try {
    const authHeader = req.headers.authorization;
    const apiKey = req.headers['x-api-key'] as string;

    // Check for API key authentication
    if (apiKey) {
      const validApiKeys = process.env.VALID_API_KEYS?.split(',') || [];
      if (validApiKeys.includes(apiKey)) {
        req.user = {
          id: 'api-user',
          email: 'api@seoforge.dev',
          role: 'api'
        };
        return next();
      } else {
        throw createError('Invalid API key', 401);
      }
    }

    // Check for JWT token authentication
    if (authHeader && authHeader.startsWith('Bearer ')) {
      const token = authHeader.substring(7);
      const jwtSecret = process.env.JWT_SECRET;

      if (!jwtSecret) {
        throw createError('JWT secret not configured', 500);
      }

      try {
        const decoded = jwt.verify(token, jwtSecret) as any;
        req.user = {
          id: decoded.id,
          email: decoded.email,
          role: decoded.role || 'user'
        };
        return next();
      } catch (jwtError) {
        throw createError('Invalid or expired token', 401);
      }
    }

    // No authentication provided
    throw createError('Authentication required', 401);

  } catch (error) {
    next(error);
  }
};

export const optionalAuth = async (
  req: AuthenticatedRequest,
  res: Response,
  next: NextFunction
): Promise<void> => {
  try {
    const authHeader = req.headers.authorization;
    const apiKey = req.headers['x-api-key'] as string;

    if (apiKey || authHeader) {
      return authMiddleware(req, res, next);
    }

    // No authentication provided, but that's okay
    next();
  } catch (error) {
    // For optional auth, we don't fail on auth errors
    next();
  }
};

export const requireRole = (roles: string[]) => {
  return (req: AuthenticatedRequest, res: Response, next: NextFunction): void => {
    if (!req.user) {
      return next(createError('Authentication required', 401));
    }

    if (!roles.includes(req.user.role)) {
      return next(createError('Insufficient permissions', 403));
    }

    next();
  };
};