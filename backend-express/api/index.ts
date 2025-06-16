/**
 * Vercel Serverless Function Entry Point
 * Simplified for reliable deployment
 */

import { VercelRequest, VercelResponse } from '@vercel/node';
import { app } from '../dist/simple-server';

// Serverless function handler
export default async function handler(req: VercelRequest, res: VercelResponse) {
  try {
    // Handle the request using the simplified app
    return app(req, res);
  } catch (error) {
    console.error('Serverless function error:', error);
    return res.status(500).json({
      success: false,
      error: {
        code: 'SERVERLESS_ERROR',
        message: 'Internal server error',
        timestamp: new Date().toISOString()
      }
    });
  }
}