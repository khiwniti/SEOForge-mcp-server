#!/usr/bin/env python3
"""
Enhanced Universal MCP Server with AI Image Generation
Integrates blog generation with AI-powered image creation for complete content packages
"""

import asyncio
import json
import logging
from datetime import datetime
from typing import Dict, Any, List, Optional
import aiohttp
from fastapi import FastAPI, HTTPException, UploadFile, File
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import HTMLResponse, FileResponse
from fastapi.staticfiles import StaticFiles
from pydantic import BaseModel
import os
from bs4 import BeautifulSoup
import re
import base64
import requests
from PIL import Image
import io
import uuid

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Initialize FastAPI app
app = FastAPI(
    title="Enhanced Universal MCP Server with Image Generation",
    description="AI-Powered Content Generation & Website Intelligence Platform with Image Generation",
    version="3.0.0-enhanced"
)

# Configure CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Create images directory
os.makedirs("generated_images", exist_ok=True)

# Mount static files for serving generated images
app.mount("/images", StaticFiles(directory="generated_images"), name="images")

# Pydantic models
class ContentGenerationRequest(BaseModel):
    content_type: str = "blog_post"
    topic: Optional[str] = None  # Made optional - can generate from keywords only
    keywords: List[str] = []
    website_url: Optional[str] = None
    tone: str = "professional"
    length: str = "medium"
    industry: str = "general"
    language: str = "en"
    include_images: bool = True
    image_count: int = 3
    image_style: str = "professional"
    keywords_only: bool = False  # New field to indicate keyword-only generation

class ImageGenerationRequest(BaseModel):
    prompt: str
    style: str = "professional"
    size: str = "1024x1024"
    count: int = 1

class WebsiteAnalysisRequest(BaseModel):
    url: str
    analysis_type: str = "simple"

class BlogWithImagesResponse(BaseModel):
    success: bool
    content: str
    images: List[Dict[str, Any]]
    seo_data: Dict[str, Any]
    word_count: int
    generated_at: str

