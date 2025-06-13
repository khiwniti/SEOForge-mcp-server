"""
Production-ready configuration management
"""

import os
import secrets
from typing import List, Optional, Any, Dict
from pydantic import BaseSettings, validator, Field
from functools import lru_cache
import logging

logger = logging.getLogger(__name__)

class Settings(BaseSettings):
    """Application settings with validation and environment support"""
    
    # Application
    app_name: str = "SEOForge MCP Server"
    app_version: str = "1.0.0"
    environment: str = Field(default="development", env="ENVIRONMENT")
    debug: bool = Field(default=False, env="DEBUG")
    
    # Server
    host: str = Field(default="0.0.0.0", env="HOST")
    port: int = Field(default=8083, env="PORT")
    workers: int = Field(default=4, env="WORKERS")
    
    # Security
    secret_key: str = Field(default_factory=lambda: secrets.token_urlsafe(32), env="SECRET_KEY")
    jwt_secret: str = Field(default_factory=lambda: secrets.token_urlsafe(32), env="JWT_SECRET")
    jwt_algorithm: str = Field(default="HS256", env="JWT_ALGORITHM")
    jwt_expire_minutes: int = Field(default=1440, env="JWT_EXPIRE_MINUTES")  # 24 hours
    
    # CORS
    cors_origins: List[str] = Field(default=[], env="CORS_ORIGINS")
    cors_credentials: bool = Field(default=True, env="CORS_CREDENTIALS")
    cors_methods: List[str] = Field(default=["GET", "POST", "PUT", "DELETE"], env="CORS_METHODS")
    cors_headers: List[str] = Field(default=["*"], env="CORS_HEADERS")
    
    # Database
    database_url: str = Field(..., env="DATABASE_URL")
    db_pool_size: int = Field(default=20, env="DB_POOL_SIZE")
    db_max_overflow: int = Field(default=30, env="DB_MAX_OVERFLOW")
    db_pool_timeout: int = Field(default=30, env="DB_POOL_TIMEOUT")
    db_pool_recycle: int = Field(default=3600, env="DB_POOL_RECYCLE")
    
    # Redis
    redis_url: str = Field(..., env="REDIS_URL")
    redis_password: Optional[str] = Field(default=None, env="REDIS_PASSWORD")
    redis_db: int = Field(default=0, env="REDIS_DB")
    redis_pool_size: int = Field(default=10, env="REDIS_POOL_SIZE")
    
    # AI Providers
    openai_api_key: Optional[str] = Field(default=None, env="OPENAI_API_KEY")
    anthropic_api_key: Optional[str] = Field(default=None, env="ANTHROPIC_API_KEY")
    google_ai_api_key: Optional[str] = Field(default=None, env="GOOGLE_AI_API_KEY")
    gemini_api_key: Optional[str] = Field(default=None, env="GEMINI_API_KEY")
    
    # Rate Limiting
    rate_limit_enabled: bool = Field(default=True, env="RATE_LIMIT_ENABLED")
    rate_limit_requests: int = Field(default=100, env="RATE_LIMIT_REQUESTS")
    rate_limit_window: int = Field(default=3600, env="RATE_LIMIT_WINDOW")  # 1 hour
    
    # Caching
    cache_enabled: bool = Field(default=True, env="CACHE_ENABLED")
    cache_ttl: int = Field(default=3600, env="CACHE_TTL")  # 1 hour
    cache_max_size: int = Field(default=1000, env="CACHE_MAX_SIZE")
    
    # Logging
    log_level: str = Field(default="INFO", env="LOG_LEVEL")
    log_format: str = Field(default="json", env="LOG_FORMAT")
    log_file: Optional[str] = Field(default=None, env="LOG_FILE")
    
    # Monitoring
    enable_metrics: bool = Field(default=True, env="ENABLE_METRICS")
    enable_tracing: bool = Field(default=True, env="ENABLE_TRACING")
    metrics_port: int = Field(default=9090, env="METRICS_PORT")
    
    # Content Limits
    max_content_length: int = Field(default=10000, env="MAX_CONTENT_LENGTH")
    max_keywords: int = Field(default=50, env="MAX_KEYWORDS")
    max_requests_per_minute: int = Field(default=60, env="MAX_REQUESTS_PER_MINUTE")
    
    # Request Timeouts
    request_timeout: int = Field(default=30, env="REQUEST_TIMEOUT")
    ai_request_timeout: int = Field(default=60, env="AI_REQUEST_TIMEOUT")
    
    # Health Check
    health_check_interval: int = Field(default=30, env="HEALTH_CHECK_INTERVAL")
    health_check_timeout: int = Field(default=5, env="HEALTH_CHECK_TIMEOUT")
    
    # Backup & Maintenance
    backup_enabled: bool = Field(default=False, env="BACKUP_ENABLED")
    maintenance_mode: bool = Field(default=False, env="MAINTENANCE_MODE")
    
    @validator("cors_origins", pre=True)
    def parse_cors_origins(cls, v):
        if isinstance(v, str):
            return [origin.strip() for origin in v.split(",") if origin.strip()]
        return v
    
    @validator("cors_methods", pre=True)
    def parse_cors_methods(cls, v):
        if isinstance(v, str):
            return [method.strip() for method in v.split(",") if method.strip()]
        return v
    
    @validator("cors_headers", pre=True)
    def parse_cors_headers(cls, v):
        if isinstance(v, str):
            return [header.strip() for header in v.split(",") if header.strip()]
        return v
    
    @validator("environment")
    def validate_environment(cls, v):
        allowed_envs = ["development", "staging", "production"]
        if v not in allowed_envs:
            raise ValueError(f"Environment must be one of: {allowed_envs}")
        return v
    
    @validator("log_level")
    def validate_log_level(cls, v):
        allowed_levels = ["DEBUG", "INFO", "WARNING", "ERROR", "CRITICAL"]
        if v.upper() not in allowed_levels:
            raise ValueError(f"Log level must be one of: {allowed_levels}")
        return v.upper()
    
    @validator("log_format")
    def validate_log_format(cls, v):
        allowed_formats = ["json", "text"]
        if v not in allowed_formats:
            raise ValueError(f"Log format must be one of: {allowed_formats}")
        return v
    
    @property
    def is_production(self) -> bool:
        return self.environment == "production"
    
    @property
    def is_development(self) -> bool:
        return self.environment == "development"
    
    @property
    def database_config(self) -> Dict[str, Any]:
        return {
            "url": self.database_url,
            "pool_size": self.db_pool_size,
            "max_overflow": self.db_max_overflow,
            "pool_timeout": self.db_pool_timeout,
            "pool_recycle": self.db_pool_recycle,
        }
    
    @property
    def redis_config(self) -> Dict[str, Any]:
        return {
            "url": self.redis_url,
            "password": self.redis_password,
            "db": self.redis_db,
            "pool_size": self.redis_pool_size,
        }
    
    @property
    def ai_providers_config(self) -> Dict[str, Optional[str]]:
        return {
            "openai": self.openai_api_key,
            "anthropic": self.anthropic_api_key,
            "google": self.google_ai_api_key or self.gemini_api_key,
        }
    
    def validate_required_settings(self) -> List[str]:
        """Validate that all required settings are present"""
        missing = []
        
        if not self.database_url:
            missing.append("DATABASE_URL")
        
        if not self.redis_url:
            missing.append("REDIS_URL")
        
        if self.is_production:
            if not any(self.ai_providers_config.values()):
                missing.append("At least one AI provider API key")
            
            if not self.cors_origins:
                missing.append("CORS_ORIGINS")
        
        return missing
    
    class Config:
        env_file = ".env"
        env_file_encoding = "utf-8"
        case_sensitive = False

@lru_cache()
def get_settings() -> Settings:
    """Get cached settings instance"""
    settings = Settings()
    
    # Validate required settings
    missing = settings.validate_required_settings()
    if missing:
        error_msg = f"Missing required environment variables: {', '.join(missing)}"
        logger.error(error_msg)
        if settings.is_production:
            raise ValueError(error_msg)
        else:
            logger.warning(f"Development mode: {error_msg}")
    
    return settings

# Global settings instance
settings = get_settings()
