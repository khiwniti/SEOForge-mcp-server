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
    """Execute content generation tool"""
    topic = params.get("topic", "")
    content_type = params.get("content_type", "blog_post")
    keywords = params.get("keywords", [])
    industry = params.get("industry", "general")
    language = params.get("language", "en")
    word_count = params.get("word_count", 800)
    
    # Mock content generation - in production, this would call an AI service
    if language == "th":
        content = f"""# {topic}

## บทนำ
บทความนี้จะกล่าวถึง {topic} ในอุตสาหกรรม {industry} โดยเน้นคำสำคัญ: {', '.join(keywords)}

## เนื้อหาหลัก
เนื้อหาที่เหมาะสมกับ SEO สำหรับ {content_type} ที่มีความยาวประมาณ {word_count} คำ

## สรุป
สรุปเนื้อหาสำคัญเกี่ยวกับ {topic}

---
*เนื้อหานี้ถูกสร้างโดย SEOForge MCP Server*
"""
    else:
        content = f"""# {topic}

## Introduction
This article discusses {topic} in the {industry} industry, focusing on keywords: {', '.join(keywords)}

## Main Content
SEO-optimized content for {content_type} with approximately {word_count} words.

## Conclusion
Summary of key points about {topic}

---
*Content generated by SEOForge MCP Server*
"""
    
    return {
        "status": "success",
        "content": content,
        "metadata": {
            "word_count": len(content.split()),
            "keywords_used": keywords,
            "language": language,
            "content_type": content_type
        }
    }

def execute_seo_analysis(params: dict) -> dict:
    """Execute SEO analysis tool"""
    url = params.get("url")
    content = params.get("content", "")
    target_keywords = params.get("target_keywords", [])
    language = params.get("language", "en")
    
    # Mock SEO analysis - in production, this would analyze actual content
    analysis = {
        "status": "success",
        "seo_score": 75,
        "analysis": {
            "title_optimization": {"score": 80, "suggestions": ["Include primary keyword in title"]},
            "meta_description": {"score": 70, "suggestions": ["Add compelling meta description"]},
            "keyword_density": {"score": 75, "suggestions": ["Optimize keyword density"]},
            "content_length": {"score": 85, "suggestions": ["Content length is good"]},
            "readability": {"score": 80, "suggestions": ["Improve sentence structure"]}
        },
        "recommendations": [
            "Include target keywords in headings",
            "Add internal links to related content",
            "Optimize images with alt text",
            "Improve page loading speed"
        ],
        "target_keywords": target_keywords,
        "language": language
    }
    
    if url:
        analysis["url"] = url
    
    return analysis

def execute_keyword_research(params: dict) -> dict:
    """Execute keyword research tool"""
    seed_keyword = params.get("seed_keyword", "")
    industry = params.get("industry", "general")
    language = params.get("language", "en")
    competition_level = params.get("competition_level", "medium")
    search_volume = params.get("search_volume", "medium")
    
    # Mock keyword research - in production, this would use keyword research APIs
    if language == "th":
        keywords = [
            {"keyword": f"{seed_keyword} คืออะไร", "volume": 1000, "difficulty": 30},
            {"keyword": f"วิธีการ {seed_keyword}", "volume": 800, "difficulty": 45},
            {"keyword": f"{seed_keyword} ดีที่สุด", "volume": 600, "difficulty": 60},
            {"keyword": f"เทคนิค {seed_keyword}", "volume": 400, "difficulty": 35},
            {"keyword": f"{seed_keyword} สำหรับมือใหม่", "volume": 300, "difficulty": 25}
        ]
    else:
        keywords = [
            {"keyword": f"what is {seed_keyword}", "volume": 1200, "difficulty": 35},
            {"keyword": f"how to {seed_keyword}", "volume": 900, "difficulty": 50},
            {"keyword": f"best {seed_keyword}", "volume": 700, "difficulty": 65},
            {"keyword": f"{seed_keyword} techniques", "volume": 500, "difficulty": 40},
            {"keyword": f"{seed_keyword} for beginners", "volume": 350, "difficulty": 30}
        ]
    
    return {
        "status": "success",
        "seed_keyword": seed_keyword,
        "keywords": keywords,
        "industry": industry,
        "language": language,
        "total_keywords": len(keywords)
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
                
                # Generate prompt based on arguments
                if prompt_name == "blog_post":
                    topic = prompt_args.get("topic", "")
                    industry = prompt_args.get("industry", "general")
                    
                    if language == "th":
                        template = f"เขียนบทความบล็อกเกี่ยวกับ '{topic}' สำหรับอุตสาหกรรม {industry} โดยเน้นการเพิ่มประสิทธิภาพ SEO"
                    else:
                        template = f"Write a blog post about '{topic}' for the {industry} industry with SEO optimization focus"
                    
                    result = {
                        "prompt": PROMPTS[prompt_name],
                        "template": template,
                        "arguments": prompt_args
                    }
                else:
                    result = {"prompt": PROMPTS[prompt_name]}
            
            elif rpc_request.method == "resources/list":
                result = {"resources": list(RESOURCES.values())}
            
            elif rpc_request.method == "resources/read":
                uri = rpc_request.params.get("uri")
                if not uri or not uri.startswith("industry://"):
                    return JSONResponse(create_error_response(-32602, "Invalid resource URI", rpc_request.id).dict())
                
                # Parse URI to extract industry and data type
                parts = uri.replace("industry://", "").split("/")
                if len(parts) < 2:
                    return JSONResponse(create_error_response(-32602, "Invalid resource URI format", rpc_request.id).dict())
                
                data_type = parts[0]
                industry = parts[1]
                language = rpc_request.params.get("language", "en")
                
                # Mock industry data
                result = {
                    "uri": uri,
                    "industry": industry,
                    "data_type": data_type,
                    "language": language,
                    "content": {
                        "trends": ["AI integration", "Mobile optimization", "Voice search"],
                        "keywords": ["digital transformation", "automation", "efficiency"],
                        "market_size": "Growing rapidly",
                        "last_updated": time.strftime("%Y-%m-%d")
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