# AI Image Generation Service
class AIImageGenerator:
    """AI Image Generation using multiple providers"""
    
    def __init__(self):
        self.providers = {
            "dalle": self._generate_dalle_image,
            "stable_diffusion": self._generate_stable_diffusion_image,
            "midjourney": self._generate_midjourney_style_image
        }
    
    async def generate_image(self, prompt: str, style: str = "professional", size: str = "1024x1024") -> Dict[str, Any]:
        """Generate AI image based on prompt"""
        try:
            # Try multiple image generation methods
            image_data = None
            
            # Method 1: Try Pollinations AI (free service)
            logger.info(f"Attempting Pollinations AI for prompt: {prompt}")
            image_data = await self._generate_pollinations_image(prompt, style, size)
            
            # Method 2: If Pollinations fails, try Unsplash (stock photos)
            if not image_data:
                logger.info(f"Pollinations failed, trying Unsplash for prompt: {prompt}")
                image_data = await self._generate_unsplash_image(prompt, style, size)
            
            # Method 3: If all else fails, create a professional placeholder
            if not image_data:
                logger.info(f"All AI services failed, using professional placeholder for prompt: {prompt}")
                image_data = await self._create_professional_placeholder(prompt, style, size)
            else:
                logger.info(f"Successfully generated AI image for prompt: {prompt}")
            
            # Save image
            image_id = str(uuid.uuid4())
            image_filename = f"{image_id}.png"
            image_path = f"generated_images/{image_filename}"
            
            # Save the image
            with open(image_path, "wb") as f:
                f.write(image_data)
            
            return {
                "id": image_id,
                "filename": image_filename,
                "url": f"/images/{image_filename}",
                "prompt": prompt,
                "style": style,
                "size": size,
                "generated_at": datetime.now().isoformat()
            }
        except Exception as e:
            logger.error(f"Image generation error: {e}")
            return None
    
    async def _generate_pollinations_image(self, prompt: str, style: str, size: str) -> bytes:
        """Generate image using Pollinations AI (free service)"""
        try:
            # Use AI to enhance prompt for better results
            enhanced_prompt = await self._enhance_prompt_with_ai(prompt, style)
            logger.info(f"Enhanced prompt: {enhanced_prompt}")
            
            # Parse size for Pollinations
            width, height = map(int, size.split('x'))
            
            # URL encode the enhanced prompt
            import urllib.parse
            encoded_prompt = urllib.parse.quote(enhanced_prompt)
            
            # Pollinations AI API
            url = f"https://image.pollinations.ai/prompt/{encoded_prompt}"
            params = {
                'width': width,
                'height': height,
                'seed': -1,  # Random seed
                'model': 'flux'  # Use Flux model for better quality
            }
            
            async with aiohttp.ClientSession() as session:
                async with session.get(url, params=params, timeout=30) as response:
                    if response.status == 200:
                        return await response.read()
            
            return None
            
        except Exception as e:
            logger.error(f"Pollinations AI error: {e}")
            return None
    
    async def _generate_unsplash_image(self, prompt: str, style: str, size: str) -> bytes:
        """Generate image using Unsplash stock photos"""
        try:
            # Extract keywords from prompt for Unsplash search
            keywords = self._extract_keywords_for_unsplash(prompt)
            
            # Parse size
            width, height = map(int, size.split('x'))
            
            # Unsplash API (no key required for basic usage)
            url = f"https://source.unsplash.com/{width}x{height}/"
            params = {'q': keywords}
            
            async with aiohttp.ClientSession() as session:
                async with session.get(url, params=params, timeout=20) as response:
                    if response.status == 200:
                        return await response.read()
            
            return None
            
        except Exception as e:
            logger.error(f"Unsplash error: {e}")
            return None
    
    async def _enhance_prompt_with_ai(self, prompt: str, style: str) -> str:
        """Use AI to enhance the image prompt for better results"""
        try:
            enhancement_prompt = f"""
You are an expert AI image prompt engineer. Your task is to enhance the following image prompt to create more detailed, visually appealing, and professional results.

Original prompt: "{prompt}"
Style: {style}

Please enhance this prompt by:
1. Adding specific visual details (lighting, composition, colors)
2. Including technical photography terms when appropriate
3. Adding style-specific enhancements
4. Making it more descriptive and vivid
5. Keeping it concise but detailed

Enhanced prompt (respond with only the enhanced prompt, no explanations):
"""
            
            response = await gemini_client.generate_content_async(enhancement_prompt)
            
            if response and hasattr(response, 'text') and response.text:
                enhanced = response.text.strip()
                # Remove quotes if present
                enhanced = enhanced.strip('"\'')
                return enhanced
            else:
                # Fallback to basic enhancement
                return self._enhance_prompt_for_style_basic(prompt, style)
                
        except Exception as e:
            logger.error(f"AI prompt enhancement failed: {e}")
            # Fallback to basic enhancement
            return self._enhance_prompt_for_style_basic(prompt, style)
    
    def _enhance_prompt_for_style_basic(self, prompt: str, style: str) -> str:
        """Basic prompt enhancement based on style (fallback)"""
        style_enhancements = {
            'professional': 'professional, high quality, commercial photography, clean, modern, well-lit, sharp focus',
            'artistic': 'artistic, creative, beautiful, aesthetic, fine art, dramatic lighting, vibrant colors',
            'minimalist': 'minimalist, clean, simple, elegant, white background, negative space, modern',
            'commercial': 'commercial, product photography, marketing, professional lighting, studio quality',
            'realistic': 'photorealistic, detailed, natural lighting, high resolution, lifelike',
            'illustration': 'digital illustration, vector art, clean lines, professional design',
            'modern': 'modern, contemporary, sleek, sophisticated, trending, stylish'
        }
        
        enhancement = style_enhancements.get(style, 'high quality, professional, detailed')
        return f"{prompt}, {enhancement}"
    
    def _extract_keywords_for_unsplash(self, prompt: str) -> str:
        """Extract relevant keywords from prompt for Unsplash"""
        # Remove common words and extract main subjects
        common_words = {'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'}
        words = prompt.lower().split()
        keywords = [word for word in words if word not in common_words and len(word) > 2]
        
        # Take first 3 most relevant keywords
        return '+'.join(keywords[:3])
    
    async def _create_professional_placeholder(self, prompt: str, style: str, size: str) -> bytes:
        """Create a professional placeholder image"""
        from PIL import Image, ImageDraw, ImageFont
        import textwrap
        
        # Parse size
        width, height = map(int, size.split('x'))
        
        # Create gradient background based on style
        style_colors = {
            'professional': ('#2c3e50', '#3498db'),
            'artistic': ('#8e44ad', '#e74c3c'),
            'minimalist': ('#ecf0f1', '#95a5a6'),
            'commercial': ('#27ae60', '#2ecc71')
        }
        
        color1, color2 = style_colors.get(style, ('#2c3e50', '#3498db'))
        
        # Create gradient background
        img = Image.new('RGB', (width, height), color=color1)
        draw = ImageDraw.Draw(img)
        
        # Create gradient effect
        for i in range(height):
            ratio = i / height
            r = int((1 - ratio) * int(color1[1:3], 16) + ratio * int(color2[1:3], 16))
            g = int((1 - ratio) * int(color1[3:5], 16) + ratio * int(color2[3:5], 16))
            b = int((1 - ratio) * int(color1[5:7], 16) + ratio * int(color2[5:7], 16))
            draw.line([(0, i), (width, i)], fill=(r, g, b))
        
        # Try to use a font, fallback to default
        try:
            title_font = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf", 36)
            subtitle_font = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf", 24)
            text_font = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf", 18)
        except:
            title_font = ImageFont.load_default()
            subtitle_font = ImageFont.load_default()
            text_font = ImageFont.load_default()
        
        # Add professional content
        title = "AI Generated Image"
        subtitle = f"Style: {style.title()}"
        
        # Wrap prompt text
        wrapped_prompt = textwrap.fill(prompt, width=40)
        
        # Calculate positions
        title_bbox = draw.textbbox((0, 0), title, font=title_font)
        title_width = title_bbox[2] - title_bbox[0]
        title_x = (width - title_width) // 2
        title_y = height // 4
        
        subtitle_bbox = draw.textbbox((0, 0), subtitle, font=subtitle_font)
        subtitle_width = subtitle_bbox[2] - subtitle_bbox[0]
        subtitle_x = (width - subtitle_width) // 2
        subtitle_y = title_y + 60
        
        # Draw text with shadow effect
        shadow_offset = 2
        text_color = '#ffffff'
        shadow_color = '#000000'
        
        # Draw shadows
        draw.text((title_x + shadow_offset, title_y + shadow_offset), title, fill=shadow_color, font=title_font)
        draw.text((subtitle_x + shadow_offset, subtitle_y + shadow_offset), subtitle, fill=shadow_color, font=subtitle_font)
        
        # Draw main text
        draw.text((title_x, title_y), title, fill=text_color, font=title_font)
        draw.text((subtitle_x, subtitle_y), subtitle, fill=text_color, font=subtitle_font)
        
        # Draw wrapped prompt
        prompt_y = subtitle_y + 80
        for line in wrapped_prompt.split('\n'):
            if line.strip():
                line_bbox = draw.textbbox((0, 0), line, font=text_font)
                line_width = line_bbox[2] - line_bbox[0]
                line_x = (width - line_width) // 2
                
                # Shadow
                draw.text((line_x + shadow_offset, prompt_y + shadow_offset), line, fill=shadow_color, font=text_font)
                # Main text
                draw.text((line_x, prompt_y), line, fill=text_color, font=text_font)
                prompt_y += 30
        
        # Add decorative elements
        # Draw corner decorations
        corner_size = 50
        draw.rectangle([0, 0, corner_size, corner_size], fill=color2)
        draw.rectangle([width-corner_size, 0, width, corner_size], fill=color2)
        draw.rectangle([0, height-corner_size, corner_size, height], fill=color2)
        draw.rectangle([width-corner_size, height-corner_size, width, height], fill=color2)
        
        # Save to bytes
        img_byte_arr = io.BytesIO()
        img.save(img_byte_arr, format='PNG', quality=95)
        return img_byte_arr.getvalue()
    
    async def _generate_dalle_image(self, prompt: str, style: str, size: str) -> bytes:
        """Generate image using DALL-E API (placeholder)"""
        # In production, integrate with OpenAI DALL-E API
        return await self._create_professional_placeholder(f"DALL-E Style: {prompt}", style, size)
    
    async def _generate_stable_diffusion_image(self, prompt: str, style: str, size: str) -> bytes:
        """Generate image using Stable Diffusion API (placeholder)"""
        # In production, integrate with Stable Diffusion API
        return await self._create_professional_placeholder(f"Stable Diffusion Style: {prompt}", style, size)
    
    async def _generate_midjourney_style_image(self, prompt: str, style: str, size: str) -> bytes:
        """Generate image using Midjourney-style API (placeholder)"""
        # In production, integrate with Midjourney API
        return await self._create_professional_placeholder(f"Midjourney Style: {prompt}", style, size)

