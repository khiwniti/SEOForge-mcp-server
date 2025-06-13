"""
Production-ready logging configuration
"""

import logging
import logging.config
import sys
import json
from datetime import datetime
from typing import Dict, Any, Optional
from pathlib import Path

from .config import settings

class JSONFormatter(logging.Formatter):
    """Custom JSON formatter for structured logging"""
    
    def format(self, record: logging.LogRecord) -> str:
        """Format log record as JSON"""
        log_entry = {
            "timestamp": datetime.utcnow().isoformat() + "Z",
            "level": record.levelname,
            "logger": record.name,
            "message": record.getMessage(),
            "module": record.module,
            "function": record.funcName,
            "line": record.lineno,
        }
        
        # Add exception info if present
        if record.exc_info:
            log_entry["exception"] = self.formatException(record.exc_info)
        
        # Add extra fields
        if hasattr(record, "user_id"):
            log_entry["user_id"] = record.user_id
        
        if hasattr(record, "request_id"):
            log_entry["request_id"] = record.request_id
        
        if hasattr(record, "endpoint"):
            log_entry["endpoint"] = record.endpoint
        
        if hasattr(record, "method"):
            log_entry["method"] = record.method
        
        if hasattr(record, "status_code"):
            log_entry["status_code"] = record.status_code
        
        if hasattr(record, "processing_time"):
            log_entry["processing_time"] = record.processing_time
        
        if hasattr(record, "ip_address"):
            log_entry["ip_address"] = record.ip_address
        
        return json.dumps(log_entry, ensure_ascii=False)

class TextFormatter(logging.Formatter):
    """Custom text formatter for human-readable logs"""
    
    def __init__(self):
        super().__init__(
            fmt="%(asctime)s - %(name)s - %(levelname)s - %(message)s",
            datefmt="%Y-%m-%d %H:%M:%S"
        )

def setup_logging():
    """Setup logging configuration"""
    
    # Create logs directory if it doesn't exist
    if settings.log_file:
        log_path = Path(settings.log_file)
        log_path.parent.mkdir(parents=True, exist_ok=True)
    
    # Choose formatter based on configuration
    if settings.log_format == "json":
        formatter_class = JSONFormatter
        formatter_args = {}
    else:
        formatter_class = TextFormatter
        formatter_args = {}
    
    # Base logging configuration
    config = {
        "version": 1,
        "disable_existing_loggers": False,
        "formatters": {
            "default": {
                "()": formatter_class,
                **formatter_args
            }
        },
        "handlers": {
            "console": {
                "class": "logging.StreamHandler",
                "formatter": "default",
                "stream": sys.stdout,
            }
        },
        "loggers": {
            "": {  # Root logger
                "level": settings.log_level,
                "handlers": ["console"],
                "propagate": False,
            },
            "uvicorn": {
                "level": "INFO",
                "handlers": ["console"],
                "propagate": False,
            },
            "uvicorn.access": {
                "level": "INFO",
                "handlers": ["console"],
                "propagate": False,
            },
            "fastapi": {
                "level": "INFO",
                "handlers": ["console"],
                "propagate": False,
            },
        }
    }
    
    # Add file handler if log file is specified
    if settings.log_file:
        config["handlers"]["file"] = {
            "class": "logging.handlers.RotatingFileHandler",
            "formatter": "default",
            "filename": settings.log_file,
            "maxBytes": 10 * 1024 * 1024,  # 10MB
            "backupCount": 5,
        }
        
        # Add file handler to all loggers
        for logger_config in config["loggers"].values():
            logger_config["handlers"].append("file")
    
    # Apply configuration
    logging.config.dictConfig(config)
    
    # Set third-party library log levels
    logging.getLogger("httpx").setLevel(logging.WARNING)
    logging.getLogger("httpcore").setLevel(logging.WARNING)
    logging.getLogger("urllib3").setLevel(logging.WARNING)
    logging.getLogger("requests").setLevel(logging.WARNING)
    
    logger = logging.getLogger(__name__)
    logger.info(f"Logging configured - Level: {settings.log_level}, Format: {settings.log_format}")

