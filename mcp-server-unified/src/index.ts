#!/usr/bin/env node

/**
 * Unified SEO Forge MCP Server
 * Consolidates all backend functionality into a single MCP server
 * Optimized for Vercel deployment
 */

import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import {
  CallToolRequestSchema,
  ErrorCode,
  ListToolsRequestSchema,
  McpError,
  ListResourcesRequestSchema,
  ReadResourceRequestSchema,
} from '@modelcontextprotocol/sdk/types.js';
import { z } from 'zod';
import { createLogger, format, transports } from 'winston';
import dotenv from 'dotenv';

// Load environment variables
dotenv.config();

// Import unified services
import { ContentGenerationService } from './services/content-generation.js';
import { SEOAnalysisService } from './services/seo-analysis.js';
import { ImageGenerationService } from './services/image-generation.js';
import { WordPressService } from './services/wordpress.js';
import { ThaiLanguageService } from './services/thai-language.js';
import { KeywordResearchService } from './services/keyword-research.js';
import { AuthenticationService } from './services/authentication.js';
import { CacheService } from './services/cache.js';

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
    })
  ]
});

// Configuration
const CONFIG = {
  server: {
    name: 'seoforge-unified-mcp',
    version: '2.0.0',
    port: parseInt(process.env.PORT || '3000'),
    host: process.env.HOST || '0.0.0.0'
  },
  ai: {
    googleApiKey: process.env.GOOGLE_API_KEY,
    openaiApiKey: process.env.OPENAI_API_KEY,
    anthropicApiKey: process.env.ANTHROPIC_API_KEY,
    replicateToken: process.env.REPLICATE_API_TOKEN,
    togetherToken: process.env.TOGETHER_API_KEY
  },
  services: {
    redisUrl: process.env.REDIS_URL,
    databaseUrl: process.env.DATABASE_URL
  }
};

// Service instances
let contentService: ContentGenerationService;
let seoService: SEOAnalysisService;
let imageService: ImageGenerationService;
let wordpressService: WordPressService;
let thaiService: ThaiLanguageService;
let keywordService: KeywordResearchService;
let authService: AuthenticationService;
let cacheService: CacheService;

// Initialize services
async function initializeServices() {
  try {
    logger.info('Initializing unified MCP services...');
    
    // Initialize cache service first
    cacheService = new CacheService(CONFIG.services.redisUrl);
    await cacheService.initialize();
    
    // Initialize authentication service
    authService = new AuthenticationService();
    await authService.initialize();
    
    // Initialize AI services
    contentService = new ContentGenerationService(CONFIG.ai);
    await contentService.initialize();
    
    seoService = new SEOAnalysisService(CONFIG.ai);
    await seoService.initialize();
    
    imageService = new ImageGenerationService(CONFIG.ai);
    await imageService.initialize();
    
    thaiService = new ThaiLanguageService(CONFIG.ai);
    await thaiService.initialize();
    
    keywordService = new KeywordResearchService(CONFIG.ai);
    await keywordService.initialize();
    
    // Initialize WordPress service
    wordpressService = new WordPressService();
    await wordpressService.initialize();
    
    logger.info('All services initialized successfully');
  } catch (error) {
    logger.error('Failed to initialize services:', error);
    throw error;
  }
}

// Create MCP server
const server = new Server(
  {
    name: CONFIG.server.name,
    version: CONFIG.server.version,
  },
  {
    capabilities: {
      tools: {},
      resources: {},
    },
  }
);

