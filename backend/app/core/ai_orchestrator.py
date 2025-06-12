"""
Multi-Model AI Orchestration System
Intelligently routes requests to the best AI model based on task type and performance
"""

import asyncio
import json
import time
from datetime import datetime, timezone
from typing import Dict, List, Any, Optional, Tuple
from enum import Enum
from dataclasses import dataclass
import logging
from abc import ABC, abstractmethod

import openai
import anthropic
import google.generativeai as genai
import aiohttp

logger = logging.getLogger(__name__)

class AIModel(Enum):
    GPT4_TURBO = "gpt-4-turbo"
    GPT4_MINI = "gpt-4o-mini"
    CLAUDE_3_5_SONNET = "claude-3-5-sonnet-20241022"
    CLAUDE_3_HAIKU = "claude-3-haiku-20240307"
    GEMINI_PRO = "gemini-1.5-pro"
    GEMINI_FLASH = "gemini-1.5-flash"

class TaskType(Enum):
    CONTENT_GENERATION = "content_generation"
    SEO_ANALYSIS = "seo_analysis"
    KEYWORD_RESEARCH = "keyword_research"
    COMPETITIVE_ANALYSIS = "competitive_analysis"
    TRANSLATION = "translation"
    SUMMARIZATION = "summarization"
    TECHNICAL_WRITING = "technical_writing"
    CREATIVE_WRITING = "creative_writing"
    DATA_ANALYSIS = "data_analysis"
    CODE_GENERATION = "code_generation"

@dataclass
class ModelPerformance:
    model: AIModel
    task_type: TaskType
    success_rate: float
    avg_response_time: float
    avg_quality_score: float
    total_requests: int
    last_updated: datetime

@dataclass
class AIRequest:
    task_type: TaskType
    prompt: str
    context: Dict[str, Any]
    max_tokens: int = 2000
    temperature: float = 0.7
    user_id: Optional[str] = None
    priority: int = 1  # 1=low, 2=medium, 3=high

@dataclass
class AIResponse:
    content: str
    model_used: AIModel
    response_time: float
    tokens_used: int
    quality_score: float
    metadata: Dict[str, Any]
    timestamp: datetime

class BaseAIProvider(ABC):
    """Abstract base class for AI providers"""
    
    def __init__(self, api_key: str):
        self.api_key = api_key
        self.rate_limit_remaining = 1000
        self.rate_limit_reset = time.time() + 3600
    
    @abstractmethod
    async def generate_response(self, request: AIRequest) -> AIResponse:
        """Generate response using the AI model"""
        pass
    
    @abstractmethod
    def get_supported_models(self) -> List[AIModel]:
        """Get list of supported models"""
        pass
    
    def can_handle_request(self) -> bool:
        """Check if provider can handle more requests"""
        return self.rate_limit_remaining > 0 or time.time() > self.rate_limit_reset

