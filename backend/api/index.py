"""
Vercel serverless function entry point for the Universal MCP Server Platform
This file serves as the main entry point for Vercel deployment
"""
import os
import sys
from pathlib import Path

# Add the backend directory to Python path
backend_dir = Path(__file__).parent.parent
sys.path.insert(0, str(backend_dir))

# Set environment for Vercel
os.environ.setdefault("ENVIRONMENT", "production")
os.environ.setdefault("VERCEL", "1")

# Import the FastAPI app
try:
    from main import app
except ImportError:
    # Fallback import
    sys.path.insert(0, str(backend_dir.parent))
    from backend.main import app

# Vercel handler using Mangum
try:
    from mangum import Mangum
    handler = Mangum(app, lifespan="off")
except ImportError:
    # Fallback if Mangum is not available
    def handler(event, context):
        return {
            "statusCode": 500,
            "body": "Mangum not available for serverless deployment"
        }

# Export for Vercel
def main(request):
    """Main entry point for Vercel"""
    return handler(request, {})

# Also export the app directly
application = app

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)
