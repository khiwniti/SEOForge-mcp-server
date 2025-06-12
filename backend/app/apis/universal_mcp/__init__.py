"""
Universal MCP Server API
Comprehensive AI-powered content and SEO platform with website intelligence
"""

import asyncio
import json
import time
from datetime import datetime, timezone, timedelta
from typing import Dict, List, Any, Optional
from fastapi import APIRouter, HTTPException, BackgroundTasks, Depends
from pydantic import BaseModel, Field
import logging

# Import our enhanced components
from app.core.mcp_context import MCPContextManager, ContextEntry, ContextType, ContextPriority, ContextQuery
from app.core.ai_orchestrator import AIOrchestrator, AIRequest, TaskType
from app.services.website_intelligence import WebsiteIntelligenceService
import databutton as db

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/universal-mcp", tags=["Universal MCP"])

# Global instances (will be initialized on startup)
context_manager: Optional[MCPContextManager] = None
ai_orchestrator: Optional[AIOrchestrator] = None
website_intelligence: Optional[WebsiteIntelligenceService] = None

# Pydantic Models
class UniversalMCPRequest(BaseModel):
    task_type: str = Field(..., description="Type of task to perform")
    prompt: str = Field(..., description="Main prompt or query")
    context: Dict[str, Any] = Field(default_factory=dict, description="Additional context")
    website_url: Optional[str] = Field(default=None, description="Website URL for analysis")
    industry: Optional[str] = Field(default="auto", description="Industry context (auto-detect if not specified)")
    language: Optional[str] = Field(default="en", description="Target language")
    max_tokens: int = Field(default=2000, description="Maximum tokens for AI response")
    temperature: float = Field(default=0.7, description="AI creativity level")
    use_website_context: bool = Field(default=True, description="Use website content as context")
    deep_analysis: bool = Field(default=False, description="Perform deep website analysis")

class UniversalMCPResponse(BaseModel):
    success: bool
    task_type: str
    result: Dict[str, Any]
    context_used: List[str]
    ai_model_used: str
    processing_time: float
    website_analysis: Optional[Dict[str, Any]] = None
    recommendations: List[str]
    timestamp: str

class WebsiteAnalysisRequest(BaseModel):
    url: str = Field(..., description="Website URL to analyze")
    deep_analysis: bool = Field(default=True, description="Perform comprehensive analysis")
    crawl_website: bool = Field(default=False, description="Crawl multiple pages")
    max_pages: int = Field(default=10, description="Maximum pages to crawl")

class ContentGenerationRequest(BaseModel):
    content_type: str = Field(..., description="Type of content to generate")
    topic: str = Field(..., description="Content topic")
    keywords: List[str] = Field(default_factory=list, description="Target keywords")
    website_url: Optional[str] = Field(default=None, description="Website for context")
    tone: str = Field(default="professional", description="Content tone")
    length: str = Field(default="medium", description="Content length")
    industry: Optional[str] = Field(default="auto", description="Industry context")
    language: str = Field(default="en", description="Content language")

class SEOAnalysisRequest(BaseModel):
    url: Optional[str] = Field(default=None, description="URL to analyze")
    content: Optional[str] = Field(default=None, description="Content to analyze")
    keywords: List[str] = Field(default_factory=list, description="Target keywords")
    competitor_urls: List[str] = Field(default_factory=list, description="Competitor URLs")
    include_recommendations: bool = Field(default=True, description="Include actionable recommendations")