// Tool definitions
const TOOLS = {
  // Content Generation Tools
  generate_content: {
    name: 'generate_content',
    description: 'Generate SEO-optimized content for various purposes',
    inputSchema: {
      type: 'object',
      properties: {
        type: { type: 'string', enum: ['blog', 'product', 'category', 'meta'] },
        topic: { type: 'string' },
        keywords: { type: 'array', items: { type: 'string' } },
        language: { type: 'string', default: 'en' },
        tone: { type: 'string', default: 'professional' },
        length: { type: 'string', enum: ['short', 'medium', 'long'], default: 'medium' }
      },
      required: ['type', 'topic']
    }
  },
  
  // SEO Analysis Tools
  analyze_seo: {
    name: 'analyze_seo',
    description: 'Perform comprehensive SEO analysis',
    inputSchema: {
      type: 'object',
      properties: {
        url: { type: 'string' },
        content: { type: 'string' },
        keywords: { type: 'array', items: { type: 'string' } },
        competitors: { type: 'array', items: { type: 'string' } }
      },
      required: ['url']
    }
  },
  
  // Image Generation Tools
  generate_image: {
    name: 'generate_image',
    description: 'Generate images using AI models',
    inputSchema: {
      type: 'object',
      properties: {
        prompt: { type: 'string' },
        style: { type: 'string', default: 'realistic' },
        size: { type: 'string', enum: ['512x512', '1024x1024', '1024x768'], default: '1024x1024' },
        model: { type: 'string', enum: ['flux', 'dalle', 'midjourney'], default: 'flux' }
      },
      required: ['prompt']
    }
  },
  
  // WordPress Integration Tools
  wordpress_sync: {
    name: 'wordpress_sync',
    description: 'Sync content with WordPress sites',
    inputSchema: {
      type: 'object',
      properties: {
        site_url: { type: 'string' },
        action: { type: 'string', enum: ['create', 'update', 'delete'] },
        content_type: { type: 'string', enum: ['post', 'page', 'product'] },
        content: { type: 'object' },
        auth_token: { type: 'string' }
      },
      required: ['site_url', 'action', 'content_type']
    }
  },
  
  // Thai Language Tools
  translate_thai: {
    name: 'translate_thai',
    description: 'Translate and localize content for Thai market',
    inputSchema: {
      type: 'object',
      properties: {
        text: { type: 'string' },
        source_language: { type: 'string', default: 'en' },
        target_language: { type: 'string', default: 'th' },
        context: { type: 'string' },
        cultural_adaptation: { type: 'boolean', default: true }
      },
      required: ['text']
    }
  },
  
  // Keyword Research Tools
  research_keywords: {
    name: 'research_keywords',
    description: 'Research and analyze keywords for SEO',
    inputSchema: {
      type: 'object',
      properties: {
        seed_keywords: { type: 'array', items: { type: 'string' } },
        market: { type: 'string', default: 'global' },
        language: { type: 'string', default: 'en' },
        industry: { type: 'string' },
        competition_level: { type: 'string', enum: ['low', 'medium', 'high'], default: 'medium' }
      },
      required: ['seed_keywords']
    }
  }
};

// Register tools
server.setRequestHandler(ListToolsRequestSchema, async () => {
  return {
    tools: Object.values(TOOLS)
  };
});

// Handle tool calls
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;
  
  try {
    logger.info(`Executing tool: ${name}`, { args });
    
    switch (name) {
      case 'generate_content':
        return await contentService.generateContent(args);
        
      case 'analyze_seo':
        return await seoService.analyzeSEO(args);
        
      case 'generate_image':
        return await imageService.generateImage(args);
        
      case 'wordpress_sync':
        return await wordpressService.syncContent(args);
        
      case 'translate_thai':
        return await thaiService.translateContent(args);
        
      case 'research_keywords':
        return await keywordService.researchKeywords(args);
        
      default:
        throw new McpError(
          ErrorCode.MethodNotFound,
          `Unknown tool: ${name}`
        );
    }
  } catch (error) {
    logger.error(`Tool execution failed: ${name}`, error);
    throw new McpError(
      ErrorCode.InternalError,
      `Tool execution failed: ${error instanceof Error ? error.message : 'Unknown error'}`
    );
  }
});

// Main function
async function main() {
  try {
    logger.info(`Starting SEO Forge Unified MCP Server v${CONFIG.server.version}`);
    
    // Initialize services
    await initializeServices();
    
    // Start server
    const transport = new StdioServerTransport();
    await server.connect(transport);
    
    logger.info('MCP Server started successfully');
  } catch (error) {
    logger.error('Failed to start MCP server:', error);
    process.exit(1);
  }
}

// Handle graceful shutdown
process.on('SIGINT', async () => {
  logger.info('Shutting down MCP server...');
  process.exit(0);
});

process.on('SIGTERM', async () => {
  logger.info('Shutting down MCP server...');
  process.exit(0);
});

// Start the server
if (import.meta.url === `file://${process.argv[1]}`) {
  main().catch((error) => {
    logger.error('Unhandled error:', error);
    process.exit(1);
  });
}

export { server, CONFIG, logger };
