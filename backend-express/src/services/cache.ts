/**
 * Cache Service
 * Handles caching for improved performance and reduced API calls
 */

interface CacheItem {
  value: any;
  expiry: number;
  created: number;
}

export class CacheService {
  private cache = new Map<string, CacheItem>();
  private redisUrl?: string;
  private initialized = false;
  private cleanupInterval?: NodeJS.Timeout;

  constructor(redisUrl?: string) {
    this.redisUrl = redisUrl;
  }

  async initialize(): Promise<void> {
    if (this.initialized) return;

    // For now, use in-memory cache
    // In production, you would connect to Redis here
    if (this.redisUrl) {
      console.log('Redis URL provided but using in-memory cache for simplicity');
    }

    // Start cleanup interval
    this.cleanupInterval = setInterval(() => {
      this.cleanup();
    }, 60000); // Cleanup every minute

    this.initialized = true;
  }

  async get(key: string): Promise<any> {
    const item = this.cache.get(key);
    
    if (!item) {
      return null;
    }

    if (Date.now() > item.expiry) {
      this.cache.delete(key);
      return null;
    }

    return item.value;
  }

  async set(key: string, value: any, ttlSeconds: number = 3600): Promise<void> {
    const expiry = Date.now() + (ttlSeconds * 1000);
    
    this.cache.set(key, {
      value,
      expiry,
      created: Date.now()
    });
  }

  async delete(key: string): Promise<void> {
    this.cache.delete(key);
  }

  async clear(): Promise<void> {
    this.cache.clear();
  }

  async exists(key: string): Promise<boolean> {
    const item = this.cache.get(key);
    
    if (!item) {
      return false;
    }

    if (Date.now() > item.expiry) {
      this.cache.delete(key);
      return false;
    }

    return true;
  }

  async getStats(): Promise<{
    total_keys: number;
    expired_keys: number;
    memory_usage: number;
    hit_rate: number;
  }> {
    let expiredCount = 0;
    const now = Date.now();

    for (const [key, item] of this.cache.entries()) {
      if (now > item.expiry) {
        expiredCount++;
      }
    }

    return {
      total_keys: this.cache.size,
      expired_keys: expiredCount,
      memory_usage: this.estimateMemoryUsage(),
      hit_rate: 0.85 // Placeholder - would track actual hit rate
    };
  }

  // Content-specific cache methods
  async cacheContentGeneration(prompt: string, content: string, ttl: number = 7200): Promise<void> {
    const key = `content:${this.hashString(prompt)}`;
    await this.set(key, content, ttl);
  }

  async getCachedContent(prompt: string): Promise<string | null> {
    const key = `content:${this.hashString(prompt)}`;
    return await this.get(key);
  }

  async cacheSEOAnalysis(url: string, analysis: any, ttl: number = 3600): Promise<void> {
    const key = `seo:${this.hashString(url)}`;
    await this.set(key, analysis, ttl);
  }

  async getCachedSEOAnalysis(url: string): Promise<any> {
    const key = `seo:${this.hashString(url)}`;
    return await this.get(key);
  }

  async cacheKeywordResearch(seedKeywords: string[], research: any, ttl: number = 86400): Promise<void> {
    const key = `keywords:${this.hashString(seedKeywords.join(','))}`;
    await this.set(key, research, ttl);
  }

  async getCachedKeywordResearch(seedKeywords: string[]): Promise<any> {
    const key = `keywords:${this.hashString(seedKeywords.join(','))}`;
    return await this.get(key);
  }

  async cacheTranslation(text: string, sourceLang: string, targetLang: string, translation: string, ttl: number = 86400): Promise<void> {
    const key = `translation:${sourceLang}:${targetLang}:${this.hashString(text)}`;
    await this.set(key, translation, ttl);
  }

  async getCachedTranslation(text: string, sourceLang: string, targetLang: string): Promise<string | null> {
    const key = `translation:${sourceLang}:${targetLang}:${this.hashString(text)}`;
    return await this.get(key);
  }

  async cacheImage(prompt: string, imageData: any, ttl: number = 604800): Promise<void> { // 7 days
    const key = `image:${this.hashString(prompt)}`;
    await this.set(key, imageData, ttl);
  }

  async getCachedImage(prompt: string): Promise<any> {
    const key = `image:${this.hashString(prompt)}`;
    return await this.get(key);
  }