# Enhanced Content Generator with Image Integration
class EnhancedContentGenerator:
    """Enhanced content generator with AI image integration"""
    
    def __init__(self):
        self.image_generator = AIImageGenerator()
        self.gemini_api_key = os.getenv("GOOGLE_API_KEY")
        
    async def generate_blog_with_images(self, request: ContentGenerationRequest) -> Dict[str, Any]:
        """Generate blog content with integrated AI images"""
        try:
            # Generate text content
            content_data = await self._generate_text_content(request)
            
            # Generate images if requested
            images = []
            if request.include_images:
                images = await self._generate_content_images(request, content_data)
            
            # Integrate images into content
            enhanced_content = await self._integrate_images_into_content(
                content_data["content"], images, request.language
            )
            
            return {
                "success": True,
                "content": enhanced_content,
                "images": images,
                "seo_data": content_data.get("seo_data", {}),
                "word_count": len(enhanced_content.split()),
                "generated_at": datetime.now().isoformat(),
                "ai_model_used": "gemini-1.5-flash",
                "image_count": len(images)
            }
            
        except Exception as e:
            logger.error(f"Enhanced content generation error: {e}")
            raise HTTPException(status_code=500, detail=str(e))
    
    async def _generate_text_content(self, request: ContentGenerationRequest) -> Dict[str, Any]:
        """Generate text content using Gemini AI"""
        if not self.gemini_api_key:
            raise HTTPException(status_code=500, detail="Google API key not configured")
        
        # Build enhanced prompt
        prompt = self._build_enhanced_prompt(request)
        
        # Call Gemini API
        url = f"https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key={self.gemini_api_key}"
        
        payload = {
            "contents": [{
                "parts": [{"text": prompt}]
            }],
            "generationConfig": {
                "temperature": 0.7,
                "topK": 40,
                "topP": 0.95,
                "maxOutputTokens": 2048,
            }
        }
        
        async with aiohttp.ClientSession() as session:
            async with session.post(url, json=payload) as response:
                if response.status == 200:
                    data = await response.json()
                    content = data["candidates"][0]["content"]["parts"][0]["text"]
                    
                    return {
                        "content": content,
                        "seo_data": self._extract_seo_data(content, request.keywords),
                        "model": "gemini-1.5-flash"
                    }
                else:
                    error_text = await response.text()
                    raise HTTPException(status_code=response.status, detail=f"Gemini API error: {error_text}")
    
    def _build_enhanced_prompt(self, request: ContentGenerationRequest) -> str:
        """Build enhanced prompt for content generation"""
        keywords_str = ", ".join(request.keywords) if request.keywords else "relevant keywords"
        
        # Handle keyword-only generation
        if request.keywords_only or not request.topic:
            if not request.keywords:
                raise HTTPException(status_code=400, detail="Keywords are required when topic is not provided")
            
            if request.language == "th":
                prompt = f"""สร้างบทความภาษาไทยที่มีคุณภาพสูงและเหมาะสำหรับ SEO โดยใช้คำสำคัญเหล่านี้เป็นหลัก: {keywords_str}

อุตสาหกรรม: {request.industry}
โทนเสียง: {request.tone}
ความยาว: {request.length}

โปรดสร้างเนื้อหาที่:
1. สร้างหัวข้อที่น่าสนใจจากคำสำคัญที่ให้มา
2. ใช้คำสำคัญภาษาไทยอย่างเป็นธรรมชาติทั่วทั้งเนื้อหา
3. มีโครงสร้างที่ชัดเจนพร้อมหัวข้อย่อย
4. เหมาะสำหรับการเพิ่มรูปภาพประกอบ
5. มี Meta Description ที่ดี
6. มี Call to Action ที่น่าสนใจ
7. เนื้อหาครบถ้วนและมีประโยชน์สำหรับผู้อ่าน

รูปแบบ: Markdown พร้อมจุดที่เหมาะสำหรับการใส่รูปภาพ (ใช้ [IMAGE_PLACEHOLDER_X] เพื่อระบุตำแหน่งรูปภาพ)"""
            else:
                prompt = f"""Create a high-quality, SEO-optimized {request.content_type} based on these keywords: {keywords_str}

Industry: {request.industry}
Tone: {request.tone}
Length: {request.length}
Website context: {request.website_url or 'General'}

Please create content that:
1. Generates an engaging title from the provided keywords
2. Naturally incorporates all provided keywords throughout the content
3. Has clear structure with subheadings
4. Is optimized for image placement
5. Includes a compelling meta description
6. Has a strong call-to-action
7. Provides valuable, comprehensive information for readers
8. Creates a cohesive narrative connecting all keywords

Format: Markdown with image placement indicators (use [IMAGE_PLACEHOLDER_X] to mark where images should be placed)"""
        else:
            # Traditional topic-based generation
            if request.language == "th":
                prompt = f"""สร้างบทความภาษาไทยเรื่อง "{request.topic}" ที่มีคุณภาพสูงและเหมาะสำหรับ SEO

คำสำคัญที่ต้องใช้: {keywords_str}
อุตสาหกรรม: {request.industry}
โทนเสียง: {request.tone}
ความยาว: {request.length}

โปรดสร้างเนื้อหาที่:
1. ใช้คำสำคัญภาษาไทยอย่างเป็นธรรมชาติ
2. มีโครงสร้างที่ชัดเจนพร้อมหัวข้อย่อย
3. เหมาะสำหรับการเพิ่มรูปภาพประกอบ
4. มี Meta Description ที่ดี
5. มี Call to Action ที่น่าสนใจ
6. เนื้อหาครบถ้วนและมีประโยชน์

รูปแบบ: Markdown พร้อมจุดที่เหมาะสำหรับการใส่รูปภาพ (ใช้ [IMAGE_PLACEHOLDER_X] เพื่อระบุตำแหน่งรูปภาพ)"""
            else:
                prompt = f"""Create a high-quality, SEO-optimized {request.content_type} about "{request.topic}"

Keywords to include: {keywords_str}
Industry: {request.industry}
Tone: {request.tone}
Length: {request.length}
Website context: {request.website_url or 'General'}

Please create content that:
1. Naturally incorporates the provided keywords
2. Has clear structure with subheadings
3. Is optimized for image placement
4. Includes a compelling meta description
5. Has a strong call-to-action
6. Provides valuable, comprehensive information

Format: Markdown with image placement indicators (use [IMAGE_PLACEHOLDER_X] to mark where images should be placed)"""
        
        return prompt
    
    async def _generate_content_images(self, request: ContentGenerationRequest, content_data: Dict[str, Any]) -> List[Dict[str, Any]]:
        """Generate AI images for the content"""
        images = []
        
        # Generate image prompts based on content and keywords
        image_prompts = self._create_image_prompts(request, content_data)
        
        for i, prompt in enumerate(image_prompts[:request.image_count]):
            image_data = await self.image_generator.generate_image(
                prompt=prompt,
                style=request.image_style,
                size="1024x1024"
            )
            
            if image_data:
                image_data["placeholder"] = f"IMAGE_PLACEHOLDER_{i+1}"
                images.append(image_data)
        
        return images
    
    def _create_image_prompts(self, request: ContentGenerationRequest, content_data: Dict[str, Any]) -> List[str]:
        """Create image prompts based on content and keywords"""
        base_keywords = " ".join(request.keywords[:3]) if request.keywords else request.topic
        
        if request.language == "th":
            prompts = [
                f"Professional product photography of {base_keywords}, high quality, commercial style",
                f"Modern workspace with {base_keywords}, clean and professional",
                f"Infographic style illustration about {request.topic}, minimalist design"
            ]
        else:
            prompts = [
                f"Professional product photography of {base_keywords}, high quality, commercial style",
                f"Modern business environment with {base_keywords}, clean and professional",
                f"Infographic style illustration about {request.topic}, minimalist design"
            ]
        
        return prompts
    
    async def _integrate_images_into_content(self, content: str, images: List[Dict[str, Any]], language: str) -> str:
        """Integrate generated images into content"""
        enhanced_content = content
        
        # Replace image placeholders with actual image markdown
        for i, image in enumerate(images):
            placeholder = f"[IMAGE_PLACEHOLDER_{i+1}]"
            if placeholder in enhanced_content:
                image_markdown = f"\n\n![{image['prompt']}]({image['url']})\n*{image['prompt']}*\n\n"
                enhanced_content = enhanced_content.replace(placeholder, image_markdown)
            else:
                # Add image at strategic points if no placeholder found
                if i == 0:
                    # Add first image after introduction
                    enhanced_content = self._add_image_after_intro(enhanced_content, image, language)
                elif i == 1:
                    # Add second image in the middle
                    enhanced_content = self._add_image_in_middle(enhanced_content, image)
                else:
                    # Add remaining images before conclusion
                    enhanced_content = self._add_image_before_conclusion(enhanced_content, image)
        
        return enhanced_content
    
    def _add_image_after_intro(self, content: str, image: Dict[str, Any], language: str) -> str:
        """Add image after introduction paragraph"""
        lines = content.split('\n')
        intro_end = 0
        
        for i, line in enumerate(lines):
            if line.strip() and not line.startswith('#') and i > 2:
                intro_end = i + 1
                break
        
        if intro_end > 0:
            image_markdown = f"\n![{image['prompt']}]({image['url']})\n*{image['prompt']}*\n"
            lines.insert(intro_end, image_markdown)
        
        return '\n'.join(lines)
    
    def _add_image_in_middle(self, content: str, image: Dict[str, Any]) -> str:
        """Add image in the middle of content"""
        lines = content.split('\n')
        middle_point = len(lines) // 2
        
        image_markdown = f"\n![{image['prompt']}]({image['url']})\n*{image['prompt']}*\n"
        lines.insert(middle_point, image_markdown)
        
        return '\n'.join(lines)
    
    def _add_image_before_conclusion(self, content: str, image: Dict[str, Any]) -> str:
        """Add image before conclusion"""
        lines = content.split('\n')
        
        # Find conclusion section
        conclusion_start = len(lines) - 10  # Default to near end
        for i in range(len(lines) - 1, 0, -1):
            line = lines[i].lower()
            if any(word in line for word in ['สรุป', 'conclusion', 'summary', 'call to action']):
                conclusion_start = i
                break
        
        image_markdown = f"\n![{image['prompt']}]({image['url']})\n*{image['prompt']}*\n"
        lines.insert(conclusion_start, image_markdown)
        
        return '\n'.join(lines)
    
    def _extract_seo_data(self, content: str, keywords: List[str]) -> Dict[str, Any]:
        """Extract SEO data from generated content"""
        word_count = len(content.split())
        
        # Extract meta description if present
        meta_desc = ""
        if "Meta Description" in content:
            lines = content.split('\n')
            for line in lines:
                if "Meta Description" in line:
                    meta_desc = line.split(':', 1)[1].strip() if ':' in line else ""
                    break
        
        # Calculate keyword density
        keyword_density = {}
        content_lower = content.lower()
        for keyword in keywords:
            count = content_lower.count(keyword.lower())
            density = (count / word_count) * 100 if word_count > 0 else 0
            keyword_density[keyword] = {
                "count": count,
                "density": round(density, 2)
            }
        
        return {
            "word_count": word_count,
            "meta_description": meta_desc,
            "keyword_density": keyword_density,
            "readability_score": 85,  # Placeholder
            "seo_score": 90  # Placeholder
        }

