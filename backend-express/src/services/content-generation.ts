/**
 * Content Generation Service
 * Handles all content generation tasks using multiple AI models
 * Prioritizes Google Gemini 2.5 Pro for enhanced accuracy
 */

import axios from 'axios';
import { z } from 'zod';
import NodeCache from 'node-cache';
import { geminiService, GeminiService } from './gemini-service';

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
  seo_requirements?: {
    target_keyword: string;
    keyword_density: string;
    meta_description_length: number;
    title_length: number;
    min_word_count: number;
  };
}

interface ContentResponse {
  title?: string;
  content: string;
  excerpt?: string;
  meta_description?: string;
  summary?: string;
  html?: string;
  description?: string;
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
  private gemini: GeminiService;
  private cache: NodeCache;
  private contentTemplates: Map<string, any>;
  private performanceMetrics: Map<string, any>;

  constructor(config: AIConfig) {
    this.config = config;
    // Initialize Gemini service with provided API key or from config
    this.gemini = new GeminiService({
      apiKey: config.googleApiKey || process.env.GOOGLE_API_KEY || 'AIzaSyDTITCw_UcgzUufrsCFuxp9HXri6Y0XrDo'
    });

    // Initialize cache with 1 hour TTL
    this.cache = new NodeCache({
      stdTTL: 3600, // 1 hour
      checkperiod: 600, // Check for expired keys every 10 minutes
      useClones: false
    });

    // Initialize content templates
    this.contentTemplates = new Map();
    this.performanceMetrics = new Map();

    this.initializeContentTemplates();
  }

  async initialize(): Promise<void> {
    if (this.initialized) return;

    // Log warning if no API keys are configured, but don't fail initialization
    if (!this.config.googleApiKey && !this.config.openaiApiKey && !this.config.anthropicApiKey) {
      console.warn('Warning: No AI API keys configured. Content generation will not be available.');
    }

    // Initialize performance tracking
    this.initializePerformanceTracking();

    this.initialized = true;
    console.log('Content Generation Service initialized with enhanced features');
  }

  private initializeContentTemplates(): void {
    // Blog post templates
    this.contentTemplates.set('blog_comprehensive', {
      sections: [
        'introduction',
        'problem_definition',
        'solution_overview',
        'detailed_steps',
        'best_practices',
        'common_mistakes',
        'case_studies',
        'conclusion'
      ],
      minWordCount: 1500,
      seoOptimized: true
    });

    this.contentTemplates.set('blog_howto', {
      sections: [
        'introduction',
        'prerequisites',
        'step_by_step_guide',
        'troubleshooting',
        'advanced_tips',
        'conclusion'
      ],
      minWordCount: 1000,
      seoOptimized: true
    });

    // Product description templates
    this.contentTemplates.set('product_detailed', {
      sections: [
        'overview',
        'key_features',
        'benefits',
        'specifications',
        'use_cases',
        'comparison',
        'call_to_action'
      ],
      minWordCount: 300,
      conversionOptimized: true
    });

    // Category page templates
    this.contentTemplates.set('category_comprehensive', {
      sections: [
        'category_overview',
        'subcategories',
        'buying_guide',
        'featured_products',
        'related_categories'
      ],
      minWordCount: 500,
      navigationOptimized: true
    });
  }

  private initializePerformanceTracking(): void {
    this.performanceMetrics.set('generation_times', []);
    this.performanceMetrics.set('success_rates', []);
    this.performanceMetrics.set('cache_hit_rates', []);
    this.performanceMetrics.set('model_performance', new Map());
  }

