import os
import json
import logging
import re
import requests
from typing import Dict, List, Any, Optional

# Configure logging
logging.basicConfig(level=logging.DEBUG)
logger = logging.getLogger(__name__)

class BaseAIGenerator:
    """Base class for AI content generators."""
    
    def __init__(self):
        self.api_key = None
    
    def set_api_key(self, api_key):
        """Set the API key for the AI service."""
        self.api_key = api_key
    
    def generate_content(self, content, title, language='english', tone='default', **kwargs):
        """
        Generate AI content from scraped content.
        
        Args:
            content (str): The original content
            title (str): The title of the article
            language (str): The language to generate content in
            tone (str): The writing tone/style
            **kwargs: Additional parameters
            
        Returns:
            dict: The generated content
        """
        raise NotImplementedError("Subclasses must implement this method")
    
    def generate_seo_data(self, content, title):
        """
        Generate SEO data for an article.
        
        Args:
            content (str): The article content
            title (str): The article title
            
        Returns:
            dict: The generated SEO data
        """
        raise NotImplementedError("Subclasses must implement this method")
    
    def _create_prompt(self, content, title, language, tone, **kwargs):
        """
        Create a prompt for the AI model.
        
        Args:
            content (str): The original content
            title (str): The title of the article
            language (str): The language to generate content in
            tone (str): The writing tone/style
            **kwargs: Additional parameters
            
        Returns:
            str: The formatted prompt
        """
        min_word_count = kwargs.get('min_word_count', 500)
        keyword_density = kwargs.get('keyword_density', 2.5)
        auto_headings = kwargs.get('auto_headings', True)
        use_lists = kwargs.get('use_lists', True)
        add_faq = kwargs.get('add_faq', True)
        add_conclusion = kwargs.get('add_conclusion', True)
        
        # Determine tone instruction based on selected tone
        tone_instruction = ""
        if tone == 'banarasi':
            tone_instruction = "Write in a Banarasi dialect style, using casual expressions typical of Varanasi region."
        elif tone == 'lucknow':
            tone_instruction = "Write in a Lucknowi Nawabi style, using elegant and polite expressions typical of Lucknow."
        elif tone == 'delhi':
            tone_instruction = "Write in a Delhi style, using direct and modern expressions typical of Delhi."
        elif tone == 'indore':
            tone_instruction = "Write in an Indori style, using casual expressions typical of Indore region."
        else:
            tone_instruction = "Write in a professional, journalistic tone."
        
        # Determine language instruction
        lang_instruction = "in English" if language.lower() == 'english' else "in Hindi"
        
        # Create the prompt
        prompt = f"""You are an expert content writer tasked with rewriting a news article {lang_instruction}. 
Original title: "{title}"

I'll give you the content from a news article, and your task is to:

1. Rewrite the content completely to make it 100% unique and plagiarism-free.
2. Improve the title to be more engaging while keeping the main topic.
3. Write at least {min_word_count} words, which should be about 50 words more than the original content.
4. {tone_instruction}
5. Maintain a keyword density of approximately {keyword_density}% for important terms.
"""

        if auto_headings:
            prompt += "6. Add appropriate H2 and H3 headings to structure the content well.\n"
        
        if use_lists:
            prompt += "7. Use bullet points or numbered lists where appropriate to improve readability.\n"
        
        if add_faq:
            prompt += "8. Include a FAQ section at the end with 3-5 relevant questions and answers.\n"
        
        if add_conclusion:
            prompt += "9. Add a conclusion section at the end summarizing the main points.\n"
        
        prompt += "\nHere's the original content to rewrite:\n\n"
        prompt += content
        
        prompt += "\n\nProvide the response in HTML format with proper heading tags, paragraph tags, and formatting."
        
        return prompt
    
    def _create_seo_prompt(self, content, title):
        """
        Create a prompt for generating SEO data.
        
        Args:
            content (str): The article content
            title (str): The article title
            
        Returns:
            str: The formatted prompt
        """
        prompt = f"""As an SEO expert, analyze the following article and provide:

1. An SEO-optimized title (max 60 characters)
2. A list of 5-8 relevant tags for the article
3. 2-3 recommended categories for the article
4. A meta description (max 160 characters)

Article Title: {title}

Article Content:
{content[:1000]}...

Return your analysis as a JSON object with the following structure:
{{
  "seo_title": "Your optimized title here",
  "tags": ["tag1", "tag2", "tag3", "tag4", "tag5"],
  "categories": ["category1", "category2"],
  "meta_description": "Your meta description here"
}}
"""
        return prompt


