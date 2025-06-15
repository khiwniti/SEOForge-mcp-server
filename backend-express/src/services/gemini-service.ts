/**
 * Google Gemini 2.5 Pro Service
 * Optimized for high accuracy content generation and analysis
 */

import axios, { AxiosResponse } from 'axios';

interface GeminiConfig {
  apiKey: string;
  model?: string;
  temperature?: number;
  topK?: number;
  topP?: number;
  maxOutputTokens?: number;
}

interface GeminiRequest {
  prompt: string;
  systemInstruction?: string;
  context?: string;
}

interface GeminiResponse {
  text: string;
  finishReason: string;
  safetyRatings?: any[];
}

export class GeminiService {
  private config: GeminiConfig;
  private readonly baseUrl = 'https://generativelanguage.googleapis.com/v1beta';
  
  constructor(config: GeminiConfig) {
    this.config = {
      model: 'gemini-2.0-flash-exp', // Latest Gemini 2.5 Pro model
      temperature: 0.7,
      topK: 40,
      topP: 0.95,
      maxOutputTokens: 8192,
      ...config
    };
  }

  /**
   * Generate content using Gemini 2.5 Pro
   */
  async generateContent(request: GeminiRequest): Promise<GeminiResponse> {
    if (!this.config.apiKey) {
      throw new Error('Google API key not configured');
    }

    try {
      const payload = {
        contents: [{
          parts: [{ text: request.prompt }]
        }],
        generationConfig: {
          temperature: this.config.temperature,
          topK: this.config.topK,
          topP: this.config.topP,
          maxOutputTokens: this.config.maxOutputTokens,
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
      };

      // Add system instruction if provided
      if (request.systemInstruction) {
        payload.contents.unshift({
          parts: [{ text: request.systemInstruction }]
        });
      }

      const response: AxiosResponse = await axios.post(
        `${this.baseUrl}/models/${this.config.model}:generateContent?key=${this.config.apiKey}`,
        payload,
        {
          headers: {
            'Content-Type': 'application/json'
          },
          timeout: 30000 // 30 second timeout
        }
      );

      return this.parseResponse(response.data);
    } catch (error) {
      if (axios.isAxiosError(error)) {
        const errorMessage = error.response?.data?.error?.message || error.message;
        throw new Error(`Gemini 2.5 Pro API error: ${errorMessage}`);
      }
      throw error;
    }
  }

  /**
   * Generate SEO-optimized content with enhanced prompts
   */
  async generateSEOContent(options: {
    topic: string;
    keywords: string[];
    contentType: 'blog' | 'product' | 'category' | 'meta';
    language?: string;
    tone?: string;
    length?: 'short' | 'medium' | 'long';
  }): Promise<GeminiResponse> {
    const systemInstruction = `You are an expert SEO content writer with deep knowledge of search engine optimization, content marketing, and user engagement. Your task is to create high-quality, SEO-optimized content that ranks well in search engines while providing genuine value to readers.

Key principles:
- Write naturally and engagingly for humans first
- Integrate keywords seamlessly without keyword stuffing
- Use proper heading structure (H1, H2, H3)
- Include relevant internal linking opportunities
- Optimize for featured snippets and voice search
- Ensure content is comprehensive and authoritative
- Use data, statistics, and examples when relevant
- Write compelling meta descriptions and titles`;

    const prompt = this.buildSEOPrompt(options);

    return await this.generateContent({
      prompt,
      systemInstruction
    });
  }

  /**
   * Analyze content for SEO optimization
   */
  async analyzeContent(content: string, targetKeywords: string[]): Promise<{
    score: number;
    suggestions: string[];
    keywordAnalysis: Record<string, number>;
    readabilityScore: number;
  }> {
    const prompt = `Analyze the following content for SEO optimization:

Content:
${content}

Target Keywords: ${targetKeywords.join(', ')}

Please provide:
1. Overall SEO score (0-100)
2. Specific improvement suggestions
3. Keyword density analysis
4. Readability assessment
5. Content structure recommendations

Format your response as JSON with the following structure:
{
  "score": number,
  "suggestions": ["suggestion1", "suggestion2"],
  "keywordAnalysis": {"keyword": density_percentage},
  "readabilityScore": number,
  "structureRecommendations": ["rec1", "rec2"]
}`;

    const response = await this.generateContent({ prompt });
    
    try {
      return JSON.parse(response.text);
    } catch (error) {
      // Fallback to basic analysis if JSON parsing fails
      return this.basicContentAnalysis(content, targetKeywords);
    }
  }

  private buildSEOPrompt(options: {
    topic: string;
    keywords: string[];
    contentType: 'blog' | 'product' | 'category' | 'meta';
    language?: string;
    tone?: string;
    length?: 'short' | 'medium' | 'long';
  }): string {
    const lengthGuide = {
      short: '500-800 words',
      medium: '1000-1500 words', 
      long: '2000-3000 words'
    };

    const basePrompt = `Create ${options.contentType} content about "${options.topic}".

Requirements:
- Target keywords: ${options.keywords.join(', ')}
- Language: ${options.language || 'English'}
- Tone: ${options.tone || 'professional and engaging'}
- Length: ${lengthGuide[options.length || 'medium']}`;

    switch (options.contentType) {
      case 'blog':
        return `${basePrompt}

Structure:
- Compelling H1 title with primary keyword
- Introduction with hook and keyword
- 3-5 main sections with H2 headings
- Subheadings (H3) where appropriate
- Conclusion with call-to-action
- Natural keyword integration throughout

Focus on:
- Providing comprehensive, valuable information
- Answering common questions about the topic
- Including actionable tips and insights
- Using examples and case studies
- Optimizing for featured snippets`;

      case 'product':
        return `${basePrompt}

Structure:
- Compelling product title
- Key benefits and features
- Technical specifications
- Use cases and applications
- Customer pain points addressed
- Call-to-action

Focus on:
- Highlighting unique selling points
- Addressing customer concerns
- Using persuasive but honest language
- Including social proof elements
- Optimizing for product search queries`;

      case 'category':
        return `${basePrompt}

Structure:
- Category overview
- Key subcategories or products
- Benefits of this category
- How to choose/buying guide
- Popular brands or options
- Related categories

Focus on:
- Helping users navigate the category
- Providing educational content
- Building topical authority
- Internal linking opportunities`;

      case 'meta':
        return `${basePrompt}

Requirements:
- 150-160 characters maximum
- Include primary keyword naturally
- Create compelling, click-worthy copy
- Accurately describe the content
- Include emotional trigger or benefit
- Use active voice
- End with call-to-action if space allows`;

      default:
        return basePrompt;
    }
  }

  private parseResponse(data: any): GeminiResponse {
    if (!data.candidates || data.candidates.length === 0) {
      throw new Error('No content generated by Gemini 2.5 Pro');
    }

    const candidate = data.candidates[0];
    
    // Check for safety blocks
    if (candidate.finishReason === 'SAFETY') {
      throw new Error('Content blocked by safety filters');
    }

    if (!candidate.content || !candidate.content.parts || candidate.content.parts.length === 0) {
      throw new Error('Invalid response format from Gemini 2.5 Pro');
    }

    return {
      text: candidate.content.parts[0].text,
      finishReason: candidate.finishReason,
      safetyRatings: candidate.safetyRatings
    };
  }

  private basicContentAnalysis(content: string, keywords: string[]): {
    score: number;
    suggestions: string[];
    keywordAnalysis: Record<string, number>;
    readabilityScore: number;
  } {
    const words = content.split(/\s+/).length;
    const suggestions: string[] = [];
    let score = 70;

    // Keyword analysis
    const keywordAnalysis: Record<string, number> = {};
    keywords.forEach(keyword => {
      const regex = new RegExp(keyword, 'gi');
      const matches = content.match(regex) || [];
      const density = (matches.length / words) * 100;
      keywordAnalysis[keyword] = density;

      if (density < 0.5) {
        suggestions.push(`Increase usage of keyword "${keyword}"`);
        score -= 5;
      } else if (density > 3) {
        suggestions.push(`Reduce keyword "${keyword}" density`);
        score -= 3;
      }
    });

    // Basic readability
    const sentences = content.split(/[.!?]+/).length;
    const avgWordsPerSentence = words / sentences;
    const readabilityScore = Math.max(0, Math.min(100, 100 - (avgWordsPerSentence * 1.5)));

    if (readabilityScore < 60) {
      suggestions.push('Use shorter sentences for better readability');
      score -= 5;
    }

    return {
      score: Math.max(0, Math.min(100, score)),
      suggestions,
      keywordAnalysis,
      readabilityScore
    };
  }
}

// Export default instance with the provided API key
export const geminiService = new GeminiService({
  apiKey: 'AIzaSyDTITCw_UcgzUufrsCFuxp9HXri6Y0XrDo'
});

export default GeminiService;