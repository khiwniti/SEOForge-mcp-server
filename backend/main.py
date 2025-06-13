import pathlib
import json
import time
import uuid
import logging

import dotenv
from fastapi import FastAPI, APIRouter, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.middleware.trustedhost import TrustedHostMiddleware
from fastapi.responses import JSONResponse

# Load environment variables
dotenv.load_dotenv()

# Import our production-ready modules
from app.core.config import settings
from app.core.security import SecurityHeaders, get_client_ip, check_rate_limit, RateLimitExceeded
from app.core.logging_config import RequestLogger

# Set up logging
logger = logging.getLogger(__name__)


def get_router_config() -> dict:
    """Get router configuration from file"""
    try:
        config_path = pathlib.Path(__file__).parent / "routers.json"
        if config_path.exists():
            with open(config_path) as f:
                return json.load(f)
    except Exception as e:
        logger.warning(f"Could not load router config: {e}")

    # Return default config if file doesn't exist
    return {
        "routers": {
            "blog_generator": {"disableAuth": True},
            "seo_analyzer": {"disableAuth": True},
            "mcp_server": {"disableAuth": True},
            "wordpress_manager": {"disableAuth": True},
            "universal_mcp": {"disableAuth": True},
            "flux_image_gen": {"disableAuth": True}
        }
    }


def is_auth_disabled(router_config: dict, name: str) -> bool:
    """Check if authentication is disabled for a router"""
    return router_config.get("routers", {}).get(name, {}).get("disableAuth", False)


def import_api_routers() -> APIRouter:
    """Create top level router including all user defined endpoints."""
    routes = APIRouter(prefix="/api")

    src_path = pathlib.Path(__file__).parent

    # Import API routers from "app/apis/*/__init__.py"
    apis_path = src_path / "app" / "apis"

    if not apis_path.exists():
        logger.warning(f"APIs path does not exist: {apis_path}")
        return routes

    api_names = [
        p.parent.name
        for p in apis_path.glob("*/__init__.py")
        if p.parent.is_dir()
    ]

    api_module_prefix = "app.apis."

    for name in api_names:
        logger.info(f"Importing API: {name}")
        try:
            api_module = __import__(api_module_prefix + name, fromlist=[name])
            api_router = getattr(api_module, "router", None)
            if isinstance(api_router, APIRouter):
                # For now, disable auth for all routes in development
                routes.include_router(api_router)
                logger.info(f"Successfully imported API router: {name}")
        except Exception as e:
            logger.error(f"Failed to import API {name}: {e}")
            continue

    logger.info(f"Loaded {len(routes.routes)} API routes")
    return routes


async def rate_limit_middleware(request: Request, call_next):
    """Rate limiting middleware"""
    try:
        # Check rate limit
        if not await check_rate_limit(request):
            raise RateLimitExceeded()

        response = await call_next(request)
        return response
    except RateLimitExceeded:
        return JSONResponse(
            status_code=429,
            content={"detail": "Rate limit exceeded"}
        )


async def request_logging_middleware(request: Request, call_next):
    """Request logging middleware"""
    start_time = time.time()
    request_id = str(uuid.uuid4())

    # Add request ID to request state
    request.state.request_id = request_id

    try:
        response = await call_next(request)
        processing_time = time.time() - start_time

        # Log request
        RequestLogger.log_request(
            method=request.method,
            url=request.url,
            status_code=response.status_code,
            processing_time=processing_time,
            ip_address=get_client_ip(request),
            request_id=request_id
        )

        # Add request ID to response headers
        response.headers["X-Request-ID"] = request_id

        return response
    except Exception as e:
        processing_time = time.time() - start_time

        # Log error
        RequestLogger.log_request(
            method=request.method,
            url=request.url,
            status_code=500,
            processing_time=processing_time,
            ip_address=get_client_ip(request),
            request_id=request_id,
            error=str(e)
        )

        raise


def create_app() -> FastAPI:
    """Create the production-ready FastAPI application"""

    # Create FastAPI app with production settings
    app = FastAPI(
        title=settings.app_name,
        version=settings.app_version,
        debug=settings.debug,
        docs_url="/docs" if not settings.is_production else None,
        redoc_url="/redoc" if not settings.is_production else None,
    )

    # Add security headers middleware
    @app.middleware("http")
    async def add_security_headers(request: Request, call_next):
        response = await call_next(request)
        return SecurityHeaders.add_security_headers(response)

    # Add request logging middleware
    app.middleware("http")(request_logging_middleware)

    # Add rate limiting middleware
    if settings.rate_limit_enabled:
        app.middleware("http")(rate_limit_middleware)

    # Add CORS middleware with production settings
    if settings.cors_origins:
        app.add_middleware(
            CORSMiddleware,
            allow_origins=settings.cors_origins,
            allow_credentials=settings.cors_credentials,
            allow_methods=settings.cors_methods,
            allow_headers=settings.cors_headers,
        )

    # Add trusted host middleware for production
    if settings.is_production and settings.cors_origins:
        trusted_hosts = [origin.replace("https://", "").replace("http://", "") for origin in settings.cors_origins]
        app.add_middleware(TrustedHostMiddleware, allowed_hosts=trusted_hosts)

    # Health check endpoint
    @app.get("/health")
    async def health_check():
        """Health check endpoint for load balancers"""
        return {
            "status": "healthy",
            "service": settings.app_name,
            "version": settings.app_version,
            "environment": settings.environment,
            "timestamp": time.time(),
            "endpoints": [
                "/api/blog-generator/generate",
                "/api/seo-analyzer/analyze",
                "/api/mcp-server/status",
                "/api/wordpress-manager/connections"
            ]
        }

    # Root endpoint
    @app.get("/")
    async def root():
        """Root endpoint"""
        if settings.is_production:
            return {"message": "SEOForge MCP Server", "version": settings.app_version}
        else:
            return {
                "message": "SEOForge MCP Server - Development",
                "version": settings.app_version,
                "docs": "/docs",
                "health": "/health"
            }

    # Maintenance mode check
    @app.middleware("http")
    async def maintenance_mode_check(request: Request, call_next):
        if settings.maintenance_mode and request.url.path not in ["/health", "/"]:
            return JSONResponse(
                status_code=503,
                content={"detail": "Service temporarily unavailable for maintenance"}
            )
        return await call_next(request)
    
    app.include_router(import_api_routers())
    
    # Include Universal MCP router (simplified version)
    try:
        from app.apis.universal_mcp_simple import router as universal_mcp_router
        app.include_router(universal_mcp_router)
        print("Universal MCP router (simplified) included successfully")
    except Exception as e:
        print(f"Failed to include Universal MCP router: {e}")
        # Fallback to basic router if available
        try:
            from app.apis.universal_mcp import router as fallback_router
            app.include_router(fallback_router)
            print("Fallback Universal MCP router included")
        except Exception as e2:
            print(f"Fallback also failed: {e2}")

    # Log all registered routes in development
    if not settings.is_production:
        logger.info("Registered routes:")
        for route in app.routes:
            if hasattr(route, "methods"):
                for method in route.methods:
                    logger.info(f"{method} {route.path}")

    # Set application state
    app.state.settings = settings
    app.state.auth_config = None  # Will be configured separately if needed

    logger.info(f"Application created - Environment: {settings.environment}")
    return app


app = create_app()