# Initialization functions
async def initialize_mcp_components():
    """Initialize MCP components"""
    global context_manager, ai_orchestrator, website_intelligence
    
    try:
        # Get configuration from environment/secrets
        redis_url = db.secrets.get("REDIS_URL", "redis://localhost:6379")
        postgres_url = db.secrets.get("POSTGRES_URL", "postgresql://user:pass@localhost:5432/mcp_db")
        
        openai_key = db.secrets.get("OPENAI_API_KEY")
        anthropic_key = db.secrets.get("ANTHROPIC_API_KEY") 
        google_key = db.secrets.get("GOOGLE_AI_API_KEY")
        
        if not any([openai_key, anthropic_key, google_key]):
            raise Exception("At least one AI API key must be configured")
        
        # Initialize components
        context_manager = MCPContextManager(redis_url, postgres_url)
        await context_manager.initialize()
        
        ai_orchestrator = AIOrchestrator(
            openai_key or "",
            anthropic_key or "",
            google_key or ""
        )
        
        website_intelligence = WebsiteIntelligenceService()
        await website_intelligence.initialize()
        
        logger.info("Universal MCP components initialized successfully")
        
    except Exception as e:
        logger.error(f"Failed to initialize MCP components: {e}")
        raise

# Dependency to ensure components are initialized
async def get_initialized_components():
    """Ensure MCP components are initialized"""
    if not all([context_manager, ai_orchestrator, website_intelligence]):
        await initialize_mcp_components()
    
    return context_manager, ai_orchestrator, website_intelligence

# API Endpoints

