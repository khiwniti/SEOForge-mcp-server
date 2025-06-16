// API Test Script
import fetch from 'node-fetch';

const API_URL = 'https://seo-forge-mcp-server-7ufjigzbd-getintheqs-projects.vercel.app';
const TEST_API_KEY = 'test-api-key'; // This should match what's in your vercel.json

async function testHealth() {
  try {
    const response = await fetch(`${API_URL}/health`, {
      headers: {
        'X-API-Key': TEST_API_KEY,
      }
    });
    const data = await response.json();
    console.log('Health Check Response:', data);
    return data;
  } catch (error) {
    console.error('Health Check Error:', error.message);
    return null;
  }
}

async function testMCPStatus() {
  try {
    const response = await fetch(`${API_URL}/mcp/status`, {
      headers: {
        'X-API-Key': TEST_API_KEY,
      }
    });
    const data = await response.json();
    console.log('MCP Status Response:', data);
    return data;
  } catch (error) {
    console.error('MCP Status Error:', error.message);
    return null;
  }
}

async function testContentGeneration() {
  try {
    const response = await fetch(`${API_URL}/api/blog-generator/generate`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-API-Key': TEST_API_KEY,
      },
      body: JSON.stringify({
        topic: 'AI Technology',
        keywords: ['artificial intelligence', 'machine learning'],
        length: 'short',
        tone: 'professional'
      })
    });
    const data = await response.json();
    console.log('Content Generation Response:', data);
    return data;
  } catch (error) {
    console.error('Content Generation Error:', error.message);
    return null;
  }
}

async function runTests() {
  console.log('Starting API Tests...');
  console.log('====================================');
  
  // Test health endpoint
  console.log('Testing Health Endpoint...');
  await testHealth();
  console.log('====================================');
  
  // Test MCP status endpoint
  console.log('Testing MCP Status Endpoint...');
  await testMCPStatus();
  console.log('====================================');
  
  // Test content generation endpoint
  console.log('Testing Content Generation Endpoint...');
  await testContentGeneration();
  console.log('====================================');
  
  console.log('API Tests Completed.');
}

runTests(); 