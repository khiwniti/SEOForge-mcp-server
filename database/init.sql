-- Universal MCP Server Database Initialization
-- This script sets up the database schema for the enhanced MCP server

-- Enable required extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";

-- Create database if not exists (this will be handled by docker-compose)
-- CREATE DATABASE IF NOT EXISTS universal_mcp;

-- MCP Contexts table for persistent context storage
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

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_mcp_contexts_type ON mcp_contexts(type);
CREATE INDEX IF NOT EXISTS idx_mcp_contexts_priority ON mcp_contexts(priority);
CREATE INDEX IF NOT EXISTS idx_mcp_contexts_created_at ON mcp_contexts(created_at);
CREATE INDEX IF NOT EXISTS idx_mcp_contexts_expires_at ON mcp_contexts(expires_at);
CREATE INDEX IF NOT EXISTS idx_mcp_contexts_tags ON mcp_contexts USING GIN(tags);
CREATE INDEX IF NOT EXISTS idx_mcp_contexts_search ON mcp_contexts USING GIN(search_vector);
CREATE INDEX IF NOT EXISTS idx_mcp_contexts_content ON mcp_contexts USING GIN(content);
CREATE INDEX IF NOT EXISTS idx_mcp_contexts_metadata ON mcp_contexts USING GIN(metadata);

-- Context relationships for building knowledge graphs
CREATE TABLE IF NOT EXISTS mcp_context_relationships (
    id SERIAL PRIMARY KEY,
    source_context_id VARCHAR(255) REFERENCES mcp_contexts(id) ON DELETE CASCADE,
    target_context_id VARCHAR(255) REFERENCES mcp_contexts(id) ON DELETE CASCADE,
    relationship_type VARCHAR(50) NOT NULL,
    strength FLOAT DEFAULT 1.0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    metadata JSONB DEFAULT '{}'::jsonb
);

CREATE INDEX IF NOT EXISTS idx_mcp_relationships_source ON mcp_context_relationships(source_context_id);
CREATE INDEX IF NOT EXISTS idx_mcp_relationships_target ON mcp_context_relationships(target_context_id);
CREATE INDEX IF NOT EXISTS idx_mcp_relationships_type ON mcp_context_relationships(relationship_type);

-- Website analysis results
CREATE TABLE IF NOT EXISTS website_analyses (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    url VARCHAR(2048) NOT NULL,
    domain VARCHAR(255) NOT NULL,
    title TEXT,
    description TEXT,
    industry VARCHAR(100),
    language VARCHAR(10),
    content_analysis JSONB,
    technical_analysis JSONB,
    seo_analysis JSONB,
    competitive_analysis JSONB,
    social_presence JSONB,
    performance_metrics JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    analysis_version VARCHAR(20) DEFAULT '2.0'
);

CREATE INDEX IF NOT EXISTS idx_website_analyses_url ON website_analyses(url);
CREATE INDEX IF NOT EXISTS idx_website_analyses_domain ON website_analyses(domain);
CREATE INDEX IF NOT EXISTS idx_website_analyses_industry ON website_analyses(industry);
CREATE INDEX IF NOT EXISTS idx_website_analyses_created_at ON website_analyses(created_at);

-- AI model performance tracking
CREATE TABLE IF NOT EXISTS ai_model_performance (
    id SERIAL PRIMARY KEY,
    model_name VARCHAR(100) NOT NULL,
    task_type VARCHAR(50) NOT NULL,
    success_rate FLOAT DEFAULT 0.0,
    avg_response_time FLOAT DEFAULT 0.0,
    avg_quality_score FLOAT DEFAULT 0.0,
    total_requests INTEGER DEFAULT 0,
    last_updated TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    metadata JSONB DEFAULT '{}'::jsonb
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_ai_performance_model_task ON ai_model_performance(model_name, task_type);
CREATE INDEX IF NOT EXISTS idx_ai_performance_updated ON ai_model_performance(last_updated);

-- User sessions and interactions
CREATE TABLE IF NOT EXISTS user_sessions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id VARCHAR(255),
    session_token VARCHAR(255) UNIQUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    expires_at TIMESTAMP WITH TIME ZONE,
    metadata JSONB DEFAULT '{}'::jsonb
);

CREATE INDEX IF NOT EXISTS idx_user_sessions_user_id ON user_sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_user_sessions_token ON user_sessions(session_token);
CREATE INDEX IF NOT EXISTS idx_user_sessions_expires ON user_sessions(expires_at);

