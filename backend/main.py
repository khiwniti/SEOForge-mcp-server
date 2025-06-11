import os
import pathlib
import json
import time
import hashlib
import logging
from typing import Dict, Any, Optional, List
from fastapi import FastAPI, HTTPException, Request, Depends, APIRouter
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
from fastapi.security import APIKeyHeader
from pydantic import BaseModel
import redis
import dotenv

# Load environment variables
dotenv.load_dotenv()

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Pydantic models for MCP
class JsonRpcRequest(BaseModel):
    jsonrpc: str
    method: str
    params: Dict[str, Any]
    id: Optional[int]

class JsonRpcResponse(BaseModel):
    jsonrpc: str = "2.0"
    result: Optional[Dict[str, Any]] = None
    error: Optional[Dict[str, Any]] = None
    id: Optional[int] = None

class WordPressAuth(BaseModel):
    site_url: str
    nonce: str
    timestamp: int

# WordPress authentication and rate limiting
class WordPressConfig:
    def __init__(self):
        self.nonce_lifetime = 24 * 60 * 60  # 24 hours
        self.rate_limit_requests = 100  # requests per hour
        self.rate_limit_window = 60 * 60  # 1 hour
        self.secret_key = os.getenv('WORDPRESS_SECRET_KEY', 'default-secret-key')
        self.allowed_domains = os.getenv('ALLOWED_WORDPRESS_DOMAINS', '').split(',')

    def validate_nonce(self, nonce: str, site_url: str, timestamp: int) -> bool:
        """Validate WordPress nonce"""
        if time.time() - timestamp > self.nonce_lifetime:
            return False
        
        # Create verification hash using secret key
        verification = hashlib.sha256(f"{site_url}:{timestamp}:{self.secret_key}".encode()).hexdigest()
        return nonce == verification

    def is_domain_allowed(self, site_url: str) -> bool:
        """Check if domain is in allowed list"""
        if not self.allowed_domains or self.allowed_domains == ['']:
            return True  # Allow all if not configured
        
        from urllib.parse import urlparse
        domain = urlparse(site_url).netloc
        return any(allowed.strip() in domain for allowed in self.allowed_domains if allowed.strip())

class RateLimiter:
    def __init__(self):
        self.requests = {}
        self.window = 60 * 60  # 1 hour window
        self.max_requests = 100  # 100 requests per hour

    def is_allowed(self, site_url: str) -> bool:
        current_time = time.time()
        if site_url not in self.requests:
            self.requests[site_url] = []
        
        # Remove old requests
        self.requests[site_url] = [
            req_time for req_time in self.requests[site_url]
            if current_time - req_time < self.window
        ]
        
        if len(self.requests[site_url]) >= self.max_requests:
            return False
        
        self.requests[site_url].append(current_time)
        return True

# Initialize components
wp_config = WordPressConfig()
rate_limiter = RateLimiter()
api_key_header = APIKeyHeader(name="X-WordPress-Key", auto_error=False)

# Initialize Redis connection
try:
    redis_client = redis.Redis(
        host=os.getenv('REDIS_HOST', 'localhost'),
        port=int(os.getenv('REDIS_PORT', 6379)),
        password=os.getenv('REDIS_PASSWORD', None),
        decode_responses=True,
        socket_connect_timeout=5,
        socket_timeout=5
    )
    # Test connection
    redis_client.ping()
    logger.info("Redis connection established")
except Exception as e:
    logger.warning(f"Redis connection failed: {e}. Using in-memory storage.")
    redis_client = None

# MCP Tools, Prompts, and Resources
TOOLS = {
    "content_generation": {
        "name": "content_generation",
        "description": {
            "en": "Generate SEO-optimized content for various industries and content types",
            "th": "สร้างเนื้อหาที่เหมาะสมกับ SEO สำหรับอุตสาหกรรมและประเภทเนื้อหาต่างๆ"
        },
        "parameters": {
            "type": "object",
            "properties": {
                "topic": {"type": "string", "description": "Main topic for content generation"},
                "content_type": {"type": "string", "enum": ["blog_post", "article", "product_description", "landing_page"]},
                "keywords": {"type": "array", "items": {"type": "string"}},
                "industry": {"type": "string"},
                "language": {"type": "string", "enum": ["en", "th"], "default": "en"},
                "word_count": {"type": "integer", "minimum": 100, "maximum": 5000, "default": 800}
            },
            "required": ["topic", "content_type"]
        }
    },
    "seo_analysis": {
        "name": "seo_analysis",
        "description": {
            "en": "Analyze SEO performance of content or URLs with detailed recommendations",
            "th": "วิเคราะห์ประสิทธิภาพ SEO ของเนื้อหาหรือ URL พร้อมคำแนะนำโดยละเอียด"
        },
        "parameters": {
            "type": "object",
            "properties": {
                "url": {"type": "string", "format": "uri"},
                "content": {"type": "string"},
                "target_keywords": {"type": "array", "items": {"type": "string"}},
                "language": {"type": "string", "enum": ["en", "th"], "default": "en"}
            },
            "anyOf": [
                {"required": ["url"]},
                {"required": ["content"]}
            ]
        }
    },
    "keyword_research": {
        "name": "keyword_research",
        "description": {
            "en": "Research and suggest relevant keywords for SEO optimization",
            "th": "วิจัยและแนะนำคำค้นหาที่เกี่ยวข้องสำหรับการเพิ่มประสิทธิภาพ SEO"
        },
        "parameters": {
            "type": "object",
            "properties": {
                "seed_keyword": {"type": "string", "description": "Primary keyword to research"},
                "industry": {"type": "string"},
                "language": {"type": "string", "enum": ["en", "th"], "default": "en"},
                "competition_level": {"type": "string", "enum": ["low", "medium", "high"], "default": "medium"},
                "search_volume": {"type": "string", "enum": ["low", "medium", "high"], "default": "medium"}
            },
            "required": ["seed_keyword"]
        }
    }
}

PROMPTS = {
    "blog_post": {
        "name": "blog_post",
        "description": {
            "en": "Generate comprehensive blog post prompts with SEO optimization",
            "th": "สร้างคำแนะนำสำหรับบทความบล็อกที่ครอบคลุมพร้อมการเพิ่มประสิทธิภาพ SEO"
        },
        "parameters": {
            "type": "object",
            "properties": {
                "topic": {"type": "string"},
                "industry": {"type": "string"},
                "target_audience": {"type": "string"},
                "language": {"type": "string", "enum": ["en", "th"], "default": "en"}
            },
            "required": ["topic"]
        }
    }
}

