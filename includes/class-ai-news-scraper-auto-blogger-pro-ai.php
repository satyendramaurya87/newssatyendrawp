<?php
/**
 * The AI functionality of the plugin.
 *
 * @link       https://satyendramaurya.site
 * @since      1.0.0
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/includes
 */

/**
 * The AI functionality of the plugin.
 *
 * Defines the methods for AI content generation
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/includes
 * @author     Satyendra Maurya <contact@satyendramaurya.site>
 */
class AI_News_Scraper_Auto_Blogger_Pro_AI {

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
     * Generate AI content from scraped content.
     *
     * @since    1.0.0
     * @param    string    $content      The original content.
     * @param    string    $title        The title of the article.
     * @param    string    $ai_model     The AI model to use (openai, gemini, claude, deepseek).
     * @param    string    $language     The language to generate content in (english, hindi).
     * @param    string    $tone         The writing tone (default, banarasi, lucknow, delhi, indore).
     * @return   array|WP_Error   The generated content or error.
     */
    public function generate_content($content, $title, $ai_model = 'openai', $language = 'english', $tone = 'default') {
        // Get options
        $options = get_option('ai_news_scraper_options', array());
        $ai_settings = isset($options['ai_settings']) ? $options['ai_settings'] : array();
        
        // Get API key for the selected model
        $api_keys = isset($ai_settings['api_keys']) ? $ai_settings['api_keys'] : array();
        $api_key = isset($api_keys[$ai_model]) ? $api_keys[$ai_model] : '';
        
        // Check if API key is available
        if (empty($api_key)) {
            return new WP_Error('missing_api_key', "API key for {$ai_model} is not configured. Please check your AI settings.");
        }
        
        // Prepare data for API request
        $api_data = array(
            'content' => $content,
            'title' => $title,
            'model' => $ai_model,
            'language' => $language,
            'tone' => $tone,
            'api_key' => $api_key,
            'min_word_count' => isset($ai_settings['min_word_count']) ? intval($ai_settings['min_word_count']) : 500,
            'keyword_density' => isset($ai_settings['keyword_density']) ? floatval($ai_settings['keyword_density']) : 2.5,
            'auto_headings' => isset($ai_settings['auto_headings']) ? (bool) $ai_settings['auto_headings'] : true,
            'use_lists' => isset($ai_settings['use_lists']) ? (bool) $ai_settings['use_lists'] : true,
            'add_faq' => isset($ai_settings['add_faq']) ? (bool) $ai_settings['add_faq'] : true,
            'add_conclusion' => isset($ai_settings['add_conclusion']) ? (bool) $ai_settings['add_conclusion'] : true,
        );
        
        // Make API request
        $response = $this->make_api_request('/ai/generate', 'POST', $api_data);
        
        if (is_wp_error($response)) {
            $this->log_error('generate', '', null, $response->get_error_message());
            return $response;
        }
        
        return $response;
    }

    /**
     * Generate AI-powered image for article.
     *
     * @since    1.0.0
     * @param    string    $prompt       The prompt to generate image from.
     * @param    string    $ai_model     The AI model to use (dall-e, stable-diffusion, midjourney).
     * @param    string    $style        The image style to use.
     * @return   array|WP_Error   The generated image URL or error.
     */
    public function generate_image($prompt, $ai_model = 'dall-e', $style = 'digital-art') {
        // Get options
        $options = get_option('ai_news_scraper_options', array());
        $ai_settings = isset($options['ai_settings']) ? $options['ai_settings'] : array();
        
        // Get API key for OpenAI (for DALL-E)
        $api_keys = isset($ai_settings['api_keys']) ? $ai_settings['api_keys'] : array();
        $api_key = '';
        
        // Determine which API key to use based on the model
        if ($ai_model === 'dall-e') {
            $api_key = isset($api_keys['openai']) ? $api_keys['openai'] : '';
        } elseif ($ai_model === 'stable-diffusion') {
            $api_key = isset($api_keys['stable_diffusion']) ? $api_keys['stable_diffusion'] : '';
        } elseif ($ai_model === 'midjourney') {
            $api_key = isset($api_keys['midjourney']) ? $api_keys['midjourney'] : '';
        }
        
        // Check if API key is available
        if (empty($api_key)) {
            return new WP_Error('missing_api_key', "API key for {$ai_model} is not configured. Please check your AI settings.");
        }
        
        // Prepare data for API request
        $api_data = array(
            'prompt' => $prompt,
            'model' => $ai_model,
            'style' => $style,
            'api_key' => $api_key,
        );
        
        // Make API request
        $response = $this->make_api_request('/ai/generate_image', 'POST', $api_data);
        
        if (is_wp_error($response)) {
            $this->log_error('generate_image', '', null, $response->get_error_message());
            return $response;
        }
        
        return $response;
    }

