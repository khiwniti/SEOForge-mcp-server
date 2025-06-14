/**
 * Image Generation Service
 * Handles AI-powered image generation using multiple models
 */

import axios from 'axios';
import { z } from 'zod';

interface AIConfig {
  replicateToken?: string;
  openaiApiKey?: string;
  togetherToken?: string;
}

interface ImageRequest {
  prompt: string;
  style?: string;
  size?: '512x512' | '1024x1024' | '1024x768';
  model?: 'flux' | 'dalle' | 'midjourney';
  negative_prompt?: string;
  steps?: number;
  guidance_scale?: number;
}

interface ImageResponse {
  success: boolean;
  image_url?: string;
  image_data?: string; // Base64 encoded
  metadata: {
    model_used: string;
    generation_time: number;
    prompt_used: string;
    size: string;
  };
  error?: string;
}

export class ImageGenerationService {
  private config: AIConfig;
  private initialized = false;

  constructor(config: AIConfig) {
    this.config = config;
  }

  async initialize(): Promise<void> {
    if (this.initialized) return;
    
    // Validate at least one image generation API is available
    if (!this.config.replicateToken && !this.config.openaiApiKey && !this.config.togetherToken) {
      console.warn('No image generation API keys configured');
    }
    
    this.initialized = true;
  }

  async generateImage(request: ImageRequest): Promise<ImageResponse> {
    if (!this.initialized) {
      throw new Error('Service not initialized');
    }

    const startTime = Date.now();
    
    try {
      // Select the best model for the request
      const model = this.selectOptimalModel(request);
      
      // Enhance the prompt for better results
      const enhancedPrompt = this.enhancePrompt(request.prompt, request.style);
      
      let result: { image_url?: string; image_data?: string };
      
      switch (model) {
        case 'flux':
          result = await this.generateWithFlux(enhancedPrompt, request);
          break;
        case 'dalle':
          result = await this.generateWithDALLE(enhancedPrompt, request);
          break;
        case 'midjourney':
          result = await this.generateWithMidjourney(enhancedPrompt, request);
          break;
        default:
          throw new Error(`Unsupported model: ${model}`);
      }

      const generationTime = Date.now() - startTime;

      return {
        success: true,
        image_url: result.image_url,
        image_data: result.image_data,
        metadata: {
          model_used: model,
          generation_time: generationTime,
          prompt_used: enhancedPrompt,
          size: request.size || '1024x1024'
        }
      };
    } catch (error) {
      return {
        success: false,
        error: error instanceof Error ? error.message : 'Unknown error',
        metadata: {
          model_used: 'none',
          generation_time: Date.now() - startTime,
          prompt_used: request.prompt,
          size: request.size || '1024x1024'
        }
      };
    }
  }

  private selectOptimalModel(request: ImageRequest): string {
    // If model is specified and available, use it
    if (request.model) {
      switch (request.model) {
        case 'flux':
          if (this.config.replicateToken) return 'flux';
          break;
        case 'dalle':
          if (this.config.openaiApiKey) return 'dalle';
          break;
        case 'midjourney':
          if (this.config.replicateToken) return 'midjourney';
          break;
      }
    }

    // Auto-select based on available APIs and prompt characteristics
    if (this.config.replicateToken) {
      // Flux is generally good for realistic images
      if (request.style === 'realistic' || !request.style) {
        return 'flux';
      }
      // Midjourney for artistic styles
      if (request.style === 'artistic' || request.style === 'creative') {
        return 'midjourney';
      }
    }

    if (this.config.openaiApiKey) {
      return 'dalle';
    }

    throw new Error('No image generation models available');
  }

  private enhancePrompt(prompt: string, style?: string): string {
    let enhancedPrompt = prompt;

    // Add style-specific enhancements
    switch (style) {
      case 'realistic':
        enhancedPrompt += ', photorealistic, high quality, detailed, professional photography';
        break;
      case 'artistic':
        enhancedPrompt += ', artistic, creative, beautiful composition, masterpiece';
        break;
      case 'minimalist':
        enhancedPrompt += ', minimalist, clean, simple, elegant design';
        break;
      case 'vintage':
        enhancedPrompt += ', vintage style, retro, classic, nostalgic';
        break;
      case 'modern':
        enhancedPrompt += ', modern, contemporary, sleek, professional';
        break;
      default:
        enhancedPrompt += ', high quality, detailed, professional';
    }

    return enhancedPrompt;
  }