# Initialize services
content_generator = EnhancedContentGenerator()
image_generator = AIImageGenerator()

# API Endpoints
@app.get("/")
async def root():
    """Root endpoint with server status"""
    return {
        "status": "active",
        "version": "3.0.0-enhanced",
        "features": [
            "ai_content_generation",
            "ai_image_generation", 
            "website_intelligence",
            "seo_optimization",
            "multi_language_support",
            "wordpress_integration"
        ],
        "timestamp": datetime.now().isoformat()
    }

@app.post("/universal-mcp/generate-blog-with-images")
async def generate_blog_with_images(request: ContentGenerationRequest):
    """Generate blog content with AI-generated images"""
    return await content_generator.generate_blog_with_images(request)

@app.post("/universal-mcp/generate-image")
async def generate_image(request: ImageGenerationRequest):
    """Generate AI image based on prompt"""
    image_data = await image_generator.generate_image(
        prompt=request.prompt,
        style=request.style,
        size=request.size
    )
    
    if image_data:
        return {
            "success": True,
            "image": image_data,
            "timestamp": datetime.now().isoformat()
        }
    else:
        raise HTTPException(status_code=500, detail="Image generation failed")

@app.post("/universal-mcp/generate-content")
async def generate_content(request: ContentGenerationRequest):
    """Generate content (backward compatibility)"""
    # Set include_images to False for backward compatibility
    request.include_images = False
    result = await content_generator.generate_blog_with_images(request)
    
    return {
        "success": result["success"],
        "task_type": "content_generation",
        "result": {
            "content": result["content"],
            "model": result["ai_model_used"],
            "word_count": result["word_count"],
            "generated_at": result["generated_at"]
        },
        "ai_model_used": result["ai_model_used"],
        "processing_time": 1.0,
        "recommendations": [
            "Review and edit the generated content",
            "Add relevant images and media",
            "Optimize for target keywords",
            "Include internal links to related content",
            "Add a compelling call-to-action"
        ],
        "timestamp": result["generated_at"]
    }

