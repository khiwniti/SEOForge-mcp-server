/**
 * MCP Service Manager
 * Manages all MCP-based AI services and provides unified interface
 */

import { createLogger } from 'winston';
import { ContentGenerationService } from './content-generation';
import { SEOAnalysisService } from './seo-analysis';
import { ImageGenerationService } from './image-generation';
import { WordPressService } from './wordpress';
import { ThaiLanguageService } from './thai-language';
import { KeywordResearchService } from './keyword-research';
import { AuthenticationService } from './authentication';
import { CacheService } from './cache';

const logger = createLogger({
  level: process.env.LOG_LEVEL || 'info'
});

export interface MCPToolRequest {
  tool: string;
  arguments: Record<string, any>;
  context?: Record<string, any>;
}

export interface MCPToolResponse {
  success: boolean;
  result?: any;
  error?: string;
  tool: string;
  executionTime: number;
  timestamp: string;
}

export class MCPServiceManager {
  private services: {
    content?: ContentGenerationService;
    seo?: SEOAnalysisService;
    image?: ImageGenerationService;
    wordpress?: WordPressService;
    thai?: ThaiLanguageService;
    keyword?: KeywordResearchService;
    auth?: AuthenticationService;
    cache?: CacheService;
  } = {};

  private initialized = false;

  constructor() {
    logger.info('MCP Service Manager created');
  }

  async initialize(): Promise<void> {
    if (this.initialized) {
      logger.warn('MCP Service Manager already initialized');
      return;
    }

    try {
      logger.info('Initializing MCP services...');

      const config = {
        // Prioritize Google Gemini 2.5 Pro for enhanced accuracy
        googleApiKey: process.env.GOOGLE_API_KEY || 'AIzaSyDTITCw_UcgzUufrsCFuxp9HXri6Y0XrDo',
        openaiApiKey: process.env.OPENAI_API_KEY || '',
        anthropicApiKey: process.env.ANTHROPIC_API_KEY || '',
        replicateToken: process.env.REPLICATE_API_TOKEN || '',
        togetherToken: process.env.TOGETHER_API_KEY || ''
      };

      // Initialize cache service first
      this.services.cache = new CacheService(process.env.REDIS_URL);
      await this.services.cache.initialize();
      logger.info('Cache service initialized');

      // Initialize authentication service
      this.services.auth = new AuthenticationService();
      await this.services.auth.initialize();
      logger.info('Authentication service initialized');

      // Initialize AI services
      this.services.content = new ContentGenerationService(config);
      await this.services.content.initialize();
      logger.info('Content generation service initialized');

      this.services.seo = new SEOAnalysisService(config);
      await this.services.seo.initialize();
      logger.info('SEO analysis service initialized');

      this.services.image = new ImageGenerationService(config);
      await this.services.image.initialize();
      logger.info('Image generation service initialized');

      this.services.thai = new ThaiLanguageService(config);
      await this.services.thai.initialize();
      logger.info('Thai language service initialized');

      this.services.keyword = new KeywordResearchService(config);
      await this.services.keyword.initialize();
      logger.info('Keyword research service initialized');

      // Initialize WordPress service
      this.services.wordpress = new WordPressService();
      await this.services.wordpress.initialize();
      logger.info('WordPress service initialized');

      this.initialized = true;
      logger.info('All MCP services initialized successfully');

    } catch (error) {
      logger.error('Failed to initialize MCP services:', error);
      throw error;
    }
  }

  async executeTool(request: MCPToolRequest): Promise<MCPToolResponse> {
    const startTime = Date.now();
    
    if (!this.initialized) {
      throw new Error('MCP Service Manager not initialized');
    }

    try {
      logger.info(`Executing MCP tool: ${request.tool}`, { arguments: request.arguments });

      let result: any;

      switch (request.tool) {
        case 'generate_content':
          if (!this.services.content) throw new Error('Content service not available');
          result = await this.services.content.generateContent(request.arguments as any);
          break;

        case 'analyze_seo':
          if (!this.services.seo) throw new Error('SEO service not available');
          result = await this.services.seo.analyzeSEO(request.arguments as any);
          break;

        case 'generate_image':
          if (!this.services.image) throw new Error('Image service not available');
          result = await this.services.image.generateImage(request.arguments as any);
          break;

        case 'wordpress_sync':
          if (!this.services.wordpress) throw new Error('WordPress service not available');
          result = await this.services.wordpress.syncContent(request.arguments as any);
          break;

        case 'translate_thai':
          if (!this.services.thai) throw new Error('Thai service not available');
          result = await this.services.thai.translateContent(request.arguments as any);
          break;

        case 'research_keywords':
          if (!this.services.keyword) throw new Error('Keyword service not available');
          result = await this.services.keyword.researchKeywords(request.arguments as any);
          break;

        // Legacy API compatibility tools
        case 'blog_generator':
          if (!this.services.content) throw new Error('Content service not available');
          result = await this.services.content.generateContent({
            type: 'blog',
            ...request.arguments
          } as any);
          break;

        case 'seo_analyzer':
          if (!this.services.seo) throw new Error('SEO service not available');
          result = await this.services.seo.analyzeSEO(request.arguments as any);
          break;

        case 'flux_image_gen':
          if (!this.services.image) throw new Error('Image service not available');
          result = await this.services.image.generateImage(request.arguments as any);
          break;

        default:
          throw new Error(`Unknown tool: ${request.tool}`);
      }

      const executionTime = Date.now() - startTime;

      return {
        success: true,
        result,
        tool: request.tool,
        executionTime,
        timestamp: new Date().toISOString()
      };

    } catch (error) {
      const executionTime = Date.now() - startTime;
      const errorMessage = error instanceof Error ? error.message : 'Unknown error';
      
      logger.error(`Tool execution failed: ${request.tool}`, { error: errorMessage });

      return {
        success: false,
        error: errorMessage,
        tool: request.tool,
        executionTime,
        timestamp: new Date().toISOString()
      };
    }
  }

  async listTools(): Promise<Array<{ name: string; description: string; inputSchema: any }>> {
    return [
      {
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
      {
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
      {
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
      {
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
      {
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
      {
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
    ];
  }

  async getServiceStatus(): Promise<Record<string, boolean>> {
    return {
      content: !!this.services.content,
      seo: !!this.services.seo,
      image: !!this.services.image,
      wordpress: !!this.services.wordpress,
      thai: !!this.services.thai,
      keyword: !!this.services.keyword,
      auth: !!this.services.auth,
      cache: !!this.services.cache
    };
  }

  async getCacheStats(): Promise<any> {
    if (!this.services.cache) {
      return { error: 'Cache service not available' };
    }
    return await this.services.cache.getStats();
  }

  async authenticate(credentials: any): Promise<any> {
    if (!this.services.auth) {
      throw new Error('Authentication service not available');
    }
    return await this.services.auth.authenticate(credentials);
  }

  async cleanup(): Promise<void> {
    logger.info('Cleaning up MCP services...');
    
    // Cleanup services in reverse order
    const cleanupPromises = Object.values(this.services)
      .filter(service => service && typeof (service as any).cleanup === 'function')
      .map(service => (service as any).cleanup());

    await Promise.all(cleanupPromises);
    
    this.services = {};
    this.initialized = false;
    
    logger.info('MCP services cleanup completed');
  }
}