class OpenAIGenerator(BaseAIGenerator):
    """Generate content using OpenAI's API."""
    
    def __init__(self):
        super().__init__()
        self.api_url = "https://api.openai.com/v1/chat/completions"
    
    def generate_content(self, content, title, language='english', tone='default', **kwargs):
        """Generate content using OpenAI."""
        if not self.api_key:
            return {"error": "OpenAI API key is required"}
        
        try:
            prompt = self._create_prompt(content, title, language, tone, **kwargs)
            
            headers = {
                "Authorization": f"Bearer {self.api_key}",
                "Content-Type": "application/json"
            }
            
            data = {
                "model": "gpt-4o",  # the newest OpenAI model is "gpt-4o" which was released May 13, 2024.
                "messages": [
                    {"role": "system", "content": "You are an expert content writer that specializes in rewriting news articles."},
                    {"role": "user", "content": prompt}
                ],
                "temperature": 0.7,
                "max_tokens": 4000
            }
            
            response = requests.post(self.api_url, headers=headers, json=data)
            response.raise_for_status()
            
            result = response.json()
            generated_content = result["choices"][0]["message"]["content"]
            
            # Extract title from the generated content
            title_match = re.search(r"<h1[^>]*>(.*?)<\/h1>", generated_content, re.DOTALL)
            if title_match:
                generated_title = title_match.group(1).strip()
            else:
                # Try to find the first line as title
                lines = generated_content.split('\n')
                for line in lines:
                    if line.strip() and not line.startswith("<"):
                        generated_title = line.strip()
                        break
                else:
                    generated_title = title
            
            return {
                "title": generated_title,
                "content": generated_content,
                "word_count": len(generated_content.split()),
                "suggested_tags": self._extract_keywords(generated_content)
            }
            
        except Exception as e:
            logger.exception(f"Error generating content with OpenAI: {str(e)}")
            return {"error": f"Failed to generate content: {str(e)}"}
    
    def generate_seo_data(self, content, title):
        """Generate SEO data using OpenAI."""
        if not self.api_key:
            return {"error": "OpenAI API key is required"}
        
        try:
            prompt = self._create_seo_prompt(content, title)
            
            headers = {
                "Authorization": f"Bearer {self.api_key}",
                "Content-Type": "application/json"
            }
            
            data = {
                "model": "gpt-4o",  # the newest OpenAI model is "gpt-4o" which was released May 13, 2024.
                "messages": [
                    {"role": "system", "content": "You are an SEO expert providing analysis for web content."},
                    {"role": "user", "content": prompt}
                ],
                "response_format": {"type": "json_object"},
                "temperature": 0.3,
                "max_tokens": 1000
            }
            
            response = requests.post(self.api_url, headers=headers, json=data)
            response.raise_for_status()
            
            result = response.json()
            seo_data = json.loads(result["choices"][0]["message"]["content"])
            
            return seo_data
            
        except Exception as e:
            logger.exception(f"Error generating SEO data with OpenAI: {str(e)}")
            return {"error": f"Failed to generate SEO data: {str(e)}"}
    
    def _extract_keywords(self, content):
        """Extract potential keywords/tags from content."""
        if not self.api_key:
            return []
        
        try:
            headers = {
                "Authorization": f"Bearer {self.api_key}",
                "Content-Type": "application/json"
            }
            
            data = {
                "model": "gpt-4o",  # the newest OpenAI model is "gpt-4o" which was released May 13, 2024.
                "messages": [
                    {"role": "system", "content": "You are a keyword extraction expert."},
                    {"role": "user", "content": f"Extract 5-7 relevant tags/keywords from this text, return only a JSON array of strings:\n\n{content[:1500]}"}
                ],
                "response_format": {"type": "json_object"},
                "temperature": 0.3,
                "max_tokens": 200
            }
            
            response = requests.post(self.api_url, headers=headers, json=data)
            response.raise_for_status()
            
            result = response.json()
            keywords_data = json.loads(result["choices"][0]["message"]["content"])
            
            if isinstance(keywords_data, dict) and "tags" in keywords_data:
                return keywords_data["tags"]
            elif isinstance(keywords_data, list):
                return keywords_data
            
            return []
            
        except Exception as e:
            logger.exception(f"Error extracting keywords with OpenAI: {str(e)}")
            return []