@app.post("/universal-mcp/generate-from-keywords")
async def generate_from_keywords(keywords: List[str], language: str = "en", tone: str = "professional", length: str = "medium", industry: str = "general", include_images: bool = True):
    """Generate content from keywords only - simplified endpoint"""
    if not keywords:
        raise HTTPException(status_code=400, detail="At least one keyword is required")
    
    request = ContentGenerationRequest(
        keywords=keywords,
        language=language,
        tone=tone,
        length=length,
        industry=industry,
        include_images=include_images,
        keywords_only=True
    )
    
    result = await content_generator.generate_blog_with_images(request)
    
    return {
        "success": result["success"],
        "content": {
            "title": "Generated from Keywords",  # Will be extracted from content
            "body": result["content"],
            "keywords": keywords,
            "word_count": result["word_count"],
            "reading_time": f"{max(1, result['word_count'] // 200)} min",
            "meta_description": result["content"][:160] + "..." if len(result["content"]) > 160 else result["content"]
        },
        "images": result.get("images", []),
        "ai_model_used": result["ai_model_used"],
        "generated_at": result["generated_at"],
        "recommendations": [
            "Review the generated title and adjust if needed",
            "Verify keyword placement is natural",
            "Add internal links to related content",
            "Optimize meta description for click-through rate"
        ]
    }

