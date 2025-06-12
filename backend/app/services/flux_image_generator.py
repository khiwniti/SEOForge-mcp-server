"""
Enhanced Flux Image Generator
Based on Hugging Face Image-Gen-Pro implementation
Integrates multiple Flux models for high-quality image generation
"""

import asyncio
import aiohttp
import torch
import numpy as np
import random
import math
import logging
from PIL import Image
from typing import Dict, Any, Optional, List
import io
import base64
import os
import uuid
from datetime import datetime

# Configure logging
logger = logging.getLogger(__name__)

class FluxImageGenerator:
    """Enhanced Flux Image Generator with multiple model support"""
    
    def __init__(self):
        self.device = "cuda" if torch.cuda.is_available() else "cpu"
        self.models_loaded = False
        self.available_models = {
            "flux-dev": "black-forest-labs/FLUX.1-dev",
            "flux-schnell": "black-forest-labs/FLUX.1-schnell", 
            "flux-pro": "black-forest-labs/FLUX.1-pro"
        }
        self.current_model = "flux-schnell"  # Default to fastest model
        
        # Hugging Face API endpoints
        self.hf_endpoints = {
            "flux-dev": "https://api-inference.huggingface.co/models/black-forest-labs/FLUX.1-dev",
            "flux-schnell": "https://api-inference.huggingface.co/models/black-forest-labs/FLUX.1-schnell",
            "flux-pro": "https://api-inference.huggingface.co/models/black-forest-labs/FLUX.1-pro"
        }
        
        # Alternative API endpoints
        self.alternative_endpoints = {
            "pollinations": "https://image.pollinations.ai/prompt/",
            "replicate": "https://api.replicate.com/v1/predictions",
            "together": "https://api.together.xyz/inference"
        }
        
        self.hf_token = os.getenv("HUGGINGFACE_TOKEN")
        self.replicate_token = os.getenv("REPLICATE_API_TOKEN")
        self.together_token = os.getenv("TOGETHER_API_KEY")
        
    async def generate_image(
        self,
        prompt: str,
        negative_prompt: str = "",
        width: int = 1024,
        height: int = 1024,
        guidance_scale: float = 7.5,
        num_inference_steps: int = 20,
        seed: Optional[int] = None,
        model: str = "flux-schnell",
        style: str = "professional",
        enhance_prompt: bool = True
    ) -> Dict[str, Any]:
        """
        Generate high-quality image using Flux models
        
        Args:
            prompt: Text description of the image
            negative_prompt: What to avoid in the image
            width: Image width (default 1024)
            height: Image height (default 1024)
            guidance_scale: How closely to follow the prompt (1.0-20.0)
            num_inference_steps: Quality vs speed tradeoff (1-50)
            seed: Random seed for reproducibility
            model: Flux model to use
            style: Image style enhancement
            enhance_prompt: Whether to enhance the prompt with AI
            
        Returns:
            Dictionary with image data and metadata
        """
        try:
            # Set random seed if not provided
            if seed is None:
                seed = random.randint(0, 999999)
            
            # Enhance prompt if requested
            if enhance_prompt:
                prompt = await self._enhance_prompt_for_flux(prompt, style)
                logger.info(f"Enhanced prompt: {prompt}")
            
            # Try multiple generation methods in order of preference
            image_data = None
            generation_method = None
            
            # Method 1: Hugging Face Inference API (if token available)
            if self.hf_token and model in self.hf_endpoints:
                logger.info(f"Attempting Hugging Face {model} generation")
                image_data = await self._generate_with_huggingface(
                    prompt, negative_prompt, width, height, 
                    guidance_scale, num_inference_steps, seed, model
                )
                if image_data:
                    generation_method = f"huggingface_{model}"
            
            # Method 2: Pollinations AI with Flux (free, reliable)
            if not image_data:
                logger.info("Attempting Pollinations AI with Flux")
                image_data = await self._generate_with_pollinations_flux(
                    prompt, width, height, seed
                )
                if image_data:
                    generation_method = "pollinations_flux"
            
            # Method 3: Replicate API (if token available)
            if not image_data and self.replicate_token:
                logger.info("Attempting Replicate Flux generation")
                image_data = await self._generate_with_replicate(
                    prompt, negative_prompt, width, height,
                    guidance_scale, num_inference_steps, seed
                )
                if image_data:
                    generation_method = "replicate_flux"
            
            # Method 4: Together AI (if token available)
            if not image_data and self.together_token:
                logger.info("Attempting Together AI Flux generation")
                image_data = await self._generate_with_together(
                    prompt, negative_prompt, width, height,
                    guidance_scale, num_inference_steps, seed
                )
                if image_data:
                    generation_method = "together_flux"
            
            # Method 5: Fallback to enhanced placeholder
            if not image_data:
                logger.warning("All Flux services failed, creating enhanced placeholder")
                image_data = await self._create_flux_style_placeholder(
                    prompt, width, height, style
                )
                generation_method = "flux_placeholder"
            
            # Save image
            image_id = str(uuid.uuid4())
            image_filename = f"flux_{image_id}.png"
            image_path = f"generated_images/{image_filename}"
            
            # Ensure directory exists
            os.makedirs("generated_images", exist_ok=True)
            
            # Save the image
            with open(image_path, "wb") as f:
                f.write(image_data)
            
            # Get image info
            img = Image.open(io.BytesIO(image_data))
            actual_width, actual_height = img.size
            
            return {
                "success": True,
                "id": image_id,
                "filename": image_filename,
                "url": f"/images/{image_filename}",
                "prompt": prompt,
                "negative_prompt": negative_prompt,
                "width": actual_width,
                "height": actual_height,
                "guidance_scale": guidance_scale,
                "num_inference_steps": num_inference_steps,
                "seed": seed,
                "model": model,
                "style": style,
                "generation_method": generation_method,
                "generated_at": datetime.now().isoformat(),
                "file_size": len(image_data)
            }
            
        except Exception as e:
            logger.error(f"Flux image generation error: {e}")
            return {
                "success": False,
                "error": str(e),
                "prompt": prompt,
                "generated_at": datetime.now().isoformat()
            }
    
    async def _enhance_prompt_for_flux(self, prompt: str, style: str) -> str:
        """Enhance prompt specifically for Flux models"""
        try:
            # Import here to avoid circular imports
            from app.core.ai_orchestrator import gemini_client
            
            enhancement_prompt = f"""
You are an expert prompt engineer for Flux AI image generation models. Enhance the following prompt to work optimally with Flux models.

Original prompt: "{prompt}"
Style: {style}

Flux models work best with:
1. Detailed, descriptive prompts
2. Specific artistic styles and techniques
3. Lighting and composition details
4. Technical photography terms
5. Clear subject descriptions

Please enhance this prompt for Flux by:
- Adding specific visual details that Flux excels at
- Including appropriate artistic style keywords
- Adding technical photography/art terms
- Making it more descriptive and vivid
- Optimizing for the specified style
- Keeping it under 200 words

Enhanced prompt (respond with only the enhanced prompt):
"""
            
            response = await gemini_client.generate_content_async(enhancement_prompt)
            
            if response and hasattr(response, 'text') and response.text:
                enhanced = response.text.strip().strip('"\'')
                return enhanced
            else:
                return self._enhance_prompt_basic_flux(prompt, style)
                
        except Exception as e:
            logger.error(f"AI prompt enhancement failed: {e}")
            return self._enhance_prompt_basic_flux(prompt, style)
    
    def _enhance_prompt_basic_flux(self, prompt: str, style: str) -> str:
        """Basic prompt enhancement for Flux models"""
        flux_style_enhancements = {
            'professional': 'professional photography, commercial quality, studio lighting, sharp focus, high resolution, clean composition, modern aesthetic',
            'artistic': 'artistic masterpiece, creative composition, dramatic lighting, vibrant colors, fine art quality, aesthetic beauty, visual impact',
            'photorealistic': 'photorealistic, ultra detailed, natural lighting, high definition, lifelike, realistic textures, professional photography',
            'minimalist': 'minimalist design, clean lines, simple composition, negative space, elegant simplicity, modern minimalism',
            'commercial': 'commercial photography, product showcase, marketing quality, professional lighting, studio setup, brand aesthetic',
            'cinematic': 'cinematic lighting, movie quality, dramatic composition, film photography, professional cinematography',
            'illustration': 'digital illustration, vector art, clean design, professional artwork, graphic design quality',
            'fantasy': 'fantasy art, magical atmosphere, ethereal lighting, mystical composition, imaginative design',
            'modern': 'modern design, contemporary style, sleek aesthetic, trending visual, stylish composition'
        }
        
        enhancement = flux_style_enhancements.get(style, 'high quality, detailed, professional, sharp focus')
        return f"{prompt}, {enhancement}, flux model optimized"
    
    async def _generate_with_huggingface(
        self, prompt: str, negative_prompt: str, width: int, height: int,
        guidance_scale: float, num_inference_steps: int, seed: int, model: str
    ) -> Optional[bytes]:
        """Generate image using Hugging Face Inference API"""
        try:
            endpoint = self.hf_endpoints.get(model)
            if not endpoint:
                return None
            
            headers = {
                "Authorization": f"Bearer {self.hf_token}",
                "Content-Type": "application/json"
            }
            
            payload = {
                "inputs": prompt,
                "parameters": {
                    "negative_prompt": negative_prompt,
                    "width": width,
                    "height": height,
                    "guidance_scale": guidance_scale,
                    "num_inference_steps": num_inference_steps,
                    "seed": seed
                }
            }
            
            async with aiohttp.ClientSession() as session:
                async with session.post(
                    endpoint, 
                    headers=headers, 
                    json=payload,
                    timeout=60
                ) as response:
                    if response.status == 200:
                        return await response.read()
                    else:
                        logger.error(f"HuggingFace API error: {response.status}")
                        return None
                        
        except Exception as e:
            logger.error(f"HuggingFace generation error: {e}")
            return None
    
    async def _generate_with_pollinations_flux(
        self, prompt: str, width: int, height: int, seed: int
    ) -> Optional[bytes]:
        """Generate image using Pollinations AI with Flux model"""
        try:
            import urllib.parse
            encoded_prompt = urllib.parse.quote(prompt)
            
            url = f"https://image.pollinations.ai/prompt/{encoded_prompt}"
            params = {
                'width': width,
                'height': height,
                'seed': seed,
                'model': 'flux',  # Specify Flux model
                'enhance': 'true',  # Enable prompt enhancement
                'nologo': 'true'   # Remove watermark
            }
            
            async with aiohttp.ClientSession() as session:
                async with session.get(url, params=params, timeout=45) as response:
                    if response.status == 200:
                        return await response.read()
                    else:
                        logger.error(f"Pollinations error: {response.status}")
                        return None
                        
        except Exception as e:
            logger.error(f"Pollinations generation error: {e}")
            return None
    
    async def _generate_with_replicate(
        self, prompt: str, negative_prompt: str, width: int, height: int,
        guidance_scale: float, num_inference_steps: int, seed: int
    ) -> Optional[bytes]:
        """Generate image using Replicate API with Flux"""
        try:
            headers = {
                "Authorization": f"Token {self.replicate_token}",
                "Content-Type": "application/json"
            }
            
            payload = {
                "version": "black-forest-labs/flux-schnell",  # Flux model on Replicate
                "input": {
                    "prompt": prompt,
                    "negative_prompt": negative_prompt,
                    "width": width,
                    "height": height,
                    "guidance_scale": guidance_scale,
                    "num_inference_steps": num_inference_steps,
                    "seed": seed
                }
            }
            
            async with aiohttp.ClientSession() as session:
                # Start prediction
                async with session.post(
                    "https://api.replicate.com/v1/predictions",
                    headers=headers,
                    json=payload,
                    timeout=30
                ) as response:
                    if response.status == 201:
                        prediction = await response.json()
                        prediction_id = prediction["id"]
                        
                        # Poll for completion
                        for _ in range(30):  # Max 5 minutes
                            await asyncio.sleep(10)
                            
                            async with session.get(
                                f"https://api.replicate.com/v1/predictions/{prediction_id}",
                                headers=headers
                            ) as status_response:
                                if status_response.status == 200:
                                    status_data = await status_response.json()
                                    
                                    if status_data["status"] == "succeeded":
                                        image_url = status_data["output"][0]
                                        
                                        # Download the image
                                        async with session.get(image_url) as img_response:
                                            if img_response.status == 200:
                                                return await img_response.read()
                                    
                                    elif status_data["status"] == "failed":
                                        logger.error(f"Replicate generation failed: {status_data.get('error')}")
                                        return None
                        
                        logger.error("Replicate generation timed out")
                        return None
                    else:
                        logger.error(f"Replicate API error: {response.status}")
                        return None
                        
        except Exception as e:
            logger.error(f"Replicate generation error: {e}")
            return None
    
    async def _generate_with_together(
        self, prompt: str, negative_prompt: str, width: int, height: int,
        guidance_scale: float, num_inference_steps: int, seed: int
    ) -> Optional[bytes]:
        """Generate image using Together AI with Flux"""
        try:
            headers = {
                "Authorization": f"Bearer {self.together_token}",
                "Content-Type": "application/json"
            }
            
            payload = {
                "model": "black-forest-labs/FLUX.1-schnell",
                "prompt": prompt,
                "negative_prompt": negative_prompt,
                "width": width,
                "height": height,
                "guidance_scale": guidance_scale,
                "steps": num_inference_steps,
                "seed": seed,
                "response_format": "b64_json"
            }
            
            async with aiohttp.ClientSession() as session:
                async with session.post(
                    "https://api.together.xyz/v1/images/generations",
                    headers=headers,
                    json=payload,
                    timeout=60
                ) as response:
                    if response.status == 200:
                        data = await response.json()
                        if data.get("data") and len(data["data"]) > 0:
                            b64_image = data["data"][0]["b64_json"]
                            return base64.b64decode(b64_image)
                    else:
                        logger.error(f"Together AI error: {response.status}")
                        return None
                        
        except Exception as e:
            logger.error(f"Together AI generation error: {e}")
            return None
    
    async def _create_flux_style_placeholder(
        self, prompt: str, width: int, height: int, style: str
    ) -> bytes:
        """Create a Flux-style placeholder image"""
        from PIL import Image, ImageDraw, ImageFont, ImageFilter
        import textwrap
        
        # Create base image with gradient
        img = Image.new('RGB', (width, height))
        draw = ImageDraw.Draw(img)
        
        # Flux-inspired color schemes
        flux_colors = {
            'professional': [(30, 58, 138), (59, 130, 246), (147, 197, 253)],
            'artistic': [(139, 69, 19), (245, 158, 11), (254, 240, 138)],
            'photorealistic': [(75, 85, 99), (156, 163, 175), (229, 231, 235)],
            'minimalist': [(241, 245, 249), (203, 213, 225), (148, 163, 184)],
            'cinematic': [(17, 24, 39), (55, 65, 81), (107, 114, 128)]
        }
        
        colors = flux_colors.get(style, flux_colors['professional'])
        
        # Create sophisticated gradient
        for y in range(height):
            ratio = y / height
            if ratio < 0.5:
                # First half: color1 to color2
                local_ratio = ratio * 2
                r = int(colors[0][0] * (1 - local_ratio) + colors[1][0] * local_ratio)
                g = int(colors[0][1] * (1 - local_ratio) + colors[1][1] * local_ratio)
                b = int(colors[0][2] * (1 - local_ratio) + colors[1][2] * local_ratio)
            else:
                # Second half: color2 to color3
                local_ratio = (ratio - 0.5) * 2
                r = int(colors[1][0] * (1 - local_ratio) + colors[2][0] * local_ratio)
                g = int(colors[1][1] * (1 - local_ratio) + colors[2][1] * local_ratio)
                b = int(colors[1][2] * (1 - local_ratio) + colors[2][2] * local_ratio)
            
            draw.line([(0, y), (width, y)], fill=(r, g, b))
        
        # Add noise for texture (Flux-like)
        noise_overlay = Image.new('RGBA', (width, height), (0, 0, 0, 0))
        noise_draw = ImageDraw.Draw(noise_overlay)
        
        for _ in range(width * height // 100):
            x = random.randint(0, width - 1)
            y = random.randint(0, height - 1)
            alpha = random.randint(10, 30)
            noise_draw.point((x, y), fill=(255, 255, 255, alpha))
        
        img = Image.alpha_composite(img.convert('RGBA'), noise_overlay).convert('RGB')
        draw = ImageDraw.Draw(img)
        
        # Load fonts
        try:
            title_font = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf", 
                                          min(48, width // 20))
            subtitle_font = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf", 
                                             min(24, width // 40))
            text_font = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf", 
                                         min(18, width // 50))
        except:
            title_font = ImageFont.load_default()
            subtitle_font = ImageFont.load_default()
            text_font = ImageFont.load_default()
        
        # Add content
        title = "FLUX AI Generated"
        subtitle = f"Style: {style.title()}"
        
        # Wrap prompt
        wrapped_prompt = textwrap.fill(prompt, width=max(30, width // 25))
        
        # Calculate positions
        title_bbox = draw.textbbox((0, 0), title, font=title_font)
        title_width = title_bbox[2] - title_bbox[0]
        title_x = (width - title_width) // 2
        title_y = height // 3
        
        subtitle_bbox = draw.textbbox((0, 0), subtitle, font=subtitle_font)
        subtitle_width = subtitle_bbox[2] - subtitle_bbox[0]
        subtitle_x = (width - subtitle_width) // 2
        subtitle_y = title_y + 60
        
        # Draw with modern styling
        shadow_offset = 3
        
        # Draw text with glow effect
        for offset in range(shadow_offset, 0, -1):
            alpha = 100 - (offset * 20)
            # Create temporary image for glow
            glow_img = Image.new('RGBA', (width, height), (0, 0, 0, 0))
            glow_draw = ImageDraw.Draw(glow_img)
            
            glow_draw.text((title_x + offset, title_y + offset), title, 
                          fill=(0, 0, 0, alpha), font=title_font)
            glow_draw.text((subtitle_x + offset, subtitle_y + offset), subtitle, 
                          fill=(0, 0, 0, alpha), font=subtitle_font)
            
            img = Image.alpha_composite(img.convert('RGBA'), glow_img).convert('RGB')
            draw = ImageDraw.Draw(img)
        
        # Draw main text
        draw.text((title_x, title_y), title, fill='white', font=title_font)
        draw.text((subtitle_x, subtitle_y), subtitle, fill='white', font=subtitle_font)
        
        # Draw prompt
        prompt_y = subtitle_y + 80
        for line in wrapped_prompt.split('\n'):
            if line.strip():
                line_bbox = draw.textbbox((0, 0), line, font=text_font)
                line_width = line_bbox[2] - line_bbox[0]
                line_x = (width - line_width) // 2
                
                draw.text((line_x, prompt_y), line, fill='white', font=text_font)
                prompt_y += 30
        
        # Add Flux-style decorative elements
        # Corner accents
        accent_size = min(width, height) // 20
        accent_color = colors[1]
        
        # Top corners
        draw.polygon([(0, 0), (accent_size, 0), (0, accent_size)], fill=accent_color)
        draw.polygon([(width, 0), (width - accent_size, 0), (width, accent_size)], fill=accent_color)
        
        # Bottom corners
        draw.polygon([(0, height), (accent_size, height), (0, height - accent_size)], fill=accent_color)
        draw.polygon([(width, height), (width - accent_size, height), (width, height - accent_size)], fill=accent_color)
        
        # Add subtle border
        border_width = 2
        draw.rectangle([0, 0, width - 1, height - 1], outline=colors[2], width=border_width)
        
        # Save to bytes
        img_byte_arr = io.BytesIO()
        img.save(img_byte_arr, format='PNG', quality=95, optimize=True)
        return img_byte_arr.getvalue()
    
    async def generate_multiple_images(
        self, 
        prompts: List[str], 
        **kwargs
    ) -> List[Dict[str, Any]]:
        """Generate multiple images concurrently"""
        tasks = []
        for prompt in prompts:
            task = self.generate_image(prompt, **kwargs)
            tasks.append(task)
        
        results = await asyncio.gather(*tasks, return_exceptions=True)
        
        # Filter out exceptions and return successful results
        successful_results = []
        for result in results:
            if isinstance(result, dict) and result.get("success"):
                successful_results.append(result)
            elif isinstance(result, Exception):
                logger.error(f"Image generation failed: {result}")
        
        return successful_results
    
    def get_available_models(self) -> List[str]:
        """Get list of available Flux models"""
        return list(self.available_models.keys())
    
    def get_model_info(self, model: str) -> Dict[str, Any]:
        """Get information about a specific model"""
        model_info = {
            "flux-dev": {
                "name": "FLUX.1-dev",
                "description": "High-quality, slower generation",
                "recommended_steps": 20-50,
                "max_resolution": "2048x2048",
                "speed": "slow",
                "quality": "highest"
            },
            "flux-schnell": {
                "name": "FLUX.1-schnell", 
                "description": "Fast generation, good quality",
                "recommended_steps": 4-8,
                "max_resolution": "1024x1024",
                "speed": "fast",
                "quality": "good"
            },
            "flux-pro": {
                "name": "FLUX.1-pro",
                "description": "Professional quality, commercial use",
                "recommended_steps": 25-50,
                "max_resolution": "2048x2048", 
                "speed": "medium",
                "quality": "professional"
            }
        }
        
        return model_info.get(model, {})

# Global instance
flux_generator = FluxImageGenerator()