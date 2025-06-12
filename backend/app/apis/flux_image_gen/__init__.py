"""
Flux Image Generation API
High-quality AI image generation using Flux models
"""

from fastapi import APIRouter, HTTPException, BackgroundTasks
from pydantic import BaseModel, Field
from typing import Dict, Any, List, Optional
import logging
from datetime import datetime

from app.services.flux_image_generator import flux_generator

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/flux-image-gen", tags=["Flux Image Generation"])

# Pydantic models
class FluxImageRequest(BaseModel):
    prompt: str = Field(..., description="Text description of the image to generate")
    negative_prompt: str = Field("", description="What to avoid in the image")
    width: int = Field(1024, ge=256, le=2048, description="Image width")
    height: int = Field(1024, ge=256, le=2048, description="Image height")
    guidance_scale: float = Field(7.5, ge=1.0, le=20.0, description="How closely to follow the prompt")
    num_inference_steps: int = Field(20, ge=1, le=50, description="Quality vs speed tradeoff")
    seed: Optional[int] = Field(None, description="Random seed for reproducibility")
    model: str = Field("flux-schnell", description="Flux model to use")
    style: str = Field("professional", description="Image style enhancement")
    enhance_prompt: bool = Field(True, description="Whether to enhance the prompt with AI")

class FluxBatchRequest(BaseModel):
    prompts: List[str] = Field(..., description="List of prompts to generate images for")
    negative_prompt: str = Field("", description="What to avoid in the images")
    width: int = Field(1024, ge=256, le=2048, description="Image width")
    height: int = Field(1024, ge=256, le=2048, description="Image height")
    guidance_scale: float = Field(7.5, ge=1.0, le=20.0, description="How closely to follow the prompts")
    num_inference_steps: int = Field(20, ge=1, le=50, description="Quality vs speed tradeoff")
    model: str = Field("flux-schnell", description="Flux model to use")
    style: str = Field("professional", description="Image style enhancement")
    enhance_prompt: bool = Field(True, description="Whether to enhance prompts with AI")

class FluxImageResponse(BaseModel):
    success: bool
    id: Optional[str] = None
    filename: Optional[str] = None
    url: Optional[str] = None
    prompt: str
    negative_prompt: Optional[str] = None
    width: Optional[int] = None
    height: Optional[int] = None
    guidance_scale: Optional[float] = None
    num_inference_steps: Optional[int] = None
    seed: Optional[int] = None
    model: Optional[str] = None
    style: Optional[str] = None
    generation_method: Optional[str] = None
    generated_at: str
    file_size: Optional[int] = None
    error: Optional[str] = None

@router.post("/generate", response_model=FluxImageResponse)
async def generate_flux_image(request: FluxImageRequest):
    """
    Generate a single high-quality image using Flux models
    
    This endpoint uses state-of-the-art Flux models to generate high-quality images
    from text descriptions. It supports multiple Flux variants and fallback methods.
    """
    try:
        logger.info(f"Generating Flux image with prompt: {request.prompt[:100]}...")
        
        result = await flux_generator.generate_image(
            prompt=request.prompt,
            negative_prompt=request.negative_prompt,
            width=request.width,
            height=request.height,
            guidance_scale=request.guidance_scale,
            num_inference_steps=request.num_inference_steps,
            seed=request.seed,
            model=request.model,
            style=request.style,
            enhance_prompt=request.enhance_prompt
        )
        
        return FluxImageResponse(**result)
        
    except Exception as e:
        logger.error(f"Flux image generation failed: {e}")
        raise HTTPException(status_code=500, detail=f"Image generation failed: {str(e)}")

@router.post("/generate-batch")
async def generate_flux_images_batch(request: FluxBatchRequest):
    """
    Generate multiple images concurrently using Flux models
    
    This endpoint allows batch generation of images for multiple prompts,
    which is useful for creating image sets or variations.
    """
    try:
        if len(request.prompts) > 10:
            raise HTTPException(status_code=400, detail="Maximum 10 prompts allowed per batch")
        
        logger.info(f"Generating {len(request.prompts)} Flux images in batch")
        
        results = await flux_generator.generate_multiple_images(
            prompts=request.prompts,
            negative_prompt=request.negative_prompt,
            width=request.width,
            height=request.height,
            guidance_scale=request.guidance_scale,
            num_inference_steps=request.num_inference_steps,
            model=request.model,
            style=request.style,
            enhance_prompt=request.enhance_prompt
        )
        
        return {
            "success": True,
            "generated_count": len(results),
            "total_requested": len(request.prompts),
            "images": results,
            "generated_at": datetime.now().isoformat()
        }
        
    except Exception as e:
        logger.error(f"Batch Flux image generation failed: {e}")
        raise HTTPException(status_code=500, detail=f"Batch generation failed: {str(e)}")

