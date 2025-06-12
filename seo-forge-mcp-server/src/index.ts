#!/usr/bin/env node

/**
 * SEO Forge MCP Server
 * Universal SEO MCP Server with Flux Image Generation
 * 
 * Usage:
 *   npx seo-forge-mcp-server
 *   npm exec seo-forge-mcp-server
 *   seo-forge-mcp --port 3000
 */

import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import {
  CallToolRequestSchema,
  ErrorCode,
  ListToolsRequestSchema,
  McpError,
} from '@modelcontextprotocol/sdk/types.js';
import { z } from 'zod';
import axios from 'axios';
import * as dotenv from 'dotenv';
import { createLogger, format, transports } from 'winston';

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
    })
  ]
});

// Configuration
const CONFIG = {
  server: {
    name: 'seo-forge-mcp-server',
    version: '1.2.0',
    port: parseInt(process.env.PORT || '3000'),
    host: process.env.HOST || '0.0.0.0'
  },
  api: {
    baseUrl: process.env.API_BASE_URL || 'https://your-seo-forge-server.com',
    timeout: parseInt(process.env.API_TIMEOUT || '30000'),
    retries: parseInt(process.env.API_RETRIES || '3')
  },
  ai: {
    googleApiKey: process.env.GOOGLE_API_KEY,
    huggingfaceToken: process.env.HUGGINGFACE_TOKEN,
    replicateToken: process.env.REPLICATE_API_TOKEN,
    togetherToken: process.env.TOGETHER_API_KEY
  }
};

// Validation schemas
const ContentGenerationSchema = z.object({
  topic: z.string().optional(),
  keywords: z.array(z.string()).default([]),
  content_type: z.string().default('blog_post'),
  language: z.string().default('en'),
  tone: z.string().default('professional'),
  length: z.string().default('medium'),
  industry: z.string().default('general'),
  include_images: z.boolean().default(false),
  image_count: z.number().min(1).max(10).default(3),
  image_style: z.string().default('professional')
});

const FluxImageGenerationSchema = z.object({
  prompt: z.string(),
  negative_prompt: z.string().default(''),
  width: z.number().min(256).max(2048).default(1024),
  height: z.number().min(256).max(2048).default(1024),
  guidance_scale: z.number().min(1).max(20).default(7.5),
  num_inference_steps: z.number().min(1).max(50).default(20),
  seed: z.number().optional(),
  model: z.enum(['flux-schnell', 'flux-dev', 'flux-pro']).default('flux-schnell'),
  style: z.string().default('professional'),
  enhance_prompt: z.boolean().default(true)
});

const SEOAnalysisSchema = z.object({
  content: z.string(),
  keywords: z.array(z.string()).default([]),
  language: z.string().default('en'),
  url: z.string().optional()
});

const KeywordResearchSchema = z.object({
  seed_keywords: z.array(z.string()),
  language: z.string().default('en'),
  location: z.string().default('global'),
  limit: z.number().min(1).max(100).default(50)
});

// API Client
class SEOForgeAPIClient {
  private baseUrl: string;
  private timeout: number;
  private retries: number;

  constructor(baseUrl: string, timeout: number = 30000, retries: number = 3) {
    this.baseUrl = baseUrl;
    this.timeout = timeout;
    this.retries = retries;
  }

  private async makeRequest(endpoint: string, data: any, method: 'GET' | 'POST' = 'POST') {
    const url = `${this.baseUrl}${endpoint}`;
    
    for (let attempt = 1; attempt <= this.retries; attempt++) {
      try {
        logger.info(`Making ${method} request to ${endpoint} (attempt ${attempt})`);
        
        const response = await axios({
          method,
          url,
          data: method === 'POST' ? data : undefined,
          params: method === 'GET' ? data : undefined,
          timeout: this.timeout,
          headers: {
            'Content-Type': 'application/json',
            'User-Agent': 'SEO-Forge-MCP-Server/1.2.0'
          }
        });

        logger.info(`Request successful: ${endpoint}`);
        return response.data;
      } catch (error: any) {
        logger.error(`Request failed (attempt ${attempt}): ${error.message}`);
        
        if (attempt === this.retries) {
          throw new McpError(
            ErrorCode.InternalError,
            `API request failed after ${this.retries} attempts: ${error.message}`
          );
        }
        
        // Wait before retry (exponential backoff)
        await new Promise(resolve => setTimeout(resolve, Math.pow(2, attempt) * 1000));
      }
    }
  }

