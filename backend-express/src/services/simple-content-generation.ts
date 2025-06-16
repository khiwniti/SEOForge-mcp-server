/**
 * Simplified Content Generation Service
 * Minimal dependencies for reliable Vercel deployment
 */

import axios from 'axios';
import NodeCache from 'node-cache';

interface ContentRequest {
  keyword: string;
  language: string;
  type?: string;
  length?: string;
  style?: string;
  additional_keywords?: string[];
  target_audience?: string;
  include_faq?: boolean;
  include_images?: boolean;
}

interface ContentResponse {
  title: string;
  content: string;
  html: string;
  excerpt: string;
  summary: string;
  meta_description: string;
  description: string;
  seo_score: number;
  suggestions: string[];
  metadata: {
    word_count: number;
    keyword_density: Record<string, number>;
    readability_score: number;
    generation_time: number;
    template_used: string;
    ai_model: string;
  };
}

export class SimpleContentGenerationService {
  private cache: NodeCache;
  private apiKey: string;

  constructor() {
    this.apiKey = process.env.GOOGLE_API_KEY || 'AIzaSyDTITCw_UcgzUufrsCFuxp9HXri6Y0XrDo';
    this.cache = new NodeCache({ 
      stdTTL: 3600, // 1 hour
      checkperiod: 600 // Check every 10 minutes
    });
  }

  async generateContent(request: ContentRequest): Promise<ContentResponse> {
    const startTime = Date.now();
    
    // Check cache first
    const cacheKey = this.generateCacheKey(request);
    const cachedResult = this.cache.get<ContentResponse>(cacheKey);
    if (cachedResult) {
      return cachedResult;
    }

    try {
      // Generate content using Google Gemini API
      const content = await this.callGeminiAPI(request);
      
      // Process and enhance the content
      const processedContent = this.processContent(content, request);
      
      const result: ContentResponse = {
        title: processedContent.title,
        content: processedContent.content,
        html: processedContent.content,
        excerpt: processedContent.excerpt,
        summary: processedContent.excerpt,
        meta_description: processedContent.meta_description,
        description: processedContent.meta_description,
        seo_score: this.calculateSEOScore(processedContent, request),
        suggestions: this.generateSuggestions(processedContent, request),
        metadata: {
          word_count: this.countWords(processedContent.content),
          keyword_density: this.calculateKeywordDensity(processedContent.content, request.keyword),
          readability_score: 85, // Simplified score
          generation_time: Date.now() - startTime,
          template_used: 'simple',
          ai_model: 'gemini-2.0-flash-exp'
        }
      };

      // Cache the result
      this.cache.set(cacheKey, result);
      
      return result;
      
    } catch (error) {
      console.error('Content generation error:', error);
      
      // Return fallback content
      return this.generateFallbackContent(request, Date.now() - startTime);
    }
  }

  private async callGeminiAPI(request: ContentRequest): Promise<any> {
    const prompt = this.buildPrompt(request);
    
    const response = await axios.post(
      `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=${this.apiKey}`,
      {
        contents: [{
          parts: [{
            text: prompt
          }]
        }],
        generationConfig: {
          temperature: 0.7,
          topK: 40,
          topP: 0.95,
          maxOutputTokens: 2048
        }
      },
      {
        headers: {
          'Content-Type': 'application/json'
        },
        timeout: 30000
      }
    );

    return response.data.candidates[0].content.parts[0].text;
  }

  private buildPrompt(request: ContentRequest): string {
    const { keyword, language, type = 'blog_post', length = 'medium', style = 'informative' } = request;
    
    let prompt = `Create a ${length} ${type} about "${keyword}" in ${language === 'th' ? 'Thai' : 'English'} language. `;
    prompt += `Style: ${style}. `;
    
    if (request.additional_keywords && request.additional_keywords.length > 0) {
      prompt += `Include these related keywords: ${request.additional_keywords.join(', ')}. `;
    }
    
    if (request.target_audience) {
      prompt += `Target audience: ${request.target_audience}. `;
    }
    
    prompt += `Format the response as JSON with the following structure:
{
  "title": "SEO-optimized title",
  "content": "Full HTML content with proper headings and structure",
  "excerpt": "Brief summary (150-200 characters)",
  "meta_description": "SEO meta description (120-160 characters)"
}

Make sure the content is:
- SEO-optimized with proper keyword usage
- Well-structured with H1, H2, H3 headings
- Engaging and informative
- Includes actionable insights
- Has proper HTML formatting`;

    if (request.include_faq) {
      prompt += `
- Includes a FAQ section at the end`;
    }

    return prompt;
  }

