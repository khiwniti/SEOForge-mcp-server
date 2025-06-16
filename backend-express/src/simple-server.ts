/**
 * Simplified Express Server for SEOForge
 * Minimal dependencies for reliable Vercel deployment
 */

import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import compression from 'compression';
import dotenv from 'dotenv';
import { createLogger, format, transports } from 'winston';
import rateLimit from 'express-rate-limit';
import { simpleV1Routes } from './routes/simple-v1';

// Load environment variables
dotenv.config();

// Logger setup optimized for serverless
const isServerless = process.env.VERCEL === '1' || process.env.AWS_LAMBDA_FUNCTION_NAME;

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

// Configuration
const CONFIG = {
  server: {
    port: parseInt(process.env.PORT || '3000'),
    host: process.env.HOST || '0.0.0.0',
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
app.use(express.json({ 
  limit: '10mb',
  verify: (req, res, buf) => {
    // Add request size tracking for monitoring
    (req as any).contentLength = buf.length;
  }
}));
app.use(express.urlencoded({ 
  extended: true, 
  limit: '10mb' 
}));

// Request logging middleware
app.use((req, res, next) => {
  const start = Date.now();
  
  res.on('finish', () => {
    const duration = Date.now() - start;
    logger.info('Request completed', {
      method: req.method,
      url: req.url,
      status: res.statusCode,
      duration: `${duration}ms`,
      userAgent: req.get('User-Agent'),
      ip: req.ip
    });
  });
  
  next();
});

// Routes
app.use('/health', (req, res) => {
  res.json({
    success: true,
    status: 'healthy',
    timestamp: new Date().toISOString(),
    uptime: process.uptime(),
    memory: process.memoryUsage(),
    version: '1.0.0',
    environment: CONFIG.server.environment
  });
});

app.use('/api/v1', simpleV1Routes);

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
      'content-generation': '/api/v1/content/generate',
      'image-generation': '/api/v1/images/generate',
      capabilities: '/api/v1/capabilities',
      'api-v1': '/api/v1'
    },
    features: [
      'AI Content Generation (English & Thai)',
      'SEO Optimization',
      'Performance Monitoring',
      'WordPress Plugin Compatible'
    ],
    ai_models: [
      'Google Gemini 2.0 Flash (Primary)'
    ],
    status: 'running',
    serverless: isServerless
  });
});

// Error handling middleware
app.use((error: any, req: express.Request, res: express.Response, next: express.NextFunction) => {
  logger.error('Unhandled error:', {
    error: error.message,
    stack: error.stack,
    url: req.url,
    method: req.method,
    ip: req.ip
  });

  const isDevelopment = CONFIG.server.environment === 'development';
  
  res.status(error.status || 500).json({
    success: false,
    error: {
      code: error.code || 'INTERNAL_ERROR',
      message: error.message || 'Internal server error',
      details: isDevelopment ? error.stack : undefined,
      timestamp: new Date().toISOString()
    }
  });
});

// 404 handler
app.use('*', (req, res) => {
  res.status(404).json({
    success: false,
    error: {
      code: 'NOT_FOUND',
      message: 'Endpoint not found',
      path: req.originalUrl,
      timestamp: new Date().toISOString()
    }
  });
});

// Start server (only if not in serverless environment)
if (!isServerless) {
  const server = app.listen(CONFIG.server.port, CONFIG.server.host, () => {
    logger.info(`SEOForge server running on ${CONFIG.server.host}:${CONFIG.server.port}`);
    logger.info(`Environment: ${CONFIG.server.environment}`);
    logger.info('Available endpoints:');
    logger.info('  GET  / - API information');
    logger.info('  GET  /health - Health check');
    logger.info('  POST /api/v1/content/generate - Content generation');
    logger.info('  POST /api/v1/images/generate - Image generation');
    logger.info('  GET  /api/v1/capabilities - API capabilities');
  });

  // Graceful shutdown
  process.on('SIGTERM', () => {
    logger.info('SIGTERM received, shutting down gracefully');
    server.close(() => {
      logger.info('Process terminated');
      process.exit(0);
    });
  });

  process.on('SIGINT', () => {
    logger.info('SIGINT received, shutting down gracefully');
    server.close(() => {
      logger.info('Process terminated');
      process.exit(0);
    });
  });
}

export { app };