class RequestLogger:
    """Request logging middleware"""
    
    @staticmethod
    def log_request(
        method: str,
        url: str,
        status_code: int,
        processing_time: float,
        user_id: Optional[str] = None,
        ip_address: Optional[str] = None,
        request_id: Optional[str] = None,
        **kwargs
    ):
        """Log HTTP request"""
        logger = logging.getLogger("app.requests")
        
        extra = {
            "method": method,
            "endpoint": str(url),
            "status_code": status_code,
            "processing_time": processing_time,
        }
        
        if user_id:
            extra["user_id"] = user_id
        
        if ip_address:
            extra["ip_address"] = ip_address
        
        if request_id:
            extra["request_id"] = request_id
        
        # Add any additional fields
        extra.update(kwargs)
        
        # Determine log level based on status code
        if status_code >= 500:
            level = logging.ERROR
        elif status_code >= 400:
            level = logging.WARNING
        else:
            level = logging.INFO
        
        message = f"{method} {url} - {status_code} - {processing_time:.3f}s"
        logger.log(level, message, extra=extra)

class SecurityLogger:
    """Security event logging"""
    
    @staticmethod
    def log_authentication_attempt(
        user_id: str,
        success: bool,
        ip_address: str,
        user_agent: str,
        **kwargs
    ):
        """Log authentication attempt"""
        logger = logging.getLogger("app.security.auth")
        
        extra = {
            "user_id": user_id,
            "ip_address": ip_address,
            "user_agent": user_agent,
            "auth_success": success,
        }
        extra.update(kwargs)
        
        if success:
            logger.info(f"Successful authentication for user {user_id}", extra=extra)
        else:
            logger.warning(f"Failed authentication attempt for user {user_id}", extra=extra)
    
    @staticmethod
    def log_rate_limit_exceeded(
        identifier: str,
        endpoint: str,
        ip_address: str,
        **kwargs
    ):
        """Log rate limit exceeded"""
        logger = logging.getLogger("app.security.rate_limit")
        
        extra = {
            "identifier": identifier,
            "endpoint": endpoint,
            "ip_address": ip_address,
        }
        extra.update(kwargs)
        
        logger.warning(f"Rate limit exceeded for {identifier} on {endpoint}", extra=extra)
    
    @staticmethod
    def log_security_event(
        event_type: str,
        description: str,
        severity: str = "medium",
        **kwargs
    ):
        """Log general security event"""
        logger = logging.getLogger("app.security.events")
        
        extra = {
            "event_type": event_type,
            "severity": severity,
        }
        extra.update(kwargs)
        
        # Determine log level based on severity
        level_map = {
            "low": logging.INFO,
            "medium": logging.WARNING,
            "high": logging.ERROR,
            "critical": logging.CRITICAL,
        }
        
        level = level_map.get(severity, logging.WARNING)
        logger.log(level, f"Security event: {description}", extra=extra)

class AILogger:
    """AI operation logging"""
    
    @staticmethod
    def log_ai_request(
        model: str,
        task_type: str,
        prompt_length: int,
        response_length: int,
        processing_time: float,
        success: bool,
        user_id: Optional[str] = None,
        **kwargs
    ):
        """Log AI request"""
        logger = logging.getLogger("app.ai.requests")
        
        extra = {
            "model": model,
            "task_type": task_type,
            "prompt_length": prompt_length,
            "response_length": response_length,
            "processing_time": processing_time,
            "success": success,
        }
        
        if user_id:
            extra["user_id"] = user_id
        
        extra.update(kwargs)
        
        if success:
            logger.info(f"AI request completed - {model} - {task_type}", extra=extra)
        else:
            logger.error(f"AI request failed - {model} - {task_type}", extra=extra)
    
    @staticmethod
    def log_model_performance(
        model: str,
        task_type: str,
        quality_score: float,
        response_time: float,
        **kwargs
    ):
        """Log model performance metrics"""
        logger = logging.getLogger("app.ai.performance")
        
        extra = {
            "model": model,
            "task_type": task_type,
            "quality_score": quality_score,
            "response_time": response_time,
        }
        extra.update(kwargs)
        
        logger.info(f"Model performance - {model} - {task_type}", extra=extra)

class DatabaseLogger:
    """Database operation logging"""
    
    @staticmethod
    def log_query(
        query_type: str,
        table: str,
        execution_time: float,
        success: bool,
        **kwargs
    ):
        """Log database query"""
        logger = logging.getLogger("app.database")
        
        extra = {
            "query_type": query_type,
            "table": table,
            "execution_time": execution_time,
            "success": success,
        }
        extra.update(kwargs)
        
        if success:
            logger.debug(f"Database query - {query_type} on {table}", extra=extra)
        else:
            logger.error(f"Database query failed - {query_type} on {table}", extra=extra)

# Initialize logging when module is imported
setup_logging()