@router.get("/status")
async def get_mcp_status():
    """Get Universal MCP server status"""
    try:
        components = await get_initialized_components()
        
        # Get AI orchestrator health
        ai_health = await ai_orchestrator.health_check() if ai_orchestrator else {"status": "not_initialized"}
        
        return {
            "status": "active",
            "version": "2.0.0",
            "components": {
                "context_manager": "active" if context_manager else "inactive",
                "ai_orchestrator": "active" if ai_orchestrator else "inactive", 
                "website_intelligence": "active" if website_intelligence else "inactive"
            },
            "ai_models": ai_health.get("providers", {}),
            "capabilities": [
                "universal_content_generation",
                "website_intelligence_analysis", 
                "competitive_seo_analysis",
                "multi_industry_support",
                "context_aware_ai",
                "real_time_website_crawling",
                "predictive_seo_analytics"
            ],
            "supported_industries": [
                "ecommerce", "healthcare", "finance", "technology", "education",
                "real_estate", "automotive", "travel", "food", "legal", "general"
            ],
            "supported_languages": ["en", "th", "es", "fr", "de", "ja", "ko"],
            "timestamp": datetime.now(timezone.utc).isoformat()
        }
        
    except Exception as e:
        logger.error(f"Status check failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.post("/process", response_model=UniversalMCPResponse)
async def process_universal_request(
    request: UniversalMCPRequest,
    background_tasks: BackgroundTasks
):
    """Process universal MCP request with intelligent routing"""
    start_time = time.time()
    
    try:
        context_mgr, ai_orch, web_intel = await get_initialized_components()
        
        # Step 1: Website Analysis (if URL provided)
        website_analysis = None
        if request.website_url and request.use_website_context:
            try:
                website_profile = await web_intel.analyze_website(
                    request.website_url, 
                    deep_analysis=request.deep_analysis
                )
                website_analysis = {
                    "url": website_profile.url,
                    "industry": website_profile.industry,
                    "content_analysis": website_profile.content_analysis,
                    "seo_analysis": website_profile.seo_analysis,
                    "technical_analysis": website_profile.technical_analysis
                }
                
                # Auto-detect industry if not specified
                if request.industry == "auto":
                    request.industry = website_profile.industry
                
                # Store website context
                website_context = ContextEntry(
                    id=f"website_{hash(request.website_url)}",
                    type=ContextType.WEBSITE,
                    content=website_analysis,
                    metadata={"url": request.website_url, "analyzed_at": datetime.now(timezone.utc).isoformat()},
                    priority=ContextPriority.HIGH,
                    created_at=datetime.now(timezone.utc),
                    updated_at=datetime.now(timezone.utc),
                    expires_at=datetime.now(timezone.utc) + timedelta(hours=24),
                    tags=[request.industry, "website_analysis"]
                )
                
                await context_mgr.store_context(website_context)
                
            except Exception as e:
                logger.warning(f"Website analysis failed: {e}")
                website_analysis = {"error": str(e)}
        
        # Step 2: Build comprehensive context
        industry_context = await context_mgr.build_industry_context(
            request.industry, 
            request.website_url
        )
        
        # Step 3: Search for relevant context
        context_query = ContextQuery(
            query=request.prompt,
            context_types=[ContextType.INDUSTRY, ContextType.WEBSITE, ContextType.KNOWLEDGE],
            max_results=5,
            min_relevance=0.3
        )
        
        relevant_contexts = await context_mgr.search_context(context_query)
        context_used = [ctx.id for ctx in relevant_contexts]
        
        # Step 4: Prepare AI request
        enhanced_context = {
            **request.context,
            "industry": request.industry,
            "language": request.language,
            "industry_context": industry_context,
            "website_analysis": website_analysis,
            "relevant_contexts": [ctx.content for ctx in relevant_contexts[:3]]
        }
        
        # Map task type
        task_type_mapping = {
            "content_generation": TaskType.CONTENT_GENERATION,
            "seo_analysis": TaskType.SEO_ANALYSIS,
            "keyword_research": TaskType.KEYWORD_RESEARCH,
            "competitive_analysis": TaskType.COMPETITIVE_ANALYSIS,
            "translation": TaskType.TRANSLATION,
            "summarization": TaskType.SUMMARIZATION,
            "technical_writing": TaskType.TECHNICAL_WRITING,
            "creative_writing": TaskType.CREATIVE_WRITING,
            "data_analysis": TaskType.DATA_ANALYSIS
        }
        
        ai_task_type = task_type_mapping.get(request.task_type, TaskType.CONTENT_GENERATION)
        
        ai_request = AIRequest(
            task_type=ai_task_type,
            prompt=request.prompt,
            context=enhanced_context,
            max_tokens=request.max_tokens,
            temperature=request.temperature
        )
        
        # Step 5: Process with AI orchestrator
        ai_response = await ai_orch.process_request(ai_request)
        
        # Step 6: Generate recommendations
        recommendations = await _generate_recommendations(
            request.task_type,
            ai_response.content,
            website_analysis,
            industry_context
        )
        
        # Step 7: Store interaction context for learning
        interaction_context = ContextEntry(
            id=f"interaction_{int(time.time())}",
            type=ContextType.CONVERSATION,
            content={
                "request": request.dict(),
                "response": ai_response.content,
                "recommendations": recommendations
            },
            metadata={
                "task_type": request.task_type,
                "industry": request.industry,
                "ai_model": ai_response.model_used.value
            },
            priority=ContextPriority.MEDIUM,
            created_at=datetime.now(timezone.utc),
            updated_at=datetime.now(timezone.utc),
            expires_at=datetime.now(timezone.utc) + timedelta(days=7),
            tags=[request.task_type, request.industry, "interaction"]
        )
        
        background_tasks.add_task(context_mgr.store_context, interaction_context)
        
        processing_time = time.time() - start_time
        
        return UniversalMCPResponse(
            success=True,
            task_type=request.task_type,
            result={
                "content": ai_response.content,
                "quality_score": ai_response.quality_score,
                "tokens_used": ai_response.tokens_used,
                "metadata": ai_response.metadata
            },
            context_used=context_used,
            ai_model_used=ai_response.model_used.value,
            processing_time=processing_time,
            website_analysis=website_analysis,
            recommendations=recommendations,
            timestamp=datetime.now(timezone.utc).isoformat()
        )
        
    except Exception as e:
        logger.error(f"Universal MCP request failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.post("/analyze-website")
async def analyze_website(request: WebsiteAnalysisRequest):
    """Comprehensive website analysis"""
    try:
        context_mgr, ai_orch, web_intel = await get_initialized_components()
        
        if request.crawl_website:
            # Crawl multiple pages
            crawl_results = await web_intel.crawl_website(
                request.url,
                max_pages=request.max_pages,
                max_depth=3
            )
            return {
                "analysis_type": "crawl",
                "results": crawl_results,
                "timestamp": datetime.now(timezone.utc).isoformat()
            }
        else:
            # Single page analysis
            website_profile = await web_intel.analyze_website(
                request.url,
                deep_analysis=request.deep_analysis
            )
            
            return {
                "analysis_type": "single_page",
                "results": {
                    "url": website_profile.url,
                    "domain": website_profile.domain,
                    "title": website_profile.title,
                    "description": website_profile.description,
                    "industry": website_profile.industry,
                    "language": website_profile.language,
                    "content_analysis": website_profile.content_analysis,
                    "technical_analysis": website_profile.technical_analysis,
                    "seo_analysis": website_profile.seo_analysis,
                    "competitive_analysis": website_profile.competitive_analysis,
                    "social_presence": website_profile.social_presence,
                    "performance_metrics": website_profile.performance_metrics
                },
                "timestamp": datetime.now(timezone.utc).isoformat()
            }
            
    except Exception as e:
        logger.error(f"Website analysis failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.post("/generate-content")
async def generate_content(request: ContentGenerationRequest):
    """Generate content with website context"""
    try:
        mcp_request = UniversalMCPRequest(
            task_type="content_generation",
            prompt=f"Create {request.content_type} about {request.topic}. Target keywords: {', '.join(request.keywords)}. Tone: {request.tone}. Length: {request.length}.",
            context={
                "content_type": request.content_type,
                "keywords": request.keywords,
                "tone": request.tone,
                "length": request.length
            },
            website_url=request.website_url,
            industry=request.industry,
            language=request.language,
            use_website_context=bool(request.website_url)
        )
        
        return await process_universal_request(mcp_request, BackgroundTasks())
        
    except Exception as e:
        logger.error(f"Content generation failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.post("/analyze-seo")
async def analyze_seo(request: SEOAnalysisRequest):
    """Comprehensive SEO analysis"""
    try:
        if request.url:
            # URL-based analysis
            mcp_request = UniversalMCPRequest(
                task_type="seo_analysis",
                prompt=f"Perform comprehensive SEO analysis for the website. Target keywords: {', '.join(request.keywords)}. Include competitor analysis: {bool(request.competitor_urls)}.",
                context={
                    "keywords": request.keywords,
                    "competitor_urls": request.competitor_urls,
                    "include_recommendations": request.include_recommendations
                },
                website_url=request.url,
                use_website_context=True,
                deep_analysis=True
            )
        else:
            # Content-based analysis
            mcp_request = UniversalMCPRequest(
                task_type="seo_analysis",
                prompt=f"Analyze the following content for SEO optimization: {request.content[:500]}... Target keywords: {', '.join(request.keywords)}.",
                context={
                    "content": request.content,
                    "keywords": request.keywords,
                    "include_recommendations": request.include_recommendations
                },
                use_website_context=False
            )
        
        return await process_universal_request(mcp_request, BackgroundTasks())
        
    except Exception as e:
        logger.error(f"SEO analysis failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.get("/industry-analysis/{industry}")
async def analyze_industry(industry: str):
    """Get comprehensive industry analysis"""
    try:
        context_mgr, ai_orch, web_intel = await get_initialized_components()
        
        industry_context = await context_mgr.build_industry_context(industry)
        
        return {
            "industry": industry,
            "analysis": industry_context,
            "timestamp": datetime.now(timezone.utc).isoformat()
        }
        
    except Exception as e:
        logger.error(f"Industry analysis failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.get("/context/search")
async def search_context(
    query: str,
    context_types: Optional[str] = None,
    max_results: int = 10
):
    """Search stored context"""
    try:
        context_mgr, _, _ = await get_initialized_components()
        
        # Parse context types
        types = []
        if context_types:
            type_map = {
                "industry": ContextType.INDUSTRY,
                "website": ContextType.WEBSITE,
                "user": ContextType.USER,
                "conversation": ContextType.CONVERSATION,
                "tool": ContextType.TOOL,
                "knowledge": ContextType.KNOWLEDGE
            }
            types = [type_map[t.strip()] for t in context_types.split(",") if t.strip() in type_map]
        
        context_query = ContextQuery(
            query=query,
            context_types=types,
            max_results=max_results
        )
        
        contexts = await context_mgr.search_context(context_query)
        
        return {
            "query": query,
            "results": [
                {
                    "id": ctx.id,
                    "type": ctx.type.value,
                    "content": ctx.content,
                    "metadata": ctx.metadata,
                    "relevance_score": ctx.relevance_score,
                    "created_at": ctx.created_at.isoformat()
                }
                for ctx in contexts
            ],
            "total_results": len(contexts),
            "timestamp": datetime.now(timezone.utc).isoformat()
        }
        
    except Exception as e:
        logger.error(f"Context search failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.get("/performance/stats")
async def get_performance_stats():
    """Get AI orchestrator performance statistics"""
    try:
        context_mgr, ai_orch, web_intel = await get_initialized_components()
        
        stats = await ai_orch.get_performance_stats()
        
        return {
            "performance_stats": stats,
            "timestamp": datetime.now(timezone.utc).isoformat()
        }
        
    except Exception as e:
        logger.error(f"Performance stats failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# Helper functions

async def _generate_recommendations(
    task_type: str,
    ai_content: str,
    website_analysis: Optional[Dict[str, Any]],
    industry_context: Dict[str, Any]
) -> List[str]:
    """Generate actionable recommendations based on analysis"""
    recommendations = []
    
    try:
        if task_type == "content_generation":
            recommendations.extend([
                "Optimize content for target keywords naturally",
                "Ensure content provides unique value to readers",
                "Include relevant internal and external links",
                "Optimize meta title and description",
                "Use proper heading structure (H1, H2, H3)"
            ])
        
        elif task_type == "seo_analysis":
            if website_analysis:
                seo_data = website_analysis.get("seo_analysis", {})
                
                # Title optimization
                title_opt = seo_data.get("title_optimization", {})
                if not title_opt.get("optimal_length", True):
                    recommendations.append("Optimize page title length (30-60 characters)")
                
                # Meta description
                meta_opt = seo_data.get("meta_description_optimization", {})
                if not meta_opt.get("exists", True):
                    recommendations.append("Add meta description")
                elif not meta_opt.get("optimal_length", True):
                    recommendations.append("Optimize meta description length (120-160 characters)")
                
                # Technical issues
                tech_data = website_analysis.get("technical_analysis", {})
                if not tech_data.get("mobile_friendly", True):
                    recommendations.append("Improve mobile responsiveness")
                if not tech_data.get("https_enabled", True):
                    recommendations.append("Enable HTTPS for security")
                
                # Image optimization
                img_data = tech_data.get("image_optimization", {})
                if img_data.get("alt_text_coverage", 100) < 90:
                    recommendations.append("Add alt text to all images")
        
        # Industry-specific recommendations
        industry = industry_context.get("industry", "general")
        if industry == "ecommerce":
            recommendations.extend([
                "Optimize product descriptions for search",
                "Implement structured data for products",
                "Focus on local SEO for physical stores"
            ])
        elif industry == "healthcare":
            recommendations.extend([
                "Ensure medical content accuracy and citations",
                "Optimize for local medical searches",
                "Include patient testimonials and reviews"
            ])
        elif industry == "finance":
            recommendations.extend([
                "Build trust signals and security badges",
                "Create educational financial content",
                "Optimize for local financial services"
            ])
        
        # Remove duplicates and limit to top 10
        recommendations = list(dict.fromkeys(recommendations))[:10]
        
    except Exception as e:
        logger.warning(f"Failed to generate recommendations: {e}")
        recommendations = ["Continue optimizing content for better search visibility"]
    
    return recommendations