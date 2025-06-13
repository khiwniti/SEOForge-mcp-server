/**
 * Vercel API endpoint for MCP Server
 * Handles all MCP protocol requests in serverless environment
 */

import { VercelRequest, VercelResponse } from '@vercel/node';
import { ContentGenerationService } from '../src/services/content-generation.js';
import { SEOAnalysisService } from '../src/services/seo-analysis.js';
import { ImageGenerationService } from '../src/services/image-generation.js';
import { WordPressService } from '../src/services/wordpress.js';
import { ThaiLanguageService } from '../src/services/thai-language.js';
import { KeywordResearchService } from '../src/services/keyword-research.js';
import { AuthenticationService } from '../src/services/authentication.js';
import { CacheService } from '../src/services/cache.js';

// Global service instances (cached across invocations)
let services: {
  content?: ContentGenerationService;
  seo?: SEOAnalysisService;
  image?: ImageGenerationService;
  wordpress?: WordPressService;
  thai?: ThaiLanguageService;
  keyword?: KeywordResearchService;
  auth?: AuthenticationService;
  cache?: CacheService;
} = {};

// Initialize services
async function initializeServices() {
  if (services.content) return services; // Already initialized

  const config = {
    googleApiKey: process.env.GOOGLE_API_KEY,
    openaiApiKey: process.env.OPENAI_API_KEY,
    anthropicApiKey: process.env.ANTHROPIC_API_KEY,
    replicateToken: process.env.REPLICATE_API_TOKEN,
    togetherToken: process.env.TOGETHER_API_KEY
  };

  // Initialize cache service first
  services.cache = new CacheService(process.env.REDIS_URL);
  await services.cache.initialize();

  // Initialize authentication service
  services.auth = new AuthenticationService();
  await services.auth.initialize();

  // Initialize AI services
  services.content = new ContentGenerationService(config);
  await services.content.initialize();

  services.seo = new SEOAnalysisService(config);
  await services.seo.initialize();

  services.image = new ImageGenerationService(config);
  await services.image.initialize();

  services.thai = new ThaiLanguageService(config);
  await services.thai.initialize();

  services.keyword = new KeywordResearchService(config);
  await services.keyword.initialize();

  // Initialize WordPress service
  services.wordpress = new WordPressService();
  await services.wordpress.initialize();

  return services;
}

// Authentication middleware
async function authenticate(req: VercelRequest): Promise<{ success: boolean; user?: any; error?: string }> {
  const authHeader = req.headers.authorization;
  const apiKey = req.headers['x-api-key'] as string;

  if (!services.auth) {
    await initializeServices();
  }

  if (apiKey) {
    return await services.auth!.authenticate({ api_key: apiKey });
  }

  if (authHeader && authHeader.startsWith('Bearer ')) {
    const token = authHeader.substring(7);
    return await services.auth!.authenticate({ token });
  }

  return { success: false, error: 'No authentication provided' };
}

// Rate limiting
async function checkRateLimit(req: VercelRequest): Promise<boolean> {
  if (!services.cache) {
    await initializeServices();
  }

  const identifier = req.headers['x-forwarded-for'] as string || 'unknown';
  const count = await services.cache!.incrementRateLimit(identifier, 3600); // 1 hour window
  
  return count <= 1000; // 1000 requests per hour
}

// Main handler
export default async function handler(req: VercelRequest, res: VercelResponse) {
  // CORS headers
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-Key, X-MCP-Version, X-Client-ID');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  try {
    // Initialize services
    await initializeServices();

    // Check rate limit
    const rateLimitOk = await checkRateLimit(req);
    if (!rateLimitOk) {
      return res.status(429).json({
        error: 'Rate limit exceeded',
        message: 'Too many requests. Please try again later.'
      });
    }

    // Handle different endpoints
    const path = req.url?.split('?')[0] || '';

    if (path === '/mcp/health') {
      return handleHealth(req, res);
    }

    if (path === '/mcp/tools/list') {
      return handleListTools(req, res);
    }

    if (path === '/mcp/tools/execute') {
      return handleExecuteTool(req, res);
    }

    if (path === '/mcp/auth/login') {
      return handleLogin(req, res);
    }

    if (path === '/mcp/auth/register') {
      return handleRegister(req, res);
    }

    // Default MCP protocol handler
    return handleMCPRequest(req, res);

  } catch (error) {
    console.error('MCP Server Error:', error);
    return res.status(500).json({
      error: 'Internal server error',
      message: error instanceof Error ? error.message : 'Unknown error'
    });
  }
}