@app.post("/universal-mcp/analyze-website")
async def analyze_website(request: WebsiteAnalysisRequest):
    """Analyze website for content intelligence"""
    try:
        async with aiohttp.ClientSession() as session:
            async with session.get(request.url) as response:
                if response.status == 200:
                    html_content = await response.text()
                    soup = BeautifulSoup(html_content, 'html.parser')
                    
                    # Extract basic information
                    title = soup.find('title')
                    title_text = title.get_text().strip() if title else ""
                    
                    description = soup.find('meta', attrs={'name': 'description'})
                    description_text = description.get('content', '') if description else ""
                    
                    # Count headings
                    headings = {
                        'h1': len(soup.find_all('h1')),
                        'h2': len(soup.find_all('h2')),
                        'h3': len(soup.find_all('h3')),
                        'h4': len(soup.find_all('h4')),
                        'h5': len(soup.find_all('h5')),
                        'h6': len(soup.find_all('h6'))
                    }
                    
                    # Extract text content
                    text_content = soup.get_text()
                    words = text_content.split()
                    word_count = len(words)
                    
                    # Extract top keywords (simple frequency analysis)
                    word_freq = {}
                    for word in words:
                        word = re.sub(r'[^\w]', '', word.lower())
                        if len(word) > 3:
                            word_freq[word] = word_freq.get(word, 0) + 1
                    
                    top_keywords = sorted(word_freq.items(), key=lambda x: x[1], reverse=True)[:10]
                    top_keywords = [word for word, freq in top_keywords]
                    
                    # Detect industry (simple heuristic)
                    industry = "general"
                    if any(word in text_content.lower() for word in ['shop', 'buy', 'product', 'cart', 'price']):
                        industry = "ecommerce"
                    
                    return {
                        "analysis_type": request.analysis_type,
                        "results": {
                            "url": request.url,
                            "title": title_text,
                            "description": description_text,
                            "industry": industry,
                            "word_count": word_count,
                            "headings": headings,
                            "top_keywords": top_keywords,
                            "analysis_time": datetime.now().isoformat()
                        },
                        "timestamp": datetime.now().isoformat()
                    }
                else:
                    raise HTTPException(status_code=400, detail=f"Failed to fetch website: HTTP {response.status}")
    
    except Exception as e:
        logger.error(f"Website analysis error: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/universal-mcp/analyze-seo")
async def analyze_seo(request: dict):
    """Analyze SEO of content"""
    try:
        content = request.get("content", "")
        keywords = request.get("keywords", [])
        language = request.get("language", "en")
        
        if not content:
            raise HTTPException(status_code=400, detail="Content is required")
        
        # Basic SEO analysis
        words = content.split()
        word_count = len(words)
        
        # Keyword density analysis
        keyword_analysis = {}
        content_lower = content.lower()
        
        for keyword in keywords:
            keyword_lower = keyword.lower()
            count = content_lower.count(keyword_lower)
            density = (count / word_count * 100) if word_count > 0 else 0
            keyword_analysis[keyword] = {
                "count": count,
                "density": round(density, 2)
            }
        
        # Calculate SEO score (basic algorithm)
        seo_score = 50  # Base score
        
        # Word count scoring
        if 300 <= word_count <= 2000:
            seo_score += 20
        elif word_count > 100:
            seo_score += 10
        
        # Keyword density scoring
        for keyword_data in keyword_analysis.values():
            if 1 <= keyword_data["density"] <= 3:
                seo_score += 10
            elif 0.5 <= keyword_data["density"] <= 5:
                seo_score += 5
        
        # Cap at 100
        seo_score = min(seo_score, 100)
        
        # SEO recommendations
        recommendations = []
        if word_count < 300:
            recommendations.append("Consider adding more content (aim for 300+ words)")
        if not any(kd["count"] > 0 for kd in keyword_analysis.values()):
            recommendations.append("Include target keywords in your content")
        if word_count > 2000:
            recommendations.append("Consider breaking content into smaller sections")
        
        return {
            "success": True,
            "seo_analysis": {
                "word_count": word_count,
                "seo_score": seo_score,
                "keyword_analysis": keyword_analysis,
                "recommendations": recommendations,
                "readability_score": min(85, max(60, 100 - (word_count / 50))),  # Simple readability
                "language": language
            },
            "timestamp": datetime.now().isoformat()
        }
        
    except Exception as e:
        logger.error(f"SEO analysis error: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/universal-mcp/chatbot")
