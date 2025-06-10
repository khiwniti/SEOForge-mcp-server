from fastapi import APIRouter, HTTPException, Depends
from pydantic import BaseModel, Field
from typing import List, Optional, Dict

import databutton as db
from openai import OpenAI

import advertools as adv
import re
from collections import Counter
from readability import Readability # py-readability-metrics

router = APIRouter(prefix="/seo-analyzer", tags=["SEO Analyzer"])

# --- Pydantic Models ---

class SeoAnalysisRequest(BaseModel):
    content: str = Field(..., min_length=100, description="The text content to analyze (minimum 100 characters).")
    keywords: Optional[List[str]] = Field(None, description="A list of target keywords to analyze for density.")
    current_meta_title: Optional[str] = Field(None, description="Current meta title of the content, if available.")
    current_meta_description: Optional[str] = Field(None, description="Current meta description of the content, if available.")

class KeywordDensityResult(BaseModel):
    keyword: str
    count: int
    density: float = Field(..., description="Percentage of the keyword in the text.")

class ReadabilityScores(BaseModel):
    flesch_reading_ease: Optional[float] = None
    flesch_kincaid_grade: Optional[float] = None
    gunning_fog: Optional[float] = None
    # Add other scores from py-readability-metrics as needed

class MetaTagSuggestion(BaseModel):
    suggested_title: Optional[str] = None
    suggested_description: Optional[str] = None

class SeoAnalysisResponse(BaseModel):
    overall_seo_score: float = Field(..., description="A heuristic score from 0.0 to 100.0 representing overall SEO quality.")
    keyword_density_results: Optional[List[KeywordDensityResult]] = None
    readability_scores: Optional[ReadabilityScores] = None
    meta_tag_suggestions: Optional[MetaTagSuggestion] = None
    actionable_recommendations: List[str] = Field([], description="A list of actionable tips to improve SEO.")

# --- OpenAI Client Helper ---
def get_openai_client():
    api_key = db.secrets.get("OPENAI_API_KEY")
    if not api_key:
        raise HTTPException(status_code=500, detail="OpenAI API key is not configured.")
    return OpenAI(api_key=api_key)

# --- Placeholder Analysis Functions (to be implemented) ---

async def calculate_keyword_density_async(text: str, keywords: Optional[List[str]]) -> Optional[List[KeywordDensityResult]]:
    if not keywords or not text.strip():
        return None

    # Normalize text: lower case and remove punctuation for better matching
    # Keep spaces to count words. Using regex to keep only alphanumeric and spaces.
    normalized_text_for_word_count = re.sub(r'[^a-z0-9\s]+', '', text.lower())
    words = normalized_text_for_word_count.split()
    total_words = len(words)

    if total_words == 0:
        return [KeywordDensityResult(keyword=kw, count=0, density=0.0) for kw in keywords]

    results: List[KeywordDensityResult] = []

    # Using advertools for word frequency on the raw text (case-insensitive implicitly by advertools)
    # We'll use the raw text for adv.word_frequency as it handles tokenization.
    # However, for the final density calculation, our own total_words count is more direct.
    try:
        # adv.word_frequency returns a DataFrame, so we convert to dict for easier lookup
        # It's usually better to analyze the text as a whole then pick keywords, 
        # but here we are given specific keywords.
        # We can count occurrences directly for the given keywords for simplicity here.
        # For more advanced analysis, adv.word_frequency(text.lower()) would give all word counts.
        
        text_lower = text.lower() # For direct counting

        for kw in keywords:
            kw_lower = kw.lower()
            # Simple count for phrase/keyword. 
            # For multi-word keywords, this is a basic substring count. More advanced phrase matching might be needed for strictness.
            # For single words, it's a word count.
            # A more robust way for phrases would be to tokenize and check sequences, or use adv features for n-grams.
            # For now, simple count of the lowercased keyword string.
            count = text_lower.count(kw_lower)
            
            density = (count / total_words) * 100 if total_words > 0 else 0.0
            # Advertools' word_frequency on the keyword itself might not be what we want.
            # We want the count of the keyword *in the text*.
            
            results.append(KeywordDensityResult(keyword=kw, count=count, density=round(density, 2)))
        
        print(f"Keyword density calculated for: {keywords}. Total words: {total_words}")
        return results

    except Exception as e:
        print(f"Error during keyword density calculation: {e}")
        # Fallback or re-raise, for now, return None or empty
        # Returning what we have, or an error indicator might be better.
        # For now, if advertools fails, we'll rely on the simple count done above or return empty if that logic was inside try.
        # The current simple count is outside the try block, so it will proceed.
        # If we used advertools' frequency table, we'd process it here.
        # For now, the direct count is the primary method.
        return results # Returns results from direct count even if adv specific part fails (which is not used here yet)