class OpenAIProvider(BaseAIProvider):
    """OpenAI GPT models provider"""
    
    def __init__(self, api_key: str):
        super().__init__(api_key)
        self.client = openai.AsyncOpenAI(api_key=api_key)
    
    async def generate_response(self, request: AIRequest) -> AIResponse:
        start_time = time.time()
        
        try:
            # Select appropriate GPT model based on task
            model = self._select_gpt_model(request.task_type)
            
            # Build messages
            messages = [
                {"role": "system", "content": self._build_system_prompt(request)},
                {"role": "user", "content": request.prompt}
            ]
            
            # Make API call
            response = await self.client.chat.completions.create(
                model=model.value,
                messages=messages,
                max_tokens=request.max_tokens,
                temperature=request.temperature
            )
            
            response_time = time.time() - start_time
            content = response.choices[0].message.content
            tokens_used = response.usage.total_tokens
            
            # Calculate quality score (simplified)
            quality_score = self._calculate_quality_score(content, request.task_type)
            
            return AIResponse(
                content=content,
                model_used=model,
                response_time=response_time,
                tokens_used=tokens_used,
                quality_score=quality_score,
                metadata={"provider": "openai", "finish_reason": response.choices[0].finish_reason},
                timestamp=datetime.now(timezone.utc)
            )
            
        except Exception as e:
            logger.error(f"OpenAI API error: {e}")
            raise
    
    def _select_gpt_model(self, task_type: TaskType) -> AIModel:
        """Select appropriate GPT model based on task type"""
        complex_tasks = [TaskType.TECHNICAL_WRITING, TaskType.CODE_GENERATION, TaskType.DATA_ANALYSIS]
        
        if task_type in complex_tasks:
            return AIModel.GPT4_TURBO
        else:
            return AIModel.GPT4_MINI
    
    def _build_system_prompt(self, request: AIRequest) -> str:
        """Build system prompt based on task type and context"""
        base_prompt = "You are an expert AI assistant specialized in "
        
        task_prompts = {
            TaskType.CONTENT_GENERATION: "creating high-quality, engaging content that is SEO-optimized and valuable to readers.",
            TaskType.SEO_ANALYSIS: "analyzing websites and content for SEO optimization opportunities and providing actionable recommendations.",
            TaskType.KEYWORD_RESEARCH: "conducting comprehensive keyword research and identifying valuable search opportunities.",
            TaskType.COMPETITIVE_ANALYSIS: "analyzing competitors and market landscapes to identify opportunities and strategies.",
            TaskType.TRANSLATION: "providing accurate, culturally-appropriate translations that maintain context and meaning.",
            TaskType.SUMMARIZATION: "creating concise, accurate summaries that capture key information and insights.",
            TaskType.TECHNICAL_WRITING: "creating clear, accurate technical documentation and explanations.",
            TaskType.CREATIVE_WRITING: "creating engaging, original creative content that resonates with target audiences.",
            TaskType.DATA_ANALYSIS: "analyzing data patterns, trends, and insights to provide actionable recommendations.",
            TaskType.CODE_GENERATION: "writing clean, efficient, well-documented code that follows best practices."
        }
        
        prompt = base_prompt + task_prompts.get(request.task_type, "providing helpful and accurate assistance.")
        
        # Add context information
        if request.context:
            industry = request.context.get("industry", "")
            if industry:
                prompt += f" You have deep expertise in the {industry} industry."
            
            language = request.context.get("language", "")
            if language and language != "en":
                prompt += f" Please respond in {language} language."
        
        return prompt
    
    def _calculate_quality_score(self, content: str, task_type: TaskType) -> float:
        """Calculate quality score for the response"""
        # Simplified quality scoring - in production, this would be more sophisticated
        score = 0.7  # Base score
        
        if len(content) > 100:
            score += 0.1
        if len(content.split()) > 50:
            score += 0.1
        if any(char.isupper() for char in content):
            score += 0.05
        if '.' in content and ',' in content:
            score += 0.05
        
        return min(score, 1.0)
    
    def get_supported_models(self) -> List[AIModel]:
        return [AIModel.GPT4_TURBO, AIModel.GPT4_MINI]

