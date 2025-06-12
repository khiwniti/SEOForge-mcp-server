from fastapi import APIRouter, HTTPException, Depends, BackgroundTasks
from pydantic import BaseModel, Field
from typing import List, Optional, Dict, Any
import databutton as db
from openai import OpenAI
import json
import uuid
from datetime import datetime, timezone
import asyncio
import aiohttp

router = APIRouter(prefix="/mcp-server", tags=["MCP Server"])

# --- Pydantic Models ---

class MCPToolRequest(BaseModel):
    tool_name: str = Field(..., description="Name of the MCP tool to execute")
    parameters: Dict[str, Any] = Field(default_factory=dict, description="Parameters for the tool")
    context: Optional[Dict[str, Any]] = Field(default_factory=dict, description="Additional context for the request")
    industry: Optional[str] = Field(default="general", description="Industry context (e.g., ecommerce, healthcare, finance)")
    language: Optional[str] = Field(default="en", description="Target language for content generation")

class MCPToolResponse(BaseModel):
    success: bool
    tool_name: str
    result: Dict[str, Any]
    execution_time: float
    timestamp: str

class IndustryTemplate(BaseModel):
    id: str
    name: str
    description: str
    industry: str
    tools: List[str]
    default_context: Dict[str, Any]
    created_at: str

class MCPServerStatus(BaseModel):
    status: str
    version: str
    available_tools: List[str]
    supported_industries: List[str]
    active_connections: int
    uptime: str

# --- Helper Functions ---

def get_openai_client():
    api_key = db.secrets.get("OPENAI_API_KEY")
    if not api_key:
        raise HTTPException(status_code=500, detail="OpenAI API key is not configured.")
    return OpenAI(api_key=api_key)

def get_industry_templates() -> List[IndustryTemplate]:
    """Get available industry templates from storage"""
    templates = db.storage.json.get("mcp_industry_templates", default=[])
    return [IndustryTemplate(**template) for template in templates if isinstance(template, dict)]

def save_industry_templates(templates: List[IndustryTemplate]):
    """Save industry templates to storage"""
    db.storage.json.put("mcp_industry_templates", [template.model_dump() for template in templates])

# --- MCP Tool Implementations ---

async def execute_content_generation_tool(parameters: Dict[str, Any], context: Dict[str, Any], client: OpenAI) -> Dict[str, Any]:
    """Universal content generation tool"""
    content_type = parameters.get("content_type", "blog_post")
    topic = parameters.get("topic", "")
    keywords = parameters.get("keywords", [])
    industry = context.get("industry", "general")
    language = context.get("language", "en")
    tone = parameters.get("tone", "professional")
    length = parameters.get("length", "medium")
    
    if not topic:
        raise ValueError("Topic is required for content generation")
    
    # Build industry-specific context
    industry_context = await build_industry_context(industry)
    
    system_prompt = f"""You are an expert content creator specializing in {industry} industry.
    Create high-quality, SEO-optimized {content_type} content that is engaging and valuable.
    
    Industry Context: {industry_context}
    Content Type: {content_type}
    Tone: {tone}
    Length: {length}
    Language: {language}
    
    Guidelines:
    - Focus on providing value to the target audience
    - Include relevant keywords naturally
    - Structure content for readability
    - Ensure accuracy and credibility
    - Follow industry best practices
    """
    
    user_prompt = f"""Create a {content_type} about: {topic}
    
    Target Keywords: {', '.join(keywords) if keywords else 'None specified'}
    
    Please provide:
    1. Compelling title
    2. Well-structured content
    3. Meta description
    4. Key takeaways
    5. Call-to-action suggestions
    """
    
    try:
        completion = client.chat.completions.create(
            model="gpt-4o-mini",
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": user_prompt}
            ],
            temperature=0.7,
            max_tokens=2000
        )
        
        content = completion.choices[0].message.content
        
        return {
            "content": content,
            "content_type": content_type,
            "topic": topic,
            "keywords": keywords,
            "industry": industry,
            "language": language,
            "word_count": len(content.split()) if content else 0,
            "generated_at": datetime.now(timezone.utc).isoformat()
        }
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Content generation failed: {str(e)}")

async def execute_seo_analysis_tool(parameters: Dict[str, Any], context: Dict[str, Any]) -> Dict[str, Any]:
    """Universal SEO analysis tool"""
    url = parameters.get("url")
    content = parameters.get("content")
    keywords = parameters.get("keywords", [])
    industry = context.get("industry", "general")
    
    if not url and not content:
        raise ValueError("Either URL or content is required for SEO analysis")
    
    # Simulate comprehensive SEO analysis
    analysis_result = {
        "overall_score": 75.5,
        "technical_seo": {
            "score": 80,
            "issues": ["Missing meta description", "Large image files"],
            "recommendations": ["Add meta descriptions", "Optimize images"]
        },
        "content_seo": {
            "score": 70,
            "keyword_density": {kw: round(2.5 + (hash(kw) % 100) / 100, 2) for kw in keywords},
            "readability_score": 65,
            "content_length": len(content.split()) if content else 0
        },
        "industry_specific": await get_industry_seo_insights(industry),
        "analyzed_at": datetime.now(timezone.utc).isoformat()
    }
    
    return analysis_result

