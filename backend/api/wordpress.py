from typing import Optional, Dict, Any
import hashlib
import time
from fastapi import HTTPException, Request, Depends
from fastapi.security import APIKeyHeader
import jwt
from pydantic import BaseModel

class WordPressAuth(BaseModel):
    site_url: str
    nonce: str
    timestamp: int

class WordPressConfig:
    def __init__(self):
        self.nonce_lifetime = 24 * 60 * 60  # 24 hours
        self.rate_limit_requests = 100  # requests per hour
        self.rate_limit_window = 60 * 60  # 1 hour

    def validate_nonce(self, nonce: str, site_url: str, timestamp: int) -> bool:
        """Validate WordPress nonce"""
        if time.time() - timestamp > self.nonce_lifetime:
            return False
        
        # Create verification hash
        verification = hashlib.sha256(f"{site_url}:{timestamp}".encode()).hexdigest()
        return nonce == verification

    def create_access_token(self, site_url: str) -> str:
        """Create JWT access token for WordPress site"""
        payload = {
            "site_url": site_url,
            "exp": time.time() + self.nonce_lifetime
        }
        return jwt.encode(payload, "your-secret-key", algorithm="HS256")

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

wp_config = WordPressConfig()
rate_limiter = RateLimiter()
api_key_header = APIKeyHeader(name="X-WordPress-Key")

async def verify_wordpress_request(
    request: Request,
    api_key: str = Depends(api_key_header)
) -> Dict[str, Any]:
    """Verify WordPress request authentication and rate limiting"""
    try:
        # Get WordPress site URL from headers
        site_url = request.headers.get("X-WordPress-Site")
        if not site_url:
            raise HTTPException(status_code=400, message="Missing WordPress site URL")

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

        if not wp_config.validate_nonce(nonce, site_url, int(timestamp)):
            raise HTTPException(
                status_code=401,
                detail="Invalid or expired authentication"
            )

        return {"site_url": site_url}

    except ValueError:
        raise HTTPException(
            status_code=400,
            detail="Invalid authentication format"
        )
    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"Authentication error: {str(e)}"
        )
