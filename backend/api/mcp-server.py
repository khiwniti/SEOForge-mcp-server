from fastapi import FastAPI, HTTPException, Request
from fastapi.responses import JSONResponse
from pydantic import BaseModel
from typing import Dict, Any, Optional, List
import json
import logging
import redis
import os
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Initialize FastAPI app
app = FastAPI()

# Initialize Redis connection
redis_client = redis.Redis(
    host=os.getenv('REDIS_HOST', 'localhost'),
    port=int(os.getenv('REDIS_PORT', 6379)),
    password=os.getenv('REDIS_PASSWORD', None),
    decode_responses=True
)

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

# Available tools
TOOLS = {
    "content_generation": {
        "name": "content_generation",
        "description": {
            "en": "Generate SEO-optimized content",
            "th": "สร้างเนื้อหาที่เหมาะสมกับ SEO"
        },
        "parameters": {
            "topic": "string",
            "content_type": "string",
            "keywords": "list[string]",
            "industry": "string",
            "language": {
                "type": "string",
                "enum": ["en", "th"]
            }
        }
    },
    "seo_analysis": {
        "name": "seo_analysis",
        "description": {
            "en": "Analyze SEO performance",
            "th": "วิเคราะห์ประสิทธิภาพ SEO"
        },
        "parameters": {
            "url": "string",
            "content": "string",
            "language": {
                "type": "string",
                "enum": ["en", "th"]
            }
        }
    },
    "keyword_research": {
        "name": "keyword_research",
        "description": {
            "en": "Research keywords for SEO",
            "th": "วิจัยคำค้นหาสำหรับ SEO"
        },
        "parameters": {
            "seed_keyword": "string",
            "industry": "string",
            "language": {
                "type": "string",
                "enum": ["en", "th"]
            }
        }
    }
}

# Default language
DEFAULT_LANGUAGE = "en"

# Available prompts
PROMPTS = {
    "blog_post": {
        "name": "blog_post",
        "description": {
            "en": "Generate blog post prompts",
            "th": "สร้างคำแนะนำสำหรับบทความบล็อก"
        },
        "parameters": {
            "topic": "string",
            "industry": "string",
            "language": {
                "type": "string",
                "enum": ["en", "th"]
            }
        }
    }
}

# Available resources
RESOURCES = {
    "industry_data": {
        "name": "industry_data",
        "description": {
            "en": "Access industry data",
            "th": "เข้าถึงข้อมูลอุตสาหกรรม"
        },
        "parameters": {
            "industry": "string",
            "language": {
                "type": "string",
                "enum": ["en", "th"]
            }
        }
    }
}

def create_error_response(code: int, message: str, id: Optional[int] = None) -> JsonRpcResponse:
    return JsonRpcResponse(
        jsonrpc="2.0",
        error={"code": code, "message": message},
        id=id
    )

def get_localized_description(description_dict: dict, language: str = None) -> str:
    """Get localized description based on language preference."""
    if not language:
        language = DEFAULT_LANGUAGE
    return description_dict.get(language, description_dict[DEFAULT_LANGUAGE])

@app.post("/mcp-server")
async def handle_mcp_request(request: Request) -> JSONResponse:
    """Handle MCP requests with language support."""
    try:
        data = await request.json()
        rpc_request = JsonRpcRequest(**data)
        
        # Log the incoming request
        logger.info(f"Received MCP request: {rpc_request.method}")
        
        # Handle different methods
        if rpc_request.method == "initialize":
            result = {
                "status": "initialized", 
                "server_info": {
                    "name": "SEO Forge MCP", 
                    "version": "1.0.0",
                    "supported_languages": ["en", "th"]
                }
            }
        
        elif rpc_request.method == "tools/list":
            result = {"tools": list(TOOLS.values())}
        
        elif rpc_request.method == "tools/call":
            tool_name = rpc_request.params.get("name")
            if tool_name not in TOOLS:
                return JSONResponse(create_error_response(-32602, f"Unknown tool: {tool_name}", rpc_request.id).dict())
            
            # Store tool call in Redis for tracking
            redis_client.hset(
                f"tool_calls:{tool_name}",
                rpc_request.id or "anonymous",
                json.dumps(rpc_request.params)
            )
            
            # Mock tool execution result
            result = {
                "status": "success",
                "tool": tool_name,
                "output": f"Executed {tool_name} with params: {rpc_request.params}"
            }
        
        elif rpc_request.method == "prompts/list":
            result = {"prompts": list(PROMPTS.values())}
        
        elif rpc_request.method == "prompts/get":
            prompt_name = rpc_request.params.get("name")
            if prompt_name not in PROMPTS:
                return JSONResponse(create_error_response(-32602, f"Unknown prompt: {prompt_name}", rpc_request.id).dict())
            
            result = {
                "prompt": PROMPTS[prompt_name],
                "template": f"Template for {prompt_name} with params: {rpc_request.params}"
            }
        
        elif rpc_request.method == "resources/list":
            result = {"resources": list(RESOURCES.values())}
        
        elif rpc_request.method == "resources/read":
            uri = rpc_request.params.get("uri")
            if not uri or not uri.startswith("industry://"):
                return JSONResponse(create_error_response(-32602, "Invalid resource URI", rpc_request.id).dict())
            
            result = {
                "uri": uri,
                "content": f"Resource data for {uri}"
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
        logger.error(f"Error processing request: {str(e)}")
        return JSONResponse(create_error_response(-32603, "Internal error").dict())

@app.get("/mcp-server/health")
async def health_check():
    try:
        # Check Redis connection
        redis_client.ping()
        redis_status = "connected"
    except:
        redis_status = "disconnected"
        
    return {
        "status": "healthy",
        "version": "1.0.0",
        "redis_status": redis_status
    }

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)