  private async generateWithFlux(prompt: string, request: ImageRequest): Promise<{ image_url?: string; image_data?: string }> {
    if (!this.config.replicateToken) {
      throw new Error('Replicate token not configured');
    }

    try {
      // Use Flux model via Replicate
      const response = await axios.post(
        'https://api.replicate.com/v1/predictions',
        {
          version: 'black-forest-labs/flux-schnell', // Fast Flux model
          input: {
            prompt: prompt,
            num_outputs: 1,
            aspect_ratio: this.convertSizeToAspectRatio(request.size || '1024x1024'),
            output_format: 'webp',
            output_quality: 90,
            num_inference_steps: request.steps || 4,
            guidance_scale: request.guidance_scale || 3.5
          }
        },
        {
          headers: {
            'Authorization': `Token ${this.config.replicateToken}`,
            'Content-Type': 'application/json'
          }
        }
      );

      const predictionId = response.data.id;
      
      // Poll for completion
      let result = await this.pollReplicateResult(predictionId);
      
      if (result.status === 'succeeded' && result.output && result.output.length > 0) {
        return { image_url: result.output[0] };
      } else {
        throw new Error('Flux generation failed or returned no output');
      }
    } catch (error) {
      throw new Error(`Flux generation failed: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  }

  private async generateWithDALLE(prompt: string, request: ImageRequest): Promise<{ image_url?: string; image_data?: string }> {
    if (!this.config.openaiApiKey) {
      throw new Error('OpenAI API key not configured');
    }

    try {
      const response = await axios.post(
        'https://api.openai.com/v1/images/generations',
        {
          model: 'dall-e-3',
          prompt: prompt,
          n: 1,
          size: request.size || '1024x1024',
          quality: 'standard',
          response_format: 'url'
        },
        {
          headers: {
            'Authorization': `Bearer ${this.config.openaiApiKey}`,
            'Content-Type': 'application/json'
          }
        }
      );

      if (response.data.data && response.data.data.length > 0) {
        return { image_url: response.data.data[0].url };
      } else {
        throw new Error('DALL-E returned no images');
      }
    } catch (error) {
      throw new Error(`DALL-E generation failed: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  }

  private async generateWithMidjourney(prompt: string, request: ImageRequest): Promise<{ image_url?: string; image_data?: string }> {
    if (!this.config.replicateToken) {
      throw new Error('Replicate token not configured');
    }

    try {
      // Use Midjourney-style model via Replicate
      const response = await axios.post(
        'https://api.replicate.com/v1/predictions',
        {
          version: 'prompthero/openjourney', // Midjourney-style model
          input: {
            prompt: prompt,
            width: parseInt(request.size?.split('x')[0] || '1024'),
            height: parseInt(request.size?.split('x')[1] || '1024'),
            num_inference_steps: request.steps || 50,
            guidance_scale: request.guidance_scale || 7,
            num_outputs: 1
          }
        },
        {
          headers: {
            'Authorization': `Token ${this.config.replicateToken}`,
            'Content-Type': 'application/json'
          }
        }
      );

      const predictionId = response.data.id;
      
      // Poll for completion
      let result = await this.pollReplicateResult(predictionId);
      
      if (result.status === 'succeeded' && result.output && result.output.length > 0) {
        return { image_url: result.output[0] };
      } else {
        throw new Error('Midjourney-style generation failed or returned no output');
      }
    } catch (error) {
      throw new Error(`Midjourney generation failed: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  }

  private convertSizeToAspectRatio(size: string): string {
    switch (size) {
      case '512x512':
        return '1:1';
      case '1024x1024':
        return '1:1';
      case '1024x768':
        return '4:3';
      default:
        return '1:1';
    }
  }

  private async pollReplicateResult(predictionId: string, maxAttempts: number = 30): Promise<any> {
    for (let attempt = 0; attempt < maxAttempts; attempt++) {
      try {
        const response = await axios.get(
          `https://api.replicate.com/v1/predictions/${predictionId}`,
          {
            headers: {
              'Authorization': `Token ${this.config.replicateToken}`,
              'Content-Type': 'application/json'
            }
          }
        );

        const result = response.data;

        if (result.status === 'succeeded' || result.status === 'failed') {
          return result;
        }

        // Wait before next poll
        await new Promise(resolve => setTimeout(resolve, 2000));
      } catch (error) {
        if (attempt === maxAttempts - 1) {
          throw error;
        }
        await new Promise(resolve => setTimeout(resolve, 2000));
      }
    }

    throw new Error('Image generation timed out');
  }

  // Utility method to generate cannabis-specific images
  async generateCannabisImage(productType: string, style: string = 'realistic'): Promise<ImageResponse> {
    const cannabisPrompts = {
      'glass_bong': 'beautiful glass water pipe, clear borosilicate glass, artistic design, professional product photography',
      'rolling_papers': 'premium rolling papers, clean white papers, elegant packaging, minimalist design',
      'grinder': 'high-quality herb grinder, metallic finish, precision engineering, professional product shot',
      'vaporizer': 'modern vaporizer device, sleek design, premium materials, technology focused',
      'accessories': 'cannabis accessories collection, organized display, premium quality items'
    };

    const prompt = cannabisPrompts[productType as keyof typeof cannabisPrompts] || 
                  `${productType} cannabis product, high quality, professional photography`;

    return await this.generateImage({
      prompt,
      style,
      size: '1024x1024',
      model: 'flux'
    });
  }
}