class GeminiGenerator(BaseAIGenerator):
    """Generate content using Google's Gemini API."""
    
    def __init__(self):
        super().__init__()
        self.api_url = "https://generativelanguage.googleapis.com/v1"
    
    def generate_content(self, content, title, language='english', tone='default', **kwargs):
        """Generate content using Gemini."""
        if not self.api_key:
            return {"error": "Gemini API key is required"}
        
        try:
            prompt = self._create_prompt(content, title, language, tone, **kwargs)
            
            url = f"{self.api_url}/models/gemini-pro:generateContent?key={self.api_key}"
            
            data = {
                "contents": [
                    {
                        "role": "user",
                        "parts": [{"text": prompt}]
                    }
                ],
                "generationConfig": {
                    "temperature": 0.7,
                    "maxOutputTokens": 4000
                }
            }
            
            response = requests.post(url, json=data)
            response.raise_for_status()
            
            result = response.json()
            generated_content = result["candidates"][0]["content"]["parts"][0]["text"]
            
            # Extract title from the generated content
            title_match = re.search(r"<h1[^>]*>(.*?)<\/h1>", generated_content, re.DOTALL)
            if title_match:
                generated_title = title_match.group(1).strip()
            else:
                # Try to find the first line as title
                lines = generated_content.split('\n')
                for line in lines:
                    if line.strip() and not line.startswith("<"):
                        generated_title = line.strip()
                        break
                else:
                    generated_title = title
            
            return {
                "title": generated_title,
                "content": generated_content,
                "word_count": len(generated_content.split()),
                "suggested_tags": self._extract_keywords(generated_content)
            }
            
        except Exception as e:
            logger.exception(f"Error generating content with Gemini: {str(e)}")
            return {"error": f"Failed to generate content: {str(e)}"}
    
    def generate_seo_data(self, content, title):
        """Generate SEO data using Gemini."""
        if not self.api_key:
            return {"error": "Gemini API key is required"}
        
        try:
            prompt = self._create_seo_prompt(content, title)
            
            url = f"{self.api_url}/models/gemini-pro:generateContent?key={self.api_key}"
            
            data = {
                "contents": [
                    {
                        "role": "user",
                        "parts": [{"text": prompt}]
                    }
                ],
                "generationConfig": {
                    "temperature": 0.3,
                    "maxOutputTokens": 1000
                }
            }
            
            response = requests.post(url, json=data)
            response.raise_for_status()
            
            result = response.json()
            generated_text = result["candidates"][0]["content"]["parts"][0]["text"]
            
            # Extract JSON from the response (Gemini might wrap the JSON in ```json blocks)
            json_match = re.search(r"```json\s*([\s\S]*?)\s*```", generated_text)
            if json_match:
                json_str = json_match.group(1)
            else:
                json_str = generated_text
            
            # Clean up the string and parse JSON
            json_str = json_str.strip()
            seo_data = json.loads(json_str)
            
            return seo_data
            
        except Exception as e:
            logger.exception(f"Error generating SEO data with Gemini: {str(e)}")
            return {"error": f"Failed to generate SEO data: {str(e)}"}
    
    def _extract_keywords(self, content):
        """Extract potential keywords/tags from content."""
        if not self.api_key:
            return []
        
        try:
            url = f"{self.api_url}/models/gemini-pro:generateContent?key={self.api_key}"
            
            data = {
                "contents": [
                    {
                        "role": "user",
                        "parts": [{"text": f"Extract 5-7 relevant tags/keywords from this text, return only a JSON array of strings:\n\n{content[:1500]}"}]
                    }
                ],
                "generationConfig": {
                    "temperature": 0.3,
                    "maxOutputTokens": 200
                }
            }
            
            response = requests.post(url, json=data)
            response.raise_for_status()
            
            result = response.json()
            generated_text = result["candidates"][0]["content"]["parts"][0]["text"]
            
            # Extract JSON from the response
            json_match = re.search(r"```json\s*([\s\S]*?)\s*```", generated_text)
            if json_match:
                json_str = json_match.group(1)
            else:
                json_str = generated_text
            
            # Clean up the string and parse JSON
            json_str = json_str.strip()
            try:
                keywords_data = json.loads(json_str)
                if isinstance(keywords_data, list):
                    return keywords_data
                elif isinstance(keywords_data, dict) and "tags" in keywords_data:
                    return keywords_data["tags"]
            except:
                # If JSON parsing fails, try to extract keywords using regex
                keywords = re.findall(r'"([^"]+)"', generated_text)
                if keywords:
                    return keywords[:7]
            
            return []
            
        except Exception as e:
            logger.exception(f"Error extracting keywords with Gemini: {str(e)}")
            return []


