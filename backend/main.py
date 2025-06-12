import os
import pathlib
import json
import dotenv
from fastapi import FastAPI, APIRouter, Depends
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import HTMLResponse
from fastapi.staticfiles import StaticFiles

dotenv.load_dotenv()

from databutton_app.mw.auth_mw import AuthConfig, get_authorized_user


def get_router_config() -> dict:
    try:
        # Note: This file is not available to the agent
        cfg = json.loads(open("routers.json").read())
    except:
        return False
    return cfg


def is_auth_disabled(router_config: dict, name: str) -> bool:
    return router_config["routers"][name]["disableAuth"]


def import_api_routers() -> APIRouter:
    """Create top level router including all user defined endpoints."""
    routes = APIRouter(prefix="/routes")

    router_config = get_router_config()

    src_path = pathlib.Path(__file__).parent

    # Import API routers from "src/app/apis/*/__init__.py"
    apis_path = src_path / "app" / "apis"

    api_names = [
        p.relative_to(apis_path).parent.as_posix()
        for p in apis_path.glob("*/__init__.py")
    ]

    api_module_prefix = "app.apis."

    for name in api_names:
        print(f"Importing API: {name}")
        try:
            api_module = __import__(api_module_prefix + name, fromlist=[name])
            api_router = getattr(api_module, "router", None)
            if isinstance(api_router, APIRouter):
                routes.include_router(
                    api_router,
                    dependencies=(
                        []
                        if is_auth_disabled(router_config, name)
                        else [Depends(get_authorized_user)]
                    ),
                )
        except Exception as e:
            print(e)
            continue

    print(routes.routes)

    return routes


def get_firebase_config() -> dict | None:
    extensions = os.environ.get("DATABUTTON_EXTENSIONS", "[]")
    extensions = json.loads(extensions)

    for ext in extensions:
        if ext["name"] == "firebase-auth":
            return ext["config"]["firebaseConfig"]

    return None


def create_app() -> FastAPI:
    """Create the app. This is called by uvicorn with the factory option to construct the app object."""
    app = FastAPI(title="SEOForge MCP Server", version="1.0.0")
    
    # Add CORS middleware for WordPress integration
    app.add_middleware(
        CORSMiddleware,
        allow_origins=["*"],  # Allow all origins for development
        allow_credentials=True,
        allow_methods=["*"],
        allow_headers=["*"],
    )
    
    # Health check endpoint
    @app.get("/")
    async def health_check():
        return {
            "status": "healthy",
            "service": "SEOForge MCP Server",
            "version": "1.0.0",
            "ai_provider": "Google Gemini",
            "endpoints": [
                "/routes/blog-generator/generate",
                "/routes/seo-analyzer/analyze",
                "/routes/mcp-server/status",
                "/routes/wordpress-manager/connections"
            ]
        }
    
    # Demo page endpoint
    @app.get("/demo", response_class=HTMLResponse)
    async def demo_page():
        try:
            with open("../wordpress_test_demo.html", "r") as f:
                return f.read()
        except FileNotFoundError:
            return HTMLResponse(
                content="<h1>Demo page not found</h1><p>Please check if wordpress_test_demo.html exists.</p>",
                status_code=404
            )
    
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

    for route in app.routes:
        if hasattr(route, "methods"):
            for method in route.methods:
                print(f"{method} {route.path}")

    firebase_config = get_firebase_config()

    if firebase_config is None:
        print("No firebase config found")
        app.state.auth_config = None
    else:
        print("Firebase config found")
        auth_config = {
            "jwks_url": "https://www.googleapis.com/service_accounts/v1/jwk/securetoken@system.gserviceaccount.com",
            "audience": firebase_config["projectId"],
            "header": "authorization",
        }

        app.state.auth_config = AuthConfig(**auth_config)

    return app


app = create_app()
