/**
 * MCP Service Layer for Universal MCP Platform
 * Handles all MCP server interactions with proper error handling and retry logic
 */

import { API_CONFIG, buildMCPUrl, handleApiError, retryRequest, apiLogger } from '../config/api';

// Types for MCP API responses
export interface MCPServerStatus {
  status: string;
  version: string;
  available_tools: string[];
  supported_industries: string[];
  active_connections: number;
  uptime: string;
}

export interface MCPToolRequest {
  tool_name: string;
  parameters: Record<string, any>;
  context?: Record<string, any>;
  industry?: string;
  language?: string;
}

export interface MCPToolResponse {
  success: boolean;
  tool_name: string;
  result: Record<string, any>;
  execution_time: number;
  timestamp: string;
}

export interface IndustryTemplate {
  id: string;
  name: string;
  description: string;
  industry: string;
  tools: string[];
  default_context: Record<string, any>;
  created_at: string;
}

// MCP Service Class
export class MCPService {
  private baseUrl: string;
  private defaultHeaders: Record<string, string>;

  constructor() {
    this.baseUrl = API_CONFIG.MCP_SERVER_URL;
    this.defaultHeaders = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
  }

  /**
   * Make HTTP request with error handling and retry logic
   */
  private async makeRequest<T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<T> {
    const url = buildMCPUrl(endpoint);
    
    const requestOptions: RequestInit = {
      ...options,
      headers: {
        ...this.defaultHeaders,
        ...options.headers,
      },
    };

    apiLogger.log(`Making request to: ${url}`, requestOptions);

    const requestFn = async () => {
      const response = await fetch(url, requestOptions);
      
      if (!response.ok) {
        const errorData = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorData}`);
      }
      
      const data = await response.json();
      apiLogger.log(`Response from ${url}:`, data);
      return data;
    };

    try {
      return await retryRequest(requestFn);
    } catch (error) {
      const handledError = handleApiError(error);
      apiLogger.error(`Request failed for ${url}:`, handledError);
      throw handledError;
    }
  }

  /**
   * Get MCP server status
   */
  async getServerStatus(): Promise<MCPServerStatus> {
    return this.makeRequest<MCPServerStatus>(API_CONFIG.ENDPOINTS.MCP_STATUS);
  }

  /**
   * Execute MCP tool
   */
  async executeTool(request: MCPToolRequest): Promise<MCPToolResponse> {
    return this.makeRequest<MCPToolResponse>(
      API_CONFIG.ENDPOINTS.MCP_EXECUTE_TOOL,
      {
        method: 'POST',
        body: JSON.stringify(request),
      }
    );
  }

  /**
   * Get available tools
   */
  async getAvailableTools(): Promise<string[]> {
    return this.makeRequest<string[]>(API_CONFIG.ENDPOINTS.MCP_TOOLS);
  }

  /**
   * Get supported industries
   */
  async getSupportedIndustries(): Promise<string[]> {
    return this.makeRequest<string[]>(API_CONFIG.ENDPOINTS.MCP_INDUSTRIES);
  }

  /**
   * Get industry templates
   */
  async getIndustryTemplates(): Promise<IndustryTemplate[]> {
    return this.makeRequest<IndustryTemplate[]>(API_CONFIG.ENDPOINTS.MCP_TEMPLATES);
  }

  /**
   * Generate content using MCP
   */
  async generateContent(params: {
    topic: string;
    content_type?: string;
    keywords?: string[];
    industry?: string;
    language?: string;
    tone?: string;
    length?: string;
  }): Promise<MCPToolResponse> {
    const request: MCPToolRequest = {
      tool_name: 'content_generation',
      parameters: {
        content_type: params.content_type || 'blog_post',
        topic: params.topic,
        keywords: params.keywords || [],
        tone: params.tone || 'professional',
        length: params.length || 'medium',
      },
      context: {
        industry: params.industry || 'general',
        language: params.language || 'en',
      },
      industry: params.industry || 'general',
      language: params.language || 'en',
    };

    return this.executeTool(request);
  }

  /**
   * Analyze SEO using MCP
   */
  async analyzeSEO(params: {
    url?: string;
    content?: string;
    keywords?: string[];
    industry?: string;
  }): Promise<MCPToolResponse> {
    const request: MCPToolRequest = {
      tool_name: 'seo_analysis',
      parameters: {
        url: params.url,
        content: params.content,
        keywords: params.keywords || [],
      },
      context: {
        industry: params.industry || 'general',
      },
      industry: params.industry || 'general',
    };

    return this.executeTool(request);
  }

  /**
   * Research keywords using MCP
   */
  async researchKeywords(params: {
    seed_keyword: string;
    industry?: string;
    language?: string;
  }): Promise<MCPToolResponse> {
    const request: MCPToolRequest = {
      tool_name: 'keyword_research',
      parameters: {
        seed_keyword: params.seed_keyword,
      },
      context: {
        industry: params.industry || 'general',
        language: params.language || 'en',
      },
      industry: params.industry || 'general',
      language: params.language || 'en',
    };

    return this.executeTool(request);
  }

  /**
   * Analyze industry using MCP
   */
  async analyzeIndustry(params: {
    industry: string;
    analysis_type?: string;
  }): Promise<MCPToolResponse> {
    const request: MCPToolRequest = {
      tool_name: 'industry_analysis',
      parameters: {
        industry: params.industry,
        analysis_type: params.analysis_type || 'overview',
      },
      context: {
        industry: params.industry,
      },
      industry: params.industry,
    };

    return this.executeTool(request);
  }

  /**
   * Test connection to MCP server
   */
  async testConnection(): Promise<{ success: boolean; message: string; response_time?: number }> {
    const startTime = Date.now();
    
    try {
      const status = await this.getServerStatus();
      const responseTime = Date.now() - startTime;
      
      return {
        success: true,
        message: 'Connection successful',
        response_time: responseTime,
      };
    } catch (error) {
      const responseTime = Date.now() - startTime;
      
      return {
        success: false,
        message: `Connection failed: ${error instanceof Error ? error.message : 'Unknown error'}`,
        response_time: responseTime,
      };
    }
  }

  /**
   * Get health status
   */
  async getHealth(): Promise<{ status: string; timestamp: string }> {
    return this.makeRequest<{ status: string; timestamp: string }>(API_CONFIG.ENDPOINTS.HEALTH);
  }
}

// Create singleton instance
export const mcpService = new MCPService();

// Export default
export default mcpService;
