#!/usr/bin/env node

/**
 * API Compliance Test Script
 * Tests the SEOForge Express Backend against API_REQUIREMENTS.md specifications
 */

const axios = require('axios');

// Configuration
const BASE_URL = process.env.API_BASE_URL || 'http://localhost:8000';
const API_KEY = process.env.API_KEY || 'test-api-key-12345';

// Test configuration
const TESTS = {
  contentGeneration: {
    endpoint: '/api/v1/content/generate',
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${API_KEY}`
    },
    data: {
      keyword: 'WordPress SEO',
      language: 'en',
      type: 'blog_post',
      length: 'long',
      style: 'informative'
    }
  },
  imageGeneration: {
    endpoint: '/api/v1/images/generate',
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${API_KEY}`
    },
    data: {
      prompt: 'WordPress SEO optimization dashboard',
      style: 'photographic',
      size: '1024x1024',
      quality: 'high'
    }
  },
  health: {
    endpoint: '/api/v1/health',
    method: 'GET'
  },
  capabilities: {
    endpoint: '/api/v1/capabilities',
    method: 'GET'
  }
};

// Test results
const results = {
  passed: 0,
  failed: 0,
  tests: []
};

// Utility functions
function log(message, type = 'info') {
  const colors = {
    info: '\x1b[36m',
    success: '\x1b[32m',
    error: '\x1b[31m',
    warning: '\x1b[33m',
    reset: '\x1b[0m'
  };
  console.log(`${colors[type]}${message}${colors.reset}`);
}

function validateContentResponse(response) {
  const errors = [];
  const data = response.data;

  if (!data.success) {
    errors.push('Response success should be true');
  }

  if (!data.data) {
    errors.push('Response should have data object');
    return errors;
  }

  const content = data.data;

  // Required fields
  if (!content.title) errors.push('Missing title field');
  if (!content.content) errors.push('Missing content field');
  if (!content.excerpt) errors.push('Missing excerpt field');
  if (!content.meta_description) errors.push('Missing meta_description field');
  if (typeof content.word_count !== 'number') errors.push('word_count should be a number');
  if (!content.language) errors.push('Missing language field');
  if (!content.keyword) errors.push('Missing keyword field');
  if (!content.generated_at) errors.push('Missing generated_at field');

  // Content validation
  if (content.title && content.title.length > 60) {
    errors.push('Title should be 60 characters or less');
  }

  if (content.content && !content.content.includes('<h2>')) {
    errors.push('Content should include H2 headings');
  }

  if (content.word_count && content.word_count < 1500) {
    errors.push('Content should be at least 1500 words');
  }

  if (content.meta_description && content.meta_description.length > 160) {
    errors.push('Meta description should be 160 characters or less');
  }

  return errors;
}

function validateImageResponse(response) {
  const errors = [];
  const data = response.data;

  if (!data.success) {
    errors.push('Response success should be true');
  }

  if (!data.data) {
    errors.push('Response should have data object');
    return errors;
  }

  const image = data.data;

  // Required fields
  if (!image.image_url) errors.push('Missing image_url field');
  if (!image.prompt) errors.push('Missing prompt field');
  if (!image.style) errors.push('Missing style field');
  if (!image.size) errors.push('Missing size field');
  if (!image.generated_at) errors.push('Missing generated_at field');

  // URL validation
  if (image.image_url && !image.image_url.startsWith('http')) {
    errors.push('image_url should be a valid HTTP URL');
  }

  // Size format validation
  if (image.size && !image.size.match(/^\d+x\d+$/)) {
    errors.push('size should be in WIDTHxHEIGHT format');
  }

  return errors;
}

