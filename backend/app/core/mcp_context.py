"""
Enhanced MCP Context Management System
Provides persistent context storage, learning, and intelligent retrieval
"""

import json
import hashlib
import asyncio
from datetime import datetime, timezone, timedelta
from typing import Dict, List, Any, Optional, Tuple
from dataclasses import dataclass, asdict
from enum import Enum
try:
    import aioredis
except ImportError:
    # Fallback for compatibility
    import redis.asyncio as aioredis
import asyncpg
from pydantic import BaseModel, Field
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import logging

logger = logging.getLogger(__name__)

class ContextType(Enum):
    INDUSTRY = "industry"
    WEBSITE = "website"
    USER = "user"
    CONVERSATION = "conversation"
    TOOL = "tool"
    KNOWLEDGE = "knowledge"

class ContextPriority(Enum):
    LOW = 1
    MEDIUM = 2
    HIGH = 3
    CRITICAL = 4

@dataclass
class ContextEntry:
    id: str
    type: ContextType
    content: Dict[str, Any]
    metadata: Dict[str, Any]
    priority: ContextPriority
    created_at: datetime
    updated_at: datetime
    expires_at: Optional[datetime] = None
    usage_count: int = 0
    relevance_score: float = 0.0
    tags: List[str] = None
    
    def __post_init__(self):
        if self.tags is None:
            self.tags = []

class ContextQuery(BaseModel):
    query: str = Field(..., description="Search query for context")
    context_types: List[ContextType] = Field(default_factory=list, description="Types of context to search")
    max_results: int = Field(default=10, description="Maximum number of results")
    min_relevance: float = Field(default=0.1, description="Minimum relevance score")
    include_expired: bool = Field(default=False, description="Include expired context")
    user_id: Optional[str] = Field(default=None, description="User ID for personalized context")