  async generateContent(params: z.infer<typeof ContentGenerationSchema>) {
    return this.makeRequest('/universal-mcp/generate-content', params);
  }

  async generateFluxImage(params: z.infer<typeof FluxImageGenerationSchema>) {
    return this.makeRequest('/universal-mcp/generate-flux-image', params);
  }

  async generateFluxBatch(prompts: string[], params: Partial<z.infer<typeof FluxImageGenerationSchema>>) {
    return this.makeRequest('/universal-mcp/generate-flux-batch', {
      prompts,
      ...params
    });
  }

  async analyzeSEO(params: z.infer<typeof SEOAnalysisSchema>) {
    return this.makeRequest('/universal-mcp/analyze-seo', params);
  }

  async researchKeywords(params: z.infer<typeof KeywordResearchSchema>) {
    return this.makeRequest('/universal-mcp/research-keywords', params);
  }

  async getStatus() {
    return this.makeRequest('/universal-mcp/status', {}, 'GET');
  }

  async getFluxModels() {
    return this.makeRequest('/universal-mcp/flux-models', {}, 'GET');
  }
}

// Initialize API client
const apiClient = new SEOForgeAPIClient(CONFIG.api.baseUrl, CONFIG.api.timeout, CONFIG.api.retries);

// MCP Server setup
const server = new Server(
  {
    name: CONFIG.server.name,
    version: CONFIG.server.version,
  }
);