  async generateContent(request: ContentRequest): Promise<ContentResponse> {
    const startTime = Date.now();

    if (!this.initialized) {
      throw new Error('Service not initialized');
    }

    // Validate request
    this.validateContentRequest(request);

    // Check cache first
    const cacheKey = this.generateCacheKey(request);
    const cachedResult = this.cache.get<ContentResponse>(cacheKey);
    if (cachedResult) {
      this.trackCacheHit();
      return cachedResult;
    }

    // Check if any API keys are available
    if (!this.config.googleApiKey && !this.config.openaiApiKey && !this.config.anthropicApiKey) {
      throw new Error('No AI API keys configured. Please configure at least one API key to use content generation.');
    }

    try {
      // Select optimal template based on content type and requirements
      const template = this.selectOptimalTemplate(request);

      // Use Gemini 2.5 Pro for enhanced accuracy with template guidance
      const geminiResponse = await this.gemini.generateSEOContent({
        topic: request.topic,
        keywords: request.keywords || [],
        contentType: request.type,
        language: request.language,
        tone: request.tone,
        length: request.length,
        seoRequirements: request.seo_requirements,
        template: template
      });

      // Extract structured content from Gemini response
      const structuredContent = this.parseStructuredContent(geminiResponse.text, request);

      // Enhance content with AI-powered improvements
      const enhancedContent = await this.enhanceContent(structuredContent, request);

      // Analyze and optimize the generated content using Gemini
      const analysis = await this.gemini.analyzeContent(enhancedContent.content, request.keywords || []);

      const result: ContentResponse = {
        title: enhancedContent.title,
        content: enhancedContent.content,
        html: enhancedContent.content, // Alias for compatibility
        excerpt: enhancedContent.excerpt,
        summary: enhancedContent.excerpt, // Alias for compatibility
        meta_description: enhancedContent.meta_description,
        description: enhancedContent.meta_description, // Alias for compatibility
        seo_score: analysis.score,
        suggestions: analysis.suggestions,
        metadata: {
          word_count: enhancedContent.content.replace(/<[^>]*>/g, '').split(/\s+/).length,
          keyword_density: analysis.keywordAnalysis,
          readability_score: analysis.readabilityScore,
          generation_time: Date.now() - startTime,
          template_used: template?.name || 'default',
          ai_model: 'gemini-2.0-flash-exp'
        }
      };

      // Cache the result
      this.cache.set(cacheKey, result);

      // Track performance metrics
      this.trackGenerationMetrics(startTime, true, 'gemini');

      return result;

    } catch (error) {
      // Fallback to other models if Gemini fails
      console.warn('Gemini failed, falling back to other models:', error);
      const fallbackResult = await this.generateContentFallback(request);

      // Track performance metrics for fallback
      this.trackGenerationMetrics(startTime, false, 'fallback');

      return fallbackResult;
    }
  }

  private validateContentRequest(request: ContentRequest): void {
    if (!request.topic || request.topic.trim().length === 0) {
      throw new Error('Topic is required and cannot be empty');
    }

    if (request.topic.length > 500) {
      throw new Error('Topic is too long (maximum 500 characters)');
    }

    if (request.keywords && request.keywords.length > 10) {
      throw new Error('Too many keywords (maximum 10)');
    }

    const validTypes = ['blog', 'product', 'category', 'meta'];
    if (!validTypes.includes(request.type)) {
      throw new Error(`Invalid content type. Must be one of: ${validTypes.join(', ')}`);
    }
  }

  private generateCacheKey(request: ContentRequest): string {
    const keyData = {
      topic: request.topic,
      type: request.type,
      keywords: request.keywords?.sort() || [],
      language: request.language || 'en',
      tone: request.tone || 'professional',
      length: request.length || 'medium'
    };

    return `content_${Buffer.from(JSON.stringify(keyData)).toString('base64')}`;
  }

  private selectOptimalTemplate(request: ContentRequest): any {
    const templateKey = `${request.type}_${this.determineTemplateVariant(request)}`;
    return this.contentTemplates.get(templateKey) || this.contentTemplates.get(`${request.type}_comprehensive`);
  }

  private determineTemplateVariant(request: ContentRequest): string {
    if (request.type === 'blog') {
      if (request.topic.toLowerCase().includes('how to') || request.topic.toLowerCase().includes('วิธี')) {
        return 'howto';
      }
      return 'comprehensive';
    }

    if (request.type === 'product') {
      return 'detailed';
    }

    return 'comprehensive';
  }

