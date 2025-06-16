/**
 * Vercel Serverless Function Entry Point
 * This file exports the Express app for Vercel deployment
 * Optimized for serverless environment with proper initialization
 */

import { VercelRequest, VercelResponse } from '@vercel/node';
import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import compression from 'compression';
import dotenv from 'dotenv';
import { createLogger, format, transports } from 'winston';
import rateLimit from 'express-rate-limit';

// Import middleware
import { errorHandler } from '../dist/middleware/error-handler';

// Import routes
import { healthRoutes } from '../dist/routes/health';
import { mcpRoutes } from '../dist/routes/mcp';
import { apiRoutes } from '../dist/routes/api';
import { authRoutes } from '../dist/routes/auth';
import { v1Routes } from '../dist/routes/v1';

// Import MCP services
import { MCPServiceManager } from '../dist/services/mcp-service-manager';

// Load environment variables
dotenv.config();

// Logger setup optimized for serverless
const logger = createLogger({
  level: process.env.LOG_LEVEL || 'info',
  format: format.combine(
    format.timestamp(),
    format.errors({ stack: true }),
    format.json()
  ),
  transports: [
    new transports.Console({
      format: format.combine(
        format.colorize(),
        format.simple()
      )
    })
  ]
});

// Configuration optimized for Vercel
const CONFIG = {
  server: {
    environment: process.env.NODE_ENV || 'production'
  },
  cors: {
    origins: process.env.CORS_ORIGINS?.split(',') || ['*'],
    credentials: process.env.CORS_CREDENTIALS === 'true'
  },
  rateLimit: {
    windowMs: 15 * 60 * 1000, // 15 minutes
    max: 1000, // limit each IP to 1000 requests per windowMs
    message: {
      success: false,
      error: {
        code: 'RATE_LIMIT_EXCEEDED',
        message: 'Too many requests from this IP, please try again later.'
      }
    }
  },
  ai: {
    googleApiKey: process.env.GOOGLE_API_KEY,
    openaiApiKey: process.env.OPENAI_API_KEY,
    anthropicApiKey: process.env.ANTHROPIC_API_KEY
  }
};

// Create Express app
const app = express();

// Global middleware optimized for serverless
app.use(helmet({
  contentSecurityPolicy: {
    directives: {
      defaultSrc: ["'self'"],
      styleSrc: ["'self'", "'unsafe-inline'"],
      scriptSrc: ["'self'"],
      imgSrc: ["'self'", "data:", "https:"],
      connectSrc: ["'self'"],
      fontSrc: ["'self'"],
      objectSrc: ["'none'"],
      mediaSrc: ["'self'"],
      frameSrc: ["'none'"],
    },
  },
  crossOriginEmbedderPolicy: false
}));

app.use(compression());

// CORS configuration
app.use(cors({
  origin: CONFIG.cors.origins,
  credentials: CONFIG.cors.credentials,
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization', 'X-API-Key', 'X-MCP-Version', 'X-Client-ID']
}));

// Rate limiting
app.use(rateLimit(CONFIG.rateLimit));

// Body parsing middleware
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Initialize MCP services for serverless (cached)
let mcpManager: MCPServiceManager | null = null;

async function initializeMCPServices() {
  if (!mcpManager) {
    try {
      logger.info('Initializing MCP services for serverless...');
      mcpManager = new MCPServiceManager();
      await mcpManager.initialize();
      logger.info('MCP services initialized successfully');
    } catch (error) {
      logger.error('Failed to initialize MCP services:', error);
      // Don't throw error, allow app to continue with limited functionality
    }
  }
  return mcpManager;
}

// Routes
app.use('/health', healthRoutes);
app.use('/auth', authRoutes);
app.use('/mcp', mcpRoutes);
app.use('/api/v1', v1Routes);  // SEO-Forge WordPress plugin compatibility
app.use('/api', apiRoutes);    // Legacy API routes

// Root endpoint
app.get('/', (req, res) => {
  res.json({
    name: 'SEOForge Public MCP Server',
    version: '1.0.0',
    description: 'Public AI-powered content generation and SEO analysis API',
    environment: CONFIG.server.environment,
    access: 'public',
    timestamp: new Date().toISOString(),
    endpoints: {
      health: '/health',
      mcp: '/mcp',
      'content-generation': '/api/v1/content/generate',
      'image-generation': '/api/v1/images/generate',
      'content-analysis': '/api/v1/content/analyze',
      capabilities: '/api/v1/capabilities',
      metrics: '/api/v1/metrics',
      'api-v1': '/api/v1',  // SEO-Forge WordPress plugin compatibility
      api: '/api'           // Legacy API routes
    },
    features: [
      'AI Content Generation (English & Thai)',
      'Advanced Image Generation',
      'SEO Content Analysis',
      'Performance Monitoring',
      'WordPress Plugin Compatible'
    ],
    ai_models: [
      'Google Gemini 2.0 Flash (Primary)',
      'OpenAI GPT-4 (Fallback)',
      'Anthropic Claude (Fallback)',
      'FLUX Image Generation'
    ],
    status: 'running',
    serverless: true
  });
});

// Error handling middleware (must be last)
app.use(errorHandler);

// Serverless function handler
export default async function handler(req: VercelRequest, res: VercelResponse) {
  try {
    // Initialize MCP services if not already done
    const manager = await initializeMCPServices();
    if (manager) {
      app.locals.mcpManager = manager;
    }

    // Handle the request
    return app(req, res);
  } catch (error) {
    logger.error('Serverless function error:', error);
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