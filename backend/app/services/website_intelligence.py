"""
Website Intelligence Service
Comprehensive website analysis, crawling, and competitive intelligence
"""

import asyncio
import aiohttp
import json
import re
import time
from datetime import datetime, timezone, timedelta
from typing import Dict, List, Any, Optional, Set, Tuple
from urllib.parse import urljoin, urlparse, parse_qs
from dataclasses import dataclass, asdict
import logging

from bs4 import BeautifulSoup, Comment
import tldextract
from textstat import flesch_reading_ease, flesch_kincaid_grade
import whois
import dns.resolver

logger = logging.getLogger(__name__)

@dataclass
class WebsiteProfile:
    url: str
    domain: str
    title: str
    description: str
    industry: str
    language: str
    content_analysis: Dict[str, Any]
    technical_analysis: Dict[str, Any]
    seo_analysis: Dict[str, Any]
    competitive_analysis: Dict[str, Any]
    social_presence: Dict[str, Any]
    performance_metrics: Dict[str, Any]
    created_at: datetime
    last_updated: datetime

@dataclass
class ContentAnalysis:
    word_count: int
    paragraph_count: int
    heading_structure: Dict[str, int]
    keyword_density: Dict[str, float]
    readability_score: float
    content_topics: List[str]
    content_quality_score: float
    duplicate_content_percentage: float

@dataclass
class TechnicalAnalysis:
    page_load_time: float
    page_size: int
    html_validation_errors: List[str]
    css_files: List[str]
    js_files: List[str]
    image_optimization: Dict[str, Any]
    mobile_friendly: bool
    https_enabled: bool
    structured_data: List[Dict[str, Any]]

@dataclass
class SEOAnalysis:
    title_optimization: Dict[str, Any]
    meta_description_optimization: Dict[str, Any]
    heading_optimization: Dict[str, Any]
    internal_linking: Dict[str, Any]
    external_linking: Dict[str, Any]
    image_seo: Dict[str, Any]
    url_structure: Dict[str, Any]
    canonical_tags: List[str]
    robots_txt_analysis: Dict[str, Any]
    sitemap_analysis: Dict[str, Any]