  private async enhanceContent(content: any, request: ContentRequest): Promise<any> {
    // Add intelligent enhancements based on content type and language
    if (request.language === 'th') {
      content = await this.enhanceThaiContent(content, request);
    } else {
      content = await this.enhanceEnglishContent(content, request);
    }

    // Add SEO enhancements
    content = this.addSEOEnhancements(content, request);

    return content;
  }

  private async enhanceThaiContent(content: any, request: ContentRequest): Promise<any> {
    // Thai-specific enhancements
    content.content = this.improveThaiReadability(content.content);
    content.meta_description = this.optimizeThaiMetaDescription(content.meta_description, request);

    return content;
  }

  private async enhanceEnglishContent(content: any, request: ContentRequest): Promise<any> {
    // English-specific enhancements
    content.content = this.improveEnglishReadability(content.content);
    content.meta_description = this.optimizeEnglishMetaDescription(content.meta_description, request);

    return content;
  }

  private addSEOEnhancements(content: any, request: ContentRequest): any {
    // Add schema markup suggestions
    content.schema_suggestions = this.generateSchemaMarkup(request);

    // Add internal linking suggestions
    content.internal_linking_suggestions = this.generateInternalLinkingSuggestions(request);

    return content;
  }

  private trackGenerationMetrics(startTime: number, success: boolean, model: string): void {
    const duration = Date.now() - startTime;

    const times = this.performanceMetrics.get('generation_times') || [];
    times.push(duration);
    this.performanceMetrics.set('generation_times', times.slice(-100)); // Keep last 100

    const successRates = this.performanceMetrics.get('success_rates') || [];
    successRates.push(success);
    this.performanceMetrics.set('success_rates', successRates.slice(-100));

    const modelPerf = this.performanceMetrics.get('model_performance') || new Map();
    const modelStats = modelPerf.get(model) || { successes: 0, failures: 0, avgTime: 0 };

    if (success) {
      modelStats.successes++;
    } else {
      modelStats.failures++;
    }

    modelStats.avgTime = (modelStats.avgTime + duration) / 2;
    modelPerf.set(model, modelStats);
    this.performanceMetrics.set('model_performance', modelPerf);
  }

  private trackCacheHit(): void {
    const cacheHits = this.performanceMetrics.get('cache_hit_rates') || [];
    cacheHits.push(true);
    this.performanceMetrics.set('cache_hit_rates', cacheHits.slice(-100));
  }

  private improveThaiReadability(content: string): string {
    // Improve Thai content readability
    return content
      .replace(/([.!?])\s*([ก-๙])/g, '$1 $2') // Add proper spacing after sentences
      .replace(/([ก-๙])\s*([.!?])/g, '$1$2') // Remove extra spaces before punctuation
      .replace(/\s{2,}/g, ' ') // Remove multiple spaces
      .trim();
  }

  private improveEnglishReadability(content: string): string {
    // Improve English content readability
    return content
      .replace(/([.!?])\s*([A-Z])/g, '$1 $2') // Ensure proper spacing after sentences
      .replace(/\s{2,}/g, ' ') // Remove multiple spaces
      .replace(/([a-z])([A-Z])/g, '$1 $2') // Add space between camelCase words
      .trim();
  }

  private optimizeThaiMetaDescription(metaDesc: string, request: ContentRequest): string {
    const maxLength = 120;
    const keyword = request.keywords?.[0] || request.topic;

    if (!metaDesc.includes(keyword)) {
      metaDesc = `${keyword} - ${metaDesc}`;
    }

    if (metaDesc.length > maxLength) {
      metaDesc = metaDesc.substring(0, maxLength - 3) + '...';
    }

    return metaDesc;
  }