async def calculate_readability_async(text: str) -> ReadabilityScores:
    # Ensure text has enough words for the library (often 100+ for some metrics)
    if len(text.split()) < 100:
        # Not enough content for a reliable full analysis by some metrics, 
        # but some scores might still work or we return what we can.
        # For now, let's return empty or minimal scores if text is too short.
        # The library itself might also handle this, but good to be aware.
        print(f"Warning: Text is short ({len(text.split())} words), readability scores might be less accurate or unavailable.")
        # We could return default/None values or try to calculate anyway if the lib allows.
        # For now, let's proceed and let the library handle errors or partial results.
        pass # Continue to attempt calculation

    try:
        r = Readability(text)
        scores = ReadabilityScores(
            flesch_reading_ease=round(r.flesch().score, 2),
            flesch_kincaid_grade=round(r.flesch_kincaid().score, 2),
            gunning_fog=round(r.gunning_fog().score, 2)
            # We can add more scores here as needed, e.g.:
            # smog=r.smog().score,
            # coleman_liau_index=r.coleman_liau().score,
            # dale_chall=r.dale_chall().score,
            # ari=r.ari().score
        )
        print(f"Readability scores calculated: Flesch Ease {scores.flesch_reading_ease}, Gunning Fog {scores.gunning_fog}")
        return scores
    except Exception as e:
        print(f"Error calculating readability scores: {e}")
        # Return empty scores or specific error indication if preferred
        return ReadabilityScores() # Return model with Nones

async def suggest_meta_tags_async(text: str, keywords: Optional[List[str]], current_title: Optional[str], current_description: Optional[str], client: OpenAI) -> MetaTagSuggestion:
    print(f"Suggesting meta tags for content starting with: {text[:100]}...")
    
    system_prompt = (
        "You are an expert SEO copywriter specializing in crafting compelling and effective meta titles and descriptions. "
        "Your goal is to generate a meta title (around 50-60 characters) and a meta description (around 150-160 characters) "
        "that are highly relevant to the provided content, incorporate important keywords naturally, and are optimized for click-through rates (CTR) in search engine results."
    )

    user_prompt_parts = [
        "Please analyze the following text content and generate an optimized SEO meta title and meta description.",
        "\n--- Main Content Snippet ---",
        text[:2000], # Provide a substantial snippet, but not excessively long to manage token usage
        "\n--- End Content Snippet ---"
    ]

    if keywords:
        user_prompt_parts.append(f"\nPlease prioritize or consider these keywords: {', '.join(keywords)}.")

    if current_title:
        user_prompt_parts.append(f"\nThe current meta title is: '{current_title}'. You can improve it or suggest a new one.")
    
    if current_description:
        user_prompt_parts.append(f"\nThe current meta description is: '{current_description}'. You can improve it or suggest a new one.")
    
    user_prompt_parts.append("\nPlease provide your suggestions in the following format:")
    user_prompt_parts.append("Title: [Your Suggested Meta Title]")
    user_prompt_parts.append("Description: [Your Suggested Meta Description]")

    user_prompt = "\n".join(user_prompt_parts)

    try:
        completion = client.chat.completions.create(
            model="gpt-4o-mini", # Good balance of capability and cost
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": user_prompt}
            ],
            temperature=0.5, # Moderately creative, but focused on conciseness
            max_tokens=150 # Meta title + description + formatting should fit easily
        )
        
        response_text = completion.choices[0].message.content
        if not response_text:
            print("OpenAI returned empty content for meta tags.")
            return MetaTagSuggestion()

        # Parse the response_text to extract Title and Description
        suggested_title = None
        suggested_description = None

        title_match = re.search(r"Title: (.*)", response_text, re.IGNORECASE)
        if title_match:
            suggested_title = title_match.group(1).strip()
        
        description_match = re.search(r"Description: (.*)", response_text, re.IGNORECASE)
        if description_match:
            suggested_description = description_match.group(1).strip()
        
        if not suggested_title and not suggested_description:
            print(f"Could not parse title/description from OpenAI response: {response_text}")
            # Fallback: attempt to use the whole response if it's short, or parts of it, as a last resort
            # This is a basic fallback, might need refinement.
            if len(response_text) < 200: # Arbitrary short length
                if not keywords: # if no keywords given, maybe the AI just gave a description
                    suggested_description = response_text.strip()
                else: # if keywords were given, maybe it's a title
                    suggested_title = response_text.strip()

        print(f"Meta tags suggested: Title='{suggested_title}', Description='{suggested_description}'")
        return MetaTagSuggestion(suggested_title=suggested_title, suggested_description=suggested_description)

    except Exception as e:
        print(f"Error during OpenAI call for meta tag suggestions: {e}")
        return MetaTagSuggestion() # Return empty suggestions on error

