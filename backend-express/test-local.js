#!/usr/bin/env node

/**
 * Local test script for SEOForge Express Backend
 * Tests the API endpoints locally before deployment
 */

import fetch from 'node-fetch';

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

async function runTests() {
  console.log('🧪 Testing SEOForge Express Backend...\n');
  
  // Test basic endpoints
  await testEndpoint('/');
  await testEndpoint('/health');
  
  console.log('\n🎉 Local tests completed!');
  console.log('💡 To run the server locally: npm run dev');
  console.log('🚀 To deploy to Vercel: ./deploy-vercel.sh');
}

runTests();