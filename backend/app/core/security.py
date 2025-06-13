"""
Production-ready security middleware and utilities
"""

import time
import hashlib
import secrets
from datetime import datetime, timedelta
from typing import Optional, Dict, Any, List
from fastapi import HTTPException, Request, Depends, status
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from jose import JWTError, jwt
from passlib.context import CryptContext
import redis
import logging
from functools import wraps

from .config import settings

logger = logging.getLogger(__name__)

# Password hashing
pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")

# JWT token handler
security = HTTPBearer(auto_error=False)

# Redis client for rate limiting and session management
redis_client = redis.from_url(settings.redis_url, decode_responses=True)

class SecurityError(Exception):
    """Custom security exception"""
    pass

class RateLimitExceeded(HTTPException):
    """Rate limit exceeded exception"""
    def __init__(self, detail: str = "Rate limit exceeded"):
        super().__init__(status_code=429, detail=detail)

class AuthenticationError(HTTPException):
    """Authentication error exception"""
    def __init__(self, detail: str = "Authentication failed"):
        super().__init__(status_code=401, detail=detail)

class AuthorizationError(HTTPException):
    """Authorization error exception"""
    def __init__(self, detail: str = "Insufficient permissions"):
        super().__init__(status_code=403, detail=detail)

def create_access_token(data: Dict[str, Any], expires_delta: Optional[timedelta] = None) -> str:
    """Create JWT access token"""
    to_encode = data.copy()
    
    if expires_delta:
        expire = datetime.utcnow() + expires_delta
    else:
        expire = datetime.utcnow() + timedelta(minutes=settings.jwt_expire_minutes)
    
    to_encode.update({"exp": expire, "iat": datetime.utcnow()})
    
    try:
        encoded_jwt = jwt.encode(to_encode, settings.jwt_secret, algorithm=settings.jwt_algorithm)
        return encoded_jwt
    except Exception as e:
        logger.error(f"Failed to create access token: {e}")
        raise SecurityError("Failed to create access token")

def verify_token(token: str) -> Dict[str, Any]:
    """Verify and decode JWT token"""
    try:
        payload = jwt.decode(token, settings.jwt_secret, algorithms=[settings.jwt_algorithm])
        return payload
    except JWTError as e:
        logger.warning(f"Invalid token: {e}")
        raise AuthenticationError("Invalid token")

def hash_password(password: str) -> str:
    """Hash password using bcrypt"""
    return pwd_context.hash(password)

def verify_password(plain_password: str, hashed_password: str) -> bool:
    """Verify password against hash"""
    return pwd_context.verify(plain_password, hashed_password)

def generate_api_key() -> str:
    """Generate secure API key"""
    return secrets.token_urlsafe(32)

def get_client_ip(request: Request) -> str:
    """Get client IP address from request"""
    # Check for forwarded headers (load balancer/proxy)
    forwarded_for = request.headers.get("X-Forwarded-For")
    if forwarded_for:
        return forwarded_for.split(",")[0].strip()
    
    real_ip = request.headers.get("X-Real-IP")
    if real_ip:
        return real_ip
    
    return request.client.host

def create_rate_limit_key(identifier: str, endpoint: str) -> str:
    """Create rate limit key for Redis"""
    return f"rate_limit:{identifier}:{endpoint}"

async def check_rate_limit(
    request: Request,
    identifier: Optional[str] = None,
    max_requests: Optional[int] = None,
    window_seconds: Optional[int] = None
) -> bool:
    """Check if request is within rate limits"""
    if not settings.rate_limit_enabled:
        return True
    
    # Use IP address if no identifier provided
    if not identifier:
        identifier = get_client_ip(request)
    
    # Use default limits if not specified
    max_requests = max_requests or settings.rate_limit_requests
    window_seconds = window_seconds or settings.rate_limit_window
    
    # Create rate limit key
    endpoint = request.url.path
    key = create_rate_limit_key(identifier, endpoint)
    
    try:
        # Get current count
        current = redis_client.get(key)
        
        if current is None:
            # First request in window
            redis_client.setex(key, window_seconds, 1)
            return True
        
        current_count = int(current)
        
        if current_count >= max_requests:
            logger.warning(f"Rate limit exceeded for {identifier} on {endpoint}")
            return False
        
        # Increment counter
        redis_client.incr(key)
        return True
        
    except Exception as e:
        logger.error(f"Rate limiting error: {e}")
        # Allow request if Redis is down (fail open)
        return True

def rate_limit(max_requests: int = None, window_seconds: int = None):
    """Rate limiting decorator"""
    def decorator(func):
        @wraps(func)
        async def wrapper(request: Request, *args, **kwargs):
            if not await check_rate_limit(request, max_requests=max_requests, window_seconds=window_seconds):
                raise RateLimitExceeded()
            return await func(request, *args, **kwargs)
        return wrapper
    return decorator

