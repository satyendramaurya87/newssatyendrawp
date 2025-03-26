import os
import re
import json
import logging
import trafilatura
import feedparser
import requests
from urllib.parse import urlparse, urljoin
from bs4 import BeautifulSoup
from datetime import datetime

# Configure logging
logging.basicConfig(level=logging.DEBUG)
logger = logging.getLogger(__name__)

class WebScraper:
    """
    Scraper for extracting content from web pages.
    Uses trafilatura for main content extraction and BeautifulSoup for targeted extraction.
    """
    
    def __init__(self):
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        })
    
    def scrape(self, url, selectors=None, fetch_images=True, fetch_social_embeds=True):
        """
        Scrape content from a URL.
        
        Args:
            url (str): The URL to scrape
            selectors (dict): CSS selectors for specific elements (title, content, author, date)
            fetch_images (bool): Whether to fetch images
            fetch_social_embeds (bool): Whether to extract social media embeds
            
        Returns:
            dict: The scraped content
        """
        try:
            # Initialize default selectors if none provided
            if selectors is None or not selectors:
                selectors = {
                    'title': 'h1, .entry-title, .article-title',
                    'content': '.entry-content, article, .post-content',
                    'author': '.author, .byline',
                    'date': '.published, .post-date, time'
                }
            
            # Download the page
            response = self.session.get(url, timeout=30)
            response.raise_for_status()
            html = response.text
            
            # Create BeautifulSoup object
            soup = BeautifulSoup(html, 'html.parser')
            
            # Extract content based on the approach
            result = {}
            result['url'] = url
            
            # Try to extract with selectors first
            title = self._extract_with_selector(soup, selectors.get('title', ''))
            content = self._extract_with_selector(soup, selectors.get('content', ''))
            author = self._extract_with_selector(soup, selectors.get('author', ''))
            date = self._extract_with_selector(soup, selectors.get('date', ''))
            
            # If selectors didn't work well, fall back to trafilatura
            if not content or len(content) < 200:
                logger.debug("Selector extraction failed or returned limited content, falling back to trafilatura")
                extracted = trafilatura.extract(html, include_images=fetch_images, include_links=True, output_format='html')
                
                if extracted:
                    # Create a soup from the trafilatura output to extract title and content
                    traf_soup = BeautifulSoup(extracted, 'html.parser')
                    
                    # If we didn't get a title from selectors, try to get it from trafilatura or page title
                    if not title:
                        title = traf_soup.find('h1')
                        if title:
                            title = title.get_text().strip()
                        else:
                            # Last resort: use the page title
                            title = soup.title.string if soup.title else "No Title Found"
                    
                    # Use the trafilatura content
                    content = str(traf_soup)
            
            # Clean and add results
            result['title'] = title.strip() if title else "No Title Found"
            result['content'] = content if content else "No Content Found"
            result['author'] = author.strip() if author else ""
            result['date'] = date.strip() if date else ""
            
            # Extract images if requested
            if fetch_images:
                result['images'] = self._extract_images(soup, url)
            
            # Extract social media embeds if requested
            if fetch_social_embeds:
                result['social_embeds'] = self._extract_social_embeds(soup)
            
            return result
            
        except Exception as e:
            logger.exception(f"Error scraping URL {url}: {str(e)}")
            return {
                'error': f"Failed to scrape content: {str(e)}",
                'url': url
            }
    
    def _extract_with_selector(self, soup, selector):
        """
        Extract content using CSS selectors.
        
        Args:
            soup (BeautifulSoup): The BeautifulSoup object
            selector (str): CSS selector string
            
        Returns:
            str: The extracted content
        """
        if not selector:
            return ""
        
        # Split by comma and try each selector
        for sel in selector.split(','):
            sel = sel.strip()
            elements = soup.select(sel)
            
            if elements:
                # If it's likely a container for content, return its HTML
                if sel in ['.entry-content', '.article-content', 'article', '.post-content']:
                    return str(elements[0])
                # Otherwise return the text
                return elements[0].get_text().strip()
        
        return ""
    
    def _extract_images(self, soup, base_url):
        """
        Extract images from the page.
        
        Args:
            soup (BeautifulSoup): The BeautifulSoup object
            base_url (str): The base URL for resolving relative URLs
            
        Returns:
            list: List of image information (url, alt text, caption)
        """
        images = []
        for img in soup.find_all('img'):
            src = img.get('src')
            data_src = img.get('data-src')  # Some sites use data-src for lazy loading
            
            # Skip if no source found
            if not src and not data_src:
                continue
            
            # Use data-src if src is not available or is a placeholder
            img_url = src if src and not src.endswith(('placeholder.jpg', 'placeholder.png')) else data_src
            
            # Skip if still no valid URL
            if not img_url:
                continue
            
            # Make relative URLs absolute
            if not img_url.startswith(('http://', 'https://', 'data:')):
                img_url = urljoin(base_url, img_url)
            
            # Get alt text and potential caption
            alt_text = img.get('alt', '')
            
            # Look for a caption in a figcaption element or nearby paragraph
            caption = ""
            if img.parent and img.parent.name == 'figure':
                figcaption = img.parent.find('figcaption')
                if figcaption:
                    caption = figcaption.get_text().strip()
            
            images.append({
                'url': img_url,
                'alt': alt_text,
                'caption': caption
            })
        
        return images
    
    def _extract_social_embeds(self, soup):
        """
        Extract social media embeds (Twitter, Instagram, etc.).
        
        Args:
            soup (BeautifulSoup): The BeautifulSoup object
            
        Returns:
            list: List of social media embeds
        """
        embeds = []
        
        # Twitter embeds
        twitter_embeds = soup.select('.twitter-tweet, blockquote.twitter-tweet, [data-tweet-id]')
        for embed in twitter_embeds:
            embed_html = str(embed)
            embeds.append({
                'type': 'twitter',
                'html': embed_html
            })
        
        # Instagram embeds
        instagram_embeds = soup.select('.instagram-media, blockquote.instagram-media, [data-instgrm-permalink]')
        for embed in instagram_embeds:
            embed_html = str(embed)
            embeds.append({
                'type': 'instagram',
                'html': embed_html
            })
        
        # YouTube embeds
        youtube_embeds = soup.select('iframe[src*="youtube.com"], iframe[src*="youtu.be"]')
        for embed in youtube_embeds:
            embed_html = str(embed)
            embeds.append({
                'type': 'youtube',
                'html': embed_html
            })
        
        return embeds