  private processContent(rawContent: string, request: ContentRequest): any {
    try {
      // Try to parse JSON response
      const parsed = JSON.parse(rawContent);
      return parsed;
    } catch (error) {
      // If not JSON, create structured content from raw text
      const lines = rawContent.split('\n').filter(line => line.trim());
      const title = lines[0] || `${request.keyword} - Complete Guide`;
      const content = lines.slice(1).join('\n\n');
      
      return {
        title,
        content: `<h1>${title}</h1>\n\n${content}`,
        excerpt: content.substring(0, 200) + '...',
        meta_description: `Learn about ${request.keyword}. ${content.substring(0, 120)}...`
      };
    }
  }

  private calculateSEOScore(content: any, request: ContentRequest): number {
    let score = 70; // Base score
    
    // Check title optimization
    if (content.title.toLowerCase().includes(request.keyword.toLowerCase())) {
      score += 10;
    }
    
    // Check content length
    const wordCount = this.countWords(content.content);
    if (wordCount >= 300) score += 10;
    if (wordCount >= 1000) score += 5;
    
    // Check meta description
    if (content.meta_description && content.meta_description.length >= 120) {
      score += 5;
    }
    
    return Math.min(score, 100);
  }

  private generateSuggestions(content: any, request: ContentRequest): string[] {
    const suggestions = [];
    
    const wordCount = this.countWords(content.content);
    if (wordCount < 300) {
      suggestions.push('Consider adding more content to reach at least 300 words');
    }
    
    if (!content.title.toLowerCase().includes(request.keyword.toLowerCase())) {
      suggestions.push('Include the main keyword in the title');
    }
    
    if (!content.meta_description || content.meta_description.length < 120) {
      suggestions.push('Optimize meta description length (120-160 characters)');
    }
    
    suggestions.push('Add internal links to related content');
    suggestions.push('Include relevant images with alt text');
    
    return suggestions;
  }

  private countWords(text: string): number {
    return text.replace(/<[^>]*>/g, '').split(/\s+/).filter(word => word.length > 0).length;
  }

  private calculateKeywordDensity(content: string, keyword: string): Record<string, number> {
    const text = content.replace(/<[^>]*>/g, '').toLowerCase();
    const words = text.split(/\s+/).length;
    const keywordMatches = (text.match(new RegExp(keyword.toLowerCase(), 'g')) || []).length;
    
    return {
      [keyword]: words > 0 ? (keywordMatches / words) * 100 : 0
    };
  }

  private generateFallbackContent(request: ContentRequest, generationTime: number): ContentResponse {
    const title = `${request.keyword} - Complete Guide`;
    const content = `<h1>${title}</h1>
<p>This is a comprehensive guide about ${request.keyword}. Our AI-powered content generation service provides detailed, SEO-optimized content to help you understand this topic better.</p>
<h2>Key Points</h2>
<ul>
<li>Understanding ${request.keyword}</li>
<li>Best practices and recommendations</li>
<li>Practical applications</li>
</ul>`;

    return {
      title,
      content,
      html: content,
      excerpt: `Learn about ${request.keyword} with this comprehensive guide.`,
      summary: `Learn about ${request.keyword} with this comprehensive guide.`,
      meta_description: `Complete guide to ${request.keyword}. Learn best practices and practical applications.`,
      description: `Complete guide to ${request.keyword}. Learn best practices and practical applications.`,
      seo_score: 75,
      suggestions: ['Add more detailed content', 'Include relevant examples'],
      metadata: {
        word_count: this.countWords(content),
        keyword_density: this.calculateKeywordDensity(content, request.keyword),
        readability_score: 80,
        generation_time: generationTime,
        template_used: 'fallback',
        ai_model: 'fallback'
      }
    };
  }

  private generateCacheKey(request: ContentRequest): string {
    const keyData = {
      keyword: request.keyword,
      language: request.language,
      type: request.type || 'blog_post',
      length: request.length || 'medium'
    };
    
    return `content_${Buffer.from(JSON.stringify(keyData)).toString('base64')}`;
  }
}
