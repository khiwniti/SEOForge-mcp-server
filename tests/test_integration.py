#!/usr/bin/env python3
"""
Test script to verify SEOForge MCP Server integration with Google Gemini API
"""

import requests
import json
import time

# Server configuration
SERVER_URL = "http://localhost:8083"

def test_blog_generator():
    """Test the blog generator API with Gemini"""
    print("🧪 Testing Blog Generator API...")
    
    url = f"{SERVER_URL}/routes/blog-generator/generate"
    payload = {
        "topic": "WordPress SEO Best Practices",
        "keywords": ["WordPress", "SEO", "optimization", "content"]
    }
    
    try:
        response = requests.post(url, json=payload, timeout=30)
        if response.status_code == 200:
            data = response.json()
            content = data.get("generated_text", "")
            print(f"✅ Blog Generator: SUCCESS")
            print(f"📝 Generated content length: {len(content)} characters")
            print(f"🎯 Content preview: {content[:200]}...")
            return True
        else:
            print(f"❌ Blog Generator: FAILED - Status {response.status_code}")
            print(f"Response: {response.text}")
            return False
    except Exception as e:
        print(f"❌ Blog Generator: ERROR - {e}")
        return False

def test_seo_analyzer():
    """Test the SEO analyzer API with Gemini"""
    print("\n🧪 Testing SEO Analyzer API...")
    
    url = f"{SERVER_URL}/routes/seo-analyzer/analyze"
    payload = {
        "content": "WordPress is a powerful content management system that helps create amazing websites. SEO optimization is crucial for WordPress sites to rank well in search engines. Content optimization and keyword research are essential for WordPress SEO success.",
        "keywords": ["WordPress", "SEO", "optimization"],
        "current_meta_title": "WordPress SEO Guide",
        "current_meta_description": "Learn WordPress SEO"
    }
    
    try:
        response = requests.post(url, json=payload, timeout=30)
        if response.status_code == 200:
            data = response.json()
            score = data.get("overall_seo_score", 0)
            keywords = data.get("keyword_density_results", [])
            meta = data.get("meta_tag_suggestions", {})
            recommendations = data.get("actionable_recommendations", [])
            
            print(f"✅ SEO Analyzer: SUCCESS")
            print(f"📊 SEO Score: {score}/100")
            print(f"🔍 Keywords analyzed: {len(keywords)}")
            print(f"🏷️ Meta suggestions: {bool(meta.get('suggested_title'))}")
            print(f"💡 Recommendations: {len(recommendations)}")
            return True
        else:
            print(f"❌ SEO Analyzer: FAILED - Status {response.status_code}")
            print(f"Response: {response.text}")
            return False
    except Exception as e:
        print(f"❌ SEO Analyzer: ERROR - {e}")
        return False

def test_server_health():
    """Test if the server is running"""
    print("🧪 Testing Server Health...")
    
    try:
        response = requests.get(f"{SERVER_URL}/", timeout=5)
        if response.status_code == 200:
            print("✅ Server: ONLINE")
            return True
        else:
            print(f"❌ Server: UNHEALTHY - Status {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Server: OFFLINE - {e}")
        return False

def main():
    """Run all integration tests"""
    print("🚀 SEOForge MCP Server Integration Test")
    print("=" * 50)
    
    # Test server health
    if not test_server_health():
        print("\n❌ Server is not running. Please start the server first.")
        return
    
    # Test APIs
    blog_success = test_blog_generator()
    seo_success = test_seo_analyzer()
    
    # Summary
    print("\n" + "=" * 50)
    print("📋 TEST SUMMARY")
    print("=" * 50)
    print(f"🌐 Server Health: ✅ ONLINE")
    print(f"📝 Blog Generator: {'✅ PASS' if blog_success else '❌ FAIL'}")
    print(f"📊 SEO Analyzer: {'✅ PASS' if seo_success else '❌ FAIL'}")
    
    if blog_success and seo_success:
        print("\n🎉 ALL TESTS PASSED! SEOForge MCP Server is ready for WordPress integration.")
        print("🔗 WordPress Plugin can now connect to: http://localhost:8083")
    else:
        print("\n⚠️ Some tests failed. Please check the server logs.")

if __name__ == "__main__":
    main()