/**
 * Type definitions for SEO Forge MCP Server
 */

export interface ServerConfig {
  server: {
    name: string;
    version: string;
    port: number;
    host: string;
  };
  api: {
    baseUrl: string;
    timeout: number;
    retries: number;
  };
  ai: {
    googleApiKey?: string;
    huggingfaceToken?: string;
    replicateToken?: string;
    togetherToken?: string;
  };
}

export interface ContentGenerationParams {
  topic?: string;
  keywords?: string[];
  content_type?: 'blog_post' | 'product_description' | 'landing_page' | 'how_to_guide' | 'news_article';
  language?: 'en' | 'th' | 'es' | 'fr' | 'de' | 'it' | 'pt' | 'ru' | 'ja' | 'ko' | 'zh';
  tone?: 'professional' | 'casual' | 'friendly' | 'authoritative' | 'conversational';
  length?: 'short' | 'medium' | 'long';
  industry?: string;
  include_images?: boolean;
  image_count?: number;
  image_style?: string;
}

export interface FluxImageGenerationParams {
  prompt: string;
  negative_prompt?: string;
  width?: number;
  height?: number;
  guidance_scale?: number;
  num_inference_steps?: number;
  seed?: number;
  model?: 'flux-schnell' | 'flux-dev' | 'flux-pro';
  style?: string;
  enhance_prompt?: boolean;
}

export interface SEOAnalysisParams {
  content: string;
  keywords?: string[];
  language?: 'en' | 'th' | 'es' | 'fr' | 'de';
  url?: string;
}

export interface KeywordResearchParams {
  seed_keywords: string[];
  language?: 'en' | 'th' | 'es' | 'fr' | 'de';
  location?: string;
  limit?: number;
}

export interface APIResponse<T = any> {
  success: boolean;
  data?: T;
  error?: string;
  message?: string;
  timestamp?: string;
}

export interface FluxImageResult {
  id: string;
  filename: string;
  url: string;
  prompt: string;
  negative_prompt?: string;
  width: number;
  height: number;
  model: string;
  style: string;
  generation_method: string;
  seed?: number;
  generated_at: string;
  file_size?: number;
}

export interface ContentGenerationResult {
  content: string;
  images?: FluxImageResult[];
  seo_data: {
    title?: string;
    description?: string;
    keywords?: string[];
    word_count?: number;
    seo_score?: number;
  };
  word_count: number;
  generated_at: string;
}

export interface SEOAnalysisResult {
  seo_score: number;
  keyword_analysis: {
    [keyword: string]: {
      count: number;
      density: number;
    };
  };
  recommendations: string[];
  meta_analysis: {
    title_length?: number;
    description_length?: number;
    has_h1?: boolean;
    heading_structure?: any;
  };
  analyzed_at: string;
}

export interface KeywordResearchResult {
  keywords: Array<{
    keyword: string;
    search_volume?: number;
    difficulty?: number;
    competition?: string;
    cpc?: number;
    related_keywords?: string[];
  }>;
  total_keywords: number;
  language: string;
  location: string;
  researched_at: string;
}

export interface ServerStatus {
  status: string;
  version: string;
  components: {
    [key: string]: string;
  };
  capabilities: string[];
  supported_languages: string[];
  image_generation: {
    providers: string[];
    flux_models: string[];
    styles: string[];
    sizes: string[];
  };
  ai_models: {
    [key: string]: string;
  };
  timestamp: string;
}

export interface FluxModelsInfo {
  success: boolean;
  available_models: string[];
  model_info: {
    [model: string]: {
      name: string;
      description: string;
      recommended_steps: string;
      max_resolution: string;
      speed: string;
      quality: string;
    };
  };
  default_model: string;
  recommended_settings: {
    [model: string]: {
      steps: number;
      guidance_scale: number;
      description: string;
    };
  };
  timestamp: string;
}