/**
 * Rate limiting middleware for API endpoints
 * Implements the rate limits specified in API_REQUIREMENTS.md
 */

import rateLimit from 'express-rate-limit';
import { Request, Response } from 'express';

// Content Generation Rate Limit: 50 requests per hour per API key
export const contentGenerationRateLimit = rateLimit({
  windowMs: 60 * 60 * 1000, // 1 hour
  max: 50, // 50 requests per hour
  message: {
    success: false,
    error: {
      code: 'RATE_LIMIT_EXCEEDED',
      message: 'Content generation rate limit exceeded. Maximum 50 requests per hour.',
      details: 'Please wait before making more requests.'
    }
  },
  standardHeaders: true,
  legacyHeaders: false,
  keyGenerator: (req: Request) => {
    // Use API key from Authorization header for rate limiting
    const authHeader = req.headers.authorization;
    if (authHeader && authHeader.startsWith('Bearer ')) {
      return authHeader.substring(7); // Remove 'Bearer ' prefix
    }
    // Fallback to IP address if no API key
    return req.ip || 'unknown';
  },
  handler: (req: Request, res: Response) => {
    res.status(429).json({
      success: false,
      error: {
        code: 'RATE_LIMIT_EXCEEDED',
        message: 'Content generation rate limit exceeded. Maximum 50 requests per hour.',
        details: 'Please wait before making more requests.'
      }
    });
  }
});

// Image Generation Rate Limit: 100 requests per hour per API key
export const imageGenerationRateLimit = rateLimit({
  windowMs: 60 * 60 * 1000, // 1 hour
  max: 100, // 100 requests per hour
  message: {
    success: false,
    error: {
      code: 'RATE_LIMIT_EXCEEDED',
      message: 'Image generation rate limit exceeded. Maximum 100 requests per hour.',
      details: 'Please wait before making more requests.'
    }
  },
  standardHeaders: true,
  legacyHeaders: false,
  keyGenerator: (req: Request) => {
    // Use API key from Authorization header for rate limiting
    const authHeader = req.headers.authorization;
    if (authHeader && authHeader.startsWith('Bearer ')) {
      return authHeader.substring(7); // Remove 'Bearer ' prefix
    }
    // Fallback to IP address if no API key
    return req.ip || 'unknown';
  },
  handler: (req: Request, res: Response) => {
    res.status(429).json({
      success: false,
      error: {
        code: 'RATE_LIMIT_EXCEEDED',
        message: 'Image generation rate limit exceeded. Maximum 100 requests per hour.',
        details: 'Please wait before making more requests.'
      }
    });
  }
});

// General API Rate Limit: 200 requests per hour per API key
export const generalApiRateLimit = rateLimit({
  windowMs: 60 * 60 * 1000, // 1 hour
  max: 200, // 200 requests per hour
  message: {
    success: false,
    error: {
      code: 'RATE_LIMIT_EXCEEDED',
      message: 'API rate limit exceeded. Maximum 200 requests per hour.',
      details: 'Please wait before making more requests.'
    }
  },
  standardHeaders: true,
  legacyHeaders: false,
  keyGenerator: (req: Request) => {
    // Use API key from Authorization header for rate limiting
    const authHeader = req.headers.authorization;
    if (authHeader && authHeader.startsWith('Bearer ')) {
      return authHeader.substring(7); // Remove 'Bearer ' prefix
    }
    // Fallback to IP address if no API key
    return req.ip || 'unknown';
  },
  handler: (req: Request, res: Response) => {
    res.status(429).json({
      success: false,
      error: {
        code: 'RATE_LIMIT_EXCEEDED',
        message: 'API rate limit exceeded. Maximum 200 requests per hour.',
        details: 'Please wait before making more requests.'
      }
    });
  }
});