RESOURCES = {
    "industry_data": {
        "name": "industry_data",
        "description": {
            "en": "Access comprehensive industry-specific data and insights",
            "th": "เข้าถึงข้อมูลและข้อมูลเชิงลึกเฉพาะอุตสาหกรรมที่ครอบคลุม"
        },
        "parameters": {
            "type": "object",
            "properties": {
                "industry": {"type": "string"},
                "data_type": {"type": "string", "enum": ["trends", "keywords", "competitors", "demographics"]},
                "language": {"type": "string", "enum": ["en", "th"], "default": "en"}
            },
            "required": ["industry"]
        }
    }
}

# WordPress authentication dependency
async def verify_wordpress_request(
    request: Request,
    api_key: Optional[str] = Depends(api_key_header)
) -> Dict[str, Any]:
    """Verify WordPress request authentication and rate limiting"""
    try:
        # Get WordPress site URL from headers
        site_url = request.headers.get("X-WordPress-Site")
        if not site_url:
            raise HTTPException(status_code=400, detail="Missing WordPress site URL")

        # Check if domain is allowed
        if not wp_config.is_domain_allowed(site_url):
            raise HTTPException(status_code=403, detail="Domain not allowed")

        # Check rate limiting
        if not rate_limiter.is_allowed(site_url):
            raise HTTPException(
                status_code=429,
                detail="Rate limit exceeded. Please try again later."
            )

        # Verify WordPress nonce
        nonce = request.headers.get("X-WordPress-Nonce")
        timestamp = request.headers.get("X-WordPress-Timestamp")
        
        if not all([nonce, timestamp]):
            raise HTTPException(
                status_code=400,
                detail="Missing authentication credentials"
            )

        try:
            timestamp_int = int(timestamp)
        except ValueError:
            raise HTTPException(
                status_code=400,
                detail="Invalid timestamp format"
            )

        if not wp_config.validate_nonce(nonce, site_url, timestamp_int):
            raise HTTPException(
                status_code=401,
                detail="Invalid or expired authentication"
            )

        return {"site_url": site_url, "timestamp": timestamp_int}

    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Authentication error: {str(e)}")
        raise HTTPException(
            status_code=500,
            detail=f"Authentication error: {str(e)}"
        )

# Helper functions
def create_error_response(code: int, message: str, id: Optional[int] = None) -> JsonRpcResponse:
    return JsonRpcResponse(
        jsonrpc="2.0",
        error={"code": code, "message": message},
        id=id
    )

def get_localized_description(description_dict: dict, language: str = "en") -> str:
    """Get localized description based on language preference."""
    return description_dict.get(language, description_dict.get("en", ""))

def store_request_log(method: str, params: dict, site_url: str):
    """Store request log in Redis or memory"""
    if redis_client:
        try:
            log_key = f"mcp_requests:{site_url}:{int(time.time())}"
            log_data = {
                "method": method,
                "params": json.dumps(params),
                "timestamp": time.time()
            }
            redis_client.hset(log_key, mapping=log_data)
            redis_client.expire(log_key, 86400)  # Expire after 24 hours
        except Exception as e:
            logger.warning(f"Failed to store request log: {e}")