-- API usage tracking
CREATE TABLE IF NOT EXISTS api_usage (
    id SERIAL PRIMARY KEY,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL,
    user_id VARCHAR(255),
    request_data JSONB,
    response_data JSONB,
    status_code INTEGER,
    processing_time FLOAT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    ip_address INET,
    user_agent TEXT
);

CREATE INDEX IF NOT EXISTS idx_api_usage_endpoint ON api_usage(endpoint);
CREATE INDEX IF NOT EXISTS idx_api_usage_user_id ON api_usage(user_id);
CREATE INDEX IF NOT EXISTS idx_api_usage_created_at ON api_usage(created_at);
CREATE INDEX IF NOT EXISTS idx_api_usage_status ON api_usage(status_code);

-- Industry knowledge base
CREATE TABLE IF NOT EXISTS industry_knowledge (
    id SERIAL PRIMARY KEY,
    industry VARCHAR(100) NOT NULL,
    knowledge_type VARCHAR(50) NOT NULL,
    content JSONB NOT NULL,
    confidence_score FLOAT DEFAULT 0.0,
    source VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    tags TEXT[] DEFAULT ARRAY[]::TEXT[]
);

CREATE INDEX IF NOT EXISTS idx_industry_knowledge_industry ON industry_knowledge(industry);
CREATE INDEX IF NOT EXISTS idx_industry_knowledge_type ON industry_knowledge(knowledge_type);
CREATE INDEX IF NOT EXISTS idx_industry_knowledge_tags ON industry_knowledge USING GIN(tags);
CREATE INDEX IF NOT EXISTS idx_industry_knowledge_content ON industry_knowledge USING GIN(content);

-- Competitive intelligence data
CREATE TABLE IF NOT EXISTS competitive_intelligence (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    target_domain VARCHAR(255) NOT NULL,
    competitor_domain VARCHAR(255) NOT NULL,
    industry VARCHAR(100),
    analysis_type VARCHAR(50) NOT NULL,
    analysis_data JSONB NOT NULL,
    confidence_score FLOAT DEFAULT 0.0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_competitive_intel_target ON competitive_intelligence(target_domain);
CREATE INDEX IF NOT EXISTS idx_competitive_intel_competitor ON competitive_intelligence(competitor_domain);
CREATE INDEX IF NOT EXISTS idx_competitive_intel_industry ON competitive_intelligence(industry);
CREATE INDEX IF NOT EXISTS idx_competitive_intel_type ON competitive_intelligence(analysis_type);

-- SEO recommendations tracking
CREATE TABLE IF NOT EXISTS seo_recommendations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    url VARCHAR(2048) NOT NULL,
    recommendation_type VARCHAR(100) NOT NULL,
    recommendation_text TEXT NOT NULL,
    priority INTEGER DEFAULT 1,
    implementation_status VARCHAR(50) DEFAULT 'pending',
    impact_score FLOAT DEFAULT 0.0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    implemented_at TIMESTAMP WITH TIME ZONE,
    metadata JSONB DEFAULT '{}'::jsonb
);

CREATE INDEX IF NOT EXISTS idx_seo_recommendations_url ON seo_recommendations(url);
CREATE INDEX IF NOT EXISTS idx_seo_recommendations_type ON seo_recommendations(recommendation_type);
CREATE INDEX IF NOT EXISTS idx_seo_recommendations_status ON seo_recommendations(implementation_status);
CREATE INDEX IF NOT EXISTS idx_seo_recommendations_priority ON seo_recommendations(priority);

-- Content generation history
CREATE TABLE IF NOT EXISTS content_generation_history (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id VARCHAR(255),
    content_type VARCHAR(100) NOT NULL,
    prompt TEXT NOT NULL,
    generated_content TEXT NOT NULL,
    ai_model VARCHAR(100) NOT NULL,
    quality_score FLOAT DEFAULT 0.0,
    tokens_used INTEGER DEFAULT 0,
    processing_time FLOAT DEFAULT 0.0,
    industry VARCHAR(100),
    language VARCHAR(10) DEFAULT 'en',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    metadata JSONB DEFAULT '{}'::jsonb
);

CREATE INDEX IF NOT EXISTS idx_content_history_user ON content_generation_history(user_id);
CREATE INDEX IF NOT EXISTS idx_content_history_type ON content_generation_history(content_type);
CREATE INDEX IF NOT EXISTS idx_content_history_model ON content_generation_history(ai_model);
CREATE INDEX IF NOT EXISTS idx_content_history_industry ON content_generation_history(industry);
CREATE INDEX IF NOT EXISTS idx_content_history_created ON content_generation_history(created_at);

