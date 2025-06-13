/**
 * SEO Analysis Service
 * Comprehensive SEO analysis and optimization recommendations
 */

import axios from 'axios';
import { z } from 'zod';

interface AIConfig {
  googleApiKey?: string;
  openaiApiKey?: string;
  anthropicApiKey?: string;
}

interface SEORequest {
  url?: string;
  content?: string;
  keywords?: string[];
  competitors?: string[];
  title?: string;
  meta_description?: string;
}

interface SEOResponse {
  overall_score: number;
  scores: {
    content: number;
    technical: number;
    keywords: number;
    structure: number;
    meta: number;
  };
  recommendations: {
    priority: 'high' | 'medium' | 'low';
    category: string;
    issue: string;
    solution: string;
  }[];
  keyword_analysis: {
    keyword: string;
    density: number;
    prominence: number;
    recommendations: string[];
  }[];
  competitor_analysis?: {
    competitor: string;
    score: number;
    strengths: string[];
    opportunities: string[];
  }[];
}

export class SEOAnalysisService {
  private config: AIConfig;
  private initialized = false;

  constructor(config: AIConfig) {
    this.config = config;
  }

  async initialize(): Promise<void> {
    if (this.initialized) return;
    this.initialized = true;
  }

  async analyzeSEO(request: SEORequest): Promise<SEOResponse> {
    if (!this.initialized) {
      throw new Error('Service not initialized');
    }

    try {
      let content = request.content || '';
      let title = request.title || '';
      let meta_description = request.meta_description || '';

      // If URL is provided, fetch content
      if (request.url && !content) {
        const pageData = await this.fetchPageContent(request.url);
        content = pageData.content;
        title = pageData.title;
        meta_description = pageData.meta_description;
      }

      // Perform comprehensive SEO analysis
      const contentScore = this.analyzeContent(content, request.keywords || []);
      const technicalScore = await this.analyzeTechnical(request.url);
      const keywordScore = this.analyzeKeywords(content, title, meta_description, request.keywords || []);
      const structureScore = this.analyzeStructure(content);
      const metaScore = this.analyzeMeta(title, meta_description, request.keywords || []);

      // Generate recommendations
      const recommendations = this.generateRecommendations({
        content: contentScore,
        technical: technicalScore,
        keywords: keywordScore,
        structure: structureScore,
        meta: metaScore
      }, content, title, meta_description, request.keywords || []);

      // Analyze keywords in detail
      const keyword_analysis = this.analyzeKeywordsDetailed(content, title, meta_description, request.keywords || []);

      // Competitor analysis if provided
      let competitor_analysis;
      if (request.competitors && request.competitors.length > 0) {
        competitor_analysis = await this.analyzeCompetitors(request.competitors, request.keywords || []);
      }

      const overall_score = Math.round(
        (contentScore.score + technicalScore.score + keywordScore.score + structureScore.score + metaScore.score) / 5
      );

      return {
        overall_score,
        scores: {
          content: contentScore.score,
          technical: technicalScore.score,
          keywords: keywordScore.score,
          structure: structureScore.score,
          meta: metaScore.score
        },
        recommendations,
        keyword_analysis,
        competitor_analysis
      };
    } catch (error) {
      throw new Error(`SEO analysis failed: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  }

  private async fetchPageContent(url: string): Promise<{ content: string; title: string; meta_description: string }> {
    try {
      const response = await axios.get(url, {
        timeout: 10000,
        headers: {
          'User-Agent': 'SEOForge-Bot/2.0'
        }
      });

      const html = response.data;
      
      // Extract title
      const titleMatch = html.match(/<title[^>]*>([^<]+)<\/title>/i);
      const title = titleMatch ? titleMatch[1].trim() : '';

      // Extract meta description
      const metaMatch = html.match(/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^"']+)["\'][^>]*>/i);
      const meta_description = metaMatch ? metaMatch[1].trim() : '';

      // Extract text content (simplified)
      const content = html
        .replace(/<script[^>]*>[\s\S]*?<\/script>/gi, '')
        .replace(/<style[^>]*>[\s\S]*?<\/style>/gi, '')
        .replace(/<[^>]+>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

      return { content, title, meta_description };
    } catch (error) {
      throw new Error(`Failed to fetch page content: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  }

  private analyzeContent(content: string, keywords: string[]): { score: number; issues: string[] } {
    const issues: string[] = [];
    let score = 100;

    // Word count analysis
    const wordCount = content.split(/\s+/).length;
    if (wordCount < 300) {
      issues.push('Content is too short (less than 300 words)');
      score -= 20;
    } else if (wordCount < 500) {
      issues.push('Content could be longer for better SEO');
      score -= 10;
    }

    // Keyword presence
    keywords.forEach(keyword => {
      const regex = new RegExp(keyword, 'gi');
      const matches = content.match(regex);
      if (!matches || matches.length === 0) {
        issues.push(`Keyword "${keyword}" not found in content`);
        score -= 15;
      }
    });

    // Readability
    const sentences = content.split(/[.!?]+/).length;
    const avgWordsPerSentence = wordCount / sentences;
    if (avgWordsPerSentence > 20) {
      issues.push('Sentences are too long, affecting readability');
      score -= 10;
    }

    return { score: Math.max(0, score), issues };
  }

  private async analyzeTechnical(url?: string): Promise<{ score: number; issues: string[] }> {
    const issues: string[] = [];
    let score = 100;

    if (!url) {
      return { score: 80, issues: ['URL not provided for technical analysis'] };
    }

    try {
      const start = Date.now();
      const response = await axios.get(url, { timeout: 10000 });
      const loadTime = Date.now() - start;

      // Page load time
      if (loadTime > 3000) {
        issues.push('Page load time is too slow (>3 seconds)');
        score -= 20;
      } else if (loadTime > 1500) {
        issues.push('Page load time could be improved');
        score -= 10;
      }

      // HTTPS check
      if (!url.startsWith('https://')) {
        issues.push('Site should use HTTPS');
        score -= 15;
      }

      // Basic HTML validation
      const html = response.data;
      if (!html.includes('<!DOCTYPE html>')) {
        issues.push('Missing DOCTYPE declaration');
        score -= 5;
      }

    } catch (error) {
      issues.push('Unable to access URL for technical analysis');
      score -= 30;
    }

    return { score: Math.max(0, score), issues };
  }

  private analyzeKeywords(content: string, title: string, meta_description: string, keywords: string[]): { score: number; issues: string[] } {
    const issues: string[] = [];
    let score = 100;

    keywords.forEach(keyword => {
      const contentRegex = new RegExp(keyword, 'gi');
      const titleRegex = new RegExp(keyword, 'gi');
      const metaRegex = new RegExp(keyword, 'gi');

      const contentMatches = content.match(contentRegex) || [];
      const titleMatches = title.match(titleRegex) || [];
      const metaMatches = meta_description.match(metaRegex) || [];

      // Keyword in title
      if (titleMatches.length === 0) {
        issues.push(`Keyword "${keyword}" not found in title`);
        score -= 15;
      }

      // Keyword in meta description
      if (metaMatches.length === 0) {
        issues.push(`Keyword "${keyword}" not found in meta description`);
        score -= 10;
      }

      // Keyword density in content
      const density = (contentMatches.length / content.split(/\s+/).length) * 100;
      if (density < 0.5) {
        issues.push(`Keyword "${keyword}" density too low (${density.toFixed(2)}%)`);
        score -= 10;
      } else if (density > 3) {
        issues.push(`Keyword "${keyword}" density too high (${density.toFixed(2)}%) - risk of over-optimization`);
        score -= 15;
      }
    });

    return { score: Math.max(0, score), issues };
  }

  private analyzeStructure(content: string): { score: number; issues: string[] } {
    const issues: string[] = [];
    let score = 100;

    // Check for heading structure (simplified)
    const h1Count = (content.match(/h1/gi) || []).length;
    const h2Count = (content.match(/h2/gi) || []).length;

    if (h1Count === 0) {
      issues.push('Missing H1 heading');
      score -= 20;
    } else if (h1Count > 1) {
      issues.push('Multiple H1 headings found');
      score -= 10;
    }

    if (h2Count === 0) {
      issues.push('No H2 headings found - consider adding subheadings');
      score -= 10;
    }

    // Check for lists and formatting
    const listCount = (content.match(/(<ul|<ol|<li)/gi) || []).length;
    if (listCount === 0 && content.split(/\s+/).length > 500) {
      issues.push('Consider adding lists or bullet points for better readability');
      score -= 5;
    }

    return { score: Math.max(0, score), issues };
  }

  private analyzeMeta(title: string, meta_description: string, keywords: string[]): { score: number; issues: string[] } {
    const issues: string[] = [];
    let score = 100;

    // Title analysis
    if (!title) {
      issues.push('Missing title tag');
      score -= 30;
    } else {
      if (title.length < 30) {
        issues.push('Title is too short');
        score -= 15;
      } else if (title.length > 60) {
        issues.push('Title is too long (may be truncated in search results)');
        score -= 10;
      }
    }

    // Meta description analysis
    if (!meta_description) {
      issues.push('Missing meta description');
      score -= 20;
    } else {
      if (meta_description.length < 120) {
        issues.push('Meta description is too short');
        score -= 10;
      } else if (meta_description.length > 160) {
        issues.push('Meta description is too long (may be truncated)');
        score -= 10;
      }
    }

    return { score: Math.max(0, score), issues };
  }

  private generateRecommendations(scores: any, content: string, title: string, meta_description: string, keywords: string[]): any[] {
    const recommendations: any[] = [];

    // Combine all issues from different analyses
    const allIssues = [
      ...scores.content.issues,
      ...scores.technical.issues,
      ...scores.keywords.issues,
      ...scores.structure.issues,
      ...scores.meta.issues
    ];

    allIssues.forEach(issue => {
      let priority: 'high' | 'medium' | 'low' = 'medium';
      let category = 'General';
      let solution = 'Review and optimize this aspect';

      // Categorize and prioritize issues
      if (issue.includes('keyword')) {
        category = 'Keywords';
        priority = 'high';
        solution = 'Optimize keyword usage and placement';
      } else if (issue.includes('title')) {
        category = 'Meta Tags';
        priority = 'high';
        solution = 'Optimize title tag length and keyword placement';
      } else if (issue.includes('meta description')) {
        category = 'Meta Tags';
        priority = 'medium';
        solution = 'Write compelling meta description with target keywords';
      } else if (issue.includes('content')) {
        category = 'Content';
        priority = 'medium';
        solution = 'Improve content quality and length';
      } else if (issue.includes('load time')) {
        category = 'Technical';
        priority = 'high';
        solution = 'Optimize page speed and performance';
      }

      recommendations.push({
        priority,
        category,
        issue,
        solution
      });
    });

    return recommendations;
  }

  private analyzeKeywordsDetailed(content: string, title: string, meta_description: string, keywords: string[]): any[] {
    return keywords.map(keyword => {
      const contentRegex = new RegExp(keyword, 'gi');
      const titleRegex = new RegExp(keyword, 'gi');
      
      const contentMatches = content.match(contentRegex) || [];
      const titleMatches = title.match(titleRegex) || [];
      
      const density = (contentMatches.length / content.split(/\s+/).length) * 100;
      const prominence = titleMatches.length > 0 ? 100 : (contentMatches.length > 0 ? 50 : 0);
      
      const recommendations: string[] = [];
      
      if (density < 0.5) {
        recommendations.push('Increase keyword usage in content');
      } else if (density > 3) {
        recommendations.push('Reduce keyword usage to avoid over-optimization');
      }
      
      if (titleMatches.length === 0) {
        recommendations.push('Add keyword to title tag');
      }
      
      return {
        keyword,
        density: parseFloat(density.toFixed(2)),
        prominence,
        recommendations
      };
    });
  }

  private async analyzeCompetitors(competitors: string[], keywords: string[]): Promise<any[]> {
    const results: any[] = [];
    
    for (const competitor of competitors.slice(0, 3)) { // Limit to 3 competitors
      try {
        const pageData = await this.fetchPageContent(competitor);
        const analysis = this.analyzeContent(pageData.content, keywords);
        
        results.push({
          competitor,
          score: analysis.score,
          strengths: ['Strong content optimization', 'Good keyword usage'],
          opportunities: ['Improve meta descriptions', 'Add more content']
        });
      } catch (error) {
        results.push({
          competitor,
          score: 0,
          strengths: [],
          opportunities: ['Unable to analyze - site may be inaccessible']
        });
      }
    }
    
    return results;
  }
}
