/**
 * Keyword Research Service
 * Handles keyword research, analysis, and SEO keyword optimization
 */

import axios from 'axios';
import { z } from 'zod';

interface AIConfig {
  googleApiKey?: string;
  openaiApiKey?: string;
  anthropicApiKey?: string;
}

interface KeywordRequest {
  seed_keywords: string[];
  market?: string;
  language?: string;
  industry?: string;
  competition_level?: 'low' | 'medium' | 'high';
}

interface KeywordResponse {
  keywords: {
    keyword: string;
    search_volume: number;
    competition: 'low' | 'medium' | 'high';
    difficulty: number;
    cpc: number;
    intent: 'informational' | 'commercial' | 'transactional' | 'navigational';
    related_keywords: string[];
  }[];
  long_tail_keywords: string[];
  questions: string[];
  trends: {
    keyword: string;
    trend_direction: 'rising' | 'stable' | 'declining';
    seasonal_patterns: string[];
  }[];
}

export class KeywordResearchService {
  private config: AIConfig;
  private initialized = false;

  // Cannabis industry keyword database
  private readonly cannabisKeywords = {
    products: [
      'glass bong', 'water pipe', 'rolling papers', 'herb grinder', 'vaporizer',
      'smoking accessories', 'glass pipe', 'bubbler', 'dab rig', 'one hitter'
    ],
    brands: [
      'RAW papers', 'OCB', 'Molino Glass', 'ROOR', 'Storz Bickel',
      'Volcano', 'Pax', 'Santa Cruz Shredder', 'Space Case'
    ],
    materials: [
      'borosilicate glass', 'titanium', 'ceramic', 'stainless steel',
      'bamboo', 'hemp paper', 'rice paper', 'wood'
    ],
    features: [
      'percolator', 'ice catcher', 'diffuser', 'carb hole', 'splash guard',
      'removable downstem', 'thick glass', 'scientific glass'
    ]
  };

  private readonly thaiKeywords = {
    products: [
      'บ้อง', 'ไปป์น้ำ', 'กระดาษม้วน', 'เครื่องบด', 'เครื่องระเหย',
      'อุปกรณ์สูบ', 'ไปป์แก้ว', 'บับเบลอร์', 'แด็บริก'
    ],
    locations: [
      'กรุงเทพ', 'เชียงใหม่', 'ภูเก็ต', 'พัทยา', 'ขอนแก่น',
      'อุดรธานี', 'นครราชสีมา', 'หาดใหญ่'
    ],
    business: [
      'ขายส่ง', 'ขายปลีก', 'ร้านค้า', 'ออนไลน์', 'จัดส่ง',
      'คุณภาพ', 'ราคาถูก', 'ของแท้', 'นำเข้า'
    ]
  };

  constructor(config: AIConfig) {
    this.config = config;
  }

  async initialize(): Promise<void> {
    if (this.initialized) return;
    this.initialized = true;
  }