async def execute_keyword_research_tool(parameters: Dict[str, Any], context: Dict[str, Any]) -> Dict[str, Any]:
    """Universal keyword research tool"""
    seed_keyword = parameters.get("seed_keyword", "")
    industry = context.get("industry", "general")
    language = context.get("language", "en")
    
    if not seed_keyword:
        raise ValueError("Seed keyword is required for keyword research")
    
    # Simulate keyword research results
    keywords = await generate_keyword_suggestions(seed_keyword, industry, language)
    
    return {
        "seed_keyword": seed_keyword,
        "suggested_keywords": keywords,
        "industry": industry,
        "language": language,
        "total_suggestions": len(keywords),
        "generated_at": datetime.now(timezone.utc).isoformat()
    }

async def execute_industry_analysis_tool(parameters: Dict[str, Any], context: Dict[str, Any]) -> Dict[str, Any]:
    """Industry-specific analysis tool"""
    industry = parameters.get("industry", context.get("industry", "general"))
    analysis_type = parameters.get("analysis_type", "overview")
    
    industry_data = await get_industry_analysis(industry, analysis_type)
    
    return {
        "industry": industry,
        "analysis_type": analysis_type,
        "data": industry_data,
        "analyzed_at": datetime.now(timezone.utc).isoformat()
    }

# --- Helper Functions for Tools ---

async def build_industry_context(industry: str) -> str:
    """Build context specific to the industry"""
    industry_contexts = {
        "ecommerce": "Focus on product descriptions, conversion optimization, customer journey, and sales-driven content.",
        "healthcare": "Emphasize accuracy, compliance with medical regulations, patient education, and evidence-based information.",
        "finance": "Prioritize security, regulatory compliance, risk management, and clear financial communication.",
        "technology": "Highlight innovation, technical accuracy, user experience, and cutting-edge solutions.",
        "education": "Focus on learning outcomes, accessibility, engagement, and pedagogical best practices.",
        "cannabis": "Emphasize legal compliance, medical benefits, responsible use, and regulatory adherence.",
        "real_estate": "Focus on market trends, property features, investment potential, and local market knowledge.",
        "automotive": "Highlight performance, safety, innovation, and customer experience.",
        "food_beverage": "Emphasize quality, taste, nutrition, and culinary experience.",
        "travel": "Focus on experiences, destinations, cultural insights, and travel planning."
    }
    
    return industry_contexts.get(industry, "General business context with focus on value creation and customer satisfaction.")

async def get_industry_seo_insights(industry: str) -> Dict[str, Any]:
    """Get SEO insights specific to the industry"""
    return {
        "industry_keywords": await get_industry_keywords(industry),
        "content_types": await get_popular_content_types(industry),
        "competitor_insights": await get_competitor_insights(industry),
        "seasonal_trends": await get_seasonal_trends(industry)
    }

async def get_industry_keywords(industry: str) -> List[str]:
    """Get popular keywords for the industry"""
    keyword_sets = {
        "ecommerce": ["online shopping", "product reviews", "best deals", "free shipping", "customer service"],
        "healthcare": ["medical treatment", "health insurance", "patient care", "medical advice", "wellness"],
        "finance": ["investment", "financial planning", "loans", "insurance", "banking"],
        "technology": ["software", "innovation", "digital transformation", "AI", "cloud computing"],
        "cannabis": ["medical cannabis", "CBD", "hemp products", "dispensary", "cannabis accessories"],
        "real_estate": ["property investment", "home buying", "real estate market", "mortgage", "property management"]
    }
    
    return keyword_sets.get(industry, ["business", "services", "solutions", "quality", "professional"])

async def get_popular_content_types(industry: str) -> List[str]:
    """Get popular content types for the industry"""
    content_types = {
        "ecommerce": ["product descriptions", "buying guides", "comparison articles", "customer testimonials"],
        "healthcare": ["medical articles", "patient guides", "treatment explanations", "health tips"],
        "finance": ["financial guides", "investment advice", "market analysis", "regulatory updates"],
        "technology": ["technical documentation", "product demos", "case studies", "innovation articles"],
        "cannabis": ["educational content", "product information", "compliance guides", "medical research"]
    }
    
    return content_types.get(industry, ["blog posts", "articles", "guides", "case studies"])

async def get_competitor_insights(industry: str) -> Dict[str, Any]:
    """Get competitor insights for the industry"""
    return {
        "top_competitors": 5,
        "average_content_length": 1200,
        "common_topics": await get_industry_keywords(industry)[:3],
        "content_frequency": "2-3 posts per week"
    }

