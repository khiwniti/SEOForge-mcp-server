/**
 * Health check routes
 */

import { Router, Request, Response } from 'express';
import { asyncHandler } from '../middleware/error-handler.js';
import { MCPServiceManager } from '../services/mcp-service-manager.js';

export const healthRoutes = Router();

healthRoutes.get('/', asyncHandler(async (req: Request, res: Response) => {
  const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
  
  const serviceStatus = await mcpManager.getServiceStatus();
  const cacheStats = await mcpManager.getCacheStats();
  
  const health = {
    status: 'healthy',
    timestamp: new Date().toISOString(),
    version: '1.0.0',
    environment: process.env.NODE_ENV || 'development',
    uptime: process.uptime(),
    memory: process.memoryUsage(),
    services: serviceStatus,
    cache: cacheStats,
    endpoints: {
      health: '/health',
      mcp: '/mcp',
      api: '/api',
      auth: '/auth'
    }
  };

  res.json(health);
}));

healthRoutes.get('/ready', asyncHandler(async (req: Request, res: Response) => {
  const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
  const serviceStatus = await mcpManager.getServiceStatus();
  
  const allServicesReady = Object.values(serviceStatus).every(status => status === true);
  
  if (allServicesReady) {
    res.json({
      status: 'ready',
      timestamp: new Date().toISOString(),
      services: serviceStatus
    });
  } else {
    res.status(503).json({
      status: 'not ready',
      timestamp: new Date().toISOString(),
      services: serviceStatus
    });
  }
}));

healthRoutes.get('/live', asyncHandler(async (req: Request, res: Response) => {
  res.json({
    status: 'alive',
    timestamp: new Date().toISOString(),
    uptime: process.uptime()
  });
}));