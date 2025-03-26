<?php
/**
 * The scraper functionality of the plugin.
 *
 * @link       https://satyendramaurya.site
 * @since      1.0.0
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/includes
 */

/**
 * The scraper functionality of the plugin.
 *
 * Defines the methods for scraping content from websites
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/includes
 * @author     Satyendra Maurya <contact@satyendramaurya.site>
 */
class AI_News_Scraper_Auto_Blogger_Pro_Scraper {

    /**
     * API endpoint for the Python Flask API.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_url    The API endpoint.
     */
    private $api_url;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->api_url = AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_API_URL;
    }

    /**
     * Scrape an article from the given URL.
     *
     * @since    1.0.0
     * @param    string    $url    The URL to scrape.
     * @param    array     $selectors    Optional. Custom CSS selectors to use.
     * @return   array|WP_Error   The scraped content or error.
     */
    public function scrape_article($url, $selectors = array()) {
        // Check if URL is valid
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_url', 'The provided URL is not valid.');
        }

        // Get options
        $options = get_option('ai_news_scraper_options', array());
        $scraper_settings = isset($options['scraper_settings']) ? $options['scraper_settings'] : array();

        // If no custom selectors provided, use default ones or saved ones for this domain
        if (empty($selectors)) {
            $saved_selectors = $this->get_saved_selectors_for_domain(parse_url($url, PHP_URL_HOST));
            if (!empty($saved_selectors)) {
                $selectors = $saved_selectors;
            } else {
                // Use default selectors
                $selectors = array(
                    'title' => 'h1, .entry-title, .article-title',
                    'content' => '.entry-content, article, .post-content',
                    'author' => '.author, .byline',
                    'date' => '.published, .post-date, time',
                );
            }
        }

        // Prepare data for API request
        $api_data = array(
            'url' => $url,
            'selectors' => $selectors,
            'fetch_images' => true,
            'fetch_social_embeds' => isset($scraper_settings['social_media_embed']) ? $scraper_settings['social_media_embed'] : true,
        );

        // Make API request
        $response = $this->make_api_request('/scrape', 'POST', $api_data);

        if (is_wp_error($response)) {
            $this->log_error('scrape', $url, null, $response->get_error_message());
            return $response;
        }

        // Process images if needed (download to media library, etc.)
        if (!empty($response['images'])) {
            $response['images'] = $this->process_scraped_images($response['images'], $url);
        }

        // Save successful selectors for this domain
        if (isset($scraper_settings['auto_scraping_enabled']) && $scraper_settings['auto_scraping_enabled']) {
            $this->save_selectors_for_domain(parse_url($url, PHP_URL_HOST), $selectors);
        }

        return $response;
    }

    /**
     * Get saved selectors for a specific domain.
     *
     * @since    1.0.0
     * @param    string    $domain    The domain to get selectors for.
     * @return   array     The saved selectors or empty array if none found.
     */
    private function get_saved_selectors_for_domain($domain) {
        $saved_domains = get_option('ai_news_scraper_saved_domains', array());
        
        if (isset($saved_domains[$domain])) {
            return $saved_domains[$domain]['selectors'];
        }
        
        return array();
    }

    /**
     * Save selectors for a specific domain.
     *
     * @since    1.0.0
     * @param    string    $domain       The domain to save selectors for.
     * @param    array     $selectors    The selectors to save.
     */
    public function save_selectors_for_domain($domain, $selectors) {
        $saved_domains = get_option('ai_news_scraper_saved_domains', array());
        
        $saved_domains[$domain] = array(
            'selectors' => $selectors,
            'last_used' => current_time('mysql'),
        );
        
        update_option('ai_news_scraper_saved_domains', $saved_domains);
    }

    /**
     * Scrape articles from an RSS feed.
     *
     * @since    1.0.0
     * @param    string    $feed_url    The RSS feed URL.
     * @param    int       $limit       The maximum number of articles to fetch.
     * @param    string    $keywords    Optional. Keywords to filter by.
     * @return   array|WP_Error    The scraped articles or error.
     */
    public function scrape_from_rss($feed_url, $limit = 10, $keywords = '') {
        // Check if URL is valid
        if (!filter_var($feed_url, FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_url', 'The provided RSS feed URL is not valid.');
        }

        // Prepare data for API request
        $api_data = array(
            'feed_url' => $feed_url,
            'limit' => intval($limit),
            'keywords' => $keywords,
        );

        // Make API request
        $response = $this->make_api_request('/scrape/rss', 'POST', $api_data);

        if (is_wp_error($response)) {
            $this->log_error('rss', $feed_url, null, $response->get_error_message());
            return $response;
        }

        return $response;
    }

    /**
     * Test an RSS feed by fetching a few items.
     *
     * @since    1.0.0
     * @param    string    $feed_url    The RSS feed URL.
     * @return   array|WP_Error    The feed items or error.
     */
    public function test_rss_feed($feed_url) {
        // Check if URL is valid
        if (!filter_var($feed_url, FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_url', 'The provided RSS feed URL is not valid.');
        }

        // Prepare data for API request
        $api_data = array(
            'feed_url' => $feed_url,
            'limit' => 5,
        );

        // Make API request
        $response = $this->make_api_request('/scrape/rss/test', 'POST', $api_data);

        if (is_wp_error($response)) {
            return $response;
        }

        return $response;
    }

    /**
     * Make a request to the Python Flask API.
     *
     * @since    1.0.0
     * @access   private
     * @param    string    $endpoint    The API endpoint.
     * @param    string    $method      The HTTP method (GET, POST, etc.).
     * @param    array     $data        The data to send with the request.
     * @return   array|WP_Error    The API response or error.
     */
    private function make_api_request($endpoint, $method = 'GET', $data = array()) {
        $url = $this->api_url . $endpoint;
        
        $args = array(
            'method'    => $method,
            'timeout'   => 60,
            'headers'   => array(
                'Content-Type' => 'application/json',
            ),
        );
        
        if (!empty($data) && $method !== 'GET') {
            $args['body'] = json_encode($data);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return new WP_Error(
                'api_error',
                'API returned error: ' . wp_remote_retrieve_response_message($response) . ' (' . $response_code . ')'
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', 'Failed to parse API response: ' . json_last_error_msg());
        }
        
        if (isset($data['error'])) {
            return new WP_Error('api_error', $data['error']);
        }
        
        return $data;
    }

    /**
     * Process scraped images (download to media library, etc.).
     *
     * @since    1.0.0
     * @access   private
     * @param    array     $images    The images to process.
     * @param    string    $source_url The source URL of the article.
     * @return   array     The processed images.
     */
    private function process_scraped_images($images, $source_url) {
        // Get image settings
        $options = get_option('ai_news_scraper_options', array());
        $image_settings = isset($options['image_settings']) ? $options['image_settings'] : array();

        // Check if we should process images or just return them as-is
        if (empty($image_settings['use_scraped_images'])) {
            return $images;
        }

        $processed_images = array();

        foreach ($images as $image) {
            $image_url = $image['url'];
            
            // Skip data URLs or invalid URLs
            if (strpos($image_url, 'data:') === 0 || !filter_var($image_url, FILTER_VALIDATE_URL)) {
                continue;
            }
            
            // Make sure URL is absolute
            if (strpos($image_url, 'http') !== 0) {
                $parsed_url = parse_url($source_url);
                $base = $parsed_url['scheme'] . '://' . $parsed_url['host'];
                
                if (strpos($image_url, '/') === 0) {
                    $image_url = $base . $image_url;
                } else {
                    $path = isset($parsed_url['path']) ? dirname($parsed_url['path']) : '';
                    $image_url = $base . $path . '/' . $image_url;
                }
            }
            
            // Add processed image
            $processed_images[] = array(
                'url' => $image_url,
                'alt' => isset($image['alt']) ? $image['alt'] : '',
                'caption' => isset($image['caption']) ? $image['caption'] : '',
            );
        }

        return $processed_images;
    }

    /**
     * Log an error.
     *
     * @since    1.0.0
     * @access   private
     * @param    string    $action       The action that failed.
     * @param    string    $source_url   The source URL if applicable.
     * @param    int       $post_id      The post ID if applicable.
     * @param    string    $message      The error message.
     */
    private function log_error($action, $source_url = '', $post_id = null, $message = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_news_scraper_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'action' => $action,
                'source_url' => $source_url,
                'post_id' => $post_id,
                'status' => 'error',
                'message' => $message,
            ),
            array('%s', '%s', '%d', '%s', '%s')
        );
    }

    /**
     * Get status of the API server.
     *
     * @since    1.0.0
     * @return   bool   True if API is running, false otherwise.
     */
    public function check_api_status() {
        $response = wp_remote_get($this->api_url . '/status', array('timeout' => 5));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        return $response_code === 200;
    }
}