async def generate_actionable_recommendations_async(
    analysis_results: dict, # Combined results from density, readability, meta suggestions
    client: OpenAI
) -> List[str]:
    print(f"Generating actionable recommendations based on analysis: {analysis_results}")

    system_prompt = (
        "You are an expert SEO advisor. Based on the provided SEO analysis data (keyword density, readability scores, meta tag suggestions), "
        "generate a concise list of 3-5 actionable recommendations to improve the content's SEO performance. "
        "Focus on the most impactful changes the user can make. Frame recommendations as clear, direct advice. "
        "If a score is already good, you can acknowledge it briefly or suggest maintaining it."
    )

    user_prompt_parts = [
        "Here is the SEO analysis data for a piece of content:",
    ]

    # Keyword Density
    if analysis_results.get("keyword_density_results"):
        user_prompt_parts.append("\n--- Keyword Density ---")
        for res in analysis_results["keyword_density_results"]:
            user_prompt_parts.append(f"- Keyword '{res.keyword}': Count={res.count}, Density={res.density:.2f}%")
    else:
        user_prompt_parts.append("\n- Keyword Density: Not analyzed or no keywords provided.")

    # Readability
    if analysis_results.get("readability_scores"):
        scores = analysis_results["readability_scores"]
        user_prompt_parts.append("\n--- Readability Scores ---")
        if scores.flesch_reading_ease is not None:
            user_prompt_parts.append(f"- Flesch Reading Ease: {scores.flesch_reading_ease:.2f}")
        if scores.flesch_kincaid_grade is not None:
            user_prompt_parts.append(f"- Flesch-Kincaid Grade Level: {scores.flesch_kincaid_grade:.2f}")
        if scores.gunning_fog is not None:
            user_prompt_parts.append(f"- Gunning Fog Index: {scores.gunning_fog:.2f}")
    else:
        user_prompt_parts.append("\n- Readability Scores: Not analyzed or unavailable.")
    
    # Meta Tags
    if analysis_results.get("meta_tag_suggestions"):
        meta = analysis_results["meta_tag_suggestions"]
        user_prompt_parts.append("\n--- Meta Tag Suggestions ---")
        if meta.suggested_title:
            user_prompt_parts.append(f"- Suggested Meta Title: '{meta.suggested_title}'")
        if meta.suggested_description:
            user_prompt_parts.append(f"- Suggested Meta Description: '{meta.suggested_description}'")
    else:
        user_prompt_parts.append("\n- Meta Tag Suggestions: Not generated or unavailable.")

    user_prompt_parts.append("\nBased on this data, please provide a list of 3-5 actionable SEO recommendations. Format each recommendation as a bullet point starting with '-'.")
    user_prompt = "\n".join(user_prompt_parts)

    try:
        completion = client.chat.completions.create(
            model="gpt-4o-mini",
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": user_prompt}
            ],
            temperature=0.6,
            max_tokens=250 # Allow for a decent list of recommendations
        )
        response_text = completion.choices[0].message.content
        if not response_text:
            print("OpenAI returned empty content for recommendations.")
            return ["Could not generate recommendations at this time."]

        # Recommendations are expected as bullet points
        recommendations = [rec.strip().lstrip('-').strip() for rec in response_text.split('\n') if rec.strip().startswith('-') or rec.strip()] # also take non-bulleted lines if they are the only ones
        
        if not recommendations:
             recommendations = [response_text.strip()] # Use the whole response if no bullets found

        print(f"Generated recommendations: {recommendations}")
        return recommendations if recommendations else ["No specific recommendations generated based on the current data."]

    except Exception as e:
        print(f"Error during OpenAI call for actionable recommendations: {e}")
        return ["An error occurred while generating recommendations."]