  private optimizeEnglishMetaDescription(metaDesc: string, request: ContentRequest): string {
    const maxLength = 160;
    const keyword = request.keywords?.[0] || request.topic;

    if (!metaDesc.toLowerCase().includes(keyword.toLowerCase())) {
      metaDesc = `${keyword} - ${metaDesc}`;
    }

    if (metaDesc.length > maxLength) {
      metaDesc = metaDesc.substring(0, maxLength - 3) + '...';
    }

    return metaDesc;
  }

  private generateSchemaMarkup(request: ContentRequest): any {
    const baseSchema = {
      "@context": "https://schema.org",
      "@type": this.getSchemaType(request.type),
      "name": request.topic,
      "description": `Comprehensive guide about ${request.topic}`,
      "keywords": request.keywords?.join(', ') || request.topic
    };

    if (request.type === 'blog') {
      return {
        ...baseSchema,
        "@type": "Article",
        "headline": request.topic,
        "author": {
          "@type": "Organization",
          "name": "SEOForge"
        },
        "publisher": {
          "@type": "Organization",
          "name": "SEOForge"
        }
      };
    }

    return baseSchema;
  }

  private getSchemaType(contentType: string): string {
    const schemaTypes = {
      'blog': 'Article',
      'product': 'Product',
      'category': 'CollectionPage',
      'meta': 'WebPage'
    };

    return schemaTypes[contentType] || 'WebPage';
  }

  private generateInternalLinkingSuggestions(request: ContentRequest): string[] {
    const suggestions = [];
    const topic = request.topic.toLowerCase();

    // Generate contextual internal linking suggestions
    if (request.type === 'blog') {
      suggestions.push(`Link to related ${topic} tutorials`);
      suggestions.push(`Reference ${topic} best practices guide`);
      suggestions.push(`Include links to ${topic} tools and resources`);
    } else if (request.type === 'product') {
      suggestions.push(`Link to product category page`);
      suggestions.push(`Reference related products`);
      suggestions.push(`Include comparison guides`);
    }

    return suggestions;
  }

  // Performance monitoring methods
  public getPerformanceMetrics(): any {
    const times = this.performanceMetrics.get('generation_times') || [];
    const successRates = this.performanceMetrics.get('success_rates') || [];
    const cacheHits = this.performanceMetrics.get('cache_hit_rates') || [];
    const modelPerf = this.performanceMetrics.get('model_performance') || new Map();

    return {
      averageGenerationTime: times.length > 0 ? times.reduce((a, b) => a + b, 0) / times.length : 0,
      successRate: successRates.length > 0 ? (successRates.filter(s => s).length / successRates.length) * 100 : 0,
      cacheHitRate: cacheHits.length > 0 ? (cacheHits.filter(h => h).length / cacheHits.length) * 100 : 0,
      modelPerformance: Object.fromEntries(modelPerf),
      totalRequests: times.length,
      cacheSize: this.cache.keys().length
    };
  }

  public clearCache(): void {
    this.cache.flushAll();
  }

  public getCacheStats(): any {
    return this.cache.getStats();
  }

