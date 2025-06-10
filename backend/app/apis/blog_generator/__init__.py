from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, Field
from typing import List, Optional
import databutton as db
from openai import OpenAI

router = APIRouter(prefix="/blog-generator", tags=["Blog Generator"])

# --- Pydantic Models ---
class BlogTopicRequest(BaseModel):
    topic: str = Field(..., description="The main topic or title for the blog post.")
    keywords: Optional[List[str]] = Field(None, description="A list of keywords to focus on for SEO.", example=["AI content", "SEO optimization", "blogging tools"])

class BlogContentResponse(BaseModel):
    generated_text: str = Field(..., description="The AI-generated blog post content.")
    # We will add more fields here later, like meta_description, seo_title, etc.

# --- Helper Functions ---
def get_openai_client():
    # This will prompt the user for the secret if it's not already set.
    api_key = db.secrets.get("OPENAI_API_KEY")
    if not api_key:
        # This path should ideally not be hit if db.secrets.get prompts,
        # but as a safeguard or if the prompt mechanism changes.
        raise HTTPException(status_code=500, detail="OpenAI API key is not configured. Please add it to secrets as OPENAI_API_KEY.")
    return OpenAI(api_key=api_key)

# --- API Endpoints ---
@router.post("/generate", response_model=BlogContentResponse)
async def generate_blog_content(request: BlogTopicRequest):
    """
    Generates blog content based on a given topic and optional keywords using OpenAI.
    This is the first version and will be enhanced with more SEO features.
    """
    print(f"Received request to generate blog content for topic: {request.topic}")
    try:
        client = get_openai_client()
    except HTTPException as e: # Catch if API key is missing after get_openai_client tried
        raise e # Re-raise the HTTPException from get_openai_client
    except Exception as e: # Catch any other error during client initialization
        print(f"Error initializing OpenAI client: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Failed to initialize OpenAI client: {str(e)}")

    prompt_keywords = ""
    if request.keywords:
        prompt_keywords = f"Please focus on the following keywords: {', '.join(request.keywords)}."

    # Simple prompt, can be significantly improved
    # Consider user's app description: "professional seo optimizer who use blog content for imporve seo quality"
    # "create a cool , stunning and powerful mcp server website for connect with wordpress plugin"
    # "make a seo optimization , analytics and imporvement tsak"
    # "all seo task was smart more for support all task like a professional"
    
    system_prompt = (
        "You are an expert SEO content writer and professional blog post generator. "
        "Your goal is to create engaging, well-structured, and SEO-friendly blog posts. "
        "The content should be cool, stunning, and powerful, reflecting a professional tone suitable for SEO experts and businesses. "
        "Ensure the content is informative and provides real value to the reader."
    )
    
    user_prompt = (
        f"Please write a comprehensive blog post about the topic: '{request.topic}'.\n"
        f"{prompt_keywords}\n"
        "The blog post should be at least 500 words and include an introduction, several main body paragraphs with headings (use markdown for headings), and a conclusion.\n"
        "Structure the content logically. Make it highly readable and engaging."
    )

    try:
        print(f"Sending request to OpenAI for topic: {request.topic}")
        completion = client.chat.completions.create(
            model="gpt-4o-mini", # Cheaper and faster, good for initial development
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": user_prompt}
            ],
            temperature=0.7, # A bit creative but not too wild
            # max_tokens=1500, # Adjust as needed, keep in mind costs/latency
        )
        
        generated_content = completion.choices[0].message.content
        
        if not generated_content:
            print("OpenAI returned empty content.")
            raise HTTPException(status_code=500, detail="AI failed to generate content. The response was empty.")
            
        print(f"Successfully generated content for topic: {request.topic} (Length: {len(generated_content)})")
        return BlogContentResponse(generated_text=generated_content)

    except HTTPException as e: # Re-raise HTTPExceptions
        raise e
    except Exception as e:
        print(f"Error during OpenAI API call: {str(e)}")
        # Consider if a more specific error should be returned to the user
        raise HTTPException(status_code=500, detail=f"An error occurred while generating blog content with AI: {str(e)}")