    /**
     * Generate SEO-friendly title, tags, and categories for an article.
     *
     * @since    1.0.0
     * @param    string    $content      The article content.
     * @param    string    $title        The original title.
     * @param    string    $ai_model     The AI model to use.
     * @return   array|WP_Error   The generated SEO data or error.
     */
    public function generate_seo_data($content, $title, $ai_model = 'openai') {
        // Get options
        $options = get_option('ai_news_scraper_options', array());
        $ai_settings = isset($options['ai_settings']) ? $options['ai_settings'] : array();
        
        // Get API key for the selected model
        $api_keys = isset($ai_settings['api_keys']) ? $ai_settings['api_keys'] : array();
        $api_key = isset($api_keys[$ai_model]) ? $api_keys[$ai_model] : '';
        
        // Check if API key is available
        if (empty($api_key)) {
            return new WP_Error('missing_api_key', "API key for {$ai_model} is not configured. Please check your AI settings.");
        }
        
        // Prepare data for API request
        $api_data = array(
            'content' => $content,
            'title' => $title,
            'model' => $ai_model,
            'api_key' => $api_key,
        );
        
        // Make API request
        $response = $this->make_api_request('/ai/generate_seo', 'POST', $api_data);
        
        if (is_wp_error($response)) {
            $this->log_error('generate_seo', '', null, $response->get_error_message());
            return $response;
        }
        
        return $response;
    }

    /**
     * Generate internal links for an article from existing posts.
     *
     * @since    1.0.0
     * @param    string    $content       The article content.
     * @param    array     $exclude_ids   Post IDs to exclude from linking.
     * @param    int       $max_links     Maximum number of links to add.
     * @return   string    The content with internal links added.
     */
    public function generate_internal_links($content, $exclude_ids = array(), $max_links = 3) {
        // Get recent posts to link to
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        if (!empty($exclude_ids)) {
            $args['post__not_in'] = $exclude_ids;
        }
        
        $posts = get_posts($args);
        
        if (empty($posts)) {
            return $content;
        }
        
        // Create an array of keywords to link
        $link_map = array();
        foreach ($posts as $post) {
            $title = strtolower($post->post_title);
            $words = explode(' ', $title);
            
            // Use the full title as a keyword if it's 1-3 words
            if (count($words) <= 3) {
                $link_map[$title] = get_permalink($post->ID);
            } else {
                // Otherwise, use the first 2-3 significant words
                $significant_words = array();
                foreach ($words as $word) {
                    if (strlen($word) > 3 && !in_array(strtolower($word), $this->get_stop_words())) {
                        $significant_words[] = $word;
                        if (count($significant_words) >= 2) {
                            break;
                        }
                    }
                }
                
                if (!empty($significant_words)) {
                    $keyword = implode(' ', $significant_words);
                    $link_map[$keyword] = get_permalink($post->ID);
                }
            }
            
            // Limit to max number of links
            if (count($link_map) >= $max_links) {
                break;
            }
        }
        
        // Add links to content
        $linked_content = $content;
        $links_added = 0;
        
        foreach ($link_map as $keyword => $link) {
            // Make sure we don't exceed max links
            if ($links_added >= $max_links) {
                break;
            }
            
            // Case-insensitive replacement, but only the first occurrence
            $pattern = '/\b(' . preg_quote($keyword, '/') . ')\b/i';
            $replacement = '<a href="' . esc_url($link) . '">$1</a>';
            
            // Only replace if the keyword isn't already part of a link
            $new_content = preg_replace($pattern, $replacement, $linked_content, 1, $count);
            
            if ($count > 0 && $new_content !== $linked_content) {
                $linked_content = $new_content;
                $links_added++;
            }
        }
        
        return $linked_content;
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
            'timeout'   => 120, // Longer timeout for AI requests
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
     * Get a list of common stop words.
     *
     * @since    1.0.0
     * @access   private
     * @return   array    A list of stop words.
     */
    private function get_stop_words() {
        return array(
            'a', 'an', 'the', 'and', 'or', 'but', 'if', 'then', 'else', 'when',
            'at', 'from', 'by', 'on', 'off', 'for', 'in', 'out', 'over', 'to',
            'into', 'with', 'about', 'against', 'before', 'after', 'above', 'below',
            'up', 'down', 'of', 'is', 'am', 'are', 'was', 'were', 'be', 'been', 'being',
            'have', 'has', 'had', 'do', 'does', 'did', 'can', 'could', 'will', 'would',
            'shall', 'should', 'may', 'might', 'must', 'this', 'that', 'these', 'those',
        );
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
}