class WebsiteIntelligenceService:
    """
    Comprehensive website analysis and intelligence gathering service
    """
    
    def __init__(self, max_concurrent_requests: int = 10):
        self.max_concurrent_requests = max_concurrent_requests
        self.session = None
        self.user_agent = "SEOForge-MCP-Bot/1.0 (+https://seoforge.ai/bot)"
        
        # Common industry keywords for classification
        self.industry_keywords = {
            "ecommerce": ["shop", "buy", "cart", "checkout", "product", "store", "sale", "price"],
            "healthcare": ["health", "medical", "doctor", "hospital", "treatment", "patient", "clinic"],
            "finance": ["bank", "loan", "investment", "insurance", "financial", "credit", "mortgage"],
            "technology": ["software", "tech", "app", "digital", "cloud", "AI", "development"],
            "education": ["school", "university", "course", "learn", "education", "student", "training"],
            "real_estate": ["property", "real estate", "home", "house", "rent", "buy", "apartment"],
            "automotive": ["car", "auto", "vehicle", "dealer", "automotive", "repair", "parts"],
            "travel": ["travel", "hotel", "flight", "vacation", "tourism", "booking", "trip"],
            "food": ["restaurant", "food", "recipe", "cooking", "menu", "dining", "catering"],
            "legal": ["law", "lawyer", "attorney", "legal", "court", "justice", "litigation"]
        }
    
    async def initialize(self):
        """Initialize the service with HTTP session"""
        connector = aiohttp.TCPConnector(limit=self.max_concurrent_requests)
        timeout = aiohttp.ClientTimeout(total=30, connect=10)
        
        self.session = aiohttp.ClientSession(
            connector=connector,
            timeout=timeout,
            headers={"User-Agent": self.user_agent}
        )
    
    async def analyze_website(self, url: str, deep_analysis: bool = True) -> WebsiteProfile:
        """
        Perform comprehensive website analysis
        """
        try:
            start_time = time.time()
            
            # Normalize URL
            if not url.startswith(('http://', 'https://')):
                url = 'https://' + url
            
            parsed_url = urlparse(url)
            domain = parsed_url.netloc
            
            logger.info(f"Starting website analysis for: {url}")
            
            # Fetch main page content
            html_content, response_info = await self._fetch_page_content(url)
            
            if not html_content:
                raise Exception("Failed to fetch website content")
            
            soup = BeautifulSoup(html_content, 'html.parser')
            
            # Basic information extraction
            title = self._extract_title(soup)
            description = self._extract_meta_description(soup)
            language = self._detect_language(soup, html_content)
            
            # Industry classification
            industry = await self._classify_industry(soup, html_content, url)
            
            # Parallel analysis tasks
            tasks = []
            
            if deep_analysis:
                tasks.extend([
                    self._analyze_content(soup, html_content),
                    self._analyze_technical_aspects(soup, html_content, response_info),
                    self._analyze_seo_factors(soup, url),
                    self._analyze_competitive_landscape(domain, industry),
                    self._analyze_social_presence(soup, domain),
                    self._analyze_performance_metrics(url, response_info)
                ])
            else:
                # Basic analysis only
                tasks.extend([
                    self._analyze_content(soup, html_content),
                    self._analyze_seo_factors(soup, url)
                ])
            
            # Execute analysis tasks
            results = await asyncio.gather(*tasks, return_exceptions=True)
            
            # Process results
            content_analysis = results[0] if not isinstance(results[0], Exception) else {}
            technical_analysis = results[1] if len(results) > 1 and not isinstance(results[1], Exception) else {}
            seo_analysis = results[2] if len(results) > 2 and not isinstance(results[2], Exception) else {}
            competitive_analysis = results[3] if len(results) > 3 and not isinstance(results[3], Exception) else {}
            social_presence = results[4] if len(results) > 4 and not isinstance(results[4], Exception) else {}
            performance_metrics = results[5] if len(results) > 5 and not isinstance(results[5], Exception) else {}
            
            # Create website profile
            profile = WebsiteProfile(
                url=url,
                domain=domain,
                title=title,
                description=description,
                industry=industry,
                language=language,
                content_analysis=content_analysis,
                technical_analysis=technical_analysis,
                seo_analysis=seo_analysis,
                competitive_analysis=competitive_analysis,
                social_presence=social_presence,
                performance_metrics=performance_metrics,
                created_at=datetime.now(timezone.utc),
                last_updated=datetime.now(timezone.utc)
            )
            
            analysis_time = time.time() - start_time
            logger.info(f"Website analysis completed in {analysis_time:.2f} seconds")
            
            return profile
            
        except Exception as e:
            logger.error(f"Website analysis failed for {url}: {e}")
            raise
    
    async def _fetch_page_content(self, url: str) -> Tuple[str, Dict[str, Any]]:
        """Fetch page content and response information"""
        try:
            start_time = time.time()
            
            async with self.session.get(url, allow_redirects=True) as response:
                response_time = time.time() - start_time
                
                if response.status == 200:
                    content = await response.text()
                    
                    response_info = {
                        "status_code": response.status,
                        "response_time": response_time,
                        "content_length": len(content),
                        "content_type": response.headers.get("content-type", ""),
                        "server": response.headers.get("server", ""),
                        "final_url": str(response.url)
                    }
                    
                    return content, response_info
                else:
                    logger.warning(f"HTTP {response.status} for {url}")
                    return None, {"status_code": response.status}
                    
        except Exception as e:
            logger.error(f"Failed to fetch {url}: {e}")
            return None, {"error": str(e)}
    
    def _extract_title(self, soup: BeautifulSoup) -> str:
        """Extract page title"""
        title_tag = soup.find('title')
        return title_tag.get_text().strip() if title_tag else ""
    
    def _extract_meta_description(self, soup: BeautifulSoup) -> str:
        """Extract meta description"""
        meta_desc = soup.find('meta', attrs={'name': 'description'})
        if meta_desc:
            return meta_desc.get('content', '').strip()
        
        # Try property="description" as fallback
        meta_desc = soup.find('meta', attrs={'property': 'description'})
        return meta_desc.get('content', '').strip() if meta_desc else ""
    
    def _detect_language(self, soup: BeautifulSoup, content: str) -> str:
        """Detect page language"""
        # Check html lang attribute
        html_tag = soup.find('html')
        if html_tag and html_tag.get('lang'):
            return html_tag.get('lang')[:2]  # Get first 2 characters
        
        # Check meta language tags
        meta_lang = soup.find('meta', attrs={'http-equiv': 'content-language'})
        if meta_lang:
            return meta_lang.get('content', 'en')[:2]
        
        # Simple heuristic based on common words
        thai_words = ['และ', 'ที่', 'ใน', 'เป็น', 'มี', 'จาก', 'ของ', 'ได้', 'ไม่', 'ให้']
        thai_count = sum(1 for word in thai_words if word in content)
        
        if thai_count > 3:
            return 'th'
        
        return 'en'  # Default to English
    
    async def _classify_industry(self, soup: BeautifulSoup, content: str, url: str) -> str:
        """Classify website industry based on content analysis"""
        content_text = soup.get_text().lower()
        url_text = url.lower()
        
        industry_scores = {}
        
        for industry, keywords in self.industry_keywords.items():
            score = 0
            for keyword in keywords:
                # Count occurrences in content
                score += content_text.count(keyword) * 2
                # Count occurrences in URL (higher weight)
                score += url_text.count(keyword) * 5
            
            industry_scores[industry] = score
        
        # Find industry with highest score
        if industry_scores:
            best_industry = max(industry_scores, key=industry_scores.get)
            if industry_scores[best_industry] > 0:
                return best_industry
        
        return "general"
    
    async def _analyze_content(self, soup: BeautifulSoup, html_content: str) -> Dict[str, Any]:
        """Analyze content quality and structure"""
        try:
            # Extract text content
            text_content = soup.get_text()
            words = text_content.split()
            
            # Count elements
            paragraphs = soup.find_all('p')
            headings = {}
            for i in range(1, 7):
                headings[f'h{i}'] = len(soup.find_all(f'h{i}'))
            
            # Keyword density analysis
            word_freq = {}
            for word in words:
                word = re.sub(r'[^\w]', '', word.lower())
                if len(word) > 3:  # Only count words longer than 3 characters
                    word_freq[word] = word_freq.get(word, 0) + 1
            
            # Top keywords by frequency
            total_words = len(words)
            keyword_density = {}
            for word, count in sorted(word_freq.items(), key=lambda x: x[1], reverse=True)[:20]:
                keyword_density[word] = round((count / total_words) * 100, 2)
            
            # Readability analysis
            try:
                readability_score = flesch_reading_ease(text_content)
            except:
                readability_score = 0
            
            # Content topics extraction (simple approach)
            content_topics = list(keyword_density.keys())[:10]
            
            # Content quality score (simplified)
            quality_score = 0.5  # Base score
            if len(words) > 300:
                quality_score += 0.2
            if len(paragraphs) > 3:
                quality_score += 0.1
            if sum(headings.values()) > 0:
                quality_score += 0.1
            if readability_score > 60:
                quality_score += 0.1
            
            return {
                "word_count": len(words),
                "paragraph_count": len(paragraphs),
                "heading_structure": headings,
                "keyword_density": keyword_density,
                "readability_score": readability_score,
                "content_topics": content_topics,
                "content_quality_score": min(quality_score, 1.0),
                "duplicate_content_percentage": 0  # Would need more sophisticated analysis
            }
            
        except Exception as e:
            logger.error(f"Content analysis failed: {e}")
            return {}
    
    async def _analyze_technical_aspects(self, soup: BeautifulSoup, html_content: str, response_info: Dict[str, Any]) -> Dict[str, Any]:
        """Analyze technical SEO aspects"""
        try:
            # CSS and JS files
            css_files = [link.get('href') for link in soup.find_all('link', rel='stylesheet') if link.get('href')]
            js_files = [script.get('src') for script in soup.find_all('script', src=True)]
            
            # Images analysis
            images = soup.find_all('img')
            images_without_alt = [img for img in images if not img.get('alt')]
            
            # Mobile viewport
            viewport_meta = soup.find('meta', attrs={'name': 'viewport'})
            mobile_friendly = viewport_meta is not None
            
            # HTTPS check
            https_enabled = response_info.get('final_url', '').startswith('https://')
            
            # Structured data
            structured_data = []
            json_ld_scripts = soup.find_all('script', type='application/ld+json')
            for script in json_ld_scripts:
                try:
                    data = json.loads(script.string)
                    structured_data.append(data)
                except:
                    pass
            
            # HTML validation (basic checks)
            validation_errors = []
            if not soup.find('title'):
                validation_errors.append("Missing title tag")
            if not soup.find('meta', attrs={'name': 'description'}):
                validation_errors.append("Missing meta description")
            if not soup.find('h1'):
                validation_errors.append("Missing H1 tag")
            
            return {
                "page_load_time": response_info.get('response_time', 0),
                "page_size": response_info.get('content_length', 0),
                "html_validation_errors": validation_errors,
                "css_files": css_files,
                "js_files": js_files,
                "image_optimization": {
                    "total_images": len(images),
                    "images_without_alt": len(images_without_alt),
                    "alt_text_coverage": round((len(images) - len(images_without_alt)) / max(len(images), 1) * 100, 2)
                },
                "mobile_friendly": mobile_friendly,
                "https_enabled": https_enabled,
                "structured_data": structured_data
            }
            
        except Exception as e:
            logger.error(f"Technical analysis failed: {e}")
            return {}
    
    async def _analyze_seo_factors(self, soup: BeautifulSoup, url: str) -> Dict[str, Any]:
        """Analyze SEO factors"""
        try:
            # Title optimization
            title = soup.find('title')
            title_text = title.get_text() if title else ""
            
            title_optimization = {
                "length": len(title_text),
                "optimal_length": 30 <= len(title_text) <= 60,
                "contains_keywords": True,  # Would need keyword analysis
                "title_text": title_text
            }
            
            # Meta description optimization
            meta_desc = soup.find('meta', attrs={'name': 'description'})
            desc_text = meta_desc.get('content', '') if meta_desc else ""
            
            meta_description_optimization = {
                "length": len(desc_text),
                "optimal_length": 120 <= len(desc_text) <= 160,
                "exists": bool(desc_text),
                "description_text": desc_text
            }
            
            # Heading optimization
            headings = {}
            heading_optimization = {"structure_score": 0}
            
            for i in range(1, 7):
                h_tags = soup.find_all(f'h{i}')
                headings[f'h{i}'] = len(h_tags)
                if i == 1 and h_tags:
                    heading_optimization["h1_exists"] = True
                    heading_optimization["h1_text"] = h_tags[0].get_text()
                    heading_optimization["structure_score"] += 0.3
            
            # Internal and external links
            all_links = soup.find_all('a', href=True)
            internal_links = []
            external_links = []
            
            parsed_url = urlparse(url)
            base_domain = parsed_url.netloc
            
            for link in all_links:
                href = link.get('href')
                if href:
                    if href.startswith('http'):
                        link_domain = urlparse(href).netloc
                        if link_domain == base_domain:
                            internal_links.append(href)
                        else:
                            external_links.append(href)
                    elif href.startswith('/'):
                        internal_links.append(href)
            
            # Canonical tags
            canonical_tags = [link.get('href') for link in soup.find_all('link', rel='canonical')]
            
            return {
                "title_optimization": title_optimization,
                "meta_description_optimization": meta_description_optimization,
                "heading_optimization": heading_optimization,
                "internal_linking": {
                    "count": len(internal_links),
                    "links": internal_links[:10]  # First 10 for analysis
                },
                "external_linking": {
                    "count": len(external_links),
                    "links": external_links[:10]  # First 10 for analysis
                },
                "image_seo": {
                    "images_with_alt": len([img for img in soup.find_all('img') if img.get('alt')]),
                    "total_images": len(soup.find_all('img'))
                },
                "url_structure": {
                    "url": url,
                    "length": len(url),
                    "contains_keywords": True,  # Would need keyword analysis
                    "uses_hyphens": '-' in url
                },
                "canonical_tags": canonical_tags
            }
            
        except Exception as e:
            logger.error(f"SEO analysis failed: {e}")
            return {}
    
    async def _analyze_competitive_landscape(self, domain: str, industry: str) -> Dict[str, Any]:
        """Analyze competitive landscape"""
        try:
            # This would typically involve:
            # 1. Identifying competitors through various APIs
            # 2. Analyzing their SEO strategies
            # 3. Comparing content strategies
            # 4. Market positioning analysis
            
            # Simplified competitive analysis
            return {
                "industry": industry,
                "estimated_competitors": 10,
                "market_position": "analyzing",
                "competitive_keywords": [],
                "content_gaps": [],
                "opportunities": [
                    "Improve content quality",
                    "Optimize for mobile",
                    "Enhance user experience",
                    "Build more backlinks"
                ]
            }
            
        except Exception as e:
            logger.error(f"Competitive analysis failed: {e}")
            return {}
    
    async def _analyze_social_presence(self, soup: BeautifulSoup, domain: str) -> Dict[str, Any]:
        """Analyze social media presence"""
        try:
            social_links = {}
            
            # Common social media patterns
            social_patterns = {
                'facebook': ['facebook.com', 'fb.com'],
                'twitter': ['twitter.com', 'x.com'],
                'instagram': ['instagram.com'],
                'linkedin': ['linkedin.com'],
                'youtube': ['youtube.com', 'youtu.be'],
                'tiktok': ['tiktok.com'],
                'pinterest': ['pinterest.com']
            }
            
            # Find social media links
            all_links = soup.find_all('a', href=True)
            
            for link in all_links:
                href = link.get('href', '').lower()
                for platform, patterns in social_patterns.items():
                    if any(pattern in href for pattern in patterns):
                        social_links[platform] = href
                        break
            
            # Open Graph tags
            og_tags = {}
            for meta in soup.find_all('meta', property=lambda x: x and x.startswith('og:')):
                og_tags[meta.get('property')] = meta.get('content')
            
            # Twitter Card tags
            twitter_tags = {}
            for meta in soup.find_all('meta', attrs={'name': lambda x: x and x.startswith('twitter:')}):
                twitter_tags[meta.get('name')] = meta.get('content')
            
            return {
                "social_links": social_links,
                "social_platforms_count": len(social_links),
                "open_graph_tags": og_tags,
                "twitter_card_tags": twitter_tags,
                "social_sharing_optimized": bool(og_tags or twitter_tags)
            }
            
        except Exception as e:
            logger.error(f"Social presence analysis failed: {e}")
            return {}
    
    async def _analyze_performance_metrics(self, url: str, response_info: Dict[str, Any]) -> Dict[str, Any]:
        """Analyze performance metrics"""
        try:
            # Basic performance metrics from response
            metrics = {
                "response_time": response_info.get('response_time', 0),
                "page_size": response_info.get('content_length', 0),
                "server": response_info.get('server', 'unknown'),
                "content_type": response_info.get('content_type', ''),
                "https_enabled": url.startswith('https://'),
                "performance_score": 0.5  # Base score
            }
            
            # Calculate performance score
            if metrics["response_time"] < 2.0:
                metrics["performance_score"] += 0.3
            elif metrics["response_time"] < 5.0:
                metrics["performance_score"] += 0.1
            
            if metrics["page_size"] < 1000000:  # Less than 1MB
                metrics["performance_score"] += 0.2
            
            if metrics["https_enabled"]:
                metrics["performance_score"] += 0.1
            
            metrics["performance_score"] = min(metrics["performance_score"], 1.0)
            
            return metrics
            
        except Exception as e:
            logger.error(f"Performance analysis failed: {e}")
            return {}
    
    async def crawl_website(self, base_url: str, max_pages: int = 50, max_depth: int = 3) -> Dict[str, Any]:
        """
        Crawl website to gather comprehensive data
        """
        try:
            crawled_pages = {}
            to_crawl = [(base_url, 0)]  # (url, depth)
            crawled_urls = set()
            
            parsed_base = urlparse(base_url)
            base_domain = parsed_base.netloc
            
            while to_crawl and len(crawled_pages) < max_pages:
                current_url, depth = to_crawl.pop(0)
                
                if current_url in crawled_urls or depth > max_depth:
                    continue
                
                crawled_urls.add(current_url)
                
                # Analyze current page
                try:
                    page_profile = await self.analyze_website(current_url, deep_analysis=False)
                    crawled_pages[current_url] = {
                        "profile": asdict(page_profile),
                        "depth": depth,
                        "crawled_at": datetime.now(timezone.utc).isoformat()
                    }
                    
                    # Extract internal links for further crawling
                    if depth < max_depth:
                        html_content, _ = await self._fetch_page_content(current_url)
                        if html_content:
                            soup = BeautifulSoup(html_content, 'html.parser')
                            links = soup.find_all('a', href=True)
                            
                            for link in links:
                                href = link.get('href')
                                if href:
                                    # Convert relative URLs to absolute
                                    absolute_url = urljoin(current_url, href)
                                    parsed_link = urlparse(absolute_url)
                                    
                                    # Only crawl internal links
                                    if (parsed_link.netloc == base_domain and 
                                        absolute_url not in crawled_urls and
                                        not any(ext in absolute_url.lower() for ext in ['.pdf', '.jpg', '.png', '.gif', '.css', '.js'])):
                                        to_crawl.append((absolute_url, depth + 1))
                
                except Exception as e:
                    logger.warning(f"Failed to crawl {current_url}: {e}")
                    continue
                
                # Rate limiting
                await asyncio.sleep(1)
            
            # Generate crawl summary
            crawl_summary = {
                "base_url": base_url,
                "total_pages_crawled": len(crawled_pages),
                "max_depth_reached": max(page["depth"] for page in crawled_pages.values()) if crawled_pages else 0,
                "crawl_completed_at": datetime.now(timezone.utc).isoformat(),
                "pages": crawled_pages
            }
            
            return crawl_summary
            
        except Exception as e:
            logger.error(f"Website crawling failed: {e}")
            raise
    
    async def close(self):
        """Close the HTTP session"""
        if self.session:
            await self.session.close()