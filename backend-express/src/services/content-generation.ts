/**
 * Content Generation Service
 * Handles all content generation tasks using multiple AI models
 */

import axios from 'axios';
import { z } from 'zod';

interface AIConfig {
  googleApiKey?: string;
  openaiApiKey?: string;
  anthropicApiKey?: string;
  replicateToken?: string;
  togetherToken?: string;
}

interface ContentRequest {
  type: 'blog' | 'product' | 'category' | 'meta';
  topic: string;
  keywords?: string[];
  language?: string;
  tone?: string;
  length?: 'short' | 'medium' | 'long';
  context?: any;
}

interface ContentResponse {
  content: string;
  seo_score: number;
  suggestions: string[];
  metadata: {
    word_count: number;
    keyword_density: Record<string, number>;
    readability_score: number;
  };
}

export class ContentGenerationService {
  private config: AIConfig;
  private initialized = false;

  constructor(config: AIConfig) {
    this.config = config;
  }

  async initialize(): Promise<void> {
    if (this.initialized) return;
    
    // Log warning if no API keys are configured, but don't fail initialization
    if (!this.config.googleApiKey && !this.config.openaiApiKey && !this.config.anthropicApiKey) {
      console.warn('Warning: No AI API keys configured. Content generation will not be available.');
    }
    
    this.initialized = true;
  }

