/**
 * MCP protocol routes
 */

import { Router, Request, Response } from 'express';
import { body, validationResult } from 'express-validator';
import { asyncHandler, createError } from '../middleware/error-handler.js';
import { authMiddleware, optionalAuth, AuthenticatedRequest } from '../middleware/auth.js';
import { MCPServiceManager } from '../services/mcp-service-manager.js';

export const mcpRoutes = Router();

// List available tools
mcpRoutes.get('/tools', asyncHandler(async (req: Request, res: Response) => {
  const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
  const tools = await mcpManager.listTools();
  
  res.json({
    success: true,
    tools,
    count: tools.length,
    timestamp: new Date().toISOString()
  });
}));

// Execute MCP tool
mcpRoutes.post('/tools/execute', 
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
        user: req.user
      }
    });

    res.json(result);
  })
);

// MCP protocol handler (for direct MCP clients)
mcpRoutes.post('/protocol', 
  optionalAuth,
  asyncHandler(async (req: AuthenticatedRequest, res: Response) => {
    const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
    const { method, params } = req.body;

    switch (method) {
      case 'initialize':
        res.json({
          protocolVersion: '2024-11-05',
          capabilities: {
            tools: {},
            resources: {}
          },
          serverInfo: {
            name: 'seoforge-express-mcp',
            version: '1.0.0'
          }
        });
        break;

      case 'tools/list':
        const tools = await mcpManager.listTools();
        res.json({ tools });
        break;

      case 'tools/call':
        if (!req.user) {
          throw createError('Authentication required for tool execution', 401);
        }
        
        const result = await mcpManager.executeTool({
          tool: params.name,
          arguments: params.arguments,
          context: {
            user: req.user
          }
        });
        res.json(result);
        break;

      default:
        throw createError(`Unknown MCP method: ${method}`, 400);
    }
  })
);

// Get MCP server status
mcpRoutes.get('/status', asyncHandler(async (req: Request, res: Response) => {
  const mcpManager = req.app.locals.mcpManager as MCPServiceManager;
  const serviceStatus = await mcpManager.getServiceStatus();
  const tools = await mcpManager.listTools();

  res.json({
    status: 'active',
    version: '1.0.0',
    protocol_version: '2024-11-05',
    services: serviceStatus,
    available_tools: tools.map(t => t.name),
    tool_count: tools.length,
    timestamp: new Date().toISOString()
  });
}));