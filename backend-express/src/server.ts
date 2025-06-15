#!/usr/bin/env node

/**
 * SEOForge Express Backend Server
 * Unified backend using MCP (Model Context Protocol) for all AI services
 * Replaces the FastAPI backend with Express.js + TypeScript
 */

import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import compression from 'compression';
import morgan from 'morgan';
import dotenv from 'dotenv';
import { createLogger, format, transports } from 'winston';
import rateLimit from 'express-rate-limit';

// Import middleware
import { errorHandler } from './middleware/error-handler.js';
import { authMiddleware } from './middleware/auth.js';
import { requestLogger } from './middleware/request-logger.js';

// Import routes
import { healthRoutes } from './routes/health.js';
import { mcpRoutes } from './routes/mcp.js';
import { apiRoutes } from './routes/api.js';
import { authRoutes } from './routes/auth.js';

// Import MCP services
import { MCPServiceManager } from './services/mcp-service-manager.js';

// Load environment variables
dotenv.config();

// Logger setup
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
    }),
    new transports.File({
      filename: 'logs/error.log',
      level: 'error'
    }),
    new transports.File({
      filename: 'logs/combined.log'
    })
  ]
});

// Configuration
const CONFIG = {
  server: {
    port: parseInt(process.env.PORT || '8000'),
    host: process.env.HOST || '0.0.0.0',
    environment: process.env.NODE_ENV || 'development'
  },
  cors: {
    origins: process.env.CORS_ORIGINS?.split(',') || ['http://localhost:3000', 'http://localhost:5173'],
    credentials: process.env.CORS_CREDENTIALS === 'true'
  },
  rateLimit: {
    windowMs: 15 * 60 * 1000, // 15 minutes
    max: 1000, // limit each IP to 1000 requests per windowMs
    message: 'Too many requests from this IP, please try again later.'
  },
  ai: {
    // Prioritize Google Gemini 2.5 Pro for enhanced accuracy
    googleApiKey: process.env.GOOGLE_API_KEY || 'AIzaSyDTITCw_UcgzUufrsCFuxp9HXri6Y0XrDo',
    openaiApiKey: process.env.OPENAI_API_KEY,
    anthropicApiKey: process.env.ANTHROPIC_API_KEY
  }
};

// Create Express app
const app = express();

// Global middleware
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
app.use(morgan('combined', { stream: { write: (message) => logger.info(message.trim()) } }));

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

// Request logging middleware
app.use(requestLogger);

// Routes
app.use('/health', healthRoutes);
app.use('/auth', authRoutes);
app.use('/mcp', mcpRoutes);
app.use('/api', apiRoutes);

// Root endpoint
app.get('/', (req, res) => {
  res.json({
    name: 'SEOForge Express Backend',
    version: '1.0.0',
    environment: CONFIG.server.environment,
    timestamp: new Date().toISOString(),
    endpoints: {
      health: '/health',
      auth: '/auth',
      mcp: '/mcp',
      api: '/api',
      docs: CONFIG.server.environment === 'development' ? '/docs' : undefined
    }
  });
});

// Error handling middleware (must be last)
app.use(errorHandler);

// Initialize MCP services
let mcpManager: MCPServiceManager;

async function initializeServices() {
  try {
    logger.info('Initializing MCP services...');
    mcpManager = new MCPServiceManager();
    await mcpManager.initialize();
    
    // Make MCP manager available to routes
    app.locals.mcpManager = mcpManager;
    
    logger.info('MCP services initialized successfully');
  } catch (error) {
    logger.error('Failed to initialize MCP services:', error);
    throw error;
  }
}

// Start server
async function startServer() {
  try {
    // Initialize services first
    await initializeServices();
    
    // Start HTTP server
    const server = app.listen(CONFIG.server.port, CONFIG.server.host, () => {
      logger.info(`SEOForge Express Backend started on ${CONFIG.server.host}:${CONFIG.server.port}`);
      logger.info(`Environment: ${CONFIG.server.environment}`);
      logger.info(`CORS Origins: ${CONFIG.cors.origins.join(', ')}`);
    });

    // Graceful shutdown
    const gracefulShutdown = async (signal: string) => {
      logger.info(`Received ${signal}. Starting graceful shutdown...`);
      
      server.close(async () => {
        logger.info('HTTP server closed');
        
        // Cleanup MCP services
        if (mcpManager) {
          await mcpManager.cleanup();
          logger.info('MCP services cleaned up');
        }
        
        logger.info('Graceful shutdown completed');
        process.exit(0);
      });
    };

    process.on('SIGTERM', () => gracefulShutdown('SIGTERM'));
    process.on('SIGINT', () => gracefulShutdown('SIGINT'));

  } catch (error) {
    logger.error('Failed to start server:', error);
    process.exit(1);
  }
}

// Handle unhandled promise rejections
process.on('unhandledRejection', (reason, promise) => {
  logger.error('Unhandled Rejection at:', promise, 'reason:', reason);
  process.exit(1);
});

// Handle uncaught exceptions
process.on('uncaughtException', (error) => {
  logger.error('Uncaught Exception:', error);
  process.exit(1);
});

// Start the server only if not in Vercel environment
if (process.env.VERCEL !== '1') {
  startServer();
}

export { app, CONFIG, logger };