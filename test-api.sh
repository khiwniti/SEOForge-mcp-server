#!/bin/bash
# API Test Script using curl

API_URL="https://seo-forge-mcp-server-7ufjigzbd-getintheqs-projects.vercel.app"
API_KEY="test-api-key"

echo "Starting API Tests..."
echo "===================================="

# Test Health Endpoint
echo "Testing Health Endpoint..."
curl -s -X GET "$API_URL/health" -H "X-API-Key: $API_KEY" | json_pp
echo "===================================="

# Test MCP Status Endpoint
echo "Testing MCP Status Endpoint..."
curl -s -X GET "$API_URL/mcp/status" -H "X-API-Key: $API_KEY" | json_pp
echo "===================================="

# Test Content Generation Endpoint
echo "Testing Content Generation Endpoint..."
curl -s -X POST "$API_URL/api/blog-generator/generate" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: $API_KEY" \
  -d '{
    "topic": "AI Technology",
    "keywords": ["artificial intelligence", "machine learning"],
    "length": "short",
    "tone": "professional"
  }' | json_pp
echo "===================================="

echo "API Tests Completed." 