# --- Main Analysis Endpoint ---
async def analyze_seo_content(request: SeoAnalysisRequest, openai_client: OpenAI = Depends(get_openai_client)):
    """
    Analyzes content for SEO metrics including keyword density, readability, 
    and provides suggestions for meta tags and actionable recommendations.
    """
    print(f"Received SEO analysis request for content starting with: {request.content[:100]}...")

    # Orchestrate analysis calls
    density_results_list = await calculate_keyword_density_async(request.content, request.keywords)
    readability_scores_model = await calculate_readability_async(request.content)
    meta_suggestions_model = await suggest_meta_tags_async(
        request.content, 
        request.keywords, 
        request.current_meta_title, 
        request.current_meta_description, 
        openai_client
    )
    
    # --- Calculate Overall SEO Score (Heuristic v1) ---
    # Base score can be, e.g., 50. Max score 100.
    overall_score = 50.0
    score_components = {
        "keyword_density": 0.0,
        "readability": 0.0,
        "meta_tags": 0.0
    }

    # 1. Keyword Density Component (Max ~20 points)
    # Simple check: if any keyword has a density between 1% and 3% (a common general guideline)
    if density_results_list:
        good_density_keywords = sum(1 for dr in density_results_list if 1.0 <= dr.density <= 3.0)
        if good_density_keywords > 0:
            score_components["keyword_density"] = min(good_density_keywords * 5, 15) # Up to 15 for good density
        # Penalty for very high density (keyword stuffing) - can be added later
    else:
        score_components["keyword_density"] = -5 # Penalty if keywords provided but no results (e.g. not found)

    # 2. Readability Component (Max ~20 points for Flesch Reading Ease)
    # Score based on Flesch Reading Ease (higher is better, 60-70 is plain English)
    if readability_scores_model and readability_scores_model.flesch_reading_ease is not None:
        fre = readability_scores_model.flesch_reading_ease
        if fre >= 70:
            score_components["readability"] = 20
        elif fre >= 60:
            score_components["readability"] = 15
        elif fre >= 50:
            score_components["readability"] = 10
        elif fre >= 30:
            score_components["readability"] = 5
        else:
            score_components["readability"] = 0
    
    # 3. Meta Tags Component (Max ~10 points if suggestions are generated)
    # Simple check: if AI suggested non-empty title and description
    if meta_suggestions_model and meta_suggestions_model.suggested_title and meta_suggestions_model.suggested_description:
        score_components["meta_tags"] = 10
    elif meta_suggestions_model and (meta_suggestions_model.suggested_title or meta_suggestions_model.suggested_description):
        score_components["meta_tags"] = 5 # Partial credit

    overall_score += score_components["keyword_density"] + score_components["readability"] + score_components["meta_tags"]
    overall_score = max(0, min(overall_score, 100.0)) # Clamp score between 0 and 100

    # Prepare analysis data for recommendation generation
    # The generate_actionable_recommendations_async expects a dict.
    # We can convert Pydantic models to dicts or access fields directly.
    # For simplicity and clarity in the prompt, let's build the dict for it.
    analysis_data_for_recommendations = {
        "keyword_density_results": [dr.model_dump() for dr in density_results_list] if density_results_list else [],
        "readability_scores": readability_scores_model.model_dump() if readability_scores_model else {},
        "meta_tag_suggestions": meta_suggestions_model.model_dump() if meta_suggestions_model else {},
        "original_content_length": len(request.content),
        "current_seo_score": overall_score # Pass the score for context to recommendations
    }

    recommendations = await generate_actionable_recommendations_async(analysis_data_for_recommendations, openai_client)

    return SeoAnalysisResponse(
        overall_seo_score=round(overall_score, 1),
        keyword_density_results=density_results_list,
        readability_scores=readability_scores_model,
        meta_tag_suggestions=meta_suggestions_model,
        actionable_recommendations=recommendations
    )