-- Website crawl results
CREATE TABLE IF NOT EXISTS website_crawl_results (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    base_url VARCHAR(2048) NOT NULL,
    crawled_url VARCHAR(2048) NOT NULL,
    depth INTEGER DEFAULT 0,
    page_data JSONB NOT NULL,
    crawl_session_id UUID,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    status VARCHAR(50) DEFAULT 'success'
);

CREATE INDEX IF NOT EXISTS idx_crawl_results_base_url ON website_crawl_results(base_url);
CREATE INDEX IF NOT EXISTS idx_crawl_results_session ON website_crawl_results(crawl_session_id);
CREATE INDEX IF NOT EXISTS idx_crawl_results_depth ON website_crawl_results(depth);
CREATE INDEX IF NOT EXISTS idx_crawl_results_status ON website_crawl_results(status);

-- Functions for automatic updates
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Triggers for automatic timestamp updates
CREATE TRIGGER update_website_analyses_updated_at BEFORE UPDATE ON website_analyses FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_industry_knowledge_updated_at BEFORE UPDATE ON industry_knowledge FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_competitive_intelligence_updated_at BEFORE UPDATE ON competitive_intelligence FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Function to clean up expired contexts
CREATE OR REPLACE FUNCTION cleanup_expired_contexts()
RETURNS INTEGER AS $$
DECLARE
    deleted_count INTEGER;
BEGIN
    DELETE FROM mcp_contexts 
    WHERE expires_at IS NOT NULL AND expires_at < NOW();
    
    GET DIAGNOSTICS deleted_count = ROW_COUNT;
    RETURN deleted_count;
END;
$$ LANGUAGE plpgsql;

-- Function to update search vectors
CREATE OR REPLACE FUNCTION update_search_vector()
RETURNS TRIGGER AS $$
BEGIN
    NEW.search_vector := to_tsvector('english', 
        COALESCE(NEW.content::text, '') || ' ' || 
        COALESCE(NEW.metadata::text, '') || ' ' ||
        COALESCE(array_to_string(NEW.tags, ' '), '')
    );
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger to automatically update search vectors
CREATE TRIGGER update_mcp_contexts_search_vector 
    BEFORE INSERT OR UPDATE ON mcp_contexts 
    FOR EACH ROW EXECUTE FUNCTION update_search_vector();

-- Create initial admin user (optional)
-- INSERT INTO user_sessions (user_id, session_token, expires_at, metadata) 
-- VALUES ('admin', 'admin_token_' || extract(epoch from now()), NOW() + INTERVAL '1 year', '{"role": "admin"}')
-- ON CONFLICT DO NOTHING;

-- Insert some initial industry knowledge
INSERT INTO industry_knowledge (industry, knowledge_type, content, confidence_score, source, tags) VALUES
('ecommerce', 'keywords', '{"primary": ["online shopping", "ecommerce", "buy online", "shop", "store"], "secondary": ["product", "cart", "checkout", "payment", "shipping"]}', 0.9, 'system', ARRAY['keywords', 'ecommerce']),
('healthcare', 'keywords', '{"primary": ["healthcare", "medical", "health", "doctor", "hospital"], "secondary": ["treatment", "patient", "clinic", "medicine", "wellness"]}', 0.9, 'system', ARRAY['keywords', 'healthcare']),
('finance', 'keywords', '{"primary": ["finance", "banking", "investment", "loan", "insurance"], "secondary": ["credit", "mortgage", "financial", "money", "savings"]}', 0.9, 'system', ARRAY['keywords', 'finance']),
('technology', 'keywords', '{"primary": ["technology", "software", "tech", "digital", "innovation"], "secondary": ["app", "cloud", "AI", "development", "solution"]}', 0.9, 'system', ARRAY['keywords', 'technology']),
('real_estate', 'keywords', '{"primary": ["real estate", "property", "home", "house", "apartment"], "secondary": ["rent", "buy", "sell", "mortgage", "investment"]}', 0.9, 'system', ARRAY['keywords', 'real_estate'])
ON CONFLICT DO NOTHING;

-- Create a view for easy context searching
CREATE OR REPLACE VIEW context_search_view AS
SELECT 
    id,
    type,
    content,
    metadata,
    priority,
    created_at,
    updated_at,
    expires_at,
    usage_count,
    relevance_score,
    tags,
    CASE 
        WHEN expires_at IS NULL OR expires_at > NOW() THEN true 
        ELSE false 
    END as is_active
FROM mcp_contexts;

-- Grant permissions (adjust as needed for your setup)
-- GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO your_app_user;
-- GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO your_app_user;

COMMIT;