/**
 * Thai Language Service
 * Handles Thai language translation, localization, and cultural adaptation
 */

import axios from 'axios';
import { z } from 'zod';

interface AIConfig {
  googleApiKey?: string;
  openaiApiKey?: string;
  anthropicApiKey?: string;
}

interface TranslationRequest {
  text: string;
  source_language?: string;
  target_language?: string;
  context?: string;
  cultural_adaptation?: boolean;
}

interface TranslationResponse {
  translated_text: string;
  source_language: string;
  target_language: string;
  cultural_notes?: string[];
  confidence_score: number;
  alternatives?: string[];
}

export class ThaiLanguageService {
  private config: AIConfig;
  private initialized = false;

  // Thai cultural context and cannabis terminology
  private readonly thaiCannabisTerms = {
    'bong': 'บ้อง',
    'water pipe': 'ไปป์น้ำ',
    'rolling papers': 'กระดาษม้วน',
    'grinder': 'เครื่องบด',
    'vaporizer': 'เครื่องระเหย',
    'hemp': 'กัญชง',
    'cannabis': 'กัญชา',
    'CBD': 'ซีบีดี',
    'THC': 'ทีเอชซี',
    'accessories': 'อุปกรณ์เสริม',
    'glass': 'แก้ว',
    'quality': 'คุณภาพ',
    'premium': 'พรีเมียม',
    'wholesale': 'ขายส่ง',
    'retail': 'ขายปลีก'
  };

  private readonly culturalGuidelines = {
    politeness: 'Use polite particles (ครับ/ค่ะ) and respectful language',
    formality: 'Maintain professional tone for business content',
    respect: 'Show respect for Thai cultural values and traditions',
    clarity: 'Use clear, simple Thai that is easy to understand',
    localization: 'Adapt content for Thai market preferences'
  };

  constructor(config: AIConfig) {
    this.config = config;
  }

  async initialize(): Promise<void> {
    if (this.initialized) return;
    
    if (!this.config.googleApiKey && !this.config.openaiApiKey && !this.config.anthropicApiKey) {
      console.warn('No AI API keys configured for Thai language service');
    }
    
    this.initialized = true;
  }

