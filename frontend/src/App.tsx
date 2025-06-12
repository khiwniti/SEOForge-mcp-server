import React from 'react';
import './index.css';

function App() {
  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      <div className="container mx-auto px-4 py-8">
        <header className="text-center mb-12">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">
            🚀 SEOForge MCP Platform
          </h1>
          <p className="text-xl text-gray-600 max-w-2xl mx-auto">
            Advanced SEO Content Generation Platform with WordPress Integration
          </p>
        </header>

        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
          <div className="bg-white rounded-lg shadow-lg p-6">
            <div className="text-3xl mb-4">📝</div>
            <h3 className="text-xl font-semibold mb-2">Content Generation</h3>
            <p className="text-gray-600">
              AI-powered content creation with SEO optimization for WordPress sites.
            </p>
          </div>

          <div className="bg-white rounded-lg shadow-lg p-6">
            <div className="text-3xl mb-4">🔌</div>
            <h3 className="text-xl font-semibold mb-2">WordPress Plugin</h3>
            <p className="text-gray-600">
              Seamless integration with WordPress through our MCP plugin system.
            </p>
          </div>

          <div className="bg-white rounded-lg shadow-lg p-6">
            <div className="text-3xl mb-4">⚡</div>
            <h3 className="text-xl font-semibold mb-2">Real-time API</h3>
            <p className="text-gray-600">
              Fast, reliable API endpoints for content generation and SEO analysis.
            </p>
          </div>

          <div className="bg-white rounded-lg shadow-lg p-6">
            <div className="text-3xl mb-4">🌐</div>
            <h3 className="text-xl font-semibold mb-2">Bilingual Support</h3>
            <p className="text-gray-600">
              Content generation in English and Thai languages with cultural context.
            </p>
          </div>

          <div className="bg-white rounded-lg shadow-lg p-6">
            <div className="text-3xl mb-4">📊</div>
            <h3 className="text-xl font-semibold mb-2">SEO Analytics</h3>
            <p className="text-gray-600">
              Advanced SEO analysis and optimization recommendations.
            </p>
          </div>

          <div className="bg-white rounded-lg shadow-lg p-6">
            <div className="text-3xl mb-4">🔒</div>
            <h3 className="text-xl font-semibold mb-2">Secure & Reliable</h3>
            <p className="text-gray-600">
              Enterprise-grade security with rate limiting and authentication.
            </p>
          </div>
        </div>

        <div className="mt-12 text-center">
          <div className="bg-white rounded-lg shadow-lg p-8 max-w-4xl mx-auto">
            <h2 className="text-2xl font-bold mb-4">API Endpoints</h2>
            <div className="grid md:grid-cols-2 gap-4 text-left">
              <div>
                <h4 className="font-semibold text-green-600">✅ Available Endpoints:</h4>
                <ul className="mt-2 space-y-1 text-sm">
                  <li><code className="bg-gray-100 px-2 py-1 rounded">/health</code> - System health check</li>
                  <li><code className="bg-gray-100 px-2 py-1 rounded">/mcp-server/health</code> - MCP server status</li>
                  <li><code className="bg-gray-100 px-2 py-1 rounded">/wordpress/plugin/health</code> - Plugin API</li>
                  <li><code className="bg-gray-100 px-2 py-1 rounded">/docs</code> - API documentation</li>
                </ul>
              </div>
              <div>
                <h4 className="font-semibold text-blue-600">📦 WordPress Plugin:</h4>
                <ul className="mt-2 space-y-1 text-sm">
                  <li>✅ Complete plugin package ready</li>
                  <li>✅ Admin interface included</li>
                  <li>✅ MCP client integration</li>
                  <li>✅ Installation guide provided</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <footer className="mt-12 text-center text-gray-500">
          <p>&copy; 2024 SEOForge MCP Platform. Built with React, FastAPI, and WordPress integration.</p>
          <p className="mt-2">
            <a href="/docs" className="text-blue-600 hover:underline">API Documentation</a> | 
            <a href="/health" className="text-blue-600 hover:underline ml-2">System Status</a>
          </p>
        </footer>
      </div>
    </div>
  );
}

export default App;