class ClaudeGenerator(BaseAIGenerator):
    """Generate content using Anthropic's Claude API."""
    
    def __init__(self):
        super().__init__()
        self.api_url = "https://api.anthropic.com/v1/messages"
    
    def generate_content(self, content, title, language='english', tone='default', **kwargs):
        """Generate content using Claude."""
        if not self.api_key:
            return {"error": "Claude API key is required"}
        
        try:
            prompt = self._create_prompt(content, title, language, tone, **kwargs)
            
            headers = {
                "x-api-key": self.api_key,
                "anthropic-version": "2023-06-01",
                "content-type": "application/json"
            }
            
            data = {
                "model": "claude-3-opus-20240229",
                "max_tokens": 4000,
                "temperature": 0.7,
                "messages": [
                    {"role": "user", "content": prompt}
                ]
            }
            
            response = requests.post(self.api_url, headers=headers, json=data)
            response.raise_for_status()
            
            result = response.json()
            generated_content = result["content"][0]["text"]
            
            # Extract title from the generated content
            title_match = re.search(r"<h1[^>]*>(.*?)<\/h1>", generated_content, re.DOTALL)
            if title_match:
                generated_title = title_match.group(1).strip()
            else:
                # Try to find the first line as title
                lines = generated_content.split('\n')
                for line in lines:
                    if line.strip() and not line.startswith("<"):
                        generated_title = line.strip()
                        break
                else:
                    generated_title = title
            
            return {
                "title": generated_title,
                "content": generated_content,
                "word_count": len(generated_content.split()),
                "suggested_tags": self._extract_keywords(generated_content)
            }
            
        except Exception as e:
            logger.exception(f"Error generating content with Claude: {str(e)}")
            return {"error": f"Failed to generate content: {str(e)}"}
    
    def generate_seo_data(self, content, title):
        """Generate SEO data using Claude."""
        if not self.api_key:
            return {"error": "Claude API key is required"}
        
        try:
            prompt = self._create_seo_prompt(content, title)
            
            headers = {
                "x-api-key": self.api_key,
                "anthropic-version": "2023-06-01",
                "content-type": "application/json"
            }
            
            data = {
                "model": "claude-3-opus-20240229",
                "max_tokens": 1000,
                "temperature": 0.3,
                "messages": [
                    {"role": "user", "content": prompt}
                ]
            }
            
            response = requests.post(self.api_url, headers=headers, json=data)
            response.raise_for_status()
            
            result = response.json()
            generated_text = result["content"][0]["text"]
            
            # Extract JSON from the response
            json_match = re.search(r"```json\s*([\s\S]*?)\s*```", generated_text)
            if json_match:
                json_str = json_match.group(1)
            else:
                json_str = generated_text
            
            # Clean up the string and parse JSON
            json_str = json_str.strip()
            seo_data = json.loads(json_str)
            
            return seo_data
            
        except Exception as e:
            logger.exception(f"Error generating SEO data with Claude: {str(e)}")
            return {"error": f"Failed to generate SEO data: {str(e)}"}
    
    def _extract_keywords(self, content):
        """Extract potential keywords/tags from content."""
        if not self.api_key:
            return []
        
        try:
            headers = {
                "x-api-key": self.api_key,
                "anthropic-version": "2023-06-01",
                "content-type": "application/json"
            }
            
            data = {
                "model": "claude-3-haiku-20240307",
                "max_tokens": 200,
                "temperature": 0.3,
                "messages": [
                    {"role": "user", "content": f"Extract 5-7 relevant tags/keywords from this text, return only a JSON array of strings:\n\n{content[:1500]}"}
                ]
            }
            
            response = requests.post(self.api_url, headers=headers, json=data)
            response.raise_for_status()
            
            result = response.json()
            generated_text = result["content"][0]["text"]
            
            # Extract JSON from the response
            json_match = re.search(r"```json\s*([\s\S]*?)\s*```", generated_text)
            if json_match:
                json_str = json_match.group(1)
            else:
                json_str = generated_text
            
            # Clean up the string and parse JSON
            json_str = json_str.strip()
            try:
                keywords_data = json.loads(json_str)
                if isinstance(keywords_data, list):
                    return keywords_data
                elif isinstance(keywords_data, dict) and "tags" in keywords_data:
                    return keywords_data["tags"]
            except:
                # If JSON parsing fails, try to extract keywords using regex
                keywords = re.findall(r'"([^"]+)"', generated_text)
                if keywords:
                    return keywords[:7]
            
            return []
            
        except Exception as e:
            logger.exception(f"Error extracting keywords with Claude: {str(e)}")
            return []


