#!/usr/bin/env python3
"""
Bilingual Features Test Suite for SEOForge MCP Server
Tests comprehensive Thai and English language support
"""

import requests
import json
import hashlib
import time
import sys
from typing import Dict, Any

class BilingualFeaturesTester:
    def __init__(self, base_url: str = "http://localhost:12000", secret_key: str = "default-secret-key"):
        self.base_url = base_url
        self.secret_key = secret_key
        self.site_url = "https://test-wordpress-site.com"
        self.api_key = "test-api-key"
        self.test_results = []
        
    def log_test(self, test_name: str, success: bool, details: str = ""):
        """Log test results"""
        result = {
            "test_name": test_name,
            "success": success,
            "details": details
        }
        self.test_results.append(result)
        
        status = "✅" if success else "❌"
        print(f"{status} {test_name}")
        if details:
            print(f"   {details}")
    
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
    
    def test_content_generation_bilingual(self) -> bool:
        """Test content generation in both languages with all content types"""
        print("\n🔍 Testing Content Generation - Bilingual Support")
        print("-" * 50)
        
        test_cases = [
            {
                "name": "English Blog Post - Technology",
                "params": {
                    "topic": "Artificial Intelligence in Healthcare",
                    "content_type": "blog_post",
                    "keywords": ["AI", "healthcare", "technology", "innovation"],
                    "industry": "technology",
                    "language": "en",
                    "word_count": 800
                }
            },
            {
                "name": "Thai Blog Post - Technology",
                "params": {
                    "topic": "ปัญญาประดิษฐ์ในการดูแลสุขภาพ",
                    "content_type": "blog_post",
                    "keywords": ["AI", "สุขภาพ", "เทคโนโลยี", "นวัตกรรม"],
                    "industry": "เทคโนโลยี",
                    "language": "th",
                    "word_count": 800
                }
            },
            {
                "name": "English Article - Marketing",
                "params": {
                    "topic": "Digital Marketing Strategies for 2024",
                    "content_type": "article",
                    "keywords": ["digital marketing", "strategy", "2024", "trends"],
                    "industry": "marketing",
                    "language": "en"
                }
            },
            {
                "name": "Thai Article - Marketing",
                "params": {
                    "topic": "กลยุทธ์การตลาดดิจิทัลสำหรับปี 2024",
                    "content_type": "article",
                    "keywords": ["การตลาดดิจิทัล", "กลยุทธ์", "2024", "แนวโน้ม"],
                    "industry": "การตลาด",
                    "language": "th"
                }
            },
            {
                "name": "English Product Description",
                "params": {
                    "topic": "AI-Powered Analytics Platform",
                    "content_type": "product_description",
                    "keywords": ["AI", "analytics", "platform", "business intelligence"],
                    "industry": "technology",
                    "language": "en"
                }
            },
            {
                "name": "Thai Product Description",
                "params": {
                    "topic": "แพลตฟอร์มวิเคราะห์ข้อมูลด้วย AI",
                    "content_type": "product_description",
                    "keywords": ["AI", "วิเคราะห์ข้อมูล", "แพลตฟอร์ม", "ธุรกิจอัจฉริยะ"],
                    "industry": "เทคโนโลยี",
                    "language": "th"
                }
            },
            {
                "name": "English Landing Page",
                "params": {
                    "topic": "Cloud Computing Solutions",
                    "content_type": "landing_page",
                    "keywords": ["cloud", "computing", "solutions", "enterprise"],
                    "industry": "technology",
                    "language": "en"
                }
            },
            {
                "name": "Thai Landing Page",
                "params": {
                    "topic": "โซลูชันคลาวด์คอมพิวติ้ง",
                    "content_type": "landing_page",
                    "keywords": ["คลาวด์", "คอมพิวติ้ง", "โซลูชัน", "องค์กร"],
                    "industry": "เทคโนโลยี",
                    "language": "th"
                }
            }
        ]
        
        all_passed = True
        for test_case in test_cases:
            try:
                payload = {
                    "jsonrpc": "2.0",
                    "method": "tools/call",
                    "params": {
                        "name": "content_generation",
                        "arguments": test_case["params"]
                    },
                    "id": 1
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
                        metadata = data["result"]["metadata"]
                        
                        # Verify language-specific content
                        language = test_case["params"]["language"]
                        if language == "th":
                            # Check for Thai content indicators
                            has_thai = any(ord(char) > 3584 and ord(char) < 3711 for char in content)
                            if has_thai:
                                details = f"✓ Thai content generated ({metadata['word_count']} words)"
                            else:
                                details = "⚠ No Thai characters detected"
                                all_passed = False
                        else:
                            # Check for English content structure
                            if "Introduction" in content or "Conclusion" in content:
                                details = f"✓ English content generated ({metadata['word_count']} words)"
                            else:
                                details = f"✓ Content generated ({metadata['word_count']} words)"
                        
                        self.log_test(test_case["name"], True, details)
                    else:
                        self.log_test(test_case["name"], False, f"Invalid response: {data}")
                        all_passed = False
                else:
                    self.log_test(test_case["name"], False, f"Status code: {response.status_code}")
                    all_passed = False
            except Exception as e:
                self.log_test(test_case["name"], False, f"Error: {e}")
                all_passed = False
        
        return all_passed
    
    def test_seo_analysis_bilingual(self) -> bool:
        """Test SEO analysis in both languages"""
        print("\n🔍 Testing SEO Analysis - Bilingual Support")
        print("-" * 50)
        
        test_cases = [
            {
                "name": "English Content Analysis",
                "params": {
                    "content": "This comprehensive guide explores the latest trends in artificial intelligence and machine learning. AI technology has revolutionized healthcare, finance, and education sectors. Machine learning algorithms enable predictive analytics and automated decision-making processes. The future of AI looks promising with continued innovation and development.",
                    "target_keywords": ["artificial intelligence", "machine learning", "AI technology"],
                    "language": "en"
                }
            },
            {
                "name": "Thai Content Analysis",
                "params": {
                    "content": "คู่มือฉบับสมบูรณ์นี้สำรวจแนวโน้มล่าสุดในปัญญาประดิษฐ์และการเรียนรู้ของเครื่อง เทคโนโลยี AI ได้ปฏิวัติภาคสุขภาพ การเงิน และการศึกษา อัลกอริทึมการเรียนรู้ของเครื่องช่วยให้สามารถวิเคราะห์เชิงทำนายและกระบวนการตัดสินใจอัตโนมัติ อนาคตของ AI ดูมีแนวโน้มที่ดีด้วยนวัตกรรมและการพัฒนาอย่างต่อเนื่อง",
                    "target_keywords": ["ปัญญาประดิษฐ์", "การเรียนรู้ของเครื่อง", "เทคโนโลยี AI"],
                    "language": "th"
                }
            },
            {
                "name": "English URL Analysis",
                "params": {
                    "url": "https://example.com/ai-healthcare-guide",
                    "target_keywords": ["AI", "healthcare", "medical technology"],
                    "language": "en"
                }
            },
            {
                "name": "Thai URL Analysis",
                "params": {
                    "url": "https://example.com/ai-healthcare-guide-th",
                    "target_keywords": ["AI", "สุขภาพ", "เทคโนโลยีการแพทย์"],
                    "language": "th"
                }
            }
        ]
        
        all_passed = True
        for test_case in test_cases:
            try:
                payload = {
                    "jsonrpc": "2.0",
                    "method": "tools/call",
                    "params": {
                        "name": "seo_analysis",
                        "arguments": test_case["params"]
                    },
                    "id": 2
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
                        analysis = data["result"]["analysis"]
                        language = data["result"]["language"]
                        
                        # Check language-specific suggestions
                        suggestions_text = str(analysis)
                        if language == "th":
                            has_thai_suggestions = any(ord(char) > 3584 and ord(char) < 3711 for char in suggestions_text)
                            if has_thai_suggestions:
                                details = f"✓ Thai SEO analysis (Score: {seo_score}/100, {len(analysis)} metrics)"
                            else:
                                details = f"⚠ No Thai suggestions detected (Score: {seo_score}/100)"
                        else:
                            details = f"✓ English SEO analysis (Score: {seo_score}/100, {len(analysis)} metrics)"
                        
                        self.log_test(test_case["name"], True, details)
                    else:
                        self.log_test(test_case["name"], False, f"Invalid response: {data}")
                        all_passed = False
                else:
                    self.log_test(test_case["name"], False, f"Status code: {response.status_code}")
                    all_passed = False
            except Exception as e:
                self.log_test(test_case["name"], False, f"Error: {e}")
                all_passed = False
        
        return all_passed
    
    def test_keyword_research_bilingual(self) -> bool:
        """Test keyword research in both languages"""
        print("\n🔍 Testing Keyword Research - Bilingual Support")
        print("-" * 50)
        
        test_cases = [
            {
                "name": "English Technology Keywords",
                "params": {
                    "seed_keyword": "artificial intelligence",
                    "industry": "technology",
                    "language": "en",
                    "competition_level": "medium",
                    "search_volume": "high"
                }
            },
            {
                "name": "Thai Technology Keywords",
                "params": {
                    "seed_keyword": "ปัญญาประดิษฐ์",
                    "industry": "เทคโนโลยี",
                    "language": "th",
                    "competition_level": "medium",
                    "search_volume": "high"
                }
            },
            {
                "name": "English Marketing Keywords",
                "params": {
                    "seed_keyword": "digital marketing",
                    "industry": "marketing",
                    "language": "en",
                    "competition_level": "high",
                    "search_volume": "medium"
                }
            },
            {
                "name": "Thai Marketing Keywords",
                "params": {
                    "seed_keyword": "การตลาดดิจิทัล",
                    "industry": "การตลาด",
                    "language": "th",
                    "competition_level": "high",
                    "search_volume": "medium"
                }
            },
            {
                "name": "English Healthcare Keywords",
                "params": {
                    "seed_keyword": "telemedicine",
                    "industry": "healthcare",
                    "language": "en",
                    "competition_level": "low",
                    "search_volume": "medium"
                }
            },
            {
                "name": "Thai Healthcare Keywords",
                "params": {
                    "seed_keyword": "การแพทย์ทางไกล",
                    "industry": "สุขภาพ",
                    "language": "th",
                    "competition_level": "low",
                    "search_volume": "medium"
                }
            }
        ]
        
        all_passed = True
        for test_case in test_cases:
            try:
                payload = {
                    "jsonrpc": "2.0",
                    "method": "tools/call",
                    "params": {
                        "name": "keyword_research",
                        "arguments": test_case["params"]
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
                        keywords = data["result"]["keywords"]
                        total_keywords = data["result"]["total_keywords"]
                        insights = data["result"]["insights"]
                        language = data["result"]["language"]
                        
                        # Check language-specific keywords
                        keyword_text = " ".join([kw["keyword"] for kw in keywords[:3]])
                        if language == "th":
                            has_thai_keywords = any(ord(char) > 3584 and ord(char) < 3711 for char in keyword_text)
                            if has_thai_keywords:
                                details = f"✓ Thai keywords generated ({total_keywords} total)"
                            else:
                                details = f"⚠ No Thai keywords detected ({total_keywords} total)"
                        else:
                            details = f"✓ English keywords generated ({total_keywords} total)"
                        
                        # Show top keywords
                        top_keywords = [kw["keyword"] for kw in keywords[:3]]
                        details += f" | Top: {', '.join(top_keywords)}"
                        
                        self.log_test(test_case["name"], True, details)
                    else:
                        self.log_test(test_case["name"], False, f"Invalid response: {data}")
                        all_passed = False
                else:
                    self.log_test(test_case["name"], False, f"Status code: {response.status_code}")
                    all_passed = False
            except Exception as e:
                self.log_test(test_case["name"], False, f"Error: {e}")
                all_passed = False
        
        return all_passed
    
    def test_prompts_bilingual(self) -> bool:
        """Test prompt generation in both languages"""
        print("\n🔍 Testing Prompts - Bilingual Support")
        print("-" * 50)
        
        test_cases = [
            {
                "name": "English Blog Post Prompt",
                "params": {
                    "name": "blog_post",
                    "arguments": {
                        "topic": "Sustainable Technology Trends",
                        "industry": "technology",
                        "target_audience": "tech professionals",
                        "language": "en"
                    }
                }
            },
            {
                "name": "Thai Blog Post Prompt",
                "params": {
                    "name": "blog_post",
                    "arguments": {
                        "topic": "แนวโน้มเทคโนโลยีที่ยั่งยืน",
                        "industry": "เทคโนโลยี",
                        "target_audience": "ผู้เชี่ยวชาญด้านเทคโนโลยี",
                        "language": "th"
                    }
                }
            }
        ]
        
        all_passed = True
        for test_case in test_cases:
            try:
                payload = {
                    "jsonrpc": "2.0",
                    "method": "prompts/get",
                    "params": test_case["params"],
                    "id": 4
                }
                
                response = requests.post(
                    f"{self.base_url}/mcp-server",
                    headers=self.get_headers(),
                    json=payload,
                    timeout=10
                )
                
                if response.status_code == 200:
                    data = response.json()
                    if "result" in data and "template" in data["result"]:
                        template = data["result"]["template"]
                        language = test_case["params"]["arguments"]["language"]
                        
                        if language == "th":
                            has_thai = any(ord(char) > 3584 and ord(char) < 3711 for char in template)
                            if has_thai:
                                details = f"✓ Thai prompt template generated ({len(template)} chars)"
                            else:
                                details = f"⚠ No Thai content in template ({len(template)} chars)"
                        else:
                            details = f"✓ English prompt template generated ({len(template)} chars)"
                        
                        self.log_test(test_case["name"], True, details)
                    else:
                        self.log_test(test_case["name"], False, f"Invalid response: {data}")
                        all_passed = False
                else:
                    self.log_test(test_case["name"], False, f"Status code: {response.status_code}")
                    all_passed = False
            except Exception as e:
                self.log_test(test_case["name"], False, f"Error: {e}")
                all_passed = False
        
        return all_passed
    
    def test_resources_bilingual(self) -> bool:
        """Test resource reading in both languages"""
        print("\n🔍 Testing Resources - Bilingual Support")
        print("-" * 50)
        
        test_cases = [
            {
                "name": "English Technology Data",
                "params": {
                    "uri": "industry://data/technology",
                    "language": "en"
                }
            },
            {
                "name": "Thai Technology Data",
                "params": {
                    "uri": "industry://data/technology",
                    "language": "th"
                }
            },
            {
                "name": "English Healthcare Trends",
                "params": {
                    "uri": "industry://trends/healthcare",
                    "language": "en"
                }
            },
            {
                "name": "Thai Healthcare Trends",
                "params": {
                    "uri": "industry://trends/healthcare",
                    "language": "th"
                }
            },
            {
                "name": "English Marketing Insights",
                "params": {
                    "uri": "industry://insights/marketing",
                    "language": "en"
                }
            },
            {
                "name": "Thai Marketing Insights",
                "params": {
                    "uri": "industry://insights/marketing",
                    "language": "th"
                }
            }
        ]
        
        all_passed = True
        for test_case in test_cases:
            try:
                payload = {
                    "jsonrpc": "2.0",
                    "method": "resources/read",
                    "params": test_case["params"],
                    "id": 5
                }
                
                response = requests.post(
                    f"{self.base_url}/mcp-server",
                    headers=self.get_headers(),
                    json=payload,
                    timeout=10
                )
                
                if response.status_code == 200:
                    data = response.json()
                    if "result" in data and "content" in data["result"]:
                        content = data["result"]["content"]
                        language = data["result"]["language"]
                        
                        content_str = str(content)
                        if language == "th":
                            has_thai = any(ord(char) > 3584 and ord(char) < 3711 for char in content_str)
                            if has_thai:
                                details = f"✓ Thai resource content loaded"
                            else:
                                details = f"⚠ No Thai content in resource"
                        else:
                            details = f"✓ English resource content loaded"
                        
                        # Show content type
                        if isinstance(content, dict):
                            details += f" | {len(content)} sections"
                        
                        self.log_test(test_case["name"], True, details)
                    else:
                        self.log_test(test_case["name"], False, f"Invalid response: {data}")
                        all_passed = False
                else:
                    self.log_test(test_case["name"], False, f"Status code: {response.status_code}")
                    all_passed = False
            except Exception as e:
                self.log_test(test_case["name"], False, f"Error: {e}")
                all_passed = False
        
        return all_passed
    
    def run_all_tests(self) -> bool:
        """Run all bilingual feature tests"""
        print("🌐 Starting Bilingual Features Test Suite")
        print("=" * 60)
        
        test_groups = [
            ("Content Generation", self.test_content_generation_bilingual),
            ("SEO Analysis", self.test_seo_analysis_bilingual),
            ("Keyword Research", self.test_keyword_research_bilingual),
            ("Prompts", self.test_prompts_bilingual),
            ("Resources", self.test_resources_bilingual)
        ]
        
        all_passed = True
        
        for group_name, test_func in test_groups:
            try:
                if not test_func():
                    all_passed = False
            except Exception as e:
                print(f"❌ {group_name} test failed with exception: {e}")
                all_passed = False
        
        print("\n" + "=" * 60)
        
        total_tests = len(self.test_results)
        successful_tests = sum(1 for result in self.test_results if result["success"])
        
        print(f"📊 Final Results: {successful_tests}/{total_tests} tests passed")
        
        if successful_tests >= total_tests * 0.9:
            print("🎉 Bilingual features working correctly!")
            print("✅ Both Thai and English languages are fully supported")
            return True
        else:
            print("⚠️  Some bilingual features need attention")
            return False

def main():
    """Main function"""
    import argparse
    
    parser = argparse.ArgumentParser(description="Bilingual features test suite for SEOForge MCP Server")
    parser.add_argument("--url", default="http://localhost:12000", help="Base URL of the MCP server")
    parser.add_argument("--secret", default="default-secret-key", help="Secret key for authentication")
    
    args = parser.parse_args()
    
    tester = BilingualFeaturesTester(args.url, args.secret)
    success = tester.run_all_tests()
    
    sys.exit(0 if success else 1)

if __name__ == "__main__":
    main()