async function handleHealth(req: VercelRequest, res: VercelResponse) {
  const stats = await services.cache!.getStats();
  
  return res.json({
    status: 'healthy',
    version: '2.0.0',
    timestamp: new Date().toISOString(),
    services: {
      content: !!services.content,
      seo: !!services.seo,
      image: !!services.image,
      wordpress: !!services.wordpress,
      thai: !!services.thai,
      keyword: !!services.keyword,
      auth: !!services.auth,
      cache: !!services.cache
    },
    cache_stats: stats
  });
}

async function handleListTools(req: VercelRequest, res: VercelResponse) {
  const tools = [
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

  return res.json({ tools });
}

async function handleExecuteTool(req: VercelRequest, res: VercelResponse) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  // Authenticate request
  const auth = await authenticate(req);
  if (!auth.success) {
    return res.status(401).json({ error: auth.error });
  }

  const { tool, arguments: args } = req.body;

  if (!tool || !args) {
    return res.status(400).json({ error: 'Missing tool or arguments' });
  }

  try {
    let result;

    switch (tool) {
      case 'generate_content':
        result = await services.content!.generateContent(args);
        break;
      case 'analyze_seo':
        result = await services.seo!.analyzeSEO(args);
        break;
      case 'generate_image':
        result = await services.image!.generateImage(args);
        break;
      case 'wordpress_sync':
        result = await services.wordpress!.syncContent(args);
        break;
      case 'translate_thai':
        result = await services.thai!.translateContent(args);
        break;
      case 'research_keywords':
        result = await services.keyword!.researchKeywords(args);
        break;
      default:
        return res.status(400).json({ error: `Unknown tool: ${tool}` });
    }

    return res.json({
      success: true,
      result,
      tool,
      timestamp: new Date().toISOString()
    });

  } catch (error) {
    console.error(`Tool execution error (${tool}):`, error);
    return res.status(500).json({
      error: 'Tool execution failed',
      message: error instanceof Error ? error.message : 'Unknown error',
      tool
    });
  }
}

async function handleLogin(req: VercelRequest, res: VercelResponse) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  const { email, password } = req.body;

  if (!email || !password) {
    return res.status(400).json({ error: 'Email and password required' });
  }

  const result = await services.auth!.authenticate({ email, password });

  if (result.success) {
    const token = await services.auth!.generateToken(result.user!.id!);
    return res.json({
      success: true,
      user: result.user,
      token
    });
  } else {
    return res.status(401).json({ error: result.error });
  }
}

async function handleRegister(req: VercelRequest, res: VercelResponse) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  const { email, password, role = 'user' } = req.body;

  if (!email || !password) {
    return res.status(400).json({ error: 'Email and password required' });
  }

  const result = await services.auth!.createUser(email, password, role);

  if (result.success) {
    return res.json(result);
  } else {
    return res.status(400).json({ error: result.error });
  }
}

async function handleMCPRequest(req: VercelRequest, res: VercelResponse) {
  // Handle generic MCP protocol requests
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  const { method, params } = req.body;

  switch (method) {
    case 'initialize':
      return res.json({
        protocolVersion: '2024-11-05',
        capabilities: {
          tools: {},
          resources: {}
        },
        serverInfo: {
          name: 'seoforge-unified-mcp',
          version: '2.0.0'
        }
      });

    case 'tools/list':
      return handleListTools(req, res);

    case 'tools/call':
      req.body = { tool: params.name, arguments: params.arguments };
      return handleExecuteTool(req, res);

    default:
      return res.status(400).json({ error: `Unknown method: ${method}` });
  }
}