@router.get("/models")
async def get_available_models():
    """
    Get list of available Flux models and their information
    """
    try:
        models = flux_generator.get_available_models()
        model_info = {}
        
        for model in models:
            model_info[model] = flux_generator.get_model_info(model)
        
        return {
            "success": True,
            "available_models": models,
            "model_info": model_info,
            "default_model": "flux-schnell",
            "recommended_settings": {
                "flux-schnell": {
                    "steps": 4,
                    "guidance_scale": 7.5,
                    "description": "Fast generation, good for previews"
                },
                "flux-dev": {
                    "steps": 20,
                    "guidance_scale": 7.5,
                    "description": "High quality, slower generation"
                },
                "flux-pro": {
                    "steps": 25,
                    "guidance_scale": 7.5,
                    "description": "Professional quality, commercial use"
                }
            }
        }
        
    except Exception as e:
        logger.error(f"Failed to get model info: {e}")
        raise HTTPException(status_code=500, detail=f"Failed to get model info: {str(e)}")

@router.get("/styles")
async def get_available_styles():
    """
    Get list of available image styles and their descriptions
    """
    styles = {
        "professional": {
            "description": "Clean, modern, business-appropriate imagery",
            "keywords": "professional, commercial, clean, modern, high-quality"
        },
        "artistic": {
            "description": "Creative, expressive, fine art style",
            "keywords": "artistic, creative, expressive, fine art, aesthetic"
        },
        "photorealistic": {
            "description": "Realistic photography style",
            "keywords": "photorealistic, natural, realistic, photography, lifelike"
        },
        "minimalist": {
            "description": "Simple, clean, minimal design",
            "keywords": "minimalist, simple, clean, elegant, negative space"
        },
        "commercial": {
            "description": "Marketing and advertising style",
            "keywords": "commercial, marketing, advertising, product, brand"
        },
        "cinematic": {
            "description": "Movie-like, dramatic lighting",
            "keywords": "cinematic, dramatic, movie-like, film, atmospheric"
        },
        "illustration": {
            "description": "Digital illustration and vector art",
            "keywords": "illustration, digital art, vector, graphic design"
        },
        "fantasy": {
            "description": "Magical, fantastical, imaginative",
            "keywords": "fantasy, magical, mystical, imaginative, ethereal"
        },
        "modern": {
            "description": "Contemporary, trendy, stylish",
            "keywords": "modern, contemporary, trendy, stylish, current"
        }
    }
    
    return {
        "success": True,
        "available_styles": list(styles.keys()),
        "style_descriptions": styles,
        "default_style": "professional"
    }

@router.post("/enhance-prompt")
async def enhance_prompt_for_flux(prompt: str, style: str = "professional"):
    """
    Enhance a prompt specifically for Flux model generation
    """
    try:
        enhanced_prompt = await flux_generator._enhance_prompt_for_flux(prompt, style)
        
        return {
            "success": True,
            "original_prompt": prompt,
            "enhanced_prompt": enhanced_prompt,
            "style": style,
            "enhancement_applied": enhanced_prompt != prompt
        }
        
    except Exception as e:
        logger.error(f"Prompt enhancement failed: {e}")
        raise HTTPException(status_code=500, detail=f"Prompt enhancement failed: {str(e)}")

@router.get("/status")
async def get_flux_generator_status():
    """
    Get the current status of the Flux image generator
    """
    try:
        import torch
        
        status = {
            "success": True,
            "service": "Flux Image Generator",
            "version": "1.0.0",
            "device": flux_generator.device,
            "cuda_available": torch.cuda.is_available(),
            "available_models": flux_generator.get_available_models(),
            "default_model": flux_generator.current_model,
            "api_tokens_configured": {
                "huggingface": bool(flux_generator.hf_token),
                "replicate": bool(flux_generator.replicate_token),
                "together": bool(flux_generator.together_token)
            },
            "endpoints": {
                "pollinations": "Available (free)",
                "huggingface": "Available" if flux_generator.hf_token else "Token required",
                "replicate": "Available" if flux_generator.replicate_token else "Token required",
                "together": "Available" if flux_generator.together_token else "Token required"
            }
        }
        
        if torch.cuda.is_available():
            status["gpu_info"] = {
                "device_count": torch.cuda.device_count(),
                "current_device": torch.cuda.current_device(),
                "device_name": torch.cuda.get_device_name(0) if torch.cuda.device_count() > 0 else None
            }
        
        return status
        
    except Exception as e:
        logger.error(f"Status check failed: {e}")
        return {
            "success": False,
            "error": str(e),
            "service": "Flux Image Generator",
            "status": "error"
        }

# Health check endpoint
@router.get("/health")
async def health_check():
    """Simple health check for the Flux image generation service"""
    return {
        "status": "healthy",
        "service": "Flux Image Generator",
        "timestamp": datetime.now().isoformat()
    }