"""
Vercel-compatible entry point for the Universal MCP Server Platform
"""
import os
import sys
from pathlib import Path

# Add the current directory to Python path
current_dir = Path(__file__).parent
sys.path.insert(0, str(current_dir))

# Set environment variables for Vercel
os.environ.setdefault("ENVIRONMENT", "production")
os.environ.setdefault("PYTHONPATH", str(current_dir))

# Import the main FastAPI app
from main import app

# Vercel handler
def handler(event, context):
    """
    Vercel serverless function handler
    """
    from mangum import Mangum
    
    # Create Mangum adapter for AWS Lambda/Vercel
    asgi_handler = Mangum(app, lifespan="off")
    
    return asgi_handler(event, context)

# Export the app for Vercel
application = app

# For local development
if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)