  // WordPress-specific cache methods
  async cacheWordPressPost(siteUrl: string, postId: number, postData: any, ttl: number = 1800): Promise<void> {
    const key = `wp:${this.hashString(siteUrl)}:post:${postId}`;
    await this.set(key, postData, ttl);
  }

  async getCachedWordPressPost(siteUrl: string, postId: number): Promise<any> {
    const key = `wp:${this.hashString(siteUrl)}:post:${postId}`;
    return await this.get(key);
  }

  async invalidateWordPressCache(siteUrl: string): Promise<void> {
    const prefix = `wp:${this.hashString(siteUrl)}:`;
    
    for (const key of this.cache.keys()) {
      if (key.startsWith(prefix)) {
        this.cache.delete(key);
      }
    }
  }

  // Rate limiting cache
  async getRateLimitCount(identifier: string): Promise<number> {
    const key = `ratelimit:${identifier}`;
    const count = await this.get(key);
    return count || 0;
  }

  async incrementRateLimit(identifier: string, windowSeconds: number = 3600): Promise<number> {
    const key = `ratelimit:${identifier}`;
    const currentCount = await this.getRateLimitCount(identifier);
    const newCount = currentCount + 1;
    
    await this.set(key, newCount, windowSeconds);
    return newCount;
  }

  async resetRateLimit(identifier: string): Promise<void> {
    const key = `ratelimit:${identifier}`;
    await this.delete(key);
  }

  // Session cache
  async cacheSession(sessionId: string, sessionData: any, ttl: number = 86400): Promise<void> {
    const key = `session:${sessionId}`;
    await this.set(key, sessionData, ttl);
  }

  async getSession(sessionId: string): Promise<any> {
    const key = `session:${sessionId}`;
    return await this.get(key);
  }

  async deleteSession(sessionId: string): Promise<void> {
    const key = `session:${sessionId}`;
    await this.delete(key);
  }

  // Utility methods
  private cleanup(): void {
    const now = Date.now();
    const keysToDelete: string[] = [];

    for (const [key, item] of this.cache.entries()) {
      if (now > item.expiry) {
        keysToDelete.push(key);
      }
    }

    keysToDelete.forEach(key => this.cache.delete(key));

    if (keysToDelete.length > 0) {
      console.log(`Cache cleanup: removed ${keysToDelete.length} expired items`);
    }
  }

  private hashString(str: string): string {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
      const char = str.charCodeAt(i);
      hash = ((hash << 5) - hash) + char;
      hash = hash & hash; // Convert to 32-bit integer
    }
    return Math.abs(hash).toString(36);
  }

  private estimateMemoryUsage(): number {
    let totalSize = 0;
    
    for (const [key, item] of this.cache.entries()) {
      // Rough estimation of memory usage
      totalSize += key.length * 2; // String overhead
      totalSize += JSON.stringify(item.value).length * 2;
      totalSize += 24; // Object overhead
    }
    
    return totalSize;
  }

  // Batch operations
  async setMultiple(items: Array<{ key: string; value: any; ttl?: number }>): Promise<void> {
    for (const item of items) {
      await this.set(item.key, item.value, item.ttl);
    }
  }

  async getMultiple(keys: string[]): Promise<Record<string, any>> {
    const result: Record<string, any> = {};
    
    for (const key of keys) {
      result[key] = await this.get(key);
    }
    
    return result;
  }

  async deleteMultiple(keys: string[]): Promise<void> {
    for (const key of keys) {
      await this.delete(key);
    }
  }

  // Pattern-based operations
  async deleteByPattern(pattern: string): Promise<number> {
    const regex = new RegExp(pattern.replace('*', '.*'));
    const keysToDelete: string[] = [];
    
    for (const key of this.cache.keys()) {
      if (regex.test(key)) {
        keysToDelete.push(key);
      }
    }
    
    keysToDelete.forEach(key => this.cache.delete(key));
    return keysToDelete.length;
  }

  async getKeysByPattern(pattern: string): Promise<string[]> {
    const regex = new RegExp(pattern.replace('*', '.*'));
    const matchingKeys: string[] = [];
    
    for (const key of this.cache.keys()) {
      if (regex.test(key)) {
        matchingKeys.push(key);
      }
    }
    
    return matchingKeys;
  }

  // Cleanup and shutdown
  async shutdown(): Promise<void> {
    if (this.cleanupInterval) {
      clearInterval(this.cleanupInterval);
    }
    
    this.cache.clear();
    this.initialized = false;
  }
}