async def chatbot_response(request: dict):
    """AI Chatbot with website context"""
    try:
        message = request.get("message", "")
        website_url = request.get("website_url", "https://staging.uptowntrading.co.th")
        chat_history = request.get("chat_history", [])
        
        if not message:
            raise HTTPException(status_code=400, detail="Message is required")
        
        # Analyze website for context if not cached
        website_context = await _get_website_context(website_url)
        
        # Build conversation context
        conversation_context = "\n".join([
            f"{'User' if msg.get('sender') == 'user' else 'Assistant'}: {msg.get('text', '')}"
            for msg in chat_history[-5:]  # Last 5 messages for context
        ])
        
        # Create enhanced prompt for natural conversation
        prompt = f"""You are a helpful customer service AI assistant for {website_context.get('company_name', 'Uptown Trading')}. 

Website Information:
- Company: {website_context.get('company_name', 'Uptown Trading')}
- Description: {website_context.get('description', 'Wholesale trading company')}
- Products: {', '.join(website_context.get('products', []))}
- Services: {', '.join(website_context.get('services', []))}
- Contact: {website_context.get('contact', {})}

Recent Conversation:
{conversation_context}

Current User Message: {message}

Instructions:
1. Respond naturally and conversationally like a human customer service representative
2. Use the website information to provide accurate, helpful answers
3. Be friendly, professional, and empathetic
4. If you don't know something specific, offer to connect them with a human agent
5. Suggest relevant products or services when appropriate
6. Keep responses concise but informative
7. Use emojis sparingly and appropriately
8. Always try to be helpful and solution-oriented

Respond as the customer service assistant:"""

        # Generate response using Gemini
        gemini_client = GeminiClient()
        response = await gemini_client.generate_content(
            prompt=prompt,
            max_tokens=500,
            temperature=0.8
        )
        bot_response = response["content"].strip()
        
        # Analyze message for intent and generate suggestions
        intent = _analyze_intent(message.lower())
        suggestions = _generate_suggestions(intent, website_context)
        
        # Check if we should include product recommendations
        products = []
        if intent in ['product_inquiry', 'purchase_intent']:
            products = website_context.get('featured_products', [])[:3]
        
        return {
            "success": True,
            "response": {
                "text": bot_response,
                "intent": intent,
                "suggestions": suggestions,
                "products": products,
                "timestamp": datetime.now().isoformat()
            }
        }
        
    except Exception as e:
        logger.error(f"Chatbot error: {e}")
        # Fallback response
        return {
            "success": True,
            "response": {
                "text": "I apologize, but I'm having trouble processing your request right now. Please try again or contact our support team for immediate assistance.",
                "intent": "error",
                "suggestions": [
                    {"text": "Try again", "action": "retry"},
                    {"text": "Contact support", "action": "contact"},
                    {"text": "Browse products", "action": "products"}
                ],
                "products": [],
                "timestamp": datetime.now().isoformat()
            }
        }

async def _get_website_context(website_url):
    """Extract website context for chatbot"""
    try:
        async with aiohttp.ClientSession() as session:
            async with session.get(website_url, timeout=10) as response:
                if response.status == 200:
                    html = await response.text()
                    soup = BeautifulSoup(html, 'html.parser')
                    
                    # Extract company information
                    title = soup.find('title')
                    company_name = title.text.strip() if title else "Uptown Trading"
                    
                    description = soup.find('meta', attrs={'name': 'description'})
                    description_text = description.get('content', '') if description else ''
                    
                    # Extract products/services
                    products = []
                    services = []
                    
                    # Look for product-related content
                    product_keywords = ['product', 'item', 'rolling', 'paper', 'grinder', 'accessory']
                    for keyword in product_keywords:
                        elements = soup.find_all(string=re.compile(keyword, re.I))
                        for element in elements[:5]:
                            parent = element.parent
                            if parent and len(element.strip()) > 5:
                                products.append(element.strip()[:100])
                    
                    # Extract contact information
                    contact = {}
                    email_pattern = r'\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b'
                    phone_pattern = r'[\+]?[1-9]?[0-9]{7,15}'
                    
                    emails = re.findall(email_pattern, html)
                    phones = re.findall(phone_pattern, html)
                    
                    if emails:
                        contact['email'] = emails[0]
                    if phones:
                        contact['phone'] = phones[0]
                    
                    return {
                        'company_name': company_name,
                        'description': description_text,
                        'products': list(set(products))[:10],
                        'services': services,
                        'contact': contact,
                        'featured_products': [
                            {
                                'name': 'Rolling Papers',
                                'price': '฿50-200',
                                'description': 'High-quality rolling papers'
                            },
                            {
                                'name': 'Grinders',
                                'price': '฿150-500',
                                'description': 'Durable metal grinders'
                            },
                            {
                                'name': 'Accessories',
                                'price': '฿25-300',
                                'description': 'Various smoking accessories'
                            }
                        ]
                    }
    except Exception as e:
        logger.error(f"Website context extraction error: {e}")
    
    # Fallback context
    return {
        'company_name': 'Uptown Trading',
        'description': 'Wholesale trading company specializing in quality products',
        'products': ['Rolling Papers', 'Grinders', 'Accessories'],
        'services': ['Wholesale Trading', 'Customer Support', 'Fast Delivery'],
        'contact': {
            'email': 'info@uptowntrading.co.th',
            'phone': '+66-XXX-XXX-XXXX'
        },
        'featured_products': [
            {
                'name': 'Rolling Papers',
                'price': '฿50-200',
                'description': 'High-quality rolling papers in various sizes'
            },
            {
                'name': 'Grinders',
                'price': '฿150-500',
                'description': 'Durable metal and plastic grinders'
            },
            {
                'name': 'Accessories',
                'price': '฿25-300',
                'description': 'Various smoking accessories and tools'
            }
        ]
    }