  async researchKeywords(request: KeywordRequest): Promise<KeywordResponse> {
    if (!this.initialized) {
      throw new Error('Service not initialized');
    }

    try {
      // Generate expanded keyword list
      const expandedKeywords = await this.expandKeywords(request.seed_keywords, request);
      
      // Analyze each keyword
      const keywordAnalysis = await this.analyzeKeywords(expandedKeywords, request);
      
      // Generate long-tail keywords
      const longTailKeywords = await this.generateLongTailKeywords(request.seed_keywords, request);
      
      // Generate question keywords
      const questions = await this.generateQuestionKeywords(request.seed_keywords, request);
      
      // Analyze trends
      const trends = await this.analyzeTrends(expandedKeywords, request);

      return {
        keywords: keywordAnalysis,
        long_tail_keywords: longTailKeywords,
        questions,
        trends
      };
    } catch (error) {
      throw new Error(`Keyword research failed: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  }

  private async expandKeywords(seedKeywords: string[], request: KeywordRequest): Promise<string[]> {
    const expandedKeywords = new Set(seedKeywords);

    // Add industry-specific keywords
    if (request.industry === 'cannabis') {
      this.addCannabisKeywords(expandedKeywords, seedKeywords);
    }

    // Add location-specific keywords for Thai market
    if (request.market === 'thailand' || request.language === 'th') {
      this.addThaiKeywords(expandedKeywords, seedKeywords);
    }

    // Use AI to generate related keywords
    const aiKeywords = await this.generateAIKeywords(seedKeywords, request);
    aiKeywords.forEach(keyword => expandedKeywords.add(keyword));

    return Array.from(expandedKeywords);
  }

  private addCannabisKeywords(keywordSet: Set<string>, seedKeywords: string[]): void {
    seedKeywords.forEach(seed => {
      // Add product variations
      this.cannabisKeywords.products.forEach(product => {
        if (product.toLowerCase().includes(seed.toLowerCase()) || 
            seed.toLowerCase().includes(product.toLowerCase())) {
          keywordSet.add(product);
          keywordSet.add(`${product} wholesale`);
          keywordSet.add(`${product} online`);
          keywordSet.add(`best ${product}`);
          keywordSet.add(`cheap ${product}`);
        }
      });

      // Add brand combinations
      this.cannabisKeywords.brands.forEach(brand => {
        keywordSet.add(`${brand} ${seed}`);
      });

      // Add material combinations
      this.cannabisKeywords.materials.forEach(material => {
        keywordSet.add(`${material} ${seed}`);
      });
    });
  }

  private addThaiKeywords(keywordSet: Set<string>, seedKeywords: string[]): void {
    // Add Thai product keywords
    this.thaiKeywords.products.forEach(thaiProduct => {
      keywordSet.add(thaiProduct);
      
      // Add location combinations
      this.thaiKeywords.locations.forEach(location => {
        keywordSet.add(`${thaiProduct} ${location}`);
      });
      
      // Add business combinations
      this.thaiKeywords.business.forEach(business => {
        keywordSet.add(`${thaiProduct} ${business}`);
      });
    });
  }

  private async generateAIKeywords(seedKeywords: string[], request: KeywordRequest): Promise<string[]> {
    const model = this.selectKeywordModel();
    
    const prompt = `Generate related keywords for SEO research based on these seed keywords: ${seedKeywords.join(', ')}

Context:
- Industry: ${request.industry || 'general'}
- Market: ${request.market || 'global'}
- Language: ${request.language || 'English'}
- Competition level: ${request.competition_level || 'medium'}

Generate 20 related keywords that would be valuable for SEO, including:
- Variations and synonyms
- Long-tail keywords
- Commercial intent keywords
- Local variations (if applicable)

Return only the keywords, one per line.`;

    const response = await this.callAIModel(model, prompt);
    
    return response
      .split('\n')
      .map(line => line.trim())
      .filter(line => line.length > 0)
      .slice(0, 20);
  }

  private async analyzeKeywords(keywords: string[], request: KeywordRequest): Promise<any[]> {
    const analysis = [];

    for (const keyword of keywords.slice(0, 50)) { // Limit to 50 keywords
      const keywordData = {
        keyword,
        search_volume: this.estimateSearchVolume(keyword, request),
        competition: this.estimateCompetition(keyword, request),
        difficulty: this.estimateDifficulty(keyword, request),
        cpc: this.estimateCPC(keyword, request),
        intent: this.determineIntent(keyword),
        related_keywords: await this.getRelatedKeywords(keyword, request)
      };

      analysis.push(keywordData);
    }

    return analysis;
  }

  private estimateSearchVolume(keyword: string, request: KeywordRequest): number {
    // Simplified search volume estimation
    let baseVolume = 1000;

    // Adjust for keyword length
    if (keyword.split(' ').length === 1) {
      baseVolume *= 5; // Single words have higher volume
    } else if (keyword.split(' ').length > 3) {
      baseVolume *= 0.3; // Long-tail keywords have lower volume
    }

    // Adjust for market
    if (request.market === 'thailand') {
      baseVolume *= 0.1; // Smaller market
    }

    // Adjust for cannabis industry
    if (request.industry === 'cannabis') {
      baseVolume *= 0.5; // Niche industry
    }

    // Add some randomness for realism
    const variance = 0.5 + Math.random();
    return Math.round(baseVolume * variance);
  }

  private estimateCompetition(keyword: string, request: KeywordRequest): 'low' | 'medium' | 'high' {
    const wordCount = keyword.split(' ').length;
    
    if (wordCount === 1) return 'high';
    if (wordCount === 2) return 'medium';
    return 'low';
  }

  private estimateDifficulty(keyword: string, request: KeywordRequest): number {
    const competition = this.estimateCompetition(keyword, request);
    
    switch (competition) {
      case 'low': return 20 + Math.random() * 30;
      case 'medium': return 40 + Math.random() * 30;
      case 'high': return 70 + Math.random() * 30;
    }
  }

  private estimateCPC(keyword: string, request: KeywordRequest): number {
    let baseCPC = 0.5;

    // Commercial keywords have higher CPC
    if (this.determineIntent(keyword) === 'commercial' || 
        this.determineIntent(keyword) === 'transactional') {
      baseCPC *= 3;
    }

    // Cannabis industry typically has higher CPC
    if (request.industry === 'cannabis') {
      baseCPC *= 2;
    }

    return parseFloat((baseCPC * (0.5 + Math.random())).toFixed(2));
  }

  private determineIntent(keyword: string): 'informational' | 'commercial' | 'transactional' | 'navigational' {
    const lowerKeyword = keyword.toLowerCase();

    // Transactional intent
    if (lowerKeyword.includes('buy') || lowerKeyword.includes('purchase') || 
        lowerKeyword.includes('order') || lowerKeyword.includes('shop') ||
        lowerKeyword.includes('wholesale') || lowerKeyword.includes('price')) {
      return 'transactional';
    }

    // Commercial intent
    if (lowerKeyword.includes('best') || lowerKeyword.includes('review') || 
        lowerKeyword.includes('compare') || lowerKeyword.includes('vs') ||
        lowerKeyword.includes('top') || lowerKeyword.includes('cheap')) {
      return 'commercial';
    }

    // Navigational intent
    if (lowerKeyword.includes('brand') || lowerKeyword.includes('website') ||
        lowerKeyword.includes('official') || lowerKeyword.includes('store')) {
      return 'navigational';
    }

    // Default to informational
    return 'informational';
  }

  private async getRelatedKeywords(keyword: string, request: KeywordRequest): Promise<string[]> {
    // Generate 3-5 related keywords
    const related = [];
    const words = keyword.split(' ');

    // Add variations
    if (words.length > 1) {
      related.push(words.reverse().join(' ')); // Reverse word order
    }

    // Add modifiers
    const modifiers = ['best', 'cheap', 'online', 'wholesale', 'quality'];
    related.push(`${modifiers[Math.floor(Math.random() * modifiers.length)]} ${keyword}`);

    // Add location if Thai market
    if (request.market === 'thailand') {
      related.push(`${keyword} thailand`);
      related.push(`${keyword} bangkok`);
    }

    return related.slice(0, 5);
  }

  private async generateLongTailKeywords(seedKeywords: string[], request: KeywordRequest): Promise<string[]> {
    const longTailKeywords = [];

    for (const seed of seedKeywords) {
      // Generate question-based long-tail keywords
      longTailKeywords.push(`how to use ${seed}`);
      longTailKeywords.push(`best ${seed} for beginners`);
      longTailKeywords.push(`where to buy ${seed}`);
      longTailKeywords.push(`${seed} buying guide`);
      longTailKeywords.push(`${seed} reviews and ratings`);

      // Add location-specific long-tail
      if (request.market === 'thailand') {
        longTailKeywords.push(`${seed} delivery thailand`);
        longTailKeywords.push(`${seed} shop bangkok`);
      }
    }

    return longTailKeywords.slice(0, 20);
  }

  private async generateQuestionKeywords(seedKeywords: string[], request: KeywordRequest): Promise<string[]> {
    const questions = [];

    for (const seed of seedKeywords) {
      questions.push(`what is ${seed}`);
      questions.push(`how does ${seed} work`);
      questions.push(`why use ${seed}`);
      questions.push(`when to use ${seed}`);
      questions.push(`where to find ${seed}`);
      questions.push(`which ${seed} is best`);
    }

    return questions.slice(0, 15);
  }

  private async analyzeTrends(keywords: string[], request: KeywordRequest): Promise<any[]> {
    // Simplified trend analysis
    return keywords.slice(0, 10).map(keyword => ({
      keyword,
      trend_direction: ['rising', 'stable', 'declining'][Math.floor(Math.random() * 3)] as 'rising' | 'stable' | 'declining',
      seasonal_patterns: this.getSeasonalPatterns(keyword)
    }));
  }

  private getSeasonalPatterns(keyword: string): string[] {
    const patterns = [];
    
    // Cannabis products might have seasonal patterns
    if (keyword.includes('outdoor') || keyword.includes('festival')) {
      patterns.push('Higher in summer months');
    }
    
    if (keyword.includes('gift') || keyword.includes('holiday')) {
      patterns.push('Peak during holidays');
    }
    
    return patterns;
  }

  private selectKeywordModel(): string {
    if (this.config.googleApiKey) return 'gemini';
    if (this.config.openaiApiKey) return 'gpt4';
    if (this.config.anthropicApiKey) return 'claude';
    throw new Error('No AI models available for keyword research');
  }

  private async callAIModel(model: string, prompt: string): Promise<string> {
    // Implementation would call the appropriate AI model
    // For now, return a placeholder
    return `keyword1\nkeyword2\nkeyword3\nkeyword4\nkeyword5`;
  }
}
