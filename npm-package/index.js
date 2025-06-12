const SEOForgeClient = require('./lib/client');

module.exports = {
  SEOForgeClient,
  // Export for programmatic use
  createClient: (baseURL) => new SEOForgeClient(baseURL),
  
  // Default client instance
  client: new SEOForgeClient(),
  
  // Convenience methods
  async generateContent(options) {
    const client = new SEOForgeClient();
    return await client.generateContent(undefined, options);
  },
  
  async analyzeSEO(options) {
    const client = new SEOForgeClient();
    return await client.analyzeSEO(undefined, options);
  },
  
  async chat(options) {
    const client = new SEOForgeClient();
    return await client.chat(undefined, options);
  },
  
  async generateImage(options) {
    const client = new SEOForgeClient();
    return await client.generateImage(undefined, options);
  },
  
  async generateBlog(options) {
    const client = new SEOForgeClient();
    return await client.generateBlog(undefined, options);
  },
  
  async getStatus() {
    const client = new SEOForgeClient();
    return await client.getStatus();
  }
};