async function runTest(testName, testConfig) {
  log(`\n🧪 Testing ${testName}...`, 'info');
  
  try {
    const startTime = Date.now();
    
    const response = await axios({
      method: testConfig.method,
      url: `${BASE_URL}${testConfig.endpoint}`,
      headers: testConfig.headers || {},
      data: testConfig.data,
      timeout: 60000 // 60 second timeout
    });

    const endTime = Date.now();
    const responseTime = endTime - startTime;

    log(`✅ ${testName} - HTTP ${response.status} (${responseTime}ms)`, 'success');

    // Validate response format
    let validationErrors = [];
    
    if (testName === 'contentGeneration') {
      validationErrors = validateContentResponse(response);
      
      // Performance check
      if (responseTime > 30000) {
        validationErrors.push('Response time should be under 30 seconds');
      }
    } else if (testName === 'imageGeneration') {
      validationErrors = validateImageResponse(response);
      
      // Performance check
      if (responseTime > 15000) {
        validationErrors.push('Response time should be under 15 seconds');
      }
    }

    if (validationErrors.length > 0) {
      log(`❌ Validation errors:`, 'error');
      validationErrors.forEach(error => log(`   - ${error}`, 'error'));
      results.failed++;
      results.tests.push({
        name: testName,
        status: 'failed',
        errors: validationErrors,
        responseTime
      });
    } else {
      log(`✅ All validations passed`, 'success');
      results.passed++;
      results.tests.push({
        name: testName,
        status: 'passed',
        responseTime
      });
    }

    // Log sample response for debugging
    if (process.env.VERBOSE) {
      log(`Response sample:`, 'info');
      console.log(JSON.stringify(response.data, null, 2));
    }

  } catch (error) {
    log(`❌ ${testName} failed:`, 'error');
    
    if (error.response) {
      log(`   HTTP ${error.response.status}: ${error.response.statusText}`, 'error');
      if (error.response.data) {
        log(`   Response: ${JSON.stringify(error.response.data, null, 2)}`, 'error');
      }
    } else if (error.request) {
      log(`   Network error: ${error.message}`, 'error');
    } else {
      log(`   Error: ${error.message}`, 'error');
    }

    results.failed++;
    results.tests.push({
      name: testName,
      status: 'failed',
      error: error.message
    });
  }
}

async function testRateLimit() {
  log(`\n🧪 Testing Rate Limiting...`, 'info');
  
  try {
    // Make multiple rapid requests to test rate limiting
    const promises = [];
    for (let i = 0; i < 5; i++) {
      promises.push(
        axios({
          method: 'GET',
          url: `${BASE_URL}/api/v1/health`,
          headers: {
            'Authorization': `Bearer ${API_KEY}`
          },
          timeout: 10000
        })
      );
    }

    const responses = await Promise.allSettled(promises);
    const successCount = responses.filter(r => r.status === 'fulfilled').length;
    
    log(`✅ Rate limiting test - ${successCount}/5 requests succeeded`, 'success');
    
    results.passed++;
    results.tests.push({
      name: 'rateLimiting',
      status: 'passed',
      note: `${successCount}/5 requests succeeded`
    });

  } catch (error) {
    log(`❌ Rate limiting test failed: ${error.message}`, 'error');
    results.failed++;
    results.tests.push({
      name: 'rateLimiting',
      status: 'failed',
      error: error.message
    });
  }
}

async function testErrorHandling() {
  log(`\n🧪 Testing Error Handling...`, 'info');
  
  try {
    // Test invalid request
    await axios({
      method: 'POST',
      url: `${BASE_URL}/api/v1/content/generate`,
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${API_KEY}`
      },
      data: {
        // Missing required keyword field
        language: 'en'
      },
      timeout: 10000
    });

    log(`❌ Error handling test failed - should have returned 400`, 'error');
    results.failed++;

  } catch (error) {
    if (error.response && error.response.status === 400) {
      const errorData = error.response.data;
      if (errorData.success === false && errorData.error && errorData.error.code) {
        log(`✅ Error handling test passed - proper 400 response`, 'success');
        results.passed++;
        results.tests.push({
          name: 'errorHandling',
          status: 'passed'
        });
      } else {
        log(`❌ Error response format incorrect`, 'error');
        results.failed++;
        results.tests.push({
          name: 'errorHandling',
          status: 'failed',
          error: 'Incorrect error response format'
        });
      }
    } else {
      log(`❌ Error handling test failed: ${error.message}`, 'error');
      results.failed++;
      results.tests.push({
        name: 'errorHandling',
        status: 'failed',
        error: error.message
      });
    }
  }
}

async function main() {
  log('🚀 Starting API Compliance Tests', 'info');
  log(`Base URL: ${BASE_URL}`, 'info');
  log(`API Key: ${API_KEY.substring(0, 8)}...`, 'info');

  // Run all tests
  for (const [testName, testConfig] of Object.entries(TESTS)) {
    await runTest(testName, testConfig);
  }

  // Additional tests
  await testRateLimit();
  await testErrorHandling();

  // Summary
  log(`\n📊 Test Summary`, 'info');
  log(`✅ Passed: ${results.passed}`, 'success');
  log(`❌ Failed: ${results.failed}`, 'error');
  log(`📈 Success Rate: ${Math.round((results.passed / (results.passed + results.failed)) * 100)}%`, 'info');

  if (results.failed === 0) {
    log(`\n🎉 All tests passed! API is fully compliant with requirements.`, 'success');
    process.exit(0);
  } else {
    log(`\n⚠️  Some tests failed. Please check the implementation.`, 'warning');
    process.exit(1);
  }
}

// Handle uncaught errors
process.on('unhandledRejection', (error) => {
  log(`Unhandled error: ${error.message}`, 'error');
  process.exit(1);
});

// Run tests
main().catch(error => {
  log(`Test runner error: ${error.message}`, 'error');
  process.exit(1);
});