// Tool definitions
const TOOLS = [
  {
    name: 'generate_content',
    description: 'Generate SEO-optimized content using AI with optional Flux image generation',
    inputSchema: {
      type: 'object',
      properties: {
        topic: {
          type: 'string',
          description: 'Main topic for content generation'
        },
        keywords: {
          type: 'array',
          items: { type: 'string' },
          description: 'Target keywords for SEO optimization'
        },
        content_type: {
          type: 'string',
          enum: ['blog_post', 'product_description', 'landing_page', 'how_to_guide', 'news_article'],
          description: 'Type of content to generate'
        },
        language: {
          type: 'string',
          enum: ['en', 'th', 'es', 'fr', 'de', 'it', 'pt', 'ru', 'ja', 'ko', 'zh'],
          description: 'Language for content generation'
        },
        tone: {
          type: 'string',
          enum: ['professional', 'casual', 'friendly', 'authoritative', 'conversational'],
          description: 'Tone of the generated content'
        },
        length: {
          type: 'string',
          enum: ['short', 'medium', 'long'],
          description: 'Length of the generated content'
        },
        industry: {
          type: 'string',
          description: 'Industry or niche for specialized content'
        },
        include_images: {
          type: 'boolean',
          description: 'Whether to generate images with content'
        },
        image_count: {
          type: 'number',
          minimum: 1,
          maximum: 10,
          description: 'Number of images to generate'
        },
        image_style: {
          type: 'string',
          enum: ['professional', 'artistic', 'photorealistic', 'minimalist', 'commercial', 'cinematic'],
          description: 'Style for generated images'
        }
      },
      required: []
    }
  },
  {
    name: 'generate_flux_image',
    description: 'Generate high-quality images using state-of-the-art Flux AI models',
    inputSchema: {
      type: 'object',
      properties: {
        prompt: {
          type: 'string',
          description: 'Text description of the image to generate'
        },
        negative_prompt: {
          type: 'string',
          description: 'What to avoid in the generated image'
        },
        width: {
          type: 'number',
          minimum: 256,
          maximum: 2048,
          description: 'Image width in pixels'
        },
        height: {
          type: 'number',
          minimum: 256,
          maximum: 2048,
          description: 'Image height in pixels'
        },
        guidance_scale: {
          type: 'number',
          minimum: 1,
          maximum: 20,
          description: 'How closely to follow the prompt (1.0-20.0)'
        },
        num_inference_steps: {
          type: 'number',
          minimum: 1,
          maximum: 50,
          description: 'Quality vs speed tradeoff (1-50 steps)'
        },
        seed: {
          type: 'number',
          description: 'Random seed for reproducible results'
        },
        model: {
          type: 'string',
          enum: ['flux-schnell', 'flux-dev', 'flux-pro'],
          description: 'Flux model variant to use'
        },
        style: {
          type: 'string',
          enum: ['professional', 'artistic', 'photorealistic', 'minimalist', 'commercial', 'cinematic', 'illustration', 'fantasy', 'modern'],
          description: 'Image style enhancement'
        },
        enhance_prompt: {
          type: 'boolean',
          description: 'Whether to enhance the prompt with AI'
        }
      },
      required: ['prompt']
    }
  },
  {
    name: 'generate_flux_batch',
    description: 'Generate multiple images using Flux models in batch',
    inputSchema: {
      type: 'object',
      properties: {
        prompts: {
          type: 'array',
          items: { type: 'string' },
          maxItems: 10,
          description: 'Array of prompts for batch generation'
        },
        model: {
          type: 'string',
          enum: ['flux-schnell', 'flux-dev', 'flux-pro'],
          description: 'Flux model variant to use'
        },
        style: {
          type: 'string',
          enum: ['professional', 'artistic', 'photorealistic', 'minimalist', 'commercial', 'cinematic'],
          description: 'Image style for all images'
        },
        width: {
          type: 'number',
          minimum: 256,
          maximum: 2048,
          description: 'Image width in pixels'
        },
        height: {
          type: 'number',
          minimum: 256,
          maximum: 2048,
          description: 'Image height in pixels'
        }
      },
      required: ['prompts']
    }
  },
  {
    name: 'analyze_seo',
    description: 'Analyze content for SEO optimization with actionable recommendations',
    inputSchema: {
      type: 'object',
      properties: {
        content: {
          type: 'string',
          description: 'Content to analyze for SEO'
        },
        keywords: {
          type: 'array',
          items: { type: 'string' },
          description: 'Target keywords for analysis'
        },
        language: {
          type: 'string',
          enum: ['en', 'th', 'es', 'fr', 'de'],
          description: 'Language of the content'
        },
        url: {
          type: 'string',
          description: 'URL of the content (optional)'
        }
      },
      required: ['content']
    }
  },
  {
    name: 'research_keywords',
    description: 'Research keywords with search volume, difficulty, and competition data',
    inputSchema: {
      type: 'object',
      properties: {
        seed_keywords: {
          type: 'array',
          items: { type: 'string' },
          description: 'Initial keywords to expand from'
        },
        language: {
          type: 'string',
          enum: ['en', 'th', 'es', 'fr', 'de'],
          description: 'Language for keyword research'
        },
        location: {
          type: 'string',
          description: 'Geographic location for localized results'
        },
        limit: {
          type: 'number',
          minimum: 1,
          maximum: 100,
          description: 'Maximum number of keywords to return'
        }
      },
      required: ['seed_keywords']
    }
  },
  {
    name: 'get_server_status',
    description: 'Get the current status and capabilities of the SEO Forge server',
    inputSchema: {
      type: 'object',
      properties: {},
      required: []
    }
  },
  {
    name: 'get_flux_models',
    description: 'Get information about available Flux models and their capabilities',
    inputSchema: {
      type: 'object',
      properties: {},
      required: []
    }
  }
];

// Register tools
server.setRequestHandler(ListToolsRequestSchema, async () => {
  return {
    tools: TOOLS
  };
});

// Handle tool calls
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;

  try {
    logger.info(`Executing tool: ${name}`);

    switch (name) {
      case 'generate_content': {
        const params = ContentGenerationSchema.parse(args);
        const result = await apiClient.generateContent(params);
        return {
          content: [
            {
              type: 'text',
              text: JSON.stringify(result, null, 2)
            }
          ]
        };
      }

      case 'generate_flux_image': {
        const params = FluxImageGenerationSchema.parse(args);
        const result = await apiClient.generateFluxImage(params);
        return {
          content: [
            {
              type: 'text',
              text: JSON.stringify(result, null, 2)
            }
          ]
        };
      }

      case 'generate_flux_batch': {
        const { prompts, ...params } = args as any;
        if (!Array.isArray(prompts) || prompts.length === 0) {
          throw new McpError(ErrorCode.InvalidParams, 'prompts must be a non-empty array');
        }
        const result = await apiClient.generateFluxBatch(prompts, params);
        return {
          content: [
            {
              type: 'text',
              text: JSON.stringify(result, null, 2)
            }
          ]
        };
      }

      case 'analyze_seo': {
        const params = SEOAnalysisSchema.parse(args);
        const result = await apiClient.analyzeSEO(params);
        return {
          content: [
            {
              type: 'text',
              text: JSON.stringify(result, null, 2)
            }
          ]
        };
      }

      case 'research_keywords': {
        const params = KeywordResearchSchema.parse(args);
        const result = await apiClient.researchKeywords(params);
        return {
          content: [
            {
              type: 'text',
              text: JSON.stringify(result, null, 2)
            }
          ]
        };
      }

      case 'get_server_status': {
        const result = await apiClient.getStatus();
        return {
          content: [
            {
              type: 'text',
              text: JSON.stringify(result, null, 2)
            }
          ]
        };
      }

      case 'get_flux_models': {
        const result = await apiClient.getFluxModels();
        return {
          content: [
            {
              type: 'text',
              text: JSON.stringify(result, null, 2)
            }
          ]
        };
      }

      default:
        throw new McpError(ErrorCode.MethodNotFound, `Unknown tool: ${name}`);
    }
  } catch (error: any) {
    logger.error(`Tool execution failed: ${error.message}`);
    
    if (error instanceof McpError) {
      throw error;
    }
    
    throw new McpError(
      ErrorCode.InternalError,
      `Tool execution failed: ${error.message}`
    );
  }
});

