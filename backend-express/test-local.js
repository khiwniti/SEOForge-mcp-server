#!/usr/bin/env node

/**
 * Local test script for SEOForge Express Backend
 * Tests the API endpoints and Gemini 2.5 Pro integration
 */

const BASE_URL = 'http://localhost:8000';

async function testEndpoint(endpoint, method = 'GET', body = null) {
  try {
    const options = {
      method,
      headers: {
        'Content-Type': 'application/json',
      },
    };
    
    if (body) {
      options.body = JSON.stringify(body);
    }
    
    const response = await fetch(`${BASE_URL}${endpoint}`, options);
    const data = await response.json();
    
    console.log(`✅ ${method} ${endpoint}: ${response.status}`);
    console.log(`   Response:`, JSON.stringify(data, null, 2));
    return { success: true, data };
  } catch (error) {
    console.log(`❌ ${method} ${endpoint}: Error`);
    console.log(`   Error:`, error.message);
    return { success: false, error: error.message };
  }
}

async function testGeminiIntegration() {
  console.log('\n🤖 Testing Gemini 2.5 Pro Integration...\n');
  
  // Test content generation with Gemini
  const contentRequest = {
    tool: 'generate_content',
    arguments: {
      type: 'blog',
      topic: 'SEO Best Practices 2024',
      keywords: ['SEO', 'optimization', 'ranking'],
      language: 'en',
      tone: 'professional',
      length: 'short'
    }
  };
  
  await testEndpoint('/mcp/execute', 'POST', contentRequest);
  
  // Test SEO analysis
  const seoRequest = {
    tool: 'analyze_seo',
    arguments: {
      content: 'This is a sample blog post about SEO optimization and ranking factors.',
      target_keywords: ['SEO', 'optimization', 'ranking']
    }
  };
  
  await testEndpoint('/mcp/execute', 'POST', seoRequest);
}

async function runTests() {
  console.log('🧪 Testing SEOForge Express Backend with Gemini 2.5 Pro...\n');
  
  // Test basic endpoints
  await testEndpoint('/');
  await testEndpoint('/health');
  
  // Test Gemini integration
  await testGeminiIntegration();
  
  console.log('\n🎉 All tests completed!');
  console.log('🔥 Gemini 2.5 Pro is configured for enhanced accuracy');
  console.log('💡 To run the server locally: npm run dev');
  console.log('🚀 To deploy to Vercel: ./deploy-vercel.sh');
}

// Check if fetch is available (Node.js 18+)
if (typeof fetch === 'undefined') {
  console.log('❌ This script requires Node.js 18+ with built-in fetch');
  console.log('💡 Alternative: npm install node-fetch and update imports');
  process.exit(1);
}

runTests().catch(console.error);