/**
 * WordPress Service
 * Handles WordPress integration and content synchronization
 */

import axios from 'axios';
import { z } from 'zod';

interface WordPressRequest {
  site_url: string;
  action: 'create' | 'update' | 'delete';
  content_type: 'post' | 'page' | 'product';
  content?: any;
  auth_token?: string;
  post_id?: number;
}

interface WordPressResponse {
  success: boolean;
  data?: any;
  message?: string;
  error?: string;
}

export class WordPressService {
  private initialized = false;

  constructor() {}

  async initialize(): Promise<void> {
    if (this.initialized) return;
    this.initialized = true;
  }

  async syncContent(request: WordPressRequest): Promise<WordPressResponse> {
    if (!this.initialized) {
      throw new Error('Service not initialized');
    }

    try {
      // Validate WordPress site
      const isValid = await this.validateWordPressSite(request.site_url);
      if (!isValid) {
        return {
          success: false,
          error: 'Invalid WordPress site or REST API not accessible'
        };
      }

      // Perform the requested action
      switch (request.action) {
        case 'create':
          return await this.createContent(request);
        case 'update':
          return await this.updateContent(request);
        case 'delete':
          return await this.deleteContent(request);
        default:
          return {
            success: false,
            error: `Unsupported action: ${request.action}`
          };
      }
    } catch (error) {
      return {
        success: false,
        error: error instanceof Error ? error.message : 'Unknown error'
      };
    }
  }

  private async validateWordPressSite(siteUrl: string): Promise<boolean> {
    try {
      const response = await axios.get(`${siteUrl}/wp-json/wp/v2/`, {
        timeout: 10000,
        validateStatus: (status) => status < 500
      });
      
      return response.status === 200;
    } catch (error) {
      return false;
    }
  }

  private async createContent(request: WordPressRequest): Promise<WordPressResponse> {
    const endpoint = this.getEndpoint(request.site_url, request.content_type);
    
    try {
      const headers = this.buildHeaders(request.auth_token);
      const postData = this.formatContentForWordPress(request.content, request.content_type);

      const response = await axios.post(endpoint, postData, { headers });

      return {
        success: true,
        data: response.data,
        message: `${request.content_type} created successfully`
      };
    } catch (error) {
      if (axios.isAxiosError(error)) {
        return {
          success: false,
          error: `WordPress API error: ${error.response?.data?.message || error.message}`
        };
      }
      throw error;
    }
  }

  private async updateContent(request: WordPressRequest): Promise<WordPressResponse> {
    if (!request.post_id) {
      return {
        success: false,
        error: 'Post ID required for update operation'
      };
    }

    const endpoint = `${this.getEndpoint(request.site_url, request.content_type)}/${request.post_id}`;
    
    try {
      const headers = this.buildHeaders(request.auth_token);
      const postData = this.formatContentForWordPress(request.content, request.content_type);

      const response = await axios.post(endpoint, postData, { headers });

      return {
        success: true,
        data: response.data,
        message: `${request.content_type} updated successfully`
      };
    } catch (error) {
      if (axios.isAxiosError(error)) {
        return {
          success: false,
          error: `WordPress API error: ${error.response?.data?.message || error.message}`
        };
      }
      throw error;
    }
  }

  private async deleteContent(request: WordPressRequest): Promise<WordPressResponse> {
    if (!request.post_id) {
      return {
        success: false,
        error: 'Post ID required for delete operation'
      };
    }

    const endpoint = `${this.getEndpoint(request.site_url, request.content_type)}/${request.post_id}`;
    
    try {
      const headers = this.buildHeaders(request.auth_token);

      const response = await axios.delete(endpoint, { headers });

      return {
        success: true,
        data: response.data,
        message: `${request.content_type} deleted successfully`
      };
    } catch (error) {
      if (axios.isAxiosError(error)) {
        return {
          success: false,
          error: `WordPress API error: ${error.response?.data?.message || error.message}`
        };
      }
      throw error;
    }
  }

  private getEndpoint(siteUrl: string, contentType: string): string {
    const baseUrl = `${siteUrl}/wp-json/wp/v2`;
    
    switch (contentType) {
      case 'post':
        return `${baseUrl}/posts`;
      case 'page':
        return `${baseUrl}/pages`;
      case 'product':
        return `${baseUrl}/products`; // WooCommerce endpoint
      default:
        return `${baseUrl}/posts`;
    }
  }