class MCPContextManager:
    """
    Advanced context management system for MCP server
    Provides persistent storage, intelligent retrieval, and learning capabilities
    """
    
    def __init__(self, redis_url: str, postgres_url: str):
        self.redis_url = redis_url
        self.postgres_url = postgres_url
        self.redis_pool = None
        self.postgres_pool = None
        self.vectorizer = TfidfVectorizer(max_features=1000, stop_words='english')
        self.context_cache = {}
        
    async def initialize(self):
        """Initialize database connections and create tables"""
        try:
            # Initialize Redis connection
            self.redis_pool = aioredis.from_url(self.redis_url, decode_responses=True)
            
            # Initialize PostgreSQL connection
            self.postgres_pool = await asyncpg.create_pool(self.postgres_url)
            
            # Create database tables
            await self._create_tables()
            
            logger.info("MCP Context Manager initialized successfully")
            
        except Exception as e:
            logger.error(f"Failed to initialize MCP Context Manager: {e}")
            raise
    
    async def _create_tables(self):
        """Create necessary database tables"""
        async with self.postgres_pool.acquire() as conn:
            await conn.execute("""
                CREATE TABLE IF NOT EXISTS mcp_contexts (
                    id VARCHAR(255) PRIMARY KEY,
                    type VARCHAR(50) NOT NULL,
                    content JSONB NOT NULL,
                    metadata JSONB NOT NULL,
                    priority INTEGER NOT NULL,
                    created_at TIMESTAMP WITH TIME ZONE NOT NULL,
                    updated_at TIMESTAMP WITH TIME ZONE NOT NULL,
                    expires_at TIMESTAMP WITH TIME ZONE,
                    usage_count INTEGER DEFAULT 0,
                    relevance_score FLOAT DEFAULT 0.0,
                    tags TEXT[] DEFAULT ARRAY[]::TEXT[],
                    search_vector TSVECTOR
                );
                
                CREATE INDEX IF NOT EXISTS idx_mcp_contexts_type ON mcp_contexts(type);
                CREATE INDEX IF NOT EXISTS idx_mcp_contexts_priority ON mcp_contexts(priority);
                CREATE INDEX IF NOT EXISTS idx_mcp_contexts_created_at ON mcp_contexts(created_at);
                CREATE INDEX IF NOT EXISTS idx_mcp_contexts_tags ON mcp_contexts USING GIN(tags);
                CREATE INDEX IF NOT EXISTS idx_mcp_contexts_search ON mcp_contexts USING GIN(search_vector);
                
                CREATE TABLE IF NOT EXISTS mcp_context_relationships (
                    id SERIAL PRIMARY KEY,
                    source_context_id VARCHAR(255) REFERENCES mcp_contexts(id) ON DELETE CASCADE,
                    target_context_id VARCHAR(255) REFERENCES mcp_contexts(id) ON DELETE CASCADE,
                    relationship_type VARCHAR(50) NOT NULL,
                    strength FLOAT DEFAULT 1.0,
                    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
                );
                
                CREATE INDEX IF NOT EXISTS idx_mcp_relationships_source ON mcp_context_relationships(source_context_id);
                CREATE INDEX IF NOT EXISTS idx_mcp_relationships_target ON mcp_context_relationships(target_context_id);
            """)
    
    async def store_context(self, context: ContextEntry) -> bool:
        """Store context entry in both PostgreSQL and Redis"""
        try:
            # Store in PostgreSQL for persistence
            async with self.postgres_pool.acquire() as conn:
                await conn.execute("""
                    INSERT INTO mcp_contexts 
                    (id, type, content, metadata, priority, created_at, updated_at, expires_at, usage_count, relevance_score, tags, search_vector)
                    VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, to_tsvector('english', $12))
                    ON CONFLICT (id) DO UPDATE SET
                        content = EXCLUDED.content,
                        metadata = EXCLUDED.metadata,
                        updated_at = EXCLUDED.updated_at,
                        usage_count = EXCLUDED.usage_count,
                        relevance_score = EXCLUDED.relevance_score,
                        tags = EXCLUDED.tags,
                        search_vector = EXCLUDED.search_vector
                """, 
                    context.id,
                    context.type.value,
                    json.dumps(context.content),
                    json.dumps(context.metadata),
                    context.priority.value,
                    context.created_at,
                    context.updated_at,
                    context.expires_at,
                    context.usage_count,
                    context.relevance_score,
                    context.tags,
                    json.dumps(context.content)  # For full-text search
                )
            
            # Cache in Redis for fast access
            cache_key = f"mcp_context:{context.id}"
            await self.redis_pool.setex(
                cache_key, 
                3600,  # 1 hour TTL
                json.dumps(asdict(context), default=str)
            )
            
            logger.debug(f"Stored context: {context.id}")
            return True
            
        except Exception as e:
            logger.error(f"Failed to store context {context.id}: {e}")
            return False
    
    async def retrieve_context(self, context_id: str) -> Optional[ContextEntry]:
        """Retrieve context by ID, first from cache, then from database"""
        try:
            # Try Redis cache first
            cache_key = f"mcp_context:{context_id}"
            cached_data = await self.redis_pool.get(cache_key)
            
            if cached_data:
                data = json.loads(cached_data)
                return self._dict_to_context_entry(data)
            
            # Fallback to PostgreSQL
            async with self.postgres_pool.acquire() as conn:
                row = await conn.fetchrow(
                    "SELECT * FROM mcp_contexts WHERE id = $1", context_id
                )
                
                if row:
                    context = self._row_to_context_entry(row)
                    
                    # Update cache
                    await self.redis_pool.setex(
                        cache_key, 
                        3600,
                        json.dumps(asdict(context), default=str)
                    )
                    
                    return context
            
            return None
            
        except Exception as e:
            logger.error(f"Failed to retrieve context {context_id}: {e}")
            return None
    
    async def search_context(self, query: ContextQuery) -> List[ContextEntry]:
        """Search for relevant context entries using semantic similarity"""
        try:
            # Build SQL query
            sql_conditions = []
            params = []
            param_count = 0
            
            # Filter by context types
            if query.context_types:
                param_count += 1
                sql_conditions.append(f"type = ANY(${param_count})")
                params.append([ct.value for ct in query.context_types])
            
            # Filter by expiration
            if not query.include_expired:
                param_count += 1
                sql_conditions.append(f"(expires_at IS NULL OR expires_at > ${param_count})")
                params.append(datetime.now(timezone.utc))
            
            # Full-text search
            param_count += 1
            sql_conditions.append(f"search_vector @@ plainto_tsquery('english', ${param_count})")
            params.append(query.query)
            
            where_clause = " AND ".join(sql_conditions) if sql_conditions else "TRUE"
            
            sql = f"""
                SELECT *, ts_rank(search_vector, plainto_tsquery('english', ${param_count})) as rank
                FROM mcp_contexts 
                WHERE {where_clause}
                ORDER BY rank DESC, relevance_score DESC, usage_count DESC
                LIMIT {query.max_results}
            """
            
            async with self.postgres_pool.acquire() as conn:
                rows = await conn.fetch(sql, *params)
                
                contexts = []
                for row in rows:
                    context = self._row_to_context_entry(row)
                    if context.relevance_score >= query.min_relevance:
                        contexts.append(context)
                
                # Update usage counts
                if contexts:
                    context_ids = [c.id for c in contexts]
                    await conn.execute(
                        "UPDATE mcp_contexts SET usage_count = usage_count + 1 WHERE id = ANY($1)",
                        context_ids
                    )
                
                return contexts
                
        except Exception as e:
            logger.error(f"Failed to search context: {e}")
            return []
    
    async def build_industry_context(self, industry: str, website_url: Optional[str] = None) -> Dict[str, Any]:
        """Build comprehensive industry context from multiple sources"""
        try:
            context_data = {
                "industry": industry,
                "website_url": website_url,
                "knowledge_base": await self._get_industry_knowledge(industry),
                "market_data": await self._get_market_data(industry),
                "competitors": await self._get_competitor_data(industry),
                "trends": await self._get_industry_trends(industry),
                "regulations": await self._get_regulatory_info(industry),
                "best_practices": await self._get_best_practices(industry)
            }
            
            # If website URL provided, analyze it for additional context
            if website_url:
                website_context = await self._analyze_website(website_url)
                context_data["website_analysis"] = website_context
            
            # Store as context entry
            context_id = self._generate_context_id("industry", industry)
            context_entry = ContextEntry(
                id=context_id,
                type=ContextType.INDUSTRY,
                content=context_data,
                metadata={"industry": industry, "website_url": website_url},
                priority=ContextPriority.HIGH,
                created_at=datetime.now(timezone.utc),
                updated_at=datetime.now(timezone.utc),
                expires_at=datetime.now(timezone.utc) + timedelta(hours=24),
                tags=[industry, "industry_context"]
            )
            
            await self.store_context(context_entry)
            
            return context_data
            
        except Exception as e:
            logger.error(f"Failed to build industry context for {industry}: {e}")
            return {"industry": industry, "error": str(e)}
    
    async def _analyze_website(self, url: str) -> Dict[str, Any]:
        """Analyze website for context building"""
        try:
            # Import web scraping tools
            import aiohttp
            from bs4 import BeautifulSoup
            import re
            
            async with aiohttp.ClientSession() as session:
                async with session.get(url, timeout=aiohttp.ClientTimeout(total=30)) as response:
                    if response.status == 200:
                        html = await response.text()
                        soup = BeautifulSoup(html, 'html.parser')
                        
                        # Extract key information
                        analysis = {
                            "title": soup.title.string if soup.title else "",
                            "meta_description": "",
                            "headings": [],
                            "content_keywords": [],
                            "images": [],
                            "links": [],
                            "structured_data": [],
                            "technology_stack": [],
                            "content_analysis": {}
                        }
                        
                        # Meta description
                        meta_desc = soup.find("meta", attrs={"name": "description"})
                        if meta_desc:
                            analysis["meta_description"] = meta_desc.get("content", "")
                        
                        # Headings
                        for i in range(1, 7):
                            headings = soup.find_all(f"h{i}")
                            for heading in headings:
                                analysis["headings"].append({
                                    "level": i,
                                    "text": heading.get_text().strip()
                                })
                        
                        # Extract main content
                        content_text = soup.get_text()
                        words = re.findall(r'\b\w+\b', content_text.lower())
                        
                        # Simple keyword extraction (top 20 most common words)
                        from collections import Counter
                        word_counts = Counter(words)
                        common_words = {'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should'}
                        analysis["content_keywords"] = [
                            {"word": word, "count": count} 
                            for word, count in word_counts.most_common(20)
                            if word not in common_words and len(word) > 2
                        ]
                        
                        # Images
                        images = soup.find_all("img")
                        analysis["images"] = [
                            {"src": img.get("src", ""), "alt": img.get("alt", "")}
                            for img in images[:10]  # Limit to first 10 images
                        ]
                        
                        # Internal links
                        links = soup.find_all("a", href=True)
                        analysis["links"] = [
                            {"href": link["href"], "text": link.get_text().strip()}
                            for link in links[:20]  # Limit to first 20 links
                        ]
                        
                        # Content analysis
                        analysis["content_analysis"] = {
                            "word_count": len(words),
                            "paragraph_count": len(soup.find_all("p")),
                            "heading_count": len(analysis["headings"]),
                            "image_count": len(analysis["images"]),
                            "link_count": len(analysis["links"])
                        }
                        
                        return analysis
                    
                    else:
                        return {"error": f"Failed to fetch website: HTTP {response.status}"}
                        
        except Exception as e:
            logger.error(f"Failed to analyze website {url}: {e}")
            return {"error": str(e)}
    
    async def _get_industry_knowledge(self, industry: str) -> Dict[str, Any]:
        """Get industry-specific knowledge base"""
        knowledge_base = {
            "ecommerce": {
                "key_metrics": ["conversion_rate", "cart_abandonment", "customer_lifetime_value"],
                "content_types": ["product_descriptions", "buying_guides", "reviews"],
                "seo_focus": ["product_pages", "category_pages", "local_seo"]
            },
            "healthcare": {
                "key_metrics": ["patient_satisfaction", "appointment_bookings", "health_outcomes"],
                "content_types": ["medical_articles", "patient_guides", "treatment_info"],
                "seo_focus": ["local_medical_seo", "medical_authority", "patient_education"]
            },
            "finance": {
                "key_metrics": ["lead_generation", "application_completion", "customer_acquisition"],
                "content_types": ["financial_guides", "market_analysis", "product_comparisons"],
                "seo_focus": ["trust_signals", "regulatory_compliance", "local_finance"]
            },
            "technology": {
                "key_metrics": ["user_engagement", "feature_adoption", "technical_performance"],
                "content_types": ["technical_docs", "tutorials", "case_studies"],
                "seo_focus": ["technical_seo", "developer_content", "product_demos"]
            },
            "real_estate": {
                "key_metrics": ["property_views", "inquiry_generation", "market_share"],
                "content_types": ["property_listings", "market_reports", "buying_guides"],
                "seo_focus": ["local_seo", "property_search", "market_analysis"]
            }
        }
        
        return knowledge_base.get(industry, {
            "key_metrics": ["traffic", "engagement", "conversions"],
            "content_types": ["blog_posts", "guides", "case_studies"],
            "seo_focus": ["content_marketing", "technical_seo", "user_experience"]
        })
    
    async def _get_market_data(self, industry: str) -> Dict[str, Any]:
        """Get market data for the industry"""
        # Simulated market data - in production, this would connect to real market APIs
        return {
            "market_size": "Growing",
            "growth_rate": "5-15% annually",
            "key_players": ["Market Leader 1", "Market Leader 2", "Market Leader 3"],
            "market_trends": ["Digital transformation", "Customer experience focus", "Sustainability"],
            "challenges": ["Competition", "Regulation", "Technology adoption"]
        }
    
    async def _get_competitor_data(self, industry: str) -> List[Dict[str, Any]]:
        """Get competitor analysis data"""
        # Simulated competitor data
        return [
            {"name": "Competitor 1", "market_share": "25%", "strengths": ["Brand recognition", "Customer service"]},
            {"name": "Competitor 2", "market_share": "20%", "strengths": ["Innovation", "Technology"]},
            {"name": "Competitor 3", "market_share": "15%", "strengths": ["Pricing", "Distribution"]}
        ]
    
    async def _get_industry_trends(self, industry: str) -> List[str]:
        """Get current industry trends"""
        trend_map = {
            "ecommerce": ["Mobile commerce", "Personalization", "Sustainability", "Social commerce"],
            "healthcare": ["Telemedicine", "AI diagnostics", "Patient experience", "Digital health"],
            "finance": ["Fintech innovation", "Digital banking", "Cryptocurrency", "Regulatory compliance"],
            "technology": ["AI/ML", "Cloud computing", "Cybersecurity", "Remote work"],
            "real_estate": ["PropTech", "Virtual tours", "Smart homes", "Sustainable building"]
        }
        
        return trend_map.get(industry, ["Digital transformation", "Customer experience", "Sustainability", "Innovation"])
    
    async def _get_regulatory_info(self, industry: str) -> Dict[str, Any]:
        """Get regulatory information for the industry"""
        return {
            "key_regulations": ["Data privacy", "Industry-specific compliance"],
            "compliance_requirements": ["Regular audits", "Documentation", "Training"],
            "recent_changes": ["Updated privacy laws", "New industry standards"]
        }
    
    async def _get_best_practices(self, industry: str) -> List[str]:
        """Get industry best practices"""
        return [
            "Focus on customer experience",
            "Maintain regulatory compliance",
            "Invest in technology",
            "Build strong brand presence",
            "Optimize for mobile"
        ]
    
    def _generate_context_id(self, context_type: str, identifier: str) -> str:
        """Generate unique context ID"""
        content = f"{context_type}:{identifier}:{datetime.now().isoformat()}"
        return hashlib.md5(content.encode()).hexdigest()
    
    def _row_to_context_entry(self, row) -> ContextEntry:
        """Convert database row to ContextEntry"""
        return ContextEntry(
            id=row['id'],
            type=ContextType(row['type']),
            content=json.loads(row['content']),
            metadata=json.loads(row['metadata']),
            priority=ContextPriority(row['priority']),
            created_at=row['created_at'],
            updated_at=row['updated_at'],
            expires_at=row['expires_at'],
            usage_count=row['usage_count'],
            relevance_score=row['relevance_score'],
            tags=row['tags'] or []
        )
    
    def _dict_to_context_entry(self, data: Dict) -> ContextEntry:
        """Convert dictionary to ContextEntry"""
        return ContextEntry(
            id=data['id'],
            type=ContextType(data['type']),
            content=data['content'],
            metadata=data['metadata'],
            priority=ContextPriority(data['priority']),
            created_at=datetime.fromisoformat(data['created_at'].replace('Z', '+00:00')),
            updated_at=datetime.fromisoformat(data['updated_at'].replace('Z', '+00:00')),
            expires_at=datetime.fromisoformat(data['expires_at'].replace('Z', '+00:00')) if data.get('expires_at') else None,
            usage_count=data['usage_count'],
            relevance_score=data['relevance_score'],
            tags=data['tags']
        )
    
    async def cleanup_expired_contexts(self):
        """Clean up expired context entries"""
        try:
            async with self.postgres_pool.acquire() as conn:
                deleted_count = await conn.fetchval("""
                    DELETE FROM mcp_contexts 
                    WHERE expires_at IS NOT NULL AND expires_at < $1
                    RETURNING COUNT(*)
                """, datetime.now(timezone.utc))
                
                logger.info(f"Cleaned up {deleted_count} expired context entries")
                
        except Exception as e:
            logger.error(f"Failed to cleanup expired contexts: {e}")
    
    async def close(self):
        """Close database connections"""
        if self.redis_pool:
            await self.redis_pool.close()
        if self.postgres_pool:
            await self.postgres_pool.close()