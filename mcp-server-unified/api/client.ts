/**
 * Vercel API endpoint for MCP Client
 * Provides a web interface and client-side functionality
 */

import { VercelRequest, VercelResponse } from '@vercel/node';

export default async function handler(req: VercelRequest, res: VercelResponse) {
  // CORS headers
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  const path = req.url?.split('?')[0] || '';

  // Serve the main client interface
  if (req.method === 'GET' && (path === '/' || path === '/client')) {
    return res.setHeader('Content-Type', 'text/html').send(getClientHTML());
  }

  // API endpoints for client functionality
  if (path === '/client/test') {
    return handleTest(req, res);
  }

  if (path === '/client/demo') {
    return handleDemo(req, res);
  }

  if (path === '/client/docs') {
    return handleDocs(req, res);
  }

  return res.status(404).json({ error: 'Not found' });
}

function getClientHTML(): string {
  return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO Forge MCP Server - Unified Platform</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }
        
        .header h1 {
            font-size: 3rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        
        .card p {
            margin-bottom: 20px;
            color: #666;
        }
        
        .btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn:hover {
            background: #5a6fd8;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .demo-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .demo-form {
            display: grid;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .result {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
            white-space: pre-wrap;
            font-family: 'Courier New', monospace;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #667eea;
        }
        
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .footer {
            text-align: center;
            color: white;
            margin-top: 40px;
            opacity: 0.8;
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 SEO Forge MCP Server</h1>
            <p>Unified AI-Powered SEO Platform for Vercel Deployment</p>
        </div>
        
        <div class="cards">
            <div class="card">
                <h3>🎯 Content Generation</h3>
                <p>Generate SEO-optimized content for blogs, products, and meta descriptions using advanced AI models.</p>
                <button class="btn" onclick="showDemo('content')">Try Content Generation</button>
            </div>
            
            <div class="card">
                <h3>📊 SEO Analysis</h3>
                <p>Comprehensive SEO analysis with actionable recommendations and competitor insights.</p>
                <button class="btn" onclick="showDemo('seo')">Try SEO Analysis</button>
            </div>
            
            <div class="card">
                <h3>🎨 Image Generation</h3>
                <p>AI-powered image generation using Flux, DALL-E, and other advanced models.</p>
                <button class="btn" onclick="showDemo('image')">Try Image Generation</button>
            </div>
            
            <div class="card">
                <h3>🌏 Thai Translation</h3>
                <p>Professional Thai language translation with cultural adaptation for local markets.</p>
                <button class="btn" onclick="showDemo('thai')">Try Thai Translation</button>
            </div>
            
            <div class="card">
                <h3>🔍 Keyword Research</h3>
                <p>Advanced keyword research and analysis for SEO optimization and content strategy.</p>
                <button class="btn" onclick="showDemo('keywords')">Try Keyword Research</button>
            </div>
            
            <div class="card">
                <h3>📝 WordPress Integration</h3>
                <p>Seamless WordPress integration for content synchronization and management.</p>
                <button class="btn" onclick="showDemo('wordpress')">Try WordPress Sync</button>
            </div>
        </div>
        
        <div id="demo-area" style="display: none;">
            <!-- Demo forms will be inserted here -->
        </div>
        
        <div class="demo-section">
            <h3>📚 API Documentation</h3>
            <p>Access the complete API documentation and integration guides.</p>
            <a href="/client/docs" class="btn btn-secondary">View Documentation</a>
            <a href="/mcp/health" class="btn btn-secondary">Health Check</a>
        </div>
        
        <div class="footer">
            <p>&copy; 2024 SEO Forge MCP Server. Built for Vercel deployment with ❤️</p>
        </div>
    </div>

    <script>
        function showDemo(type) {
            const demoArea = document.getElementById('demo-area');
            demoArea.style.display = 'block';
            demoArea.scrollIntoView({ behavior: 'smooth' });
            
            let html = '';
            
            switch(type) {
                case 'content':
                    html = getContentDemoHTML();
                    break;
                case 'seo':
                    html = getSEODemoHTML();
                    break;
                case 'image':
                    html = getImageDemoHTML();
                    break;
                case 'thai':
                    html = getThaiDemoHTML();
                    break;
                case 'keywords':
                    html = getKeywordsDemoHTML();
                    break;
                case 'wordpress':
                    html = getWordPressDemoHTML();
                    break;
            }
            
            demoArea.innerHTML = html;
        }
        
        function getContentDemoHTML() {
            return \`
                <div class="demo-section">
                    <h3>🎯 Content Generation Demo</h3>
                    <form class="demo-form" onsubmit="testContentGeneration(event)">
                        <div class="form-group">
                            <label>Content Type:</label>
                            <select name="type" required>
                                <option value="blog">Blog Post</option>
                                <option value="product">Product Description</option>
                                <option value="category">Category Description</option>
                                <option value="meta">Meta Description</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Topic:</label>
                            <input type="text" name="topic" placeholder="e.g., Glass Bongs for Cannabis Enthusiasts" required>
                        </div>
                        <div class="form-group">
                            <label>Keywords (comma-separated):</label>
                            <input type="text" name="keywords" placeholder="glass bong, water pipe, smoking accessories">
                        </div>
                        <div class="form-group">
                            <label>Language:</label>
                            <select name="language">
                                <option value="en">English</option>
                                <option value="th">Thai</option>
                            </select>
                        </div>
                        <button type="submit" class="btn">Generate Content</button>
                    </form>
                    <div id="content-result"></div>
                </div>
            \`;
        }
        
        function getSEODemoHTML() {
            return \`
                <div class="demo-section">
                    <h3>📊 SEO Analysis Demo</h3>
                    <form class="demo-form" onsubmit="testSEOAnalysis(event)">
                        <div class="form-group">
                            <label>Website URL:</label>
                            <input type="url" name="url" placeholder="https://example.com" required>
                        </div>
                        <div class="form-group">
                            <label>Target Keywords (comma-separated):</label>
                            <input type="text" name="keywords" placeholder="cannabis, bong, smoking accessories">
                        </div>
                        <button type="submit" class="btn">Analyze SEO</button>
                    </form>
                    <div id="seo-result"></div>
                </div>
            \`;
        }
        
        function getImageDemoHTML() {
            return \`
                <div class="demo-section">
                    <h3>🎨 Image Generation Demo</h3>
                    <form class="demo-form" onsubmit="testImageGeneration(event)">
                        <div class="form-group">
                            <label>Image Prompt:</label>
                            <textarea name="prompt" placeholder="A beautiful glass bong with intricate designs, professional product photography" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Style:</label>
                            <select name="style">
                                <option value="realistic">Realistic</option>
                                <option value="artistic">Artistic</option>
                                <option value="minimalist">Minimalist</option>
                                <option value="vintage">Vintage</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Size:</label>
                            <select name="size">
                                <option value="1024x1024">1024x1024</option>
                                <option value="1024x768">1024x768</option>
                                <option value="512x512">512x512</option>
                            </select>
                        </div>
                        <button type="submit" class="btn">Generate Image</button>
                    </form>
                    <div id="image-result"></div>
                </div>
            \`;
        }
        
        function getThaiDemoHTML() {
            return \`
                <div class="demo-section">
                    <h3>🌏 Thai Translation Demo</h3>
                    <form class="demo-form" onsubmit="testThaiTranslation(event)">
                        <div class="form-group">
                            <label>Text to Translate:</label>
                            <textarea name="text" placeholder="High-quality glass bongs and smoking accessories" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Source Language:</label>
                            <select name="source_language">
                                <option value="en">English</option>
                                <option value="th">Thai</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Target Language:</label>
                            <select name="target_language">
                                <option value="th">Thai</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="cultural_adaptation" checked>
                                Cultural Adaptation
                            </label>
                        </div>
                        <button type="submit" class="btn">Translate</button>
                    </form>
                    <div id="thai-result"></div>
                </div>
            \`;
        }
        
        function getKeywordsDemoHTML() {
            return \`
                <div class="demo-section">
                    <h3>🔍 Keyword Research Demo</h3>
                    <form class="demo-form" onsubmit="testKeywordResearch(event)">
                        <div class="form-group">
                            <label>Seed Keywords (comma-separated):</label>
                            <input type="text" name="seed_keywords" placeholder="glass bong, water pipe, smoking" required>
                        </div>
                        <div class="form-group">
                            <label>Market:</label>
                            <select name="market">
                                <option value="global">Global</option>
                                <option value="thailand">Thailand</option>
                                <option value="usa">USA</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Industry:</label>
                            <input type="text" name="industry" placeholder="cannabis" value="cannabis">
                        </div>
                        <button type="submit" class="btn">Research Keywords</button>
                    </form>
                    <div id="keywords-result"></div>
                </div>
            \`;
        }
        
        function getWordPressDemoHTML() {
            return \`
                <div class="demo-section">
                    <h3>📝 WordPress Integration Demo</h3>
                    <form class="demo-form" onsubmit="testWordPressSync(event)">
                        <div class="form-group">
                            <label>WordPress Site URL:</label>
                            <input type="url" name="site_url" placeholder="https://yoursite.com" required>
                        </div>
                        <div class="form-group">
                            <label>Action:</label>
                            <select name="action">
                                <option value="create">Create</option>
                                <option value="update">Update</option>
                                <option value="delete">Delete</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Content Type:</label>
                            <select name="content_type">
                                <option value="post">Post</option>
                                <option value="page">Page</option>
                                <option value="product">Product</option>
                            </select>
                        </div>
                        <button type="submit" class="btn">Test Connection</button>
                    </form>
                    <div id="wordpress-result"></div>
                </div>
            \`;
        }
        
        async function makeAPICall(endpoint, data) {
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                return await response.json();
            } catch (error) {
                return { error: error.message };
            }
        }
        
        async function testContentGeneration(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const data = {
                tool: 'generate_content',
                arguments: {
                    type: formData.get('type'),
                    topic: formData.get('topic'),
                    keywords: formData.get('keywords')?.split(',').map(k => k.trim()).filter(k => k),
                    language: formData.get('language')
                }
            };
            
            const resultDiv = document.getElementById('content-result');
            resultDiv.innerHTML = '<div class="result loading">Generating content...</div>';
            
            const result = await makeAPICall('/mcp/tools/execute', data);
            resultDiv.innerHTML = \`<div class="result \${result.error ? 'error' : 'success'}">\${JSON.stringify(result, null, 2)}</div>\`;
        }
        
        async function testSEOAnalysis(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const data = {
                tool: 'analyze_seo',
                arguments: {
                    url: formData.get('url'),
                    keywords: formData.get('keywords')?.split(',').map(k => k.trim()).filter(k => k)
                }
            };
            
            const resultDiv = document.getElementById('seo-result');
            resultDiv.innerHTML = '<div class="result loading">Analyzing SEO...</div>';
            
            const result = await makeAPICall('/mcp/tools/execute', data);
            resultDiv.innerHTML = \`<div class="result \${result.error ? 'error' : 'success'}">\${JSON.stringify(result, null, 2)}</div>\`;
        }
        
        async function testImageGeneration(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const data = {
                tool: 'generate_image',
                arguments: {
                    prompt: formData.get('prompt'),
                    style: formData.get('style'),
                    size: formData.get('size')
                }
            };
            
            const resultDiv = document.getElementById('image-result');
            resultDiv.innerHTML = '<div class="result loading">Generating image...</div>';
            
            const result = await makeAPICall('/mcp/tools/execute', data);
            resultDiv.innerHTML = \`<div class="result \${result.error ? 'error' : 'success'}">\${JSON.stringify(result, null, 2)}</div>\`;
        }
        
        async function testThaiTranslation(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const data = {
                tool: 'translate_thai',
                arguments: {
                    text: formData.get('text'),
                    source_language: formData.get('source_language'),
                    target_language: formData.get('target_language'),
                    cultural_adaptation: formData.get('cultural_adaptation') === 'on'
                }
            };
            
            const resultDiv = document.getElementById('thai-result');
            resultDiv.innerHTML = '<div class="result loading">Translating...</div>';
            
            const result = await makeAPICall('/mcp/tools/execute', data);
            resultDiv.innerHTML = \`<div class="result \${result.error ? 'error' : 'success'}">\${JSON.stringify(result, null, 2)}</div>\`;
        }
        
        async function testKeywordResearch(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const data = {
                tool: 'research_keywords',
                arguments: {
                    seed_keywords: formData.get('seed_keywords').split(',').map(k => k.trim()).filter(k => k),
                    market: formData.get('market'),
                    industry: formData.get('industry')
                }
            };
            
            const resultDiv = document.getElementById('keywords-result');
            resultDiv.innerHTML = '<div class="result loading">Researching keywords...</div>';
            
            const result = await makeAPICall('/mcp/tools/execute', data);
            resultDiv.innerHTML = \`<div class="result \${result.error ? 'error' : 'success'}">\${JSON.stringify(result, null, 2)}</div>\`;
        }
        
        async function testWordPressSync(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const data = {
                tool: 'wordpress_sync',
                arguments: {
                    site_url: formData.get('site_url'),
                    action: formData.get('action'),
                    content_type: formData.get('content_type'),
                    content: { title: 'Test Post', content: 'Test content' }
                }
            };
            
            const resultDiv = document.getElementById('wordpress-result');
            resultDiv.innerHTML = '<div class="result loading">Testing WordPress connection...</div>';
            
            const result = await makeAPICall('/mcp/tools/execute', data);
            resultDiv.innerHTML = \`<div class="result \${result.error ? 'error' : 'success'}">\${JSON.stringify(result, null, 2)}</div>\`;
        }
    </script>
</body>
</html>
  `;
}

async function handleTest(req: VercelRequest, res: VercelResponse) {
  return res.json({
    status: 'success',
    message: 'MCP Client is working correctly',
    timestamp: new Date().toISOString(),
    endpoints: {
      mcp_server: '/mcp',
      health: '/mcp/health',
      tools: '/mcp/tools/list',
      execute: '/mcp/tools/execute'
    }
  });
}

async function handleDemo(req: VercelRequest, res: VercelResponse) {
  return res.json({
    demo_tools: [
      {
        name: 'generate_content',
        description: 'Generate SEO content',
        example: {
          tool: 'generate_content',
          arguments: {
            type: 'blog',
            topic: 'Glass Bongs Guide',
            keywords: ['glass bong', 'water pipe'],
            language: 'en'
          }
        }
      },
      {
        name: 'analyze_seo',
        description: 'Analyze website SEO',
        example: {
          tool: 'analyze_seo',
          arguments: {
            url: 'https://example.com',
            keywords: ['cannabis', 'bong']
          }
        }
      }
    ]
  });
}

async function handleDocs(req: VercelRequest, res: VercelResponse) {
  return res.json({
    api_documentation: {
      base_url: 'https://your-deployment.vercel.app',
      endpoints: {
        health: 'GET /mcp/health',
        list_tools: 'GET /mcp/tools/list',
        execute_tool: 'POST /mcp/tools/execute',
        login: 'POST /mcp/auth/login',
        register: 'POST /mcp/auth/register'
      },
      authentication: {
        methods: ['API Key', 'JWT Token'],
        headers: {
          api_key: 'X-API-Key: your_api_key',
          jwt: 'Authorization: Bearer your_jwt_token'
        }
      },
      tools: [
        'generate_content',
        'analyze_seo',
        'generate_image',
        'wordpress_sync',
        'translate_thai',
        'research_keywords'
      ]
    }
  });
}