# Tool implementations
def execute_content_generation(params: dict) -> dict:
    """Execute content generation tool with full bilingual support"""
    topic = params.get("topic", "")
    content_type = params.get("content_type", "blog_post")
    keywords = params.get("keywords", [])
    industry = params.get("industry", "general")
    language = params.get("language", "en")
    word_count = params.get("word_count", 800)
    
    # Enhanced content generation with more detailed templates
    if language == "th":
        # Thai content templates
        content_templates = {
            "blog_post": f"""# {topic}

## บทนำ
ในยุคดิจิทัลปัจจุบัน {topic} ได้กลายเป็นหัวข้อที่สำคัญในอุตสาหกรรม {industry} การเข้าใจและประยุกต์ใช้แนวคิดเหล่านี้จะช่วยให้ธุรกิจสามารถแข่งขันได้อย่างมีประสิทธิภาพ

## เนื้อหาหลัก

### ความสำคัญของ {topic}
{topic} มีบทบาทสำคัญในการพัฒนาธุรกิจสมัยใหม่ โดยเฉพาะในด้าน:
- การเพิ่มประสิทธิภาพการทำงาน
- การลดต้นทุนการดำเนินงาน
- การสร้างประสบการณ์ที่ดีให้กับลูกค้า

### กลยุทธ์การนำไปใช้
การนำ {topic} มาประยุกต์ใช้ในอุตสาหกรรม {industry} ต้องคำนึงถึงปัจจัยต่างๆ ดังนี้:

1. **การวิเคราะห์ความต้องการ**: ศึกษาความต้องการของตลาดและลูกค้า
2. **การวางแผนกลยุทธ์**: กำหนดเป้าหมายและแนวทางการดำเนินงาน
3. **การดำเนินการ**: นำแผนไปสู่การปฏิบัติอย่างเป็นระบบ
4. **การประเมินผล**: ติดตามและประเมินผลการดำเนินงาน

### คำสำคัญที่เกี่ยวข้อง
บทความนี้เน้นคำสำคัญหลัก: {', '.join(keywords)} ซึ่งเป็นองค์ประกอบสำคัญในการทำ SEO

## ข้อดีและประโยชน์
การนำ {topic} มาใช้จะก่อให้เกิดประโยชน์ดังนี้:
- เพิ่มความสามารถในการแข่งขัน
- ปรับปรุงประสิทธิภาพการทำงาน
- สร้างมูลค่าเพิ่มให้กับธุรกิจ

## สรุป
{topic} เป็นเครื่องมือที่มีประสิทธิภาพสำหรับการพัฒนาธุรกิจในอุตสาหกรรม {industry} การนำไปใช้อย่างถูกต้องจะช่วยให้องค์กรสามารถบรรลุเป้าหมายและสร้างความสำเร็จได้อย่างยั่งยืน

---
*เนื้อหานี้ถูกสร้างโดย SEOForge MCP Server สำหรับการเพิ่มประสิทธิภาพ SEO*
""",
            "article": f"""# {topic}: คู่มือฉบับสมบูรณ์

## ภาพรวม
{topic} เป็นหัวข้อที่ได้รับความสนใจอย่างมากในวงการ {industry} ในปัจจุบัน บทความนี้จะนำเสนอข้อมูลครบถ้วนเกี่ยวกับ {topic} พร้อมแนวทางการประยุกต์ใช้ในทางปฏิบัติ

## รายละเอียดเชิงลึก
การศึกษา {topic} ต้องเข้าใจหลักการพื้นฐานและการประยุกต์ใช้ในบริบทของอุตสาหกรรม {industry}

### หลักการสำคัญ
- ความเข้าใจพื้นฐาน
- การวิเคราะห์และประเมิน
- การนำไปใช้ในทางปฏิบัติ

## บทสรุป
{topic} มีความสำคัญต่อการพัฒนาในอุตสาหกรรม {industry} อย่างมาก

---
*บทความนี้เน้นคำสำคัญ: {', '.join(keywords)}*
""",
            "product_description": f"""# {topic}

## รายละเอียดผลิตภัณฑ์
{topic} เป็นโซลูชันที่ออกแบบมาเพื่อตอบสนองความต้องการในอุตสาหกรรม {industry}

### คุณสมบัติเด่น
- ประสิทธิภาพสูง
- ใช้งานง่าย
- ความปลอดภัย

### ประโยชน์ที่ได้รับ
การใช้ {topic} จะช่วยเพิ่มประสิทธิภาพและลดต้นทุนการดำเนินงาน

---
*คำสำคัญ: {', '.join(keywords)}*
""",
            "landing_page": f"""# {topic} - โซลูชันที่ดีที่สุดสำหรับ {industry}

## ทำไมต้องเลือก {topic}?
เพราะเราเข้าใจความต้องการของอุตสาหกรรม {industry} และพร้อมให้บริการที่ดีที่สุด

### ข้อเสนอพิเศษ
- ทดลองใช้ฟรี 30 วัน
- การสนับสนุน 24/7
- การรับประกันความพึงพอใจ

### ติดต่อเราวันนี้
เริ่มต้นการเปลี่ยนแปลงธุรกิจของคุณด้วย {topic}

---
*เน้นคำสำคัญ: {', '.join(keywords)}*
"""
        }
        content = content_templates.get(content_type, content_templates["blog_post"])
    else:
        # English content templates
        content_templates = {
            "blog_post": f"""# {topic}

## Introduction
In today's rapidly evolving digital landscape, {topic} has emerged as a crucial element in the {industry} industry. Understanding and implementing these concepts can significantly enhance business competitiveness and operational efficiency.

## Main Content

### The Importance of {topic}
{topic} plays a vital role in modern business development, particularly in:
- Enhancing operational efficiency
- Reducing operational costs
- Creating superior customer experiences
- Driving innovation and growth

### Implementation Strategies
Successfully implementing {topic} in the {industry} industry requires careful consideration of several factors:

1. **Needs Analysis**: Thoroughly assess market demands and customer requirements
2. **Strategic Planning**: Define clear objectives and implementation roadmaps
3. **Execution**: Systematically implement plans with proper resource allocation
4. **Evaluation**: Monitor progress and measure outcomes for continuous improvement

### Key Focus Areas
This article emphasizes the following keywords: {', '.join(keywords)}, which are essential components for effective SEO optimization and industry relevance.

## Benefits and Advantages
Implementing {topic} provides numerous benefits including:
- Enhanced competitive positioning
- Improved operational efficiency
- Increased business value and ROI
- Better customer satisfaction and retention

### Best Practices
To maximize the benefits of {topic}, consider these best practices:
- Regular assessment and optimization
- Continuous learning and adaptation
- Stakeholder engagement and communication
- Data-driven decision making

## Future Outlook
The future of {topic} in the {industry} industry looks promising, with emerging trends pointing toward:
- Increased automation and AI integration
- Enhanced personalization capabilities
- Greater focus on sustainability
- Improved user experience design

## Conclusion
{topic} represents a powerful tool for business development in the {industry} industry. When implemented correctly, it enables organizations to achieve their goals and create sustainable success in an increasingly competitive marketplace.

---
*Content generated by SEOForge MCP Server for SEO optimization*
""",
            "article": f"""# {topic}: A Comprehensive Guide

## Overview
{topic} has become a subject of significant interest in the {industry} sector. This comprehensive article provides in-depth information about {topic} along with practical implementation guidelines.

## Detailed Analysis
Understanding {topic} requires a thorough grasp of fundamental principles and their application within the {industry} context.

### Core Principles
- Fundamental understanding and knowledge base
- Analytical and assessment capabilities
- Practical implementation strategies
- Continuous improvement methodologies

### Industry Applications
The application of {topic} in {industry} encompasses various aspects:
- Strategic planning and execution
- Operational optimization
- Technology integration
- Performance measurement

## Implementation Framework
A structured approach to implementing {topic} includes:

1. **Assessment Phase**: Evaluate current state and requirements
2. **Planning Phase**: Develop comprehensive implementation strategy
3. **Execution Phase**: Deploy solutions with proper change management
4. **Optimization Phase**: Continuously improve and refine processes

## Conclusion
{topic} holds significant importance for development in the {industry} industry, offering substantial opportunities for growth and improvement.

---
*This article focuses on keywords: {', '.join(keywords)}*
""",
            "product_description": f"""# {topic}

## Product Overview
{topic} is a cutting-edge solution designed to meet the specific needs of the {industry} industry, offering unparalleled performance and reliability.

### Key Features
- High-performance capabilities
- User-friendly interface
- Enterprise-grade security
- Scalable architecture
- 24/7 support availability

### Benefits
Implementing {topic} will help:
- Increase operational efficiency
- Reduce operational costs
- Improve customer satisfaction
- Enhance competitive advantage

### Technical Specifications
- Advanced technology integration
- Cloud-native architecture
- API-first design
- Mobile-responsive interface

### Why Choose {topic}?
Our solution stands out because of:
- Proven track record in {industry}
- Comprehensive feature set
- Excellent customer support
- Competitive pricing

---
*Keywords: {', '.join(keywords)}*
""",
            "landing_page": f"""# {topic} - The Ultimate Solution for {industry}

## Why Choose {topic}?
Because we understand the unique needs of the {industry} industry and are committed to delivering exceptional results.

### What Makes Us Different?
- Industry expertise and experience
- Cutting-edge technology solutions
- Proven results and success stories
- Dedicated customer support

### Special Offers
- Free 30-day trial
- 24/7 customer support
- 100% satisfaction guarantee
- No setup fees

### Success Stories
Join thousands of satisfied customers who have transformed their business with {topic}.

### Get Started Today
Transform your business with {topic} and experience the difference.

**Contact us now for a free consultation!**

---
*Focus keywords: {', '.join(keywords)}*
"""
        }
        content = content_templates.get(content_type, content_templates["blog_post"])
    
    return {
        "status": "success",
        "content": content,
        "metadata": {
            "word_count": len(content.split()),
            "keywords_used": keywords,
            "language": language,
            "content_type": content_type,
            "industry": industry,
            "topic": topic
        }
    }

