#!/usr/bin/env python3
"""
Comprehensive test suite for Universal MCP Server
Tests all enhanced features including website intelligence, AI orchestration, and context management
"""

import asyncio
import aiohttp
import json
import time
from typing import Dict, List, Any
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class UniversalMCPTester:
    """Comprehensive tester for Universal MCP Server"""
    
    def __init__(self, base_url: str = "http://localhost:8083"):
        self.base_url = base_url
        self.session = None
        
    async def __aenter__(self):
        self.session = aiohttp.ClientSession()
        return self
    
    async def __aexit__(self, exc_type, exc_val, exc_tb):
        if self.session:
            await self.session.close()
    
    async def test_health_check(self) -> bool:
        """Test basic health check"""
        try:
            async with self.session.get(f"{self.base_url}/") as response:
                if response.status == 200:
                    data = await response.json()
                    logger.info(f"✅ Health check passed: {data.get('status')}")
                    return True
                else:
                    logger.error(f"❌ Health check failed: HTTP {response.status}")
                    return False
        except Exception as e:
            logger.error(f"❌ Health check error: {e}")
            return False
    
    async def test_universal_mcp_status(self) -> bool:
        """Test Universal MCP status endpoint"""
        try:
            async with self.session.get(f"{self.base_url}/universal-mcp/status") as response:
                if response.status == 200:
                    data = await response.json()
                    logger.info(f"✅ Universal MCP Status: {data.get('status')}")
                    logger.info(f"   Components: {data.get('components')}")
                    logger.info(f"   Capabilities: {len(data.get('capabilities', []))} features")
                    logger.info(f"   Industries: {len(data.get('supported_industries', []))} supported")
                    return True
                else:
                    logger.error(f"❌ Universal MCP status failed: HTTP {response.status}")
                    return False
        except Exception as e:
            logger.error(f"❌ Universal MCP status error: {e}")
            return False
    
    async def test_website_analysis(self, url: str = "https://example.com") -> bool:
        """Test website analysis functionality"""
        try:
            payload = {
                "url": url,
                "deep_analysis": True,
                "crawl_website": False
            }
            
            logger.info(f"🔍 Testing website analysis for: {url}")
            
            async with self.session.post(
                f"{self.base_url}/universal-mcp/analyze-website",
                json=payload
            ) as response:
                if response.status == 200:
                    data = await response.json()
                    results = data.get("results", {})
                    
                    logger.info(f"✅ Website analysis completed")
                    logger.info(f"   Title: {results.get('title', 'N/A')}")
                    logger.info(f"   Industry: {results.get('industry', 'N/A')}")
                    logger.info(f"   Language: {results.get('language', 'N/A')}")
                    
                    # Check content analysis
                    content_analysis = results.get("content_analysis", {})
                    if content_analysis:
                        logger.info(f"   Word count: {content_analysis.get('word_count', 0)}")
                        logger.info(f"   Quality score: {content_analysis.get('content_quality_score', 0):.2f}")
                    
                    # Check SEO analysis
                    seo_analysis = results.get("seo_analysis", {})
                    if seo_analysis:
                        title_opt = seo_analysis.get("title_optimization", {})
                        logger.info(f"   Title length: {title_opt.get('length', 0)} chars")
                    
                    return True
                else:
                    error_text = await response.text()
                    logger.error(f"❌ Website analysis failed: HTTP {response.status}")
                    logger.error(f"   Error: {error_text}")
                    return False
                    
        except Exception as e:
            logger.error(f"❌ Website analysis error: {e}")
            return False
    
    async def test_content_generation(self) -> bool:
        """Test AI-powered content generation"""
        try:
            payload = {
                "content_type": "blog_post",
                "topic": "The Future of AI in Digital Marketing",
                "keywords": ["AI marketing", "digital transformation", "automation"],
                "tone": "professional",
                "length": "medium",
                "industry": "technology",
                "language": "en"
            }
            
            logger.info("✍️ Testing content generation...")
            
            async with self.session.post(
                f"{self.base_url}/universal-mcp/generate-content",
                json=payload
            ) as response:
                if response.status == 200:
                    data = await response.json()
                    result = data.get("result", {})
                    
                    logger.info(f"✅ Content generation completed")
                    logger.info(f"   AI Model: {data.get('ai_model_used', 'N/A')}")
                    logger.info(f"   Processing time: {data.get('processing_time', 0):.2f}s")
                    logger.info(f"   Quality score: {result.get('quality_score', 0):.2f}")
                    logger.info(f"   Tokens used: {result.get('tokens_used', 0)}")
                    
                    content = result.get("content", "")
                    if content:
                        logger.info(f"   Content preview: {content[:100]}...")
                    
                    recommendations = data.get("recommendations", [])
                    if recommendations:
                        logger.info(f"   Recommendations: {len(recommendations)} provided")
                    
                    return True
                else:
                    error_text = await response.text()
                    logger.error(f"❌ Content generation failed: HTTP {response.status}")
                    logger.error(f"   Error: {error_text}")
                    return False
                    
        except Exception as e:
            logger.error(f"❌ Content generation error: {e}")
            return False
    
    async def test_seo_analysis(self, url: str = "https://example.com") -> bool:
        """Test SEO analysis functionality"""
        try:
            payload = {
                "url": url,
                "keywords": ["example", "demo", "test"],
                "include_recommendations": True
            }
            
            logger.info(f"📊 Testing SEO analysis for: {url}")
            
            async with self.session.post(
                f"{self.base_url}/universal-mcp/analyze-seo",
                json=payload
            ) as response:
                if response.status == 200:
                    data = await response.json()
                    result = data.get("result", {})
                    
                    logger.info(f"✅ SEO analysis completed")
                    logger.info(f"   AI Model: {data.get('ai_model_used', 'N/A')}")
                    logger.info(f"   Processing time: {data.get('processing_time', 0):.2f}s")
                    
                    website_analysis = data.get("website_analysis", {})
                    if website_analysis:
                        logger.info(f"   Website analyzed: {website_analysis.get('url', 'N/A')}")
                        logger.info(f"   Industry detected: {website_analysis.get('industry', 'N/A')}")
                    
                    recommendations = data.get("recommendations", [])
                    logger.info(f"   Recommendations: {len(recommendations)} provided")
                    for i, rec in enumerate(recommendations[:3], 1):
                        logger.info(f"     {i}. {rec}")
                    
                    return True
                else:
                    error_text = await response.text()
                    logger.error(f"❌ SEO analysis failed: HTTP {response.status}")
                    logger.error(f"   Error: {error_text}")
                    return False
                    
        except Exception as e:
            logger.error(f"❌ SEO analysis error: {e}")
            return False
    
    async def test_universal_mcp_process(self) -> bool:
        """Test the main Universal MCP processing endpoint"""
        try:
            payload = {
                "task_type": "content_generation",
                "prompt": "Create a comprehensive guide about sustainable business practices for small companies",
                "context": {
                    "target_audience": "small business owners",
                    "focus_areas": ["sustainability", "cost-effectiveness", "implementation"]
                },
                "industry": "general",
                "language": "en",
                "use_website_context": False,
                "deep_analysis": False
            }
            
            logger.info("🚀 Testing Universal MCP processing...")
            
            async with self.session.post(
                f"{self.base_url}/universal-mcp/process",
                json=payload
            ) as response:
                if response.status == 200:
                    data = await response.json()
                    
                    logger.info(f"✅ Universal MCP processing completed")
                    logger.info(f"   Success: {data.get('success')}")
                    logger.info(f"   Task type: {data.get('task_type')}")
                    logger.info(f"   AI Model: {data.get('ai_model_used', 'N/A')}")
                    logger.info(f"   Processing time: {data.get('processing_time', 0):.2f}s")
                    logger.info(f"   Context used: {len(data.get('context_used', []))} entries")
                    
                    result = data.get("result", {})
                    if result:
                        logger.info(f"   Quality score: {result.get('quality_score', 0):.2f}")
                        logger.info(f"   Tokens used: {result.get('tokens_used', 0)}")
                    
                    recommendations = data.get("recommendations", [])
                    logger.info(f"   Recommendations: {len(recommendations)} provided")
                    
                    return True
                else:
                    error_text = await response.text()
                    logger.error(f"❌ Universal MCP processing failed: HTTP {response.status}")
                    logger.error(f"   Error: {error_text}")
                    return False
                    
        except Exception as e:
            logger.error(f"❌ Universal MCP processing error: {e}")
            return False
    
    async def test_industry_analysis(self, industry: str = "technology") -> bool:
        """Test industry analysis functionality"""
        try:
            logger.info(f"🏭 Testing industry analysis for: {industry}")
            
            async with self.session.get(
                f"{self.base_url}/universal-mcp/industry-analysis/{industry}"
            ) as response:
                if response.status == 200:
                    data = await response.json()
                    analysis = data.get("analysis", {})
                    
                    logger.info(f"✅ Industry analysis completed")
                    logger.info(f"   Industry: {data.get('industry')}")
                    
                    if "knowledge_base" in analysis:
                        kb = analysis["knowledge_base"]
                        logger.info(f"   Key metrics: {kb.get('key_metrics', [])}")
                        logger.info(f"   Content types: {kb.get('content_types', [])}")
                    
                    if "market_data" in analysis:
                        md = analysis["market_data"]
                        logger.info(f"   Market size: {md.get('market_size', 'N/A')}")
                        logger.info(f"   Growth rate: {md.get('growth_rate', 'N/A')}")
                    
                    return True
                else:
                    error_text = await response.text()
                    logger.error(f"❌ Industry analysis failed: HTTP {response.status}")
                    logger.error(f"   Error: {error_text}")
                    return False
                    
        except Exception as e:
            logger.error(f"❌ Industry analysis error: {e}")
            return False
    
    async def test_context_search(self) -> bool:
        """Test context search functionality"""
        try:
            logger.info("🔍 Testing context search...")
            
            params = {
                "query": "technology industry",
                "max_results": 5
            }
            
            async with self.session.get(
                f"{self.base_url}/universal-mcp/context/search",
                params=params
            ) as response:
                if response.status == 200:
                    data = await response.json()
                    
                    logger.info(f"✅ Context search completed")
                    logger.info(f"   Query: {data.get('query')}")
                    logger.info(f"   Results: {data.get('total_results', 0)} found")
                    
                    results = data.get("results", [])
                    for i, result in enumerate(results[:3], 1):
                        logger.info(f"     {i}. Type: {result.get('type')}, Score: {result.get('relevance_score', 0):.2f}")
                    
                    return True
                else:
                    error_text = await response.text()
                    logger.error(f"❌ Context search failed: HTTP {response.status}")
                    logger.error(f"   Error: {error_text}")
                    return False
                    
        except Exception as e:
            logger.error(f"❌ Context search error: {e}")
            return False
    
    async def test_performance_stats(self) -> bool:
        """Test performance statistics endpoint"""
        try:
            logger.info("📈 Testing performance statistics...")
            
            async with self.session.get(
                f"{self.base_url}/universal-mcp/performance/stats"
            ) as response:
                if response.status == 200:
                    data = await response.json()
                    stats = data.get("performance_stats", {})
                    
                    logger.info(f"✅ Performance stats retrieved")
                    logger.info(f"   Total models: {stats.get('total_models', 0)}")
                    logger.info(f"   Active requests: {stats.get('active_requests', 0)}")
                    
                    model_perf = stats.get("model_performance", {})
                    if model_perf:
                        logger.info(f"   Model performance entries: {len(model_perf)}")
                        for key, perf in list(model_perf.items())[:3]:
                            logger.info(f"     {key}: {perf.get('total_requests', 0)} requests, {perf.get('avg_quality_score', 0):.2f} quality")
                    
                    return True
                else:
                    error_text = await response.text()
                    logger.error(f"❌ Performance stats failed: HTTP {response.status}")
                    logger.error(f"   Error: {error_text}")
                    return False
                    
        except Exception as e:
            logger.error(f"❌ Performance stats error: {e}")
            return False
    
    async def run_comprehensive_test(self) -> Dict[str, bool]:
        """Run all tests and return results"""
        logger.info("🚀 Starting Universal MCP Server Comprehensive Test Suite")
        logger.info("=" * 60)
        
        test_results = {}
        
        # Basic connectivity tests
        test_results["health_check"] = await self.test_health_check()
        test_results["universal_mcp_status"] = await self.test_universal_mcp_status()
        
        # Core functionality tests
        test_results["website_analysis"] = await self.test_website_analysis()
        test_results["content_generation"] = await self.test_content_generation()
        test_results["seo_analysis"] = await self.test_seo_analysis()
        test_results["universal_mcp_process"] = await self.test_universal_mcp_process()
        test_results["industry_analysis"] = await self.test_industry_analysis()
        test_results["context_search"] = await self.test_context_search()
        test_results["performance_stats"] = await self.test_performance_stats()
        
        # Summary
        logger.info("=" * 60)
        logger.info("📊 TEST RESULTS SUMMARY")
        logger.info("=" * 60)
        
        passed = sum(1 for result in test_results.values() if result)
        total = len(test_results)
        
        for test_name, result in test_results.items():
            status = "✅ PASS" if result else "❌ FAIL"
            logger.info(f"{status} {test_name.replace('_', ' ').title()}")
        
        logger.info("=" * 60)
        logger.info(f"🎯 OVERALL RESULT: {passed}/{total} tests passed ({passed/total*100:.1f}%)")
        
        if passed == total:
            logger.info("🎉 ALL TESTS PASSED! Universal MCP Server is fully functional.")
        else:
            logger.warning(f"⚠️  {total - passed} tests failed. Please check the logs above.")
        
        return test_results

async def main():
    """Main test function"""
    import argparse
    
    parser = argparse.ArgumentParser(description="Test Universal MCP Server")
    parser.add_argument("--url", default="http://localhost:8083", help="Base URL of the server")
    parser.add_argument("--test", help="Run specific test (e.g., 'website_analysis')")
    args = parser.parse_args()
    
    async with UniversalMCPTester(args.url) as tester:
        if args.test:
            # Run specific test
            test_method = getattr(tester, f"test_{args.test}", None)
            if test_method:
                result = await test_method()
                print(f"Test {args.test}: {'PASS' if result else 'FAIL'}")
            else:
                print(f"Test '{args.test}' not found")
        else:
            # Run comprehensive test suite
            await tester.run_comprehensive_test()

if __name__ == "__main__":
    asyncio.run(main())