class DeepSeekGenerator(BaseAIGenerator):
    """Generate content using DeepSeek AI API."""
    
    def __init__(self):
        super().__init__()
        self.api_url = "https://api.deepseek.com/v1/chat/completions"
    
    def generate_content(self, content, title, language='english', tone='default', **kwargs):
        """Generate content using DeepSeek."""
        if not self.api_key:
            return {"error": "DeepSeek API key is required"}
        
        try:
            prompt = self._create_prompt(content, title, language, tone, **kwargs)
            
            headers = {
                "Authorization": f"Bearer {self.api_key}",
                "Content-Type": "application/json"
            }
            
            data = {
                "model": "deepseek-chat",
                "messages": [
                    {"role": "system", "content": "You are an expert content writer that specializes in rewriting news articles."},
                    {"role": "user", "content": prompt}
                ],
                "temperature": 0.7,
                "max_tokens": 4000
            }
            
            response = requests.post(self.api_url, headers=headers, json=data)
            response.raise_for_status()
            
            result = response.json()
            generated_content = result["choices"][0]["message"]["content"]
            
            # Extract title from the generated content
            title_match = re.search(r"<h1[^>]*>(.*?)<\/h1>", generated_content, re.DOTALL)
            if title_match:
                generated_title = title_match.group(1).strip()
            else:
                # Try to find the first line as title
                lines = generated_content.split('\n')
                for line in lines:
                    if line.strip() and not line.startswith("<"):
                        generated_title = line.strip()
                        break
                else:
                    generated_title = title
            
            return {
                "title": generated_title,
                "content": generated_content,
                "word_count": len(generated_content.split()),
                "suggested_tags": self._extract_keywords(generated_content)
            }
            
        except Exception as e:
            logger.exception(f"Error generating content with DeepSeek: {str(e)}")
            return {"error": f"Failed to generate content: {str(e)}"}
    
    def generate_seo_data(self, content, title):
        """Generate SEO data using DeepSeek."""
        if not self.api_key:
            return {"error": "DeepSeek API key is required"}
        
        try:
            prompt = self._create_seo_prompt(content, title)
            
            headers = {
                "Authorization": f"Bearer {self.api_key}",
                "Content-Type": "application/json"
            }
            
            data = {
                "model": "deepseek-chat",
                "messages": [
                    {"role": "system", "content": "You are an SEO expert providing analysis for web content."},
                    {"role": "user", "content": prompt}
                ],
                "temperature": 0.3,
                "max_tokens": 1000
            }
            
            response = requests.post(self.api_url, headers=headers, json=data)
            response.raise_for_status()
            
            result = response.json()
            generated_text = result["choices"][0]["message"]["content"]
            
            # Extract JSON from the response
            json_match = re.search(r"```json\s*([\s\S]*?)\s*```", generated_text)
            if json_match:
                json_str = json_match.group(1)
            else:
                json_str = generated_text
            
            # Clean up the string and parse JSON
            json_str = json_str.strip()
            seo_data = json.loads(json_str)
            
            return seo_data
            
        except Exception as e:
            logger.exception(f"Error generating SEO data with DeepSeek: {str(e)}")
            return {"error": f"Failed to generate SEO data: {str(e)}"}
    
    def _extract_keywords(self, content):
        """Extract potential keywords/tags from content."""
        if not self.api_key:
            return []
        
        try:
            headers = {
                "Authorization": f"Bearer {self.api_key}",
                "Content-Type": "application/json"
            }
            
            data = {
                "model": "deepseek-chat",
                "messages": [
                    {"role": "system", "content": "You are a keyword extraction expert."},
                    {"role": "user", "content": f"Extract 5-7 relevant tags/keywords from this text, return only a JSON array of strings:\n\n{content[:1500]}"}
                ],
                "temperature": 0.3,
                "max_tokens": 200
            }
            
            response = requests.post(self.api_url, headers=headers, json=data)
            response.raise_for_status()
            
            result = response.json()
            generated_text = result["choices"][0]["message"]["content"]
            
            # Extract JSON from the response
            json_match = re.search(r"```json\s*([\s\S]*?)\s*```", generated_text)
            if json_match:
                json_str = json_match.group(1)
            else:
                json_str = generated_text
            
            # Clean up the string and parse JSON
            json_str = json_str.strip()
            try:
                keywords_data = json.loads(json_str)
                if isinstance(keywords_data, list):
                    return keywords_data
                elif isinstance(keywords_data, dict) and "tags" in keywords_data:
                    return keywords_data["tags"]
            except:
                # If JSON parsing fails, try to extract keywords using regex
                keywords = re.findall(r'"([^"]+)"', generated_text)
                if keywords:
                    return keywords[:7]
            
            return []
            
        except Exception as e:
            logger.exception(f"Error extracting keywords with DeepSeek: {str(e)}")
            return []