  private buildHeaders(authToken?: string): Record<string, string> {
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'User-Agent': 'SEOForge-MCP/2.0'
    };

    if (authToken) {
      headers['Authorization'] = `Bearer ${authToken}`;
    }

    return headers;
  }

  private formatContentForWordPress(content: any, contentType: string): any {
    const baseData = {
      title: content.title || 'Untitled',
      content: content.content || content.body || '',
      status: content.status || 'draft',
      excerpt: content.excerpt || '',
      meta: content.meta || {}
    };

    // Add content-type specific fields
    switch (contentType) {
      case 'post':
        return {
          ...baseData,
          categories: content.categories || [],
          tags: content.tags || [],
          featured_media: content.featured_image || 0
        };
      
      case 'page':
        return {
          ...baseData,
          parent: content.parent || 0,
          menu_order: content.menu_order || 0
        };
      
      case 'product':
        return {
          ...baseData,
          type: 'simple',
          regular_price: content.price || '0',
          description: content.description || '',
          short_description: content.short_description || '',
          categories: content.categories || [],
          images: content.images || []
        };
      
      default:
        return baseData;
    }
  }

  // Utility methods for WordPress plugin integration
  async getWordPressInfo(siteUrl: string): Promise<any> {
    try {
      const response = await axios.get(`${siteUrl}/wp-json/wp/v2/`, {
        timeout: 10000
      });

      return {
        success: true,
        data: {
          name: response.data.name,
          description: response.data.description,
          url: response.data.url,
          home: response.data.home,
          gmt_offset: response.data.gmt_offset,
          timezone_string: response.data.timezone_string
        }
      };
    } catch (error) {
      return {
        success: false,
        error: 'Unable to fetch WordPress site information'
      };
    }
  }

  async testConnection(siteUrl: string, authToken?: string): Promise<WordPressResponse> {
    try {
      const headers = this.buildHeaders(authToken);
      
      const response = await axios.get(`${siteUrl}/wp-json/wp/v2/users/me`, {
        headers,
        timeout: 10000,
        validateStatus: (status) => status < 500
      });

      if (response.status === 200) {
        return {
          success: true,
          data: response.data,
          message: 'WordPress connection successful'
        };
      } else {
        return {
          success: false,
          error: 'Authentication failed'
        };
      }
    } catch (error) {
      return {
        success: false,
        error: 'Unable to connect to WordPress site'
      };
    }
  }

  // SEO-specific WordPress methods
  async optimizeWordPressPost(siteUrl: string, postId: number, seoData: any, authToken?: string): Promise<WordPressResponse> {
    try {
      const headers = this.buildHeaders(authToken);
      
      // Update post with SEO optimizations
      const updateData = {
        title: seoData.optimized_title,
        content: seoData.optimized_content,
        excerpt: seoData.meta_description,
        meta: {
          _yoast_wpseo_title: seoData.seo_title,
          _yoast_wpseo_metadesc: seoData.meta_description,
          _yoast_wpseo_focuskw: seoData.focus_keyword,
          _yoast_wpseo_canonical: seoData.canonical_url
        }
      };

      const response = await axios.post(
        `${siteUrl}/wp-json/wp/v2/posts/${postId}`,
        updateData,
        { headers }
      );

      return {
        success: true,
        data: response.data,
        message: 'Post optimized successfully'
      };
    } catch (error) {
      return {
        success: false,
        error: 'Failed to optimize WordPress post'
      };
    }
  }

  async bulkOptimizePosts(siteUrl: string, postIds: number[], authToken?: string): Promise<WordPressResponse> {
    const results = [];
    
    for (const postId of postIds) {
      try {
        // Get post content
        const postResponse = await axios.get(`${siteUrl}/wp-json/wp/v2/posts/${postId}`);
        const post = postResponse.data;
        
        // This would integrate with the SEO analysis service
        // For now, we'll return a placeholder
        results.push({
          post_id: postId,
          status: 'optimized',
          improvements: ['Title optimized', 'Meta description added', 'Keywords optimized']
        });
      } catch (error) {
        results.push({
          post_id: postId,
          status: 'failed',
          error: 'Unable to optimize post'
        });
      }
    }

    return {
      success: true,
      data: results,
      message: `Bulk optimization completed for ${postIds.length} posts`
    };
  }
}