  private parseStructuredContent(rawContent: string, request: ContentRequest): {
    title: string;
    content: string;
    excerpt: string;
    meta_description: string;
  } {
    // Try to extract structured content from Gemini response
    const lines = rawContent.split('\n');
    let title = '';
    let content = '';
    let excerpt = '';
    let meta_description = '';

    // Look for structured markers in the response
    let currentSection = '';
    for (const line of lines) {
      const trimmed = line.trim();
      
      if (trimmed.toLowerCase().includes('title:') || trimmed.toLowerCase().includes('# ')) {
        title = trimmed.replace(/^(title:|#)\s*/i, '').trim();
      } else if (trimmed.toLowerCase().includes('excerpt:')) {
        excerpt = trimmed.replace(/^excerpt:\s*/i, '').trim();
      } else if (trimmed.toLowerCase().includes('meta description:')) {
        meta_description = trimmed.replace(/^meta description:\s*/i, '').trim();
      } else if (trimmed.toLowerCase().includes('content:')) {
        currentSection = 'content';
        continue;
      } else if (currentSection === 'content' && trimmed) {
        content += line + '\n';
      } else if (!title && !content && trimmed) {
        // If no structured format, treat as content
        content += line + '\n';
      }
    }

    // Fallback: generate missing parts
    if (!title) {
      title = this.generateTitle(request.topic, request.keywords?.[0] || '');
    }
    
    if (!content) {
      content = rawContent; // Use the entire response as content
    }
    
    if (!excerpt) {
      excerpt = this.generateExcerpt(content, request.language);
    }
    
    if (!meta_description) {
      meta_description = this.generateMetaDescription(request.topic, request.keywords?.[0] || '', request.language);
    }

    return {
      title: title.substring(0, request.seo_requirements?.title_length || 60),
      content: this.formatContentHTML(content, request),
      excerpt: excerpt.substring(0, request.language === 'th' ? 100 : 160),
      meta_description: meta_description.substring(0, request.seo_requirements?.meta_description_length || 160)
    };
  }

  private generateTitle(topic: string, keyword: string): string {
    if (keyword && !topic.toLowerCase().includes(keyword.toLowerCase())) {
      return `${topic} - ${keyword} Guide`;
    }
    return topic;
  }

  private generateExcerpt(content: string, language?: string): string {
    const plainText = content.replace(/<[^>]*>/g, '').trim();
    const sentences = plainText.split(/[.!?]+/).filter(s => s.trim().length > 0);
    
    if (sentences.length === 0) return '';
    
    const maxLength = language === 'th' ? 100 : 160;
    let excerpt = sentences[0].trim();
    
    if (excerpt.length > maxLength) {
      excerpt = excerpt.substring(0, maxLength - 3) + '...';
    }
    
    return excerpt;
  }

  private generateMetaDescription(topic: string, keyword: string, language?: string): string {
    const maxLength = language === 'th' ? 100 : 160;
    let description = '';
    
    if (language === 'th') {
      description = `เรียนรู้เกี่ยวกับ ${topic}${keyword ? ` และ ${keyword}` : ''} ในคู่มือฉบับสมบูรณ์นี้`;
    } else {
      description = `Learn about ${topic}${keyword ? ` and ${keyword}` : ''} in this comprehensive guide.`;
    }
    
    if (description.length > maxLength) {
      description = description.substring(0, maxLength - 3) + '...';
    }
    
    return description;
  }

  private formatContentHTML(content: string, request: ContentRequest): string {
    // Ensure proper HTML structure for blog posts
    if (request.type === 'blog') {
      return this.formatBlogHTML(content, request);
    }
    
    // For other content types, ensure basic HTML formatting
    if (!content.includes('<')) {
      // Convert plain text to HTML
      return content
        .split('\n\n')
        .map(paragraph => paragraph.trim())
        .filter(p => p.length > 0)
        .map(p => `<p>${p}</p>`)
        .join('\n');
    }
    
    return content;
  }

  private formatBlogHTML(content: string, request: ContentRequest): string {
    const keyword = request.keywords?.[0] || request.topic;
    const language = request.language || 'en';
    
    // If content doesn't have proper structure, create it
    if (!content.includes('<h2>')) {
      return this.createStructuredBlogContent(content, keyword, language);
    }
    
    return content;
  }

  private createStructuredBlogContent(content: string, keyword: string, language: string): string {
    const sections = language === 'th' ? {
      intro: 'บทนำ',
      what: `${keyword} คืออะไร?`,
      benefits: `ประโยชน์ของ ${keyword}`,
      howTo: `วิธีเริ่มต้นกับ ${keyword}`,
      bestPractices: `แนวทางปฏิบัติที่ดีที่สุดสำหรับ ${keyword}`,
      mistakes: 'ข้อผิดพลาดที่ควรหลีกเลี่ยง',
      conclusion: 'บทสรุป'
    } : {
      intro: 'Introduction',
      what: `What is ${keyword}?`,
      benefits: `Benefits of ${keyword}`,
      howTo: `How to Get Started with ${keyword}`,
      bestPractices: `Best Practices for ${keyword}`,
      mistakes: 'Common Mistakes to Avoid',
      conclusion: 'Conclusion'
    };

    // Split content into paragraphs and create structured HTML
    const paragraphs = content.split('\n\n').filter(p => p.trim().length > 0);
    const sectionsPerPart = Math.ceil(paragraphs.length / 7);
    
    let html = `<h2>${sections.intro}</h2>\n<p>${paragraphs[0] || `Learn about ${keyword} in this comprehensive guide.`}</p>\n\n`;
    
    let currentIndex = 1;
    Object.values(sections).slice(1, -1).forEach(sectionTitle => {
      html += `<h2>${sectionTitle}</h2>\n`;
      for (let i = 0; i < sectionsPerPart && currentIndex < paragraphs.length; i++) {
        html += `<p>${paragraphs[currentIndex]}</p>\n`;
        currentIndex++;
      }
      html += '\n';
    });
    
    html += `<h2>${sections.conclusion}</h2>\n<p>${paragraphs[paragraphs.length - 1] || `${keyword} is an important topic that requires careful consideration and implementation.`}</p>`;
    
    return html;
  }

  private async generateContentFallback(request: ContentRequest): Promise<ContentResponse> {
    try {
      // Choose the best available fallback model
      const model = this.selectFallbackModel(request);
      
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
          content = await this.generateMetaDescriptionContent(request, model);
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

  private selectFallbackModel(request: ContentRequest): string {
    // Fallback model selection when Gemini is not available
    if (request.type === 'blog' && this.config.anthropicApiKey) {
      return 'claude'; // Good for long-form content
    }
    
    if (this.config.openaiApiKey) {
      return 'gpt4'; // Good all-around model
    }
    
    throw new Error('No fallback AI model available');
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

  private async generateMetaDescriptionContent(request: ContentRequest, model: string): Promise<string> {
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

    try {
      // Use Gemini 2.5 Pro for better accuracy and performance
      const response = await axios.post(
        `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=${this.config.googleApiKey}`,
        {
          contents: [{
            parts: [{ text: prompt }]
          }],
          generationConfig: {
            temperature: 0.7,
            topK: 40,
            topP: 0.95,
            maxOutputTokens: 8192,
            candidateCount: 1
          },
          safetySettings: [
            {
              category: "HARM_CATEGORY_HARASSMENT",
              threshold: "BLOCK_MEDIUM_AND_ABOVE"
            },
            {
              category: "HARM_CATEGORY_HATE_SPEECH", 
              threshold: "BLOCK_MEDIUM_AND_ABOVE"
            },
            {
              category: "HARM_CATEGORY_SEXUALLY_EXPLICIT",
              threshold: "BLOCK_MEDIUM_AND_ABOVE"
            },
            {
              category: "HARM_CATEGORY_DANGEROUS_CONTENT",
              threshold: "BLOCK_MEDIUM_AND_ABOVE"
            }
          ]
        },
        {
          headers: {
            'Content-Type': 'application/json'
          },
          timeout: 30000 // 30 second timeout
        }
      );

      if (!response.data.candidates || response.data.candidates.length === 0) {
        throw new Error('No content generated by Gemini 2.5 Pro');
      }

      const candidate = response.data.candidates[0];
      
      // Check for safety blocks
      if (candidate.finishReason === 'SAFETY') {
        throw new Error('Content blocked by safety filters');
      }

      if (!candidate.content || !candidate.content.parts || candidate.content.parts.length === 0) {
        throw new Error('Invalid response format from Gemini 2.5 Pro');
      }

      return candidate.content.parts[0].text;
    } catch (error) {
      if (axios.isAxiosError(error)) {
        const errorMessage = error.response?.data?.error?.message || error.message;
        throw new Error(`Gemini 2.5 Pro API error: ${errorMessage}`);
      }
      throw error;
    }
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