// CLI argument parsing
function parseArgs() {
  const args = process.argv.slice(2);
  const options: any = {};
  
  for (let i = 0; i < args.length; i++) {
    const arg = args[i];
    
    if (arg === '--port' || arg === '-p') {
      options.port = parseInt(args[++i]);
    } else if (arg === '--host' || arg === '-h') {
      options.host = args[++i];
    } else if (arg === '--api-url' || arg === '-u') {
      options.apiUrl = args[++i];
    } else if (arg === '--help') {
      console.log(`
SEO Forge MCP Server v${CONFIG.server.version}

Usage:
  npx seo-forge-mcp-server [options]
  npm exec seo-forge-mcp-server [options]

Options:
  --port, -p <port>     Server port (default: 3000)
  --host, -h <host>     Server host (default: 0.0.0.0)
  --api-url, -u <url>   API base URL
  --help                Show this help message

Environment Variables:
  PORT                  Server port
  HOST                  Server host
  API_BASE_URL          API base URL
  GOOGLE_API_KEY        Google AI API key
  HUGGINGFACE_TOKEN     Hugging Face API token
  REPLICATE_API_TOKEN   Replicate API token
  TOGETHER_API_KEY      Together AI API key
  LOG_LEVEL             Logging level (debug, info, warn, error)

Examples:
  npx seo-forge-mcp-server
  npx seo-forge-mcp-server --port 8080
  npm exec seo-forge-mcp-server -- --host localhost --port 3000
      `);
      process.exit(0);
    }
  }
  
  return options;
}

// Main function
async function main() {
  const options = parseArgs();
  
  // Update config with CLI options
  if (options.port) CONFIG.server.port = options.port;
  if (options.host) CONFIG.server.host = options.host;
  if (options.apiUrl) CONFIG.api.baseUrl = options.apiUrl;

  logger.info(`Starting SEO Forge MCP Server v${CONFIG.server.version}`);
  logger.info(`Configuration: ${JSON.stringify(CONFIG, null, 2)}`);

  // Test API connection
  try {
    logger.info('Testing API connection...');
    await apiClient.getStatus();
    logger.info('API connection successful');
  } catch (error: any) {
    logger.warn(`API connection failed: ${error.message}`);
    logger.warn('Server will start but some features may not work');
  }

  // Start MCP server
  const transport = new StdioServerTransport();
  await server.connect(transport);
  
  logger.info(`SEO Forge MCP Server is running and ready to accept connections`);
  logger.info(`Available tools: ${TOOLS.map(t => t.name).join(', ')}`);
}

// Error handling
process.on('uncaughtException', (error) => {
  logger.error('Uncaught exception:', error);
  process.exit(1);
});

process.on('unhandledRejection', (reason, promise) => {
  logger.error('Unhandled rejection at:', promise, 'reason:', reason);
  process.exit(1);
});

// Graceful shutdown
process.on('SIGINT', () => {
  logger.info('Received SIGINT, shutting down gracefully');
  process.exit(0);
});

process.on('SIGTERM', () => {
  logger.info('Received SIGTERM, shutting down gracefully');
  process.exit(0);
});

// Start the server
if (require.main === module) {
  main().catch((error) => {
    logger.error('Failed to start server:', error);
    process.exit(1);
  });
}

export { server, apiClient, CONFIG };