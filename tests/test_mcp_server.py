#!/usr/bin/env python3
"""
Test script for SEOForge MCP Server
Tests the MCP server functionality locally before deployment
"""

import requests
import json
import time
import hashlib
import sys
from typing import Dict, Any

class MCPServerTester:
    def __init__(self, base_url: str = "http://localhost:8000", secret_key: str = "default-secret-key"):
        self.base_url = base_url
        self.secret_key = secret_key
        self.site_url = "https://test-wordpress-site.com"
        self.api_key = "test-api-key"
        
    def generate_nonce(self) -> tuple:
        """Generate nonce and timestamp for authentication"""
        timestamp = int(time.time())
        nonce = hashlib.sha256(f"{self.site_url}:{timestamp}:{self.secret_key}".encode()).hexdigest()
        return nonce, timestamp
    
    def get_headers(self) -> Dict[str, str]:
        """Get headers for WordPress authentication"""
        nonce, timestamp = self.generate_nonce()
        return {
            "Content-Type": "application/json",
            "X-WordPress-Key": self.api_key,
            "X-WordPress-Site": self.site_url,
            "X-WordPress-Nonce": nonce,
            "X-WordPress-Timestamp": str(timestamp)
        }
    
    def test_health_check(self) -> bool:
        """Test the health check endpoint"""
        print("Testing health check endpoint...")
        try:
            response = requests.get(f"{self.base_url}/health", timeout=10)
            if response.status_code == 200:
                data = response.json()
                print(f"✅ Health check passed: {data}")
                return True
            else:
                print(f"❌ Health check failed with status {response.status_code}")
                return False
        except Exception as e:
            print(f"❌ Health check error: {e}")
            return False
    
    def test_mcp_initialize(self) -> bool:
        """Test MCP initialize method"""
        print("Testing MCP initialize...")
        try:
            payload = {
                "jsonrpc": "2.0",
                "method": "initialize",
                "params": {},
                "id": 1
            }
            
            response = requests.post(
                f"{self.base_url}/mcp-server",
                headers=self.get_headers(),
                json=payload,
                timeout=10
            )
            
            if response.status_code == 200:
                data = response.json()
                if "result" in data and data["result"]["status"] == "initialized":
                    print(f"✅ MCP initialize passed: {data['result']}")
                    return True
                else:
                    print(f"❌ MCP initialize failed: {data}")
                    return False
            else:
                print(f"❌ MCP initialize failed with status {response.status_code}")
                return False
        except Exception as e:
            print(f"❌ MCP initialize error: {e}")
            return False
    
    def test_tools_list(self) -> bool:
        """Test tools/list method"""
        print("Testing tools/list...")
        try:
            payload = {
                "jsonrpc": "2.0",
                "method": "tools/list",
                "params": {},
                "id": 2
            }
            
            response = requests.post(
                f"{self.base_url}/mcp-server",
                headers=self.get_headers(),
                json=payload,
                timeout=10
            )
            
            if response.status_code == 200:
                data = response.json()
                if "result" in data and "tools" in data["result"]:
                    tools = data["result"]["tools"]
                    print(f"✅ Tools list passed: Found {len(tools)} tools")
                    for tool in tools:
                        print(f"   - {tool['name']}: {tool['description'].get('en', 'No description')}")
                    return True
                else:
                    print(f"❌ Tools list failed: {data}")
                    return False
            else:
                print(f"❌ Tools list failed with status {response.status_code}")
                return False
        except Exception as e:
            print(f"❌ Tools list error: {e}")
            return False
    
    def test_content_generation(self) -> bool:
        """Test content generation tool"""
        print("Testing content generation tool...")
        try:
            payload = {
                "jsonrpc": "2.0",
                "method": "tools/call",
                "params": {
                    "name": "content_generation",
                    "arguments": {
                        "topic": "Digital Marketing Strategies",
                        "content_type": "blog_post",
                        "keywords": ["SEO", "content marketing", "digital strategy"],
                        "industry": "technology",
                        "language": "en",
                        "word_count": 500
                    }
                },
                "id": 3
            }
            
            response = requests.post(
                f"{self.base_url}/mcp-server",
                headers=self.get_headers(),
                json=payload,
                timeout=15
            )
            
            if response.status_code == 200:
                data = response.json()
                if "result" in data and data["result"]["status"] == "success":
                    content = data["result"]["content"]
                    print(f"✅ Content generation passed")
                    print(f"   Generated content preview: {content[:100]}...")
                    return True
                else:
                    print(f"❌ Content generation failed: {data}")
                    return False
            else:
                print(f"❌ Content generation failed with status {response.status_code}")
                return False
        except Exception as e:
            print(f"❌ Content generation error: {e}")
            return False
    
    def test_seo_analysis(self) -> bool:
        """Test SEO analysis tool"""
        print("Testing SEO analysis tool...")
        try:
            payload = {
                "jsonrpc": "2.0",
                "method": "tools/call",
                "params": {
                    "name": "seo_analysis",
                    "arguments": {
                        "content": "This is a sample blog post about digital marketing. It discusses various strategies for improving online presence and driving traffic to websites.",
                        "target_keywords": ["digital marketing", "SEO", "online presence"],
                        "language": "en"
                    }
                },
                "id": 4
            }
            
            response = requests.post(
                f"{self.base_url}/mcp-server",
                headers=self.get_headers(),
                json=payload,
                timeout=15
            )
            
            if response.status_code == 200:
                data = response.json()
                if "result" in data and data["result"]["status"] == "success":
                    seo_score = data["result"]["seo_score"]
                    print(f"✅ SEO analysis passed")
                    print(f"   SEO Score: {seo_score}/100")
                    return True
                else:
                    print(f"❌ SEO analysis failed: {data}")
                    return False
            else:
                print(f"❌ SEO analysis failed with status {response.status_code}")
                return False
        except Exception as e:
            print(f"❌ SEO analysis error: {e}")
            return False
    
    def test_keyword_research(self) -> bool:
        """Test keyword research tool"""
        print("Testing keyword research tool...")
        try:
            payload = {
                "jsonrpc": "2.0",
                "method": "tools/call",
                "params": {
                    "name": "keyword_research",
                    "arguments": {
                        "seed_keyword": "digital marketing",
                        "industry": "technology",
                        "language": "en"
                    }
                },
                "id": 5
            }
            
            response = requests.post(
                f"{self.base_url}/mcp-server",
                headers=self.get_headers(),
                json=payload,
                timeout=15
            )
            
            if response.status_code == 200:
                data = response.json()
                if "result" in data and data["result"]["status"] == "success":
                    keywords = data["result"]["keywords"]
                    print(f"✅ Keyword research passed")
                    print(f"   Found {len(keywords)} keywords")
                    for kw in keywords[:3]:  # Show first 3
                        print(f"   - {kw['keyword']}: Volume {kw['volume']}, Difficulty {kw['difficulty']}")
                    return True
                else:
                    print(f"❌ Keyword research failed: {data}")
                    return False
            else:
                print(f"❌ Keyword research failed with status {response.status_code}")
                return False
        except Exception as e:
            print(f"❌ Keyword research error: {e}")
            return False
    
    def test_wordpress_plugin_api(self) -> bool:
        """Test WordPress plugin API endpoint"""
        print("Testing WordPress plugin API...")
        try:
            payload = {
                "action": "generate_content",
                "data": {
                    "topic": "Test Blog Post",
                    "content_type": "blog_post",
                    "keywords": ["test", "blog", "content"],
                    "industry": "technology",
                    "language": "en"
                },
                "site_url": self.site_url,
                "user_id": 1
            }
            
            response = requests.post(
                f"{self.base_url}/wordpress/plugin",
                headers=self.get_headers(),
                json=payload,
                timeout=15
            )
            
            if response.status_code == 200:
                data = response.json()
                if data.get("status") == "success":
                    print(f"✅ WordPress plugin API passed")
                    return True
                else:
                    print(f"❌ WordPress plugin API failed: {data}")
                    return False
            else:
                print(f"❌ WordPress plugin API failed with status {response.status_code}")
                return False
        except Exception as e:
            print(f"❌ WordPress plugin API error: {e}")
            return False
    
    def run_all_tests(self) -> bool:
        """Run all tests"""
        print("🚀 Starting SEOForge MCP Server Tests")
        print("=" * 50)
        
        tests = [
            self.test_health_check,
            self.test_mcp_initialize,
            self.test_tools_list,
            self.test_content_generation,
            self.test_seo_analysis,
            self.test_keyword_research,
            self.test_wordpress_plugin_api
        ]
        
        passed = 0
        total = len(tests)
        
        for test in tests:
            try:
                if test():
                    passed += 1
                print("-" * 30)
            except Exception as e:
                print(f"❌ Test failed with exception: {e}")
                print("-" * 30)
        
        print("=" * 50)
        print(f"📊 Test Results: {passed}/{total} tests passed")
        
        if passed == total:
            print("🎉 All tests passed! MCP server is working correctly.")
            return True
        else:
            print("⚠️  Some tests failed. Please check the server configuration.")
            return False

def main():
    """Main function"""
    import argparse
    
    parser = argparse.ArgumentParser(description="Test SEOForge MCP Server")
    parser.add_argument("--url", default="http://localhost:8000", help="Base URL of the MCP server")
    parser.add_argument("--secret", default="default-secret-key", help="Secret key for authentication")
    
    args = parser.parse_args()
    
    tester = MCPServerTester(args.url, args.secret)
    success = tester.run_all_tests()
    
    sys.exit(0 if success else 1)

if __name__ == "__main__":
    main()