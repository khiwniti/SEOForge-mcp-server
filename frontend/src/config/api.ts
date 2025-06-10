/**
 * API Configuration for Universal MCP Platform
 * Handles different environments and API endpoints
 */

// Environment detection
const isDevelopment = process.env.NODE_ENV === 'development';
const isProduction = process.env.NODE_ENV === 'production';
const isVercel = process.env.VERCEL === '1';

// API Base URLs for different environments
export const API_CONFIG = {
  // Base API URL
  BASE_URL: isDevelopment 
    ? 'http://localhost:8000'
    : (process.env.REACT_APP_API_URL || window.location.origin),
  
  // MCP Server URL
  MCP_SERVER_URL: isDevelopment
    ? 'http://localhost:3000'
    : (process.env.REACT_APP_MCP_SERVER_URL || `${window.location.origin}/api`),
  
  // API Endpoints
  ENDPOINTS: {
    // Health and status
    HEALTH: '/health',
    STATUS: '/api/status',
    
    // MCP Server endpoints
    MCP_STATUS: '/api/mcp-server/status',
    MCP_EXECUTE_TOOL: '/api/mcp-server/execute-tool',
    MCP_TOOLS: '/api/mcp-server/tools',
    MCP_INDUSTRIES: '/api/mcp-server/industries',
    MCP_TEMPLATES: '/api/mcp-server/templates',
    
    // Blog generator
    BLOG_GENERATE: '/api/blog-generator/generate',
    
    // SEO analyzer
    SEO_ANALYZE: '/api/seo-analyzer/analyze',
    
    // WordPress manager
    WORDPRESS_POSTS: '/api/wordpress-manager/posts',
    WORDPRESS_PAGES: '/api/wordpress-manager/pages',
  },
  
  // Request configuration
  TIMEOUT: 30000, // 30 seconds
  RETRY_ATTEMPTS: 3,
  RETRY_DELAY: 1000, // 1 second
};

// API Client configuration
export const createApiClient = () => {
  const baseURL = API_CONFIG.BASE_URL;
  
  return {
    baseURL,
    timeout: API_CONFIG.TIMEOUT,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
  };
};

// MCP Client configuration
export const createMCPClient = () => {
  const baseURL = API_CONFIG.MCP_SERVER_URL;
  
  return {
    baseURL,
    timeout: API_CONFIG.TIMEOUT,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
  };
};

// Utility functions for API calls
export const buildApiUrl = (endpoint: string): string => {
  const baseUrl = API_CONFIG.BASE_URL.replace(/\/$/, '');
  const cleanEndpoint = endpoint.startsWith('/') ? endpoint : `/${endpoint}`;
  return `${baseUrl}${cleanEndpoint}`;
};

export const buildMCPUrl = (endpoint: string): string => {
  const baseUrl = API_CONFIG.MCP_SERVER_URL.replace(/\/$/, '');
  const cleanEndpoint = endpoint.startsWith('/') ? endpoint : `/${endpoint}`;
  return `${baseUrl}${cleanEndpoint}`;
};

// Error handling utilities
export const handleApiError = (error: any) => {
  console.error('API Error:', error);
  
  if (error.response) {
    // Server responded with error status
    return {
      message: error.response.data?.message || 'Server error occurred',
      status: error.response.status,
      data: error.response.data,
    };
  } else if (error.request) {
    // Request was made but no response received
    return {
      message: 'Network error - please check your connection',
      status: 0,
      data: null,
    };
  } else {
    // Something else happened
    return {
      message: error.message || 'An unexpected error occurred',
      status: -1,
      data: null,
    };
  }
};

// Retry logic for failed requests
export const retryRequest = async (
  requestFn: () => Promise<any>,
  maxAttempts: number = API_CONFIG.RETRY_ATTEMPTS,
  delay: number = API_CONFIG.RETRY_DELAY
): Promise<any> => {
  let lastError: any;
  
  for (let attempt = 1; attempt <= maxAttempts; attempt++) {
    try {
      return await requestFn();
    } catch (error) {
      lastError = error;
      
      if (attempt === maxAttempts) {
        throw lastError;
      }
      
      // Wait before retrying
      await new Promise(resolve => setTimeout(resolve, delay * attempt));
    }
  }
  
  throw lastError;
};

// Environment-specific logging
export const apiLogger = {
  log: (message: string, data?: any) => {
    if (isDevelopment) {
      console.log(`[API] ${message}`, data);
    }
  },
  
  error: (message: string, error?: any) => {
    if (isDevelopment) {
      console.error(`[API Error] ${message}`, error);
    }
  },
  
  warn: (message: string, data?: any) => {
    if (isDevelopment) {
      console.warn(`[API Warning] ${message}`, data);
    }
  },
};

// Export environment info for debugging
export const ENV_INFO = {
  NODE_ENV: process.env.NODE_ENV,
  VERCEL: process.env.VERCEL,
  API_URL: process.env.REACT_APP_API_URL,
  MCP_SERVER_URL: process.env.REACT_APP_MCP_SERVER_URL,
  isDevelopment,
  isProduction,
  isVercel,
};

// Default export
export default API_CONFIG;