class RssScraper:
    """
    Scraper for extracting content from RSS feeds.
    """
    
    def __init__(self):
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        })
    
    def scrape_feed(self, feed_url, limit=10, keywords=''):
        """
        Scrape articles from an RSS feed.
        
        Args:
            feed_url (str): The RSS feed URL
            limit (int): Maximum number of articles to fetch
            keywords (str): Keywords to filter by (comma-separated)
            
        Returns:
            dict: The scraped feed data and items
        """
        try:
            # Parse the feed
            feed = feedparser.parse(feed_url)
            
            # Check if parsing was successful
            if not feed or not feed.entries:
                return {
                    'error': 'Failed to parse feed or no entries found',
                    'feed_url': feed_url
                }
            
            # Extract feed information
            feed_info = {
                'title': feed.feed.get('title', ''),
                'description': feed.feed.get('description', ''),
                'link': feed.feed.get('link', ''),
                'updated': feed.feed.get('updated', '')
            }
            
            # Process feed entries
            items = []
            keyword_list = [k.strip().lower() for k in keywords.split(',') if k.strip()]
            
            for entry in feed.entries[:limit]:
                # Filter by keywords if provided
                if keyword_list:
                    title = entry.get('title', '').lower()
                    description = entry.get('description', '').lower()
                    
                    # Skip if no keywords match
                    if not any(keyword in title or keyword in description for keyword in keyword_list):
                        continue
                
                # Extract entry information
                item = {
                    'title': entry.get('title', ''),
                    'link': entry.get('link', ''),
                    'description': entry.get('description', ''),
                    'published': entry.get('published', ''),
                    'author': entry.get('author', '')
                }
                
                # Try to extract the publish date in a consistent format
                published_date = entry.get('published_parsed') or entry.get('updated_parsed')
                if published_date:
                    try:
                        item['date'] = datetime(*published_date[:6]).strftime('%Y-%m-%d %H:%M:%S')
                    except Exception:
                        item['date'] = ''
                
                # Extract media content if available
                if 'media_content' in entry:
                    media_urls = [m.get('url', '') for m in entry.media_content if 'url' in m]
                    item['media'] = media_urls
                
                items.append(item)
            
            return {
                'feed': feed_info,
                'items': items
            }
            
        except Exception as e:
            logger.exception(f"Error scraping RSS feed {feed_url}: {str(e)}")
            return {
                'error': f"Failed to scrape feed: {str(e)}",
                'feed_url': feed_url
            }
    
    def test_feed(self, feed_url, limit=5):
        """
        Test an RSS feed by fetching a few items.
        
        Args:
            feed_url (str): The RSS feed URL
            limit (int): Maximum number of items to fetch
            
        Returns:
            dict: Feed information and items
        """
        # Simply call scrape_feed with a small limit
        return self.scrape_feed(feed_url, limit)