class AnthropicProvider(BaseAIProvider):
    """Anthropic Claude models provider"""
    
    def __init__(self, api_key: str):
        super().__init__(api_key)
        self.client = anthropic.AsyncAnthropic(api_key=api_key)
    
    async def generate_response(self, request: AIRequest) -> AIResponse:
        start_time = time.time()
        
        try:
            # Select appropriate Claude model
            model = self._select_claude_model(request.task_type)
            
            # Build prompt
            prompt = self._build_claude_prompt(request)
            
            # Make API call
            response = await self.client.messages.create(
                model=model.value,
                max_tokens=request.max_tokens,
                temperature=request.temperature,
                messages=[{"role": "user", "content": prompt}]
            )
            
            response_time = time.time() - start_time
            content = response.content[0].text
            tokens_used = response.usage.input_tokens + response.usage.output_tokens
            
            quality_score = self._calculate_quality_score(content, request.task_type)
            
            return AIResponse(
                content=content,
                model_used=model,
                response_time=response_time,
                tokens_used=tokens_used,
                quality_score=quality_score,
                metadata={"provider": "anthropic", "stop_reason": response.stop_reason},
                timestamp=datetime.now(timezone.utc)
            )
            
        except Exception as e:
            logger.error(f"Anthropic API error: {e}")
            raise
    
    def _select_claude_model(self, task_type: TaskType) -> AIModel:
        """Select appropriate Claude model based on task type"""
        complex_tasks = [TaskType.TECHNICAL_WRITING, TaskType.DATA_ANALYSIS, TaskType.COMPETITIVE_ANALYSIS]
        
        if task_type in complex_tasks:
            return AIModel.CLAUDE_3_5_SONNET
        else:
            return AIModel.CLAUDE_3_HAIKU
    
    def _build_claude_prompt(self, request: AIRequest) -> str:
        """Build prompt for Claude"""
        system_prompt = self._build_system_prompt(request)
        return f"{system_prompt}\n\n{request.prompt}"
    
    def _build_system_prompt(self, request: AIRequest) -> str:
        """Build system prompt for Claude"""
        # Similar to OpenAI but adapted for Claude's style
        base_prompt = "You are Claude, an AI assistant created by Anthropic. You are "
        
        task_descriptions = {
            TaskType.CONTENT_GENERATION: "an expert content creator who produces high-quality, SEO-optimized content.",
            TaskType.SEO_ANALYSIS: "an SEO specialist who provides detailed analysis and actionable recommendations.",
            TaskType.KEYWORD_RESEARCH: "a keyword research expert who identifies valuable search opportunities.",
            TaskType.COMPETITIVE_ANALYSIS: "a market analyst who provides comprehensive competitive insights.",
            TaskType.TRANSLATION: "a professional translator who maintains cultural context and accuracy.",
            TaskType.SUMMARIZATION: "skilled at creating concise, accurate summaries of complex information.",
            TaskType.TECHNICAL_WRITING: "a technical writing expert who creates clear, comprehensive documentation.",
            TaskType.CREATIVE_WRITING: "a creative writer who produces engaging, original content.",
            TaskType.DATA_ANALYSIS: "a data analyst who identifies patterns and provides actionable insights.",
            TaskType.CODE_GENERATION: "a software engineer who writes clean, efficient, well-documented code."
        }
        
        return base_prompt + task_descriptions.get(request.task_type, "helpful, harmless, and honest.")
    
    def _calculate_quality_score(self, content: str, task_type: TaskType) -> float:
        """Calculate quality score for Claude responses"""
        # Claude tends to be more verbose and structured
        score = 0.75  # Base score
        
        if len(content) > 200:
            score += 0.1
        if content.count('\n') > 2:  # Well-structured
            score += 0.1
        if any(word in content.lower() for word in ['however', 'therefore', 'additionally']):
            score += 0.05  # Good transitions
        
        return min(score, 1.0)
    
    def get_supported_models(self) -> List[AIModel]:
        return [AIModel.CLAUDE_3_5_SONNET, AIModel.CLAUDE_3_HAIKU]

class GoogleAIProvider(BaseAIProvider):
    """Google Gemini models provider"""
    
    def __init__(self, api_key: str):
        super().__init__(api_key)
        genai.configure(api_key=api_key)
    
    async def generate_response(self, request: AIRequest) -> AIResponse:
        start_time = time.time()
        
        try:
            # Select appropriate Gemini model
            model_name = self._select_gemini_model(request.task_type)
            model = genai.GenerativeModel(model_name.value)
            
            # Build prompt
            prompt = self._build_gemini_prompt(request)
            
            # Configure generation
            generation_config = genai.types.GenerationConfig(
                max_output_tokens=request.max_tokens,
                temperature=request.temperature
            )
            
            # Make API call
            response = await asyncio.to_thread(
                model.generate_content,
                prompt,
                generation_config=generation_config
            )
            
            response_time = time.time() - start_time
            content = response.text
            tokens_used = model.count_tokens(prompt).total_tokens + model.count_tokens(content).total_tokens
            
            quality_score = self._calculate_quality_score(content, request.task_type)
            
            return AIResponse(
                content=content,
                model_used=model_name,
                response_time=response_time,
                tokens_used=tokens_used,
                quality_score=quality_score,
                metadata={"provider": "google", "finish_reason": response.candidates[0].finish_reason.name},
                timestamp=datetime.now(timezone.utc)
            )
            
        except Exception as e:
            logger.error(f"Google AI API error: {e}")
            raise
    
    def _select_gemini_model(self, task_type: TaskType) -> AIModel:
        """Select appropriate Gemini model based on task type"""
        complex_tasks = [TaskType.TRANSLATION, TaskType.DATA_ANALYSIS, TaskType.TECHNICAL_WRITING]
        
        if task_type in complex_tasks:
            return AIModel.GEMINI_PRO
        else:
            return AIModel.GEMINI_FLASH
    
    def _build_gemini_prompt(self, request: AIRequest) -> str:
        """Build prompt for Gemini"""
        system_prompt = self._build_system_prompt(request)
        return f"{system_prompt}\n\nTask: {request.prompt}"
    
    def _build_system_prompt(self, request: AIRequest) -> str:
        """Build system prompt for Gemini"""
        base_prompt = "You are a helpful AI assistant with expertise in "
        
        task_expertise = {
            TaskType.CONTENT_GENERATION: "content creation, SEO optimization, and digital marketing.",
            TaskType.SEO_ANALYSIS: "search engine optimization, web analytics, and digital marketing.",
            TaskType.KEYWORD_RESEARCH: "keyword research, search trends, and SEO strategy.",
            TaskType.COMPETITIVE_ANALYSIS: "market analysis, competitive intelligence, and business strategy.",
            TaskType.TRANSLATION: "multilingual communication, cultural adaptation, and localization.",
            TaskType.SUMMARIZATION: "information synthesis, content analysis, and communication.",
            TaskType.TECHNICAL_WRITING: "technical documentation, software development, and engineering.",
            TaskType.CREATIVE_WRITING: "creative content, storytelling, and brand communication.",
            TaskType.DATA_ANALYSIS: "data science, statistical analysis, and business intelligence.",
            TaskType.CODE_GENERATION: "software development, programming, and technical implementation."
        }
        
        return base_prompt + task_expertise.get(request.task_type, "general knowledge and problem-solving.")
    
    def _calculate_quality_score(self, content: str, task_type: TaskType) -> float:
        """Calculate quality score for Gemini responses"""
        score = 0.7  # Base score
        
        # Gemini is good at structured responses
        if content.count('*') > 2 or content.count('-') > 2:  # Bullet points
            score += 0.1
        if len(content.split('\n')) > 3:  # Multi-paragraph
            score += 0.1
        if any(char.isdigit() for char in content):  # Contains numbers/data
            score += 0.1
        
        return min(score, 1.0)
    
    def get_supported_models(self) -> List[AIModel]:
        return [AIModel.GEMINI_PRO, AIModel.GEMINI_FLASH]

