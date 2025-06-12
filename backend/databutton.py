"""
Mock databutton module for local development
Provides the same interface as the real databutton module
"""

import os
import json
from typing import Any, Dict, Optional

class MockSecrets:
    """Mock secrets manager"""
    
    @staticmethod
    def get(key: str, default: Optional[str] = None) -> Optional[str]:
        """Get secret from environment variables"""
        return os.getenv(key, default)

class MockStorage:
    """Mock storage manager"""
    
    class json:
        @staticmethod
        def get(key: str, default: Any = None) -> Any:
            """Get JSON data from storage (mock implementation)"""
            # In a real implementation, this would read from persistent storage
            # For now, return default values
            if key == "mcp_industry_templates":
                return []
            return default
        
        @staticmethod
        def put(key: str, value: Any) -> None:
            """Store JSON data (mock implementation)"""
            # In a real implementation, this would write to persistent storage
            print(f"Mock storage: Storing {key} = {value}")

# Module-level instances
secrets = MockSecrets()
storage = MockStorage()

# For compatibility
class MockDB:
    secrets = MockSecrets()
    storage = MockStorage()

# Export the mock module interface
__all__ = ['secrets', 'storage']