  async generateContent(request: ContentRequest): Promise<ContentResponse> {
    if (!this.initialized) {
      throw new Error('Service not initialized');
    }

    // Check if any API keys are available
    if (!this.config.googleApiKey && !this.config.openaiApiKey && !this.config.anthropicApiKey) {
      throw new Error('No AI API keys configured. Please configure at least one API key to use content generation.');
    }

    try {
      // Choose the best AI model for the task
      const model = this.selectOptimalModel(request);
      
      // Generate content based on type
      let content: string;
      
      switch (request.type) {
        case 'blog':
          content = await this.generateBlogPost(request, model);
          break;
        case 'product':
          content = await this.generateProductDescription(request, model);
          break;
        case 'category':
          content = await this.generateCategoryDescription(request, model);
          break;
        case 'meta':
          content = await this.generateMetaDescription(request, model);
          break;
        default:
          throw new Error(`Unsupported content type: ${request.type}`);
      }

      // Analyze and optimize the generated content
      const analysis = await this.analyzeContent(content, request.keywords || []);
      
      return {
        content,
        seo_score: analysis.seo_score,
        suggestions: analysis.suggestions,
        metadata: analysis.metadata
      };
    } catch (error) {
      throw new Error(`Content generation failed: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  }

  private selectOptimalModel(request: ContentRequest): string {
    // Select model based on content type and language
    if (request.language === 'th' && this.config.googleApiKey) {
      return 'gemini'; // Best for Thai language
    }
    
    if (request.type === 'blog' && this.config.anthropicApiKey) {
      return 'claude'; // Best for long-form content
    }
    
    if (this.config.openaiApiKey) {
      return 'gpt4'; // Good all-around model
    }
    
    if (this.config.googleApiKey) {
      return 'gemini'; // Fallback
    }
    
    throw new Error('No suitable AI model available');
  }

  private async generateBlogPost(request: ContentRequest, model: string): Promise<string> {
    const prompt = this.buildBlogPrompt(request);
    return await this.callAIModel(model, prompt);
  }

  private async generateProductDescription(request: ContentRequest, model: string): Promise<string> {
    const prompt = this.buildProductPrompt(request);
    return await this.callAIModel(model, prompt);
  }

  private async generateCategoryDescription(request: ContentRequest, model: string): Promise<string> {
    const prompt = this.buildCategoryPrompt(request);
    return await this.callAIModel(model, prompt);
  }

  private async generateMetaDescription(request: ContentRequest, model: string): Promise<string> {
    const prompt = this.buildMetaPrompt(request);
    return await this.callAIModel(model, prompt);
  }

  private buildBlogPrompt(request: ContentRequest): string {
    const keywordText = request.keywords?.join(', ') || '';
    const lengthGuide = {
      short: '500-800 words',
      medium: '1000-1500 words',
      long: '2000-3000 words'
    };

    return `Write a comprehensive blog post about "${request.topic}".

Requirements:
- Target keywords: ${keywordText}
- Language: ${request.language || 'English'}
- Tone: ${request.tone || 'professional'}
- Length: ${lengthGuide[request.length || 'medium']}
- Include proper headings (H1, H2, H3)
- Optimize for SEO
- Make it engaging and informative
- Include a compelling introduction and conclusion

Focus on providing valuable, actionable information while naturally incorporating the target keywords.`;
  }

  private buildProductPrompt(request: ContentRequest): string {
    const keywordText = request.keywords?.join(', ') || '';
    
    return `Create a compelling product description for "${request.topic}".

Requirements:
- Target keywords: ${keywordText}
- Language: ${request.language || 'English'}
- Tone: ${request.tone || 'persuasive'}
- Highlight key features and benefits
- Include technical specifications if relevant
- Address customer pain points
- Include a clear call-to-action
- Optimize for search engines
- Keep it concise but informative (200-400 words)

Make it compelling and conversion-focused while being SEO-friendly.`;
  }

  private buildCategoryPrompt(request: ContentRequest): string {
    const keywordText = request.keywords?.join(', ') || '';
    
    return `Write a category description for "${request.topic}".

Requirements:
- Target keywords: ${keywordText}
- Language: ${request.language || 'English'}
- Tone: ${request.tone || 'informative'}
- Explain what the category contains
- Highlight key benefits and features
- Help users understand what they'll find
- Include relevant keywords naturally
- Keep it concise (150-300 words)
- Make it helpful for both users and search engines

Focus on clarity and usefulness while optimizing for SEO.`;
  }

  private buildMetaPrompt(request: ContentRequest): string {
    const keywordText = request.keywords?.join(', ') || '';
    
    return `Create an SEO-optimized meta description for "${request.topic}".

Requirements:
- Target keywords: ${keywordText}
- Language: ${request.language || 'English'}
- Length: 150-160 characters
- Include primary keyword
- Make it compelling and click-worthy
- Accurately describe the content
- Include a call-to-action if appropriate

Create a meta description that will improve click-through rates from search results.`;
  }

  private async callAIModel(model: string, prompt: string): Promise<string> {
    switch (model) {
      case 'gemini':
        return await this.callGemini(prompt);
      case 'gpt4':
        return await this.callOpenAI(prompt);
      case 'claude':
        return await this.callClaude(prompt);
      default:
        throw new Error(`Unsupported model: ${model}`);
    }
  }

  private async callGemini(prompt: string): Promise<string> {
    if (!this.config.googleApiKey) {
      throw new Error('Google API key not configured');
    }

    const response = await axios.post(
      `https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=${this.config.googleApiKey}`,
      {
        contents: [{
          parts: [{ text: prompt }]
        }]
      }
    );

    return response.data.candidates[0].content.parts[0].text;
  }

  private async callOpenAI(prompt: string): Promise<string> {
    if (!this.config.openaiApiKey) {
      throw new Error('OpenAI API key not configured');
    }

    const response = await axios.post(
      'https://api.openai.com/v1/chat/completions',
      {
        model: 'gpt-4',
        messages: [{ role: 'user', content: prompt }],
        max_tokens: 2000
      },
      {
        headers: {
          'Authorization': `Bearer ${this.config.openaiApiKey}`,
          'Content-Type': 'application/json'
        }
      }
    );

    return response.data.choices[0].message.content;
  }

  private async callClaude(prompt: string): Promise<string> {
    if (!this.config.anthropicApiKey) {
      throw new Error('Anthropic API key not configured');
    }

    const response = await axios.post(
      'https://api.anthropic.com/v1/messages',
      {
        model: 'claude-3-sonnet-20240229',
        max_tokens: 2000,
        messages: [{ role: 'user', content: prompt }]
      },
      {
        headers: {
          'x-api-key': this.config.anthropicApiKey,
          'Content-Type': 'application/json',
          'anthropic-version': '2023-06-01'
        }
      }
    );

    return response.data.content[0].text;
  }

  private async analyzeContent(content: string, keywords: string[]): Promise<{
    seo_score: number;
    suggestions: string[];
    metadata: {
      word_count: number;
      keyword_density: Record<string, number>;
      readability_score: number;
    };
  }> {
    const words = content.split(/\s+/).length;
    const suggestions: string[] = [];
    let seo_score = 70; // Base score

    // Calculate keyword density
    const keyword_density: Record<string, number> = {};
    keywords.forEach(keyword => {
      const regex = new RegExp(keyword, 'gi');
      const matches = content.match(regex) || [];
      const density = (matches.length / words) * 100;
      keyword_density[keyword] = density;

      if (density < 0.5) {
        suggestions.push(`Increase usage of keyword "${keyword}" (current: ${density.toFixed(2)}%)`);
        seo_score -= 5;
      } else if (density > 3) {
        suggestions.push(`Reduce usage of keyword "${keyword}" to avoid over-optimization (current: ${density.toFixed(2)}%)`);
        seo_score -= 3;
      } else {
        seo_score += 5;
      }
    });

    // Word count analysis
    if (words < 300) {
      suggestions.push('Content is too short. Consider adding more valuable information.');
      seo_score -= 10;
    } else if (words > 3000) {
      suggestions.push('Content is very long. Consider breaking it into multiple pieces.');
      seo_score -= 5;
    }

    // Simple readability score (Flesch-like)
    const sentences = content.split(/[.!?]+/).length;
    const avgWordsPerSentence = words / sentences;
    let readability_score = 100 - (avgWordsPerSentence * 1.5);
    readability_score = Math.max(0, Math.min(100, readability_score));

    if (readability_score < 60) {
      suggestions.push('Consider using shorter sentences to improve readability.');
      seo_score -= 5;
    }

    return {
      seo_score: Math.max(0, Math.min(100, seo_score)),
      suggestions,
      metadata: {
        word_count: words,
        keyword_density,
        readability_score
      }
    };
  }
}