async def get_seasonal_trends(industry: str) -> Dict[str, Any]:
    """Get seasonal trends for the industry"""
    return {
        "peak_seasons": ["Q4", "Summer"],
        "trending_topics": await get_industry_keywords(industry)[:2],
        "content_calendar_suggestions": ["Holiday content", "Seasonal promotions"]
    }

async def generate_keyword_suggestions(seed_keyword: str, industry: str, language: str) -> List[Dict[str, Any]]:
    """Generate keyword suggestions based on seed keyword"""
    base_keywords = await get_industry_keywords(industry)
    
    suggestions = []
    for i, keyword in enumerate(base_keywords[:10]):
        suggestions.append({
            "keyword": f"{seed_keyword} {keyword}",
            "search_volume": 1000 + (i * 100),
            "difficulty": 30 + (i * 5),
            "cpc": round(1.5 + (i * 0.3), 2),
            "intent": "informational" if i % 2 == 0 else "commercial"
        })
    
    return suggestions

async def get_industry_analysis(industry: str, analysis_type: str) -> Dict[str, Any]:
    """Get comprehensive industry analysis"""
    return {
        "market_size": "Growing",
        "key_trends": await get_industry_keywords(industry)[:3],
        "opportunities": ["Digital transformation", "Customer experience", "Sustainability"],
        "challenges": ["Competition", "Regulation", "Technology adoption"],
        "growth_rate": "5-10% annually",
        "key_players": ["Market Leader 1", "Market Leader 2", "Market Leader 3"]
    }

# --- MCP Tool Registry ---

MCP_TOOLS = {
    "content_generation": execute_content_generation_tool,
    "seo_analysis": execute_seo_analysis_tool,
    "keyword_research": execute_keyword_research_tool,
    "industry_analysis": execute_industry_analysis_tool
}

# --- API Endpoints ---

@router.get("/status", response_model=MCPServerStatus)
async def get_mcp_server_status():
    """Get MCP server status and capabilities"""
    return MCPServerStatus(
        status="active",
        version="1.0.0",
        available_tools=list(MCP_TOOLS.keys()),
        supported_industries=["ecommerce", "healthcare", "finance", "technology", "education", "cannabis", "real_estate", "automotive", "food_beverage", "travel", "general"],
        active_connections=1,
        uptime="24h 30m"
    )

@router.post("/execute-tool", response_model=MCPToolResponse)
async def execute_mcp_tool(request: MCPToolRequest, openai_client: OpenAI = Depends(get_openai_client)):
    """Execute an MCP tool with the given parameters"""
    start_time = datetime.now()
    
    if request.tool_name not in MCP_TOOLS:
        raise HTTPException(status_code=404, detail=f"Tool '{request.tool_name}' not found")
    
    try:
        tool_function = MCP_TOOLS[request.tool_name]
        
        # Add OpenAI client to context if the tool needs it
        if request.tool_name == "content_generation":
            result = await tool_function(request.parameters, request.context, openai_client)
        else:
            result = await tool_function(request.parameters, request.context)
        
        execution_time = (datetime.now() - start_time).total_seconds()
        
        return MCPToolResponse(
            success=True,
            tool_name=request.tool_name,
            result=result,
            execution_time=execution_time,
            timestamp=datetime.now(timezone.utc).isoformat()
        )
        
    except Exception as e:
        execution_time = (datetime.now() - start_time).total_seconds()
        
        return MCPToolResponse(
            success=False,
            tool_name=request.tool_name,
            result={"error": str(e)},
            execution_time=execution_time,
            timestamp=datetime.now(timezone.utc).isoformat()
        )

@router.get("/tools", response_model=List[str])
async def list_available_tools():
    """List all available MCP tools"""
    return list(MCP_TOOLS.keys())

@router.get("/industries", response_model=List[str])
async def list_supported_industries():
    """List all supported industries"""
    return ["ecommerce", "healthcare", "finance", "technology", "education", "cannabis", "real_estate", "automotive", "food_beverage", "travel", "general"]

@router.get("/templates", response_model=List[IndustryTemplate])
async def get_industry_templates():
    """Get all industry templates"""
    return get_industry_templates()

@router.post("/templates", response_model=IndustryTemplate)
async def create_industry_template(template_data: Dict[str, Any]):
    """Create a new industry template"""
    templates = get_industry_templates()
    
    new_template = IndustryTemplate(
        id=str(uuid.uuid4()),
        name=template_data.get("name", ""),
        description=template_data.get("description", ""),
        industry=template_data.get("industry", "general"),
        tools=template_data.get("tools", []),
        default_context=template_data.get("default_context", {}),
        created_at=datetime.now(timezone.utc).isoformat()
    )
    
    templates.append(new_template)
    save_industry_templates(templates)
    
    return new_template