  async translateContent(request: TranslationRequest): Promise<TranslationResponse> {
    if (!this.initialized) {
      throw new Error('Service not initialized');
    }

    try {
      const sourceLanguage = request.source_language || 'en';
      const targetLanguage = request.target_language || 'th';

      // Handle different translation scenarios
      if (sourceLanguage === 'en' && targetLanguage === 'th') {
        return await this.translateToThai(request);
      } else if (sourceLanguage === 'th' && targetLanguage === 'en') {
        return await this.translateFromThai(request);
      } else {
        // Use general translation
        return await this.generalTranslation(request);
      }
    } catch (error) {
      throw new Error(`Translation failed: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  }

  private async translateToThai(request: TranslationRequest): Promise<TranslationResponse> {
    // Pre-process text to handle cannabis terminology
    let processedText = this.preprocessEnglishText(request.text);
    
    // Choose best AI model for Thai translation
    const model = this.selectTranslationModel();
    
    // Build culturally-aware prompt
    const prompt = this.buildThaiTranslationPrompt(processedText, request.context, request.cultural_adaptation);
    
    // Get translation from AI
    const translatedText = await this.callAIForTranslation(model, prompt);
    
    // Post-process translation
    const finalTranslation = this.postprocessThaiText(translatedText);
    
    // Generate cultural notes if requested
    const culturalNotes = request.cultural_adaptation ? 
      await this.generateCulturalNotes(request.text, finalTranslation) : [];

    return {
      translated_text: finalTranslation,
      source_language: 'en',
      target_language: 'th',
      cultural_notes: culturalNotes,
      confidence_score: 0.9, // Would be calculated based on AI response
      alternatives: [] // Could generate alternative translations
    };
  }

  private async translateFromThai(request: TranslationRequest): Promise<TranslationResponse> {
    const model = this.selectTranslationModel();
    
    const prompt = `Translate the following Thai text to English, maintaining the meaning and context:

Thai text: ${request.text}

Context: ${request.context || 'General business content'}

Provide a natural, accurate English translation that preserves the original meaning.`;

    const translatedText = await this.callAIForTranslation(model, prompt);

    return {
      translated_text: translatedText,
      source_language: 'th',
      target_language: 'en',
      confidence_score: 0.85,
      alternatives: []
    };
  }

  private async generalTranslation(request: TranslationRequest): Promise<TranslationResponse> {
    const model = this.selectTranslationModel();
    
    const prompt = `Translate from ${request.source_language} to ${request.target_language}:

Text: ${request.text}
Context: ${request.context || 'General content'}

Provide an accurate translation that maintains the original meaning.`;

    const translatedText = await this.callAIForTranslation(model, prompt);

    return {
      translated_text: translatedText,
      source_language: request.source_language || 'auto',
      target_language: request.target_language || 'en',
      confidence_score: 0.8,
      alternatives: []
    };
  }

  private preprocessEnglishText(text: string): string {
    let processedText = text;
    
    // Replace cannabis terms with Thai equivalents where appropriate
    Object.entries(this.thaiCannabisTerms).forEach(([english, thai]) => {
      const regex = new RegExp(`\\b${english}\\b`, 'gi');
      // Keep English terms but note Thai equivalents for context
      processedText = processedText.replace(regex, `${english} (${thai})`);
    });

    return processedText;
  }

  private buildThaiTranslationPrompt(text: string, context?: string, culturalAdaptation?: boolean): string {
    let prompt = `Translate the following English text to Thai, following these guidelines:

1. Use natural, fluent Thai language
2. Maintain professional business tone
3. Use appropriate politeness particles (ครับ/ค่ะ)
4. Adapt for Thai cultural context
5. Ensure cannabis terminology is appropriate for Thai market

English text: ${text}

Context: ${context || 'Cannabis business content for Thai market'}`;

    if (culturalAdaptation) {
      prompt += `

Additional cultural adaptation requirements:
- Adapt content for Thai cultural values and preferences
- Use respectful language appropriate for Thai business culture
- Consider local market conditions and regulations
- Ensure content is culturally sensitive and appropriate`;
    }

    prompt += `

Provide only the Thai translation, ensuring it sounds natural to native Thai speakers.`;

    return prompt;
  }

  private postprocessThaiText(text: string): string {
    // Clean up common translation issues
    let processedText = text
      .replace(/\s+/g, ' ') // Normalize whitespace
      .trim();

    // Ensure proper Thai punctuation
    processedText = processedText
      .replace(/\s*,\s*/g, ', ')
      .replace(/\s*\.\s*/g, ' ')
      .replace(/\s*!\s*/g, '! ')
      .replace(/\s*\?\s*/g, '? ');

    return processedText;
  }

  private async generateCulturalNotes(originalText: string, translatedText: string): Promise<string[]> {
    const notes: string[] = [];

    // Check for cultural adaptations needed
    if (originalText.toLowerCase().includes('cannabis') || originalText.toLowerCase().includes('hemp')) {
      notes.push('Cannabis terminology adapted for Thai legal and cultural context');
    }

    if (originalText.includes('wholesale') || originalText.includes('business')) {
      notes.push('Business language adapted for Thai commercial culture');
    }

    if (originalText.includes('quality') || originalText.includes('premium')) {
      notes.push('Quality descriptors adapted for Thai market preferences');
    }

    return notes;
  }

  private selectTranslationModel(): string {
    // Google Translate API is generally best for Thai
    if (this.config.googleApiKey) {
      return 'gemini';
    }
    
    if (this.config.openaiApiKey) {
      return 'gpt4';
    }
    
    if (this.config.anthropicApiKey) {
      return 'claude';
    }
    
    throw new Error('No AI models available for translation');
  }

  private async callAIForTranslation(model: string, prompt: string): Promise<string> {
    switch (model) {
      case 'gemini':
        return await this.callGemini(prompt);
      case 'gpt4':
        return await this.callOpenAI(prompt);
      case 'claude':
        return await this.callClaude(prompt);
      default:
        throw new Error(`Unsupported translation model: ${model}`);
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
        max_tokens: 1000
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
        max_tokens: 1000,
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

  // Utility methods for Thai content optimization
  async optimizeThaiSEO(content: string, keywords: string[]): Promise<{
    optimized_content: string;
    keyword_analysis: any[];
    suggestions: string[];
  }> {
    // Analyze Thai keyword usage
    const keywordAnalysis = keywords.map(keyword => {
      const regex = new RegExp(keyword, 'gi');
      const matches = content.match(regex) || [];
      return {
        keyword,
        count: matches.length,
        density: (matches.length / content.split(' ').length) * 100
      };
    });

    const suggestions = [
      'Use natural Thai language flow',
      'Include relevant Thai keywords',
      'Maintain cultural appropriateness',
      'Optimize for Thai search behavior'
    ];

    return {
      optimized_content: content, // Would apply optimizations
      keyword_analysis: keywordAnalysis,
      suggestions
    };
  }

  async generateThaiKeywords(englishKeywords: string[]): Promise<string[]> {
    const thaiKeywords: string[] = [];
    
    for (const keyword of englishKeywords) {
      // Translate keyword to Thai
      const translation = await this.translateContent({
        text: keyword,
        source_language: 'en',
        target_language: 'th'
      });
      
      thaiKeywords.push(translation.translated_text);
      
      // Add variations and related terms
      const lowerKeyword = keyword.toLowerCase();
      if (lowerKeyword in this.thaiCannabisTerms) {
        thaiKeywords.push((this.thaiCannabisTerms as any)[lowerKeyword]);
      }
    }
    
    return [...new Set(thaiKeywords)]; // Remove duplicates
  }
}