async def get_current_user(credentials: HTTPAuthorizationCredentials = Depends(security)) -> Optional[Dict[str, Any]]:
    """Get current authenticated user from JWT token"""
    if not credentials:
        return None
    
    try:
        payload = verify_token(credentials.credentials)
        return payload
    except AuthenticationError:
        return None

async def require_auth(credentials: HTTPAuthorizationCredentials = Depends(security)) -> Dict[str, Any]:
    """Require authentication for endpoint"""
    if not credentials:
        raise AuthenticationError("Missing authentication token")
    
    try:
        payload = verify_token(credentials.credentials)
        return payload
    except AuthenticationError:
        raise

def require_permissions(required_permissions: List[str]):
    """Require specific permissions for endpoint"""
    def decorator(func):
        @wraps(func)
        async def wrapper(*args, **kwargs):
            # Get user from dependencies
            user = None
            for arg in args:
                if isinstance(arg, dict) and "user_id" in arg:
                    user = arg
                    break
            
            if not user:
                raise AuthenticationError("Authentication required")
            
            user_permissions = user.get("permissions", [])
            
            if not all(perm in user_permissions for perm in required_permissions):
                raise AuthorizationError("Insufficient permissions")
            
            return await func(*args, **kwargs)
        return wrapper
    return decorator

def validate_api_key(api_key: str) -> bool:
    """Validate API key"""
    if not api_key:
        return False
    
    # In production, this should check against a database
    # For now, we'll use a simple validation
    try:
        # Check if API key exists in Redis cache
        key = f"api_key:{hashlib.sha256(api_key.encode()).hexdigest()}"
        exists = redis_client.exists(key)
        return bool(exists)
    except Exception as e:
        logger.error(f"API key validation error: {e}")
        return False

async def get_api_key_user(request: Request) -> Optional[Dict[str, Any]]:
    """Get user from API key"""
    api_key = request.headers.get("X-API-Key")
    if not api_key:
        return None
    
    if not validate_api_key(api_key):
        return None
    
    # Return basic user info for API key authentication
    return {
        "user_id": f"api_key_{hashlib.sha256(api_key.encode()).hexdigest()[:8]}",
        "auth_type": "api_key",
        "permissions": ["basic_access"]
    }

def sanitize_input(data: Any) -> Any:
    """Sanitize input data to prevent injection attacks"""
    if isinstance(data, str):
        # Remove potentially dangerous characters
        dangerous_chars = ["<", ">", "&", "\"", "'", "/", "\\"]
        for char in dangerous_chars:
            data = data.replace(char, "")
        return data.strip()
    
    elif isinstance(data, dict):
        return {key: sanitize_input(value) for key, value in data.items()}
    
    elif isinstance(data, list):
        return [sanitize_input(item) for item in data]
    
    return data

def validate_content_length(content: str, max_length: Optional[int] = None) -> bool:
    """Validate content length"""
    max_length = max_length or settings.max_content_length
    return len(content) <= max_length

def validate_keywords(keywords: List[str], max_keywords: Optional[int] = None) -> bool:
    """Validate keywords list"""
    max_keywords = max_keywords or settings.max_keywords
    return len(keywords) <= max_keywords

class SecurityHeaders:
    """Security headers middleware"""
    
    @staticmethod
    def add_security_headers(response):
        """Add security headers to response"""
        response.headers["X-Content-Type-Options"] = "nosniff"
        response.headers["X-Frame-Options"] = "DENY"
        response.headers["X-XSS-Protection"] = "1; mode=block"
        response.headers["Strict-Transport-Security"] = "max-age=31536000; includeSubDomains"
        response.headers["Referrer-Policy"] = "strict-origin-when-cross-origin"
        response.headers["Content-Security-Policy"] = "default-src 'self'"
        
        if settings.is_production:
            response.headers["Server"] = "SEOForge"
        
        return response

# Session management
class SessionManager:
    """Manage user sessions"""
    
    @staticmethod
    def create_session(user_id: str, metadata: Dict[str, Any] = None) -> str:
        """Create new user session"""
        session_id = secrets.token_urlsafe(32)
        session_data = {
            "user_id": user_id,
            "created_at": datetime.utcnow().isoformat(),
            "metadata": metadata or {}
        }
        
        try:
            redis_client.setex(
                f"session:{session_id}",
                settings.jwt_expire_minutes * 60,
                str(session_data)
            )
            return session_id
        except Exception as e:
            logger.error(f"Failed to create session: {e}")
            raise SecurityError("Failed to create session")
    
    @staticmethod
    def get_session(session_id: str) -> Optional[Dict[str, Any]]:
        """Get session data"""
        try:
            session_data = redis_client.get(f"session:{session_id}")
            if session_data:
                return eval(session_data)  # In production, use proper JSON parsing
            return None
        except Exception as e:
            logger.error(f"Failed to get session: {e}")
            return None
    
    @staticmethod
    def delete_session(session_id: str) -> bool:
        """Delete session"""
        try:
            result = redis_client.delete(f"session:{session_id}")
            return bool(result)
        except Exception as e:
            logger.error(f"Failed to delete session: {e}")
            return False