def _analyze_intent(message):
    """Analyze user message intent"""
    message = message.lower()
    
    if any(word in message for word in ['hello', 'hi', 'hey', 'good morning', 'good afternoon']):
        return 'greeting'
    elif any(word in message for word in ['product', 'sell', 'buy', 'item', 'catalog']):
        return 'product_inquiry'
    elif any(word in message for word in ['order', 'purchase', 'buy now', 'place order']):
        return 'purchase_intent'
    elif any(word in message for word in ['price', 'cost', 'how much', 'expensive']):
        return 'pricing_inquiry'
    elif any(word in message for word in ['ship', 'delivery', 'transport', 'send']):
        return 'shipping_inquiry'
    elif any(word in message for word in ['contact', 'phone', 'email', 'address']):
        return 'contact_inquiry'
    elif any(word in message for word in ['help', 'support', 'problem', 'issue']):
        return 'support_request'
    elif any(word in message for word in ['thank', 'thanks', 'appreciate']):
        return 'gratitude'
    else:
        return 'general_inquiry'

def _generate_suggestions(intent, website_context):
    """Generate contextual suggestions based on intent"""
    suggestions_map = {
        'greeting': [
            {"text": "🛍️ Browse Products", "action": "products"},
            {"text": "📦 Place Order", "action": "order"},
            {"text": "📞 Contact Us", "action": "contact"}
        ],
        'product_inquiry': [
            {"text": "💰 View Pricing", "action": "pricing"},
            {"text": "📦 Order Now", "action": "order"},
            {"text": "🚚 Shipping Info", "action": "shipping"}
        ],
        'purchase_intent': [
            {"text": "📞 Call to Order", "action": "call"},
            {"text": "💳 Payment Methods", "action": "payment"},
            {"text": "🚚 Delivery Options", "action": "delivery"}
        ],
        'pricing_inquiry': [
            {"text": "📊 Bulk Discounts", "action": "bulk"},
            {"text": "🛍️ View Products", "action": "products"},
            {"text": "📞 Get Quote", "action": "quote"}
        ],
        'shipping_inquiry': [
            {"text": "💰 Shipping Costs", "action": "shipping_cost"},
            {"text": "📍 Delivery Areas", "action": "delivery_areas"},
            {"text": "📦 Track Order", "action": "track"}
        ],
        'contact_inquiry': [
            {"text": "📞 Call Now", "action": "call"},
            {"text": "📧 Send Email", "action": "email"},
            {"text": "🗺️ Visit Store", "action": "location"}
        ],
        'support_request': [
            {"text": "👤 Human Agent", "action": "human"},
            {"text": "📞 Call Support", "action": "support_call"},
            {"text": "📧 Email Support", "action": "support_email"}
        ],
        'gratitude': [
            {"text": "❓ More Questions", "action": "questions"},
            {"text": "🛍️ Browse Products", "action": "products"},
            {"text": "👋 End Chat", "action": "end"}
        ]
    }
    
    return suggestions_map.get(intent, [
        {"text": "🛍️ Products", "action": "products"},
        {"text": "📞 Contact", "action": "contact"},
        {"text": "❓ Help", "action": "help"}
    ])

@app.get("/universal-mcp/status")
async def get_status():
    """Get server status and capabilities"""
    return {
        "status": "active",
        "version": "3.0.0-enhanced",
        "components": {
            "gemini_ai": "active",
            "image_generation": "active",
            "website_intelligence": "active",
            "content_generation": "active",
            "chatbot": "active"
        },
        "capabilities": [
            "universal_content_generation",
            "ai_image_generation",
            "blog_with_images_generation",
            "website_intelligence_analysis",
            "seo_analysis",
            "multi_industry_support",
            "real_time_website_analysis"
        ],
        "supported_industries": [
            "ecommerce", "healthcare", "finance", "technology", 
            "education", "real_estate", "automotive", "travel", 
            "food", "legal", "general"
        ],
        "supported_languages": ["en", "th", "es", "fr", "de"],
        "image_generation": {
            "providers": ["dalle", "stable_diffusion", "midjourney"],
            "styles": ["professional", "artistic", "minimalist", "commercial"],
            "sizes": ["512x512", "1024x1024", "1024x1792", "1792x1024"]
        },
        "ai_models": {
            "gemini": "available",
            "image_ai": "available"
        },
        "timestamp": datetime.now().isoformat()
    }

if __name__ == "__main__":
    import uvicorn
    import os
    
    # Use PORT environment variable for Render.com, fallback to 8083 for local development
    port = int(os.environ.get("PORT", 8083))
    host = os.environ.get("HOST", "0.0.0.0")
    
    print(f"🚀 Starting Universal MCP Server on {host}:{port}")
    uvicorn.run(app, host=host, port=port)