class AIOrchestrator:
    """
    Main orchestrator that manages multiple AI providers and intelligently routes requests
    """
    
    def __init__(self, openai_key: str, anthropic_key: str, google_key: str):
        self.providers = {
            "openai": OpenAIProvider(openai_key),
            "anthropic": AnthropicProvider(anthropic_key),
            "google": GoogleAIProvider(google_key)
        }
        
        self.performance_history: Dict[str, ModelPerformance] = {}
        self.request_queue = asyncio.Queue()
        self.active_requests = 0
        self.max_concurrent_requests = 10
        
        # Model routing preferences based on task type
        self.task_model_preferences = {
            TaskType.CONTENT_GENERATION: [AIModel.GPT4_TURBO, AIModel.CLAUDE_3_5_SONNET, AIModel.GEMINI_PRO],
            TaskType.SEO_ANALYSIS: [AIModel.CLAUDE_3_5_SONNET, AIModel.GPT4_TURBO, AIModel.GEMINI_PRO],
            TaskType.KEYWORD_RESEARCH: [AIModel.GPT4_MINI, AIModel.GEMINI_FLASH, AIModel.CLAUDE_3_HAIKU],
            TaskType.COMPETITIVE_ANALYSIS: [AIModel.CLAUDE_3_5_SONNET, AIModel.GPT4_TURBO, AIModel.GEMINI_PRO],
            TaskType.TRANSLATION: [AIModel.GEMINI_PRO, AIModel.GPT4_TURBO, AIModel.CLAUDE_3_5_SONNET],
            TaskType.SUMMARIZATION: [AIModel.CLAUDE_3_HAIKU, AIModel.GPT4_MINI, AIModel.GEMINI_FLASH],
            TaskType.TECHNICAL_WRITING: [AIModel.GPT4_TURBO, AIModel.CLAUDE_3_5_SONNET, AIModel.GEMINI_PRO],
            TaskType.CREATIVE_WRITING: [AIModel.GPT4_TURBO, AIModel.CLAUDE_3_5_SONNET, AIModel.GEMINI_PRO],
            TaskType.DATA_ANALYSIS: [AIModel.CLAUDE_3_5_SONNET, AIModel.GPT4_TURBO, AIModel.GEMINI_PRO],
            TaskType.CODE_GENERATION: [AIModel.GPT4_TURBO, AIModel.CLAUDE_3_5_SONNET, AIModel.GEMINI_PRO]
        }
    
    async def process_request(self, request: AIRequest) -> AIResponse:
        """Process AI request with intelligent model selection"""
        try:
            # Select best model for the task
            selected_model, provider = await self._select_optimal_model(request)
            
            # Check rate limits
            if not provider.can_handle_request():
                # Try fallback models
                for fallback_model in self.task_model_preferences[request.task_type][1:]:
                    fallback_provider = self._get_provider_for_model(fallback_model)
                    if fallback_provider and fallback_provider.can_handle_request():
                        selected_model = fallback_model
                        provider = fallback_provider
                        break
                else:
                    raise Exception("All providers are rate limited")
            
            # Process request
            self.active_requests += 1
            try:
                response = await provider.generate_response(request)
                
                # Update performance metrics
                await self._update_performance_metrics(selected_model, request.task_type, response)
                
                return response
                
            finally:
                self.active_requests -= 1
                
        except Exception as e:
            logger.error(f"Failed to process AI request: {e}")
            raise
    
    async def _select_optimal_model(self, request: AIRequest) -> Tuple[AIModel, BaseAIProvider]:
        """Select the optimal model based on task type and performance history"""
        preferred_models = self.task_model_preferences.get(request.task_type, [AIModel.GPT4_MINI])
        
        best_model = None
        best_provider = None
        best_score = 0
        
        for model in preferred_models:
            provider = self._get_provider_for_model(model)
            if not provider:
                continue
            
            # Calculate selection score based on performance history
            performance_key = f"{model.value}:{request.task_type.value}"
            performance = self.performance_history.get(performance_key)
            
            if performance:
                # Score based on success rate, quality, and response time
                score = (
                    performance.success_rate * 0.4 +
                    performance.avg_quality_score * 0.4 +
                    (1 / max(performance.avg_response_time, 0.1)) * 0.2
                )
            else:
                # Default score for new model/task combinations
                score = 0.5
            
            if score > best_score:
                best_score = score
                best_model = model
                best_provider = provider
        
        if not best_model:
            # Fallback to first available model
            best_model = preferred_models[0]
            best_provider = self._get_provider_for_model(best_model)
        
        return best_model, best_provider
    
    def _get_provider_for_model(self, model: AIModel) -> Optional[BaseAIProvider]:
        """Get the provider that supports the given model"""
        for provider in self.providers.values():
            if model in provider.get_supported_models():
                return provider
        return None
    
    async def _update_performance_metrics(self, model: AIModel, task_type: TaskType, response: AIResponse):
        """Update performance metrics for the model/task combination"""
        performance_key = f"{model.value}:{task_type.value}"
        
        if performance_key in self.performance_history:
            perf = self.performance_history[performance_key]
            
            # Update running averages
            total_requests = perf.total_requests + 1
            perf.avg_response_time = (
                (perf.avg_response_time * perf.total_requests + response.response_time) / total_requests
            )
            perf.avg_quality_score = (
                (perf.avg_quality_score * perf.total_requests + response.quality_score) / total_requests
            )
            perf.total_requests = total_requests
            perf.last_updated = datetime.now(timezone.utc)
            
        else:
            # Create new performance record
            self.performance_history[performance_key] = ModelPerformance(
                model=model,
                task_type=task_type,
                success_rate=1.0,
                avg_response_time=response.response_time,
                avg_quality_score=response.quality_score,
                total_requests=1,
                last_updated=datetime.now(timezone.utc)
            )
    
    async def get_performance_stats(self) -> Dict[str, Any]:
        """Get performance statistics for all models"""
        stats = {
            "total_models": len(self.performance_history),
            "active_requests": self.active_requests,
            "model_performance": {}
        }
        
        for key, perf in self.performance_history.items():
            stats["model_performance"][key] = {
                "model": perf.model.value,
                "task_type": perf.task_type.value,
                "success_rate": perf.success_rate,
                "avg_response_time": perf.avg_response_time,
                "avg_quality_score": perf.avg_quality_score,
                "total_requests": perf.total_requests,
                "last_updated": perf.last_updated.isoformat()
            }
        
        return stats
    
    async def health_check(self) -> Dict[str, Any]:
        """Check health of all AI providers"""
        health_status = {
            "overall_status": "healthy",
            "providers": {},
            "timestamp": datetime.now(timezone.utc).isoformat()
        }
        
        for name, provider in self.providers.items():
            try:
                # Simple health check - try to make a minimal request
                test_request = AIRequest(
                    task_type=TaskType.SUMMARIZATION,
                    prompt="Test",
                    context={},
                    max_tokens=10
                )
                
                start_time = time.time()
                await provider.generate_response(test_request)
                response_time = time.time() - start_time
                
                health_status["providers"][name] = {
                    "status": "healthy",
                    "response_time": response_time,
                    "rate_limit_remaining": provider.rate_limit_remaining
                }
                
            except Exception as e:
                health_status["providers"][name] = {
                    "status": "unhealthy",
                    "error": str(e)
                }
                health_status["overall_status"] = "degraded"
        
        return health_status