class DallEImageGenerator:
    """Generate images using OpenAI's DALL-E API."""
    
    def __init__(self):
        self.api_key = None
        self.api_url = "https://api.openai.com/v1/images/generations"
    
    def set_api_key(self, api_key):
        """Set the API key for the DALL-E service."""
        self.api_key = api_key
    
    def generate_image(self, prompt, style="digital-art"):
        """
        Generate an image using DALL-E.
        
        Args:
            prompt (str): The prompt to generate an image from
            style (str): The style of the image to generate
            
        Returns:
            dict: The generated image URL
        """
        if not self.api_key:
            return {"error": "OpenAI API key is required"}
        
        try:
            # Enhance the prompt with style instructions
            enhanced_prompt = self._enhance_prompt(prompt, style)
            
            headers = {
                "Authorization": f"Bearer {self.api_key}",
                "Content-Type": "application/json"
            }
            
            data = {
                "model": "dall-e-3",
                "prompt": enhanced_prompt,
                "n": 1,
                "size": "1024x1024",
                "quality": "standard"
            }
            
            response = requests.post(self.api_url, headers=headers, json=data)
            response.raise_for_status()
            
            result = response.json()
            image_url = result["data"][0]["url"]
            
            return {
                "url": image_url,
                "prompt": enhanced_prompt
            }
            
        except Exception as e:
            logger.exception(f"Error generating image with DALL-E: {str(e)}")
            return {"error": f"Failed to generate image: {str(e)}"}
    
    def _enhance_prompt(self, prompt, style):
        """
        Enhance the prompt with style instructions.
        
        Args:
            prompt (str): The original prompt
            style (str): The desired style
            
        Returns:
            str: The enhanced prompt
        """
        style_instructions = {
            "realistic": "Create a photorealistic image with natural lighting and realistic details",
            "digital-art": "Create a digital art illustration with vibrant colors and clean lines",
            "cartoon": "Create a cartoon-style illustration with bold outlines and simplified shapes",
            "3d-render": "Create a 3D rendered image with depth, lighting, and realistic textures",
            "sketch": "Create a hand-drawn sketch with pencil lines and shading",
            "watercolor": "Create a watercolor painting with soft edges and translucent colors",
            "oil-painting": "Create an oil painting with rich textures and visible brushstrokes",
            "minimalist": "Create a minimalist design with simple shapes and limited color palette"
        }
        
        style_instruction = style_instructions.get(style, "Create a high-quality image")
        
        # Limit prompt length to avoid token limits
        max_prompt_length = 800
        trimmed_prompt = prompt[:max_prompt_length] if len(prompt) > max_prompt_length else prompt
        
        enhanced_prompt = f"{style_instruction} of {trimmed_prompt}. Make it suitable as a featured image for a news article or blog post."
        
        return enhanced_prompt
