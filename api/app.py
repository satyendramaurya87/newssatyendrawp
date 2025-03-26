import os
from flask import Flask, request, jsonify
from flask_cors import CORS
import logging

# Configure logging
logging.basicConfig(level=logging.DEBUG, 
                    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

# Import our modules
from scraper import WebScraper, RssScraper
from ai_models import (
    OpenAIGenerator, 
    GeminiGenerator, 
    ClaudeGenerator, 
    DeepSeekGenerator, 
    DallEImageGenerator
)

# Create Flask app
app = Flask(__name__)
CORS(app)  # Enable CORS for all routes

# Initialize components
web_scraper = WebScraper()
rss_scraper = RssScraper()

# Initialize AI generators
ai_generators = {
    'openai': OpenAIGenerator(),
    'gemini': GeminiGenerator(),
    'claude': ClaudeGenerator(),
    'deepseek': DeepSeekGenerator()
}

# Initialize Image generator
image_generator = DallEImageGenerator()

@app.route('/status', methods=['GET'])
def status():
    """Health check endpoint"""
    return jsonify({
        'status': 'ok',
        'message': 'API is running'
    })

@app.route('/scrape', methods=['POST'])
def scrape_article():
    """Scrape an article from a URL"""
    try:
        data = request.get_json()
        
        if not data or 'url' not in data:
            return jsonify({'error': 'URL is required'}), 400
        
        url = data['url']
        selectors = data.get('selectors', {})
        fetch_images = data.get('fetch_images', True)
        fetch_social_embeds = data.get('fetch_social_embeds', True)
        
        logger.debug(f"Scraping URL: {url}")
        
        result = web_scraper.scrape(
            url, 
            selectors, 
            fetch_images=fetch_images, 
            fetch_social_embeds=fetch_social_embeds
        )
        
        return jsonify(result)
    
    except Exception as e:
        logger.exception("Error in scrape_article endpoint")
        return jsonify({'error': str(e)}), 500

@app.route('/scrape/rss', methods=['POST'])
def scrape_rss():
    """Scrape articles from an RSS feed"""
    try:
        data = request.get_json()
        
        if not data or 'feed_url' not in data:
            return jsonify({'error': 'Feed URL is required'}), 400
        
        feed_url = data['feed_url']
        limit = data.get('limit', 10)
        keywords = data.get('keywords', '')
        
        logger.debug(f"Scraping RSS feed: {feed_url}")
        
        result = rss_scraper.scrape_feed(feed_url, limit, keywords)
        
        return jsonify(result)
    
    except Exception as e:
        logger.exception("Error in scrape_rss endpoint")
        return jsonify({'error': str(e)}), 500

@app.route('/scrape/rss/test', methods=['POST'])
def test_rss_feed():
    """Test an RSS feed by fetching a few items"""
    try:
        data = request.get_json()
        
        if not data or 'feed_url' not in data:
            return jsonify({'error': 'Feed URL is required'}), 400
        
        feed_url = data['feed_url']
        limit = data.get('limit', 5)
        
        logger.debug(f"Testing RSS feed: {feed_url}")
        
        result = rss_scraper.test_feed(feed_url, limit)
        
        return jsonify(result)
    
    except Exception as e:
        logger.exception("Error in test_rss_feed endpoint")
        return jsonify({'error': str(e)}), 500

@app.route('/ai/generate', methods=['POST'])
def generate_content():
    """Generate AI content from scraped content"""
    try:
        data = request.get_json()
        
        required_fields = ['content', 'title', 'model', 'api_key']
        missing_fields = [field for field in required_fields if field not in data]
        
        if missing_fields:
            return jsonify({'error': f'Missing required fields: {", ".join(missing_fields)}'}), 400
        
        content = data['content']
        title = data['title']
        model = data['model']
        api_key = data['api_key']
        language = data.get('language', 'english')
        tone = data.get('tone', 'default')
        
        # Additional parameters
        params = {
            'min_word_count': data.get('min_word_count', 500),
            'keyword_density': data.get('keyword_density', 2.5),
            'auto_headings': data.get('auto_headings', True),
            'use_lists': data.get('use_lists', True),
            'add_faq': data.get('add_faq', True),
            'add_conclusion': data.get('add_conclusion', True),
        }
        
        logger.debug(f"Generating content using {model} model")
        
        # Select the appropriate AI generator
        if model not in ai_generators:
            return jsonify({'error': f'Unknown AI model: {model}'}), 400
        
        generator = ai_generators[model]
        generator.set_api_key(api_key)
        
        result = generator.generate_content(content, title, language, tone, **params)
        
        return jsonify(result)
    
    except Exception as e:
        logger.exception("Error in generate_content endpoint")
        return jsonify({'error': str(e)}), 500

@app.route('/ai/generate_image', methods=['POST'])
def generate_image():
    """Generate an image using AI"""
    try:
        data = request.get_json()
        
        required_fields = ['prompt', 'api_key']
        missing_fields = [field for field in required_fields if field not in data]
        
        if missing_fields:
            return jsonify({'error': f'Missing required fields: {", ".join(missing_fields)}'}), 400
        
        prompt = data['prompt']
        api_key = data['api_key']
        model = data.get('model', 'dall-e')  # Default to DALL-E
        style = data.get('style', 'digital-art')
        
        logger.debug(f"Generating image using {model} model")
        
        # Currently only support DALL-E
        if model.lower() != 'dall-e':
            return jsonify({'error': f'Unsupported image model: {model}. Only DALL-E is currently supported.'}), 400
        
        image_generator.set_api_key(api_key)
        result = image_generator.generate_image(prompt, style)
        
        return jsonify(result)
    
    except Exception as e:
        logger.exception("Error in generate_image endpoint")
        return jsonify({'error': str(e)}), 500

@app.route('/ai/generate_seo', methods=['POST'])
def generate_seo():
    """Generate SEO data for an article"""
    try:
        data = request.get_json()
        
        required_fields = ['content', 'title', 'model', 'api_key']
        missing_fields = [field for field in required_fields if field not in data]
        
        if missing_fields:
            return jsonify({'error': f'Missing required fields: {", ".join(missing_fields)}'}), 400
        
        content = data['content']
        title = data['title']
        model = data['model']
        api_key = data['api_key']
        
        logger.debug(f"Generating SEO data using {model} model")
        
        # Select the appropriate AI generator
        if model not in ai_generators:
            return jsonify({'error': f'Unknown AI model: {model}'}), 400
        
        generator = ai_generators[model]
        generator.set_api_key(api_key)
        
        result = generator.generate_seo_data(content, title)
        
        return jsonify(result)
    
    except Exception as e:
        logger.exception("Error in generate_seo endpoint")
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