def execute_seo_analysis(params: dict) -> dict:
    """Execute SEO analysis tool with full bilingual support"""
    url = params.get("url")
    content = params.get("content", "")
    target_keywords = params.get("target_keywords", [])
    language = params.get("language", "en")
    
    # Calculate content metrics
    word_count = len(content.split()) if content else 0
    keyword_density = 0
    if content and target_keywords:
        total_keywords = sum(content.lower().count(keyword.lower()) for keyword in target_keywords)
        keyword_density = (total_keywords / word_count * 100) if word_count > 0 else 0
    
    # Generate language-specific analysis
    if language == "th":
        analysis = {
            "status": "success",
            "seo_score": min(85, max(45, 60 + (word_count // 50) + int(keyword_density * 2))),
            "analysis": {
                "title_optimization": {
                    "score": 80,
                    "suggestions": [
                        "รวมคำสำคัญหลักในชื่อเรื่อง",
                        "ใช้ชื่อเรื่องที่น่าสนใจและดึงดูดผู้อ่าน",
                        "ควบคุมความยาวชื่อเรื่องไม่เกิน 60 ตัวอักษร"
                    ]
                },
                "meta_description": {
                    "score": 70,
                    "suggestions": [
                        "เพิ่ม meta description ที่น่าสนใจ",
                        "รวมคำสำคัญในคำอธิบาย",
                        "ควบคุมความยาวไม่เกิน 160 ตัวอักษร"
                    ]
                },
                "keyword_density": {
                    "score": min(90, max(50, int(keyword_density * 10))),
                    "suggestions": [
                        f"ความหนาแน่นคำสำคัญปัจจุบัน: {keyword_density:.1f}%",
                        "ควรมีความหนาแน่นคำสำคัญ 1-3%",
                        "กระจายคำสำคัญอย่างเป็นธรรมชาติ"
                    ]
                },
                "content_length": {
                    "score": min(95, max(40, word_count // 10)),
                    "suggestions": [
                        f"จำนวนคำปัจจุบัน: {word_count} คำ",
                        "เนื้อหาควรมีอย่างน้อย 300 คำ",
                        "เนื้อหายาวช่วยเพิ่มอันดับ SEO"
                    ]
                },
                "readability": {
                    "score": 80,
                    "suggestions": [
                        "ปรับปรุงโครงสร้างประโยค",
                        "ใช้ภาษาที่เข้าใจง่าย",
                        "แบ่งย่อหน้าให้เหมาะสม"
                    ]
                },
                "heading_structure": {
                    "score": 75,
                    "suggestions": [
                        "ใช้โครงสร้าง H1, H2, H3 อย่างถูกต้อง",
                        "รวมคำสำคัญในหัวข้อย่อย",
                        "จัดลำดับหัวข้อให้เป็นระบบ"
                    ]
                }
            },
            "recommendations": [
                "รวมคำสำคัญเป้าหมายในหัวข้อย่อย",
                "เพิ่มลิงก์ภายในไปยังเนื้อหาที่เกี่ยวข้อง",
                "เพิ่ม alt text ให้กับรูปภาพ",
                "ปรับปรุงความเร็วในการโหลดหน้า",
                "เพิ่มข้อมูล Schema Markup",
                "ปรับปรุงการตอบสนองบนมือถือ"
            ],
            "target_keywords": target_keywords,
            "language": language,
            "content_metrics": {
                "word_count": word_count,
                "keyword_density": f"{keyword_density:.1f}%",
                "estimated_reading_time": f"{max(1, word_count // 200)} นาที"
            }
        }
    else:
        analysis = {
            "status": "success",
            "seo_score": min(85, max(45, 60 + (word_count // 50) + int(keyword_density * 2))),
            "analysis": {
                "title_optimization": {
                    "score": 80,
                    "suggestions": [
                        "Include primary keyword in title",
                        "Use compelling and engaging titles",
                        "Keep title length under 60 characters"
                    ]
                },
                "meta_description": {
                    "score": 70,
                    "suggestions": [
                        "Add compelling meta description",
                        "Include target keywords in description",
                        "Keep description under 160 characters"
                    ]
                },
                "keyword_density": {
                    "score": min(90, max(50, int(keyword_density * 10))),
                    "suggestions": [
                        f"Current keyword density: {keyword_density:.1f}%",
                        "Aim for 1-3% keyword density",
                        "Distribute keywords naturally throughout content"
                    ]
                },
                "content_length": {
                    "score": min(95, max(40, word_count // 10)),
                    "suggestions": [
                        f"Current word count: {word_count} words",
                        "Content should be at least 300 words",
                        "Longer content tends to rank better"
                    ]
                },
                "readability": {
                    "score": 80,
                    "suggestions": [
                        "Improve sentence structure",
                        "Use clear and simple language",
                        "Break content into digestible paragraphs"
                    ]
                },
                "heading_structure": {
                    "score": 75,
                    "suggestions": [
                        "Use proper H1, H2, H3 hierarchy",
                        "Include keywords in subheadings",
                        "Organize headings logically"
                    ]
                }
            },
            "recommendations": [
                "Include target keywords in headings",
                "Add internal links to related content",
                "Optimize images with alt text",
                "Improve page loading speed",
                "Add Schema markup for better SERP display",
                "Ensure mobile responsiveness"
            ],
            "target_keywords": target_keywords,
            "language": language,
            "content_metrics": {
                "word_count": word_count,
                "keyword_density": f"{keyword_density:.1f}%",
                "estimated_reading_time": f"{max(1, word_count // 200)} minutes"
            }
        }
    
    if url:
        analysis["url"] = url
    
    return analysis

def execute_keyword_research(params: dict) -> dict:
    """Execute keyword research tool with comprehensive bilingual support"""
    seed_keyword = params.get("seed_keyword", "")
    industry = params.get("industry", "general")
    language = params.get("language", "en")
    competition_level = params.get("competition_level", "medium")
    search_volume = params.get("search_volume", "medium")
    
    # Volume multipliers based on search volume preference
    volume_multipliers = {"low": 0.5, "medium": 1.0, "high": 2.0}
    volume_mult = volume_multipliers.get(search_volume, 1.0)
    
    # Difficulty adjustments based on competition level
    difficulty_adjustments = {"low": -15, "medium": 0, "high": 20}
    difficulty_adj = difficulty_adjustments.get(competition_level, 0)
    
    # Enhanced keyword research with industry-specific and language-specific keywords
    if language == "th":
        # Thai keyword patterns with industry-specific variations
        base_keywords = [
            {"keyword": f"{seed_keyword} คืออะไร", "base_volume": 1000, "base_difficulty": 30},
            {"keyword": f"วิธีการ {seed_keyword}", "base_volume": 800, "base_difficulty": 45},
            {"keyword": f"{seed_keyword} ดีที่สุด", "base_volume": 600, "base_difficulty": 60},
            {"keyword": f"เทคนิค {seed_keyword}", "base_volume": 400, "base_difficulty": 35},
            {"keyword": f"{seed_keyword} สำหรับมือใหม่", "base_volume": 300, "base_difficulty": 25},
            {"keyword": f"ประโยชน์ของ {seed_keyword}", "base_volume": 450, "base_difficulty": 40},
            {"keyword": f"การใช้ {seed_keyword}", "base_volume": 550, "base_difficulty": 38},
            {"keyword": f"{seed_keyword} ฟรี", "base_volume": 700, "base_difficulty": 55},
            {"keyword": f"เรียนรู้ {seed_keyword}", "base_volume": 350, "base_difficulty": 28},
            {"keyword": f"{seed_keyword} ออนไลน์", "base_volume": 480, "base_difficulty": 42}
        ]
        
        # Industry-specific Thai keywords
        if industry in ["เทคโนโลยี", "technology"]:
            base_keywords.extend([
                {"keyword": f"AI {seed_keyword}", "base_volume": 320, "base_difficulty": 50},
                {"keyword": f"{seed_keyword} ดิจิทัล", "base_volume": 280, "base_difficulty": 45},
                {"keyword": f"ระบบ {seed_keyword}", "base_volume": 200, "base_difficulty": 40}
            ])
        elif industry in ["การตลาด", "marketing"]:
            base_keywords.extend([
                {"keyword": f"{seed_keyword} การตลาด", "base_volume": 380, "base_difficulty": 48},
                {"keyword": f"กลยุทธ์ {seed_keyword}", "base_volume": 250, "base_difficulty": 52},
                {"keyword": f"{seed_keyword} โซเชียล", "base_volume": 420, "base_difficulty": 44}
            ])
        elif industry in ["สุขภาพ", "healthcare"]:
            base_keywords.extend([
                {"keyword": f"{seed_keyword} สุขภาพ", "base_volume": 290, "base_difficulty": 35},
                {"keyword": f"การรักษา {seed_keyword}", "base_volume": 180, "base_difficulty": 38},
                {"keyword": f"{seed_keyword} ธรรมชาติ", "base_volume": 220, "base_difficulty": 32}
            ])
        
    else:
        # English keyword patterns with industry-specific variations
        base_keywords = [
            {"keyword": f"what is {seed_keyword}", "base_volume": 1200, "base_difficulty": 35},
            {"keyword": f"how to {seed_keyword}", "base_volume": 900, "base_difficulty": 50},
            {"keyword": f"best {seed_keyword}", "base_volume": 700, "base_difficulty": 65},
            {"keyword": f"{seed_keyword} techniques", "base_volume": 500, "base_difficulty": 40},
            {"keyword": f"{seed_keyword} for beginners", "base_volume": 350, "base_difficulty": 30},
            {"keyword": f"{seed_keyword} benefits", "base_volume": 450, "base_difficulty": 42},
            {"keyword": f"{seed_keyword} guide", "base_volume": 600, "base_difficulty": 38},
            {"keyword": f"free {seed_keyword}", "base_volume": 800, "base_difficulty": 55},
            {"keyword": f"learn {seed_keyword}", "base_volume": 400, "base_difficulty": 28},
            {"keyword": f"{seed_keyword} online", "base_volume": 550, "base_difficulty": 45},
            {"keyword": f"{seed_keyword} tutorial", "base_volume": 480, "base_difficulty": 35},
            {"keyword": f"{seed_keyword} tips", "base_volume": 380, "base_difficulty": 32}
        ]
        
        # Industry-specific English keywords
        if industry in ["technology", "เทคโนโลยี"]:
            base_keywords.extend([
                {"keyword": f"AI {seed_keyword}", "base_volume": 420, "base_difficulty": 58},
                {"keyword": f"{seed_keyword} software", "base_volume": 350, "base_difficulty": 52},
                {"keyword": f"{seed_keyword} automation", "base_volume": 280, "base_difficulty": 48},
                {"keyword": f"digital {seed_keyword}", "base_volume": 320, "base_difficulty": 45}
            ])
        elif industry in ["marketing", "การตลาด"]:
            base_keywords.extend([
                {"keyword": f"{seed_keyword} strategy", "base_volume": 480, "base_difficulty": 55},
                {"keyword": f"{seed_keyword} campaign", "base_volume": 320, "base_difficulty": 50},
                {"keyword": f"social media {seed_keyword}", "base_volume": 420, "base_difficulty": 48},
                {"keyword": f"{seed_keyword} ROI", "base_volume": 250, "base_difficulty": 52}
            ])
        elif industry in ["healthcare", "สุขภาพ"]:
            base_keywords.extend([
                {"keyword": f"{seed_keyword} treatment", "base_volume": 380, "base_difficulty": 45},
                {"keyword": f"{seed_keyword} therapy", "base_volume": 290, "base_difficulty": 42},
                {"keyword": f"natural {seed_keyword}", "base_volume": 220, "base_difficulty": 35},
                {"keyword": f"{seed_keyword} prevention", "base_volume": 180, "base_difficulty": 38}
            ])
        elif industry in ["finance", "การเงิน"]:
            base_keywords.extend([
                {"keyword": f"{seed_keyword} investment", "base_volume": 350, "base_difficulty": 60},
                {"keyword": f"{seed_keyword} calculator", "base_volume": 280, "base_difficulty": 40},
                {"keyword": f"{seed_keyword} rates", "base_volume": 420, "base_difficulty": 55}
            ])
        elif industry in ["education", "การศึกษา"]:
            base_keywords.extend([
                {"keyword": f"{seed_keyword} course", "base_volume": 450, "base_difficulty": 45},
                {"keyword": f"{seed_keyword} certification", "base_volume": 320, "base_difficulty": 50},
                {"keyword": f"{seed_keyword} training", "base_volume": 380, "base_difficulty": 42}
            ])
    
    # Apply volume and difficulty adjustments
    keywords = []
    for kw in base_keywords:
        adjusted_volume = int(kw["base_volume"] * volume_mult)
        adjusted_difficulty = max(10, min(90, kw["base_difficulty"] + difficulty_adj))
        
        # Add CPC estimation based on industry and competition
        cpc_base = {"low": 0.5, "medium": 1.2, "high": 2.5}
        estimated_cpc = round(cpc_base.get(competition_level, 1.2) * (1 + adjusted_difficulty / 100), 2)
        
        keywords.append({
            "keyword": kw["keyword"],
            "volume": adjusted_volume,
            "difficulty": adjusted_difficulty,
            "cpc": estimated_cpc,
            "trend": "stable",  # Could be "rising", "falling", "stable"
            "intent": "informational"  # Could be "commercial", "navigational", "transactional"
        })
    
    # Sort by volume (descending)
    keywords.sort(key=lambda x: x["volume"], reverse=True)
    
    # Generate insights based on language
    if language == "th":
        insights = {
            "top_opportunities": [kw["keyword"] for kw in keywords[:3] if kw["difficulty"] < 40],
            "competitive_keywords": [kw["keyword"] for kw in keywords if kw["difficulty"] > 60],
            "long_tail_suggestions": [kw["keyword"] for kw in keywords if len(kw["keyword"].split()) > 3],
            "summary": f"พบคำสำคัญที่เกี่ยวข้องกับ '{seed_keyword}' จำนวน {len(keywords)} คำ ในอุตสาหกรรม {industry}"
        }
    else:
        insights = {
            "top_opportunities": [kw["keyword"] for kw in keywords[:3] if kw["difficulty"] < 40],
            "competitive_keywords": [kw["keyword"] for kw in keywords if kw["difficulty"] > 60],
            "long_tail_suggestions": [kw["keyword"] for kw in keywords if len(kw["keyword"].split()) > 3],
            "summary": f"Found {len(keywords)} related keywords for '{seed_keyword}' in the {industry} industry"
        }
    
    return {
        "status": "success",
        "seed_keyword": seed_keyword,
        "keywords": keywords,
        "industry": industry,
        "language": language,
        "total_keywords": len(keywords),
        "search_parameters": {
            "competition_level": competition_level,
            "search_volume": search_volume,
            "volume_multiplier": volume_mult,
            "difficulty_adjustment": difficulty_adj
        },
        "insights": insights
    }

def create_app() -> FastAPI:
    """Create the FastAPI application with MCP server integration"""
    app = FastAPI(
        title="SEOForge MCP Server",
        description="WordPress-integrated MCP server for SEO content generation and analysis",
        version="1.0.0",
        docs_url="/docs",
        redoc_url="/redoc"
    )

    # Add CORS middleware
    allowed_origins = ["*"]  # In production, use specific WordPress domains
    if wp_config.allowed_domains and wp_config.allowed_domains != ['']:
        allowed_origins = [f"https://{domain.strip()}" for domain in wp_config.allowed_domains if domain.strip()]
        allowed_origins.extend([f"http://{domain.strip()}" for domain in wp_config.allowed_domains if domain.strip()])

    app.add_middleware(
        CORSMiddleware,
        allow_origins=allowed_origins,
        allow_credentials=True,
        allow_methods=["GET", "POST", "PUT", "DELETE", "OPTIONS"],
        allow_headers=["*"],
        expose_headers=["*"]
    )

    # MCP Server endpoints
    @app.post("/mcp-server")
    async def handle_mcp_request(
        request: Request,
        wp_auth: dict = Depends(verify_wordpress_request)
    ) -> JSONResponse:
        """Handle MCP requests with WordPress authentication"""
        try:
            data = await request.json()
            rpc_request = JsonRpcRequest(**data)
            
            # Log the incoming request
            logger.info(f"MCP request from {wp_auth['site_url']}: {rpc_request.method}")
            store_request_log(rpc_request.method, rpc_request.params, wp_auth['site_url'])
            
            # Handle different methods
            if rpc_request.method == "initialize":
                result = {
                    "status": "initialized", 
                    "server_info": {
                        "name": "SEOForge MCP Server", 
                        "version": "1.0.0",
                        "supported_languages": ["en", "th"],
                        "capabilities": ["tools", "prompts", "resources"]
                    },
                    "client_info": {
                        "site_url": wp_auth['site_url'],
                        "authenticated": True
                    }
                }
            
            elif rpc_request.method == "tools/list":
                result = {"tools": list(TOOLS.values())}
            
            elif rpc_request.method == "tools/call":
                tool_name = rpc_request.params.get("name")
                if tool_name not in TOOLS:
                    return JSONResponse(create_error_response(-32602, f"Unknown tool: {tool_name}", rpc_request.id).dict())
                
                tool_args = rpc_request.params.get("arguments", {})
                
                # Execute the appropriate tool
                if tool_name == "content_generation":
                    result = execute_content_generation(tool_args)
                elif tool_name == "seo_analysis":
                    result = execute_seo_analysis(tool_args)
                elif tool_name == "keyword_research":
                    result = execute_keyword_research(tool_args)
                else:
                    result = {"status": "error", "message": f"Tool {tool_name} not implemented"}
            
            elif rpc_request.method == "prompts/list":
                result = {"prompts": list(PROMPTS.values())}
            
            elif rpc_request.method == "prompts/get":
                prompt_name = rpc_request.params.get("name")
                if prompt_name not in PROMPTS:
                    return JSONResponse(create_error_response(-32602, f"Unknown prompt: {prompt_name}", rpc_request.id).dict())
                
                prompt_args = rpc_request.params.get("arguments", {})
                language = prompt_args.get("language", "en")
                
                # Generate comprehensive prompt based on arguments
                if prompt_name == "blog_post":
                    topic = prompt_args.get("topic", "")
                    industry = prompt_args.get("industry", "general")
                    target_audience = prompt_args.get("target_audience", "general audience")
                    
                    if language == "th":
                        template = f"""เขียนบทความบล็อกเกี่ยวกับ '{topic}' สำหรับอุตสาหกรรม {industry}

คำแนะนำการเขียน:
1. เริ่มต้นด้วยหัวข้อที่น่าสนใจและมีคำสำคัญ
2. เขียนบทนำที่ดึงดูดความสนใจของ {target_audience}
3. แบ่งเนื้อหาเป็นหัวข้อย่อยที่ชัดเจน
4. ใช้คำสำคัญอย่างเป็นธรรมชาติ
5. เพิ่มข้อมูลที่มีประโยชน์และน่าเชื่อถือ
6. สรุปด้วยข้อคิดสำคัญและ call-to-action

เป้าหมาย SEO:
- ความยาวบทความ 800-1500 คำ
- ใช้คำสำคัญหลักใน title, headings และเนื้อหา
- เขียนให้อ่านง่ายและมีประโยชน์
- เพิ่มลิงก์ภายในและภายนอกที่เกี่ยวข้อง"""
                    else:
                        template = f"""Write a comprehensive blog post about '{topic}' for the {industry} industry

Writing Guidelines:
1. Start with an engaging title that includes target keywords
2. Write an attention-grabbing introduction for {target_audience}
3. Structure content with clear subheadings
4. Use keywords naturally throughout the content
5. Include valuable and credible information
6. Conclude with key takeaways and a call-to-action

SEO Objectives:
- Target 800-1500 words in length
- Include primary keywords in title, headings, and body
- Write for readability and user value
- Add relevant internal and external links
- Optimize for featured snippets where possible"""
                    
                    result = {
                        "prompt": PROMPTS[prompt_name],
                        "template": template,
                        "arguments": prompt_args,
                        "additional_suggestions": {
                            "content_structure": [
                                "Introduction (10-15% of content)",
                                "Main body with 3-5 key sections",
                                "Conclusion with actionable insights"
                            ],
                            "seo_tips": [
                                "Use H1 for main title, H2-H3 for subheadings",
                                "Include target keywords in first 100 words",
                                "Add meta description (150-160 characters)",
                                "Use bullet points and numbered lists",
                                "Include relevant images with alt text"
                            ]
                        }
                    }
                else:
                    result = {"prompt": PROMPTS[prompt_name]}
            
            elif rpc_request.method == "resources/list":
                result = {"resources": list(RESOURCES.values())}
            
            elif rpc_request.method == "resources/read":
                uri = rpc_request.params.get("uri")
                language = rpc_request.params.get("language", "en")
                
                if not uri or not uri.startswith("industry://"):
                    return JSONResponse(create_error_response(-32602, "Invalid resource URI", rpc_request.id).dict())
                
                # Parse URI to extract industry and data type
                parts = uri.replace("industry://", "").split("/")
                if len(parts) < 2:
                    return JSONResponse(create_error_response(-32602, "Invalid resource URI format", rpc_request.id).dict())
                
                data_type = parts[0]
                industry = parts[1]
                
                # Enhanced bilingual industry data
                if language == "th":
                    content_data = {
                        "data": {
                            "trends": [
                                "การผสานเทคโนโลยี AI",
                                "การเพิ่มประสิทธิภาพมือถือ",
                                "การค้นหาด้วยเสียง",
                                "การเปลี่ยนผ่านดิจิทัล",
                                "ความยั่งยืนทางธุรกิจ"
                            ],
                            "keywords": [
                                "การเปลี่ยนแปลงดิจิทัล",
                                "ระบบอัตโนมัติ",
                                "ประสิทธิภาพ",
                                "นวัตกรรม",
                                "การวิเคราะห์ข้อมูล"
                            ],
                            "market_insights": {
                                "growth_rate": "15-25% ต่อปี",
                                "market_size": "เติบโตอย่างรวดเร็ว",
                                "key_players": "50-100 บริษัทหลัก",
                                "investment_focus": "เทคโนโลยีและนวัตกรรม"
                            },
                            "challenges": [
                                "การแข่งขันที่รุนแรง",
                                "การเปลี่ยนแปลงของเทคโนโลยี",
                                "ความต้องการของลูกค้าที่เปลี่ยนไป",
                                "กฎระเบียบใหม่"
                            ],
                            "opportunities": [
                                "ตลาดใหม่ในเอเชีย",
                                "การพัฒนาผลิตภัณฑ์นวัตกรรม",
                                "การใช้ประโยชน์จาก Big Data",
                                "พันธมิตรเชิงกลยุทธ์"
                            ]
                        },
                        "trends": {
                            "emerging_trends": [
                                "ปัญญาประดิษฐ์และการเรียนรู้ของเครื่อง",
                                "Internet of Things (IoT)",
                                "Blockchain และ Cryptocurrency",
                                "การทำงานแบบไฮบริด",
                                "ความยั่งยืนและ ESG"
                            ],
                            "market_drivers": [
                                "ความต้องการของผู้บริโภค",
                                "การพัฒนาเทคโนโลยี",
                                "นโยบายรัฐบาล",
                                "สภาวะเศรษฐกิจโลก"
                            ],
                            "future_outlook": "อุตสาหกรรมมีแนวโน้มเติบโตต่อเนื่อง โดยเฉพาะในด้านเทคโนโลยีและนวัตกรรม"
                        },
                        "insights": {
                            "strategic_recommendations": [
                                "ลงทุนในเทคโนโลยีดิจิทัล",
                                "พัฒนาทักษะบุคลากร",
                                "สร้างวัฒนธรรมนวัตกรรม",
                                "สร้างพันธมิตรเชิงกลยุทธ์"
                            ],
                            "success_factors": [
                                "ความเป็นผู้นำในการเปลี่ยนแปลง",
                                "การลงทุนในเทคโนโลยีและบุคลากร",
                                "ความสัมพันธ์ที่แข็งแกร่งกับลูกค้า",
                                "ความเป็นเลิศในการดำเนินงาน"
                            ]
                        }
                    }
                else:
                    content_data = {
                        "data": {
                            "trends": [
                                "AI and Machine Learning integration",
                                "Mobile-first optimization",
                                "Voice search technology",
                                "Digital transformation",
                                "Sustainable business practices"
                            ],
                            "keywords": [
                                "digital transformation",
                                "automation",
                                "efficiency",
                                "innovation",
                                "data analytics"
                            ],
                            "market_insights": {
                                "growth_rate": "15-25% annually",
                                "market_size": "Rapidly growing",
                                "key_players": "50-100 major companies",
                                "investment_focus": "Technology and innovation"
                            },
                            "challenges": [
                                "Intense market competition",
                                "Rapid technological changes",
                                "Evolving customer demands",
                                "Regulatory compliance"
                            ],
                            "opportunities": [
                                "Emerging Asian markets",
                                "Innovative product development",
                                "Big Data utilization",
                                "Strategic partnerships"
                            ]
                        },
                        "trends": {
                            "emerging_trends": [
                                "Artificial Intelligence and Machine Learning",
                                "Internet of Things (IoT)",
                                "Blockchain and Cryptocurrency",
                                "Hybrid work models",
                                "Sustainability and ESG focus"
                            ],
                            "market_drivers": [
                                "Consumer demand evolution",
                                "Technological advancement",
                                "Government policies",
                                "Global economic conditions"
                            ],
                            "future_outlook": "The industry shows strong growth potential, particularly in technology and innovation sectors"
                        },
                        "insights": {
                            "strategic_recommendations": [
                                "Invest in digital technologies",
                                "Develop workforce capabilities",
                                "Foster innovation culture",
                                "Build strategic partnerships"
                            ],
                            "success_factors": [
                                "Leadership commitment to change",
                                "Investment in technology and talent",
                                "Strong customer relationships",
                                "Operational excellence"
                            ]
                        }
                    }
                
                # Get the specific content based on data type
                content = content_data.get(data_type, content_data["data"])
                
                result = {
                    "uri": uri,
                    "industry": industry,
                    "data_type": data_type,
                    "language": language,
                    "content": content,
                    "metadata": {
                        "last_updated": time.strftime("%Y-%m-%d"),
                        "source": "SEOForge MCP Server",
                        "content_type": "industry_analysis",
                        "supported_languages": ["en", "th"]
                    }
                }
            
            else:
                return JSONResponse(create_error_response(-32601, f"Method not found: {rpc_request.method}", rpc_request.id).dict())
            
            # Create successful response
            response = JsonRpcResponse(
                jsonrpc="2.0",
                result=result,
                id=rpc_request.id
            )
            
            return JSONResponse(response.dict())
        
        except json.JSONDecodeError:
            return JSONResponse(create_error_response(-32700, "Parse error").dict())
        except Exception as e:
            logger.error(f"Error processing MCP request: {str(e)}")
            return JSONResponse(create_error_response(-32603, f"Internal error: {str(e)}").dict())

    @app.get("/mcp-server/health")
    async def mcp_health_check(wp_auth: dict = Depends(verify_wordpress_request)):
        """Health check for MCP server"""
        try:
            redis_status = "connected" if redis_client and redis_client.ping() else "disconnected"
        except:
            redis_status = "disconnected"
            
        return {
            "status": "healthy",
            "service": "mcp-server",
            "version": "1.0.0",
            "redis_status": redis_status,
            "authenticated_site": wp_auth['site_url'],
            "timestamp": time.time()
        }

    # WordPress Plugin API endpoints
    @app.post("/wordpress/plugin")
    async def wordpress_plugin_api(
        request: Request,
        wp_auth: dict = Depends(verify_wordpress_request)
    ):
        """WordPress plugin API endpoint"""
        try:
            data = await request.json()
            action = data.get("action")
            request_data = data.get("data", {})
            
            logger.info(f"WordPress plugin request from {wp_auth['site_url']}: {action}")
            
            if action == "generate_content":
                # Convert WordPress plugin format to MCP format
                mcp_params = {
                    "name": "content_generation",
                    "arguments": {
                        "topic": request_data.get("topic", ""),
                        "content_type": request_data.get("content_type", "blog_post"),
                        "keywords": request_data.get("keywords", []),
                        "industry": request_data.get("industry", "general"),
                        "language": request_data.get("language", "en")
                    }
                }
                result = execute_content_generation(mcp_params["arguments"])
                
            elif action == "analyze_seo":
                mcp_params = {
                    "name": "seo_analysis",
                    "arguments": {
                        "content": request_data.get("content", ""),
                        "url": request_data.get("url", ""),
                        "target_keywords": request_data.get("keywords", []),
                        "language": request_data.get("language", "en")
                    }
                }
                result = execute_seo_analysis(mcp_params["arguments"])
                
            elif action == "research_keywords":
                mcp_params = {
                    "name": "keyword_research",
                    "arguments": {
                        "seed_keyword": request_data.get("keyword", ""),
                        "industry": request_data.get("industry", "general"),
                        "language": request_data.get("language", "en")
                    }
                }
                result = execute_keyword_research(mcp_params["arguments"])
                
            else:
                return JSONResponse({"error": f"Unknown action: {action}"}, status_code=400)
            
            return JSONResponse(result)
            
        except Exception as e:
            logger.error(f"WordPress plugin API error: {str(e)}")
            return JSONResponse({"error": str(e)}, status_code=500)

    @app.get("/wordpress/plugin/health")
    async def wordpress_plugin_health(wp_auth: dict = Depends(verify_wordpress_request)):
        """Health check for WordPress plugin integration"""
        return {
            "status": "healthy",
            "service": "wordpress-plugin",
            "version": "1.0.0",
            "site_url": wp_auth['site_url'],
            "timestamp": time.time()
        }

    # General health check (no auth required)
    @app.get("/health")
    async def general_health_check():
        """General health check endpoint"""
        try:
            redis_status = "connected" if redis_client and redis_client.ping() else "disconnected"
        except:
            redis_status = "disconnected"
            
        return {
            "status": "healthy",
            "service": "seoforge-mcp-server",
            "version": "1.0.0",
            "redis_status": redis_status,
            "timestamp": time.time()
        }

    # Root endpoint
    @app.get("/")
    async def root():
        """Root endpoint with service information"""
        return {
            "service": "SEOForge MCP Server",
            "version": "1.0.0",
            "description": "WordPress-integrated MCP server for SEO content generation and analysis",
            "endpoints": {
                "mcp_server": "/mcp-server",
                "wordpress_plugin": "/wordpress/plugin",
                "health": "/health",
                "docs": "/docs"
            }
        }

    return app

app = create_app()
