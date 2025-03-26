<?php
/**
 * The RSS feed processing functionality of the plugin.
 *
 * @link       https://satyendramaurya.site
 * @since      1.0.0
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/includes
 */

/**
 * The RSS feed processing functionality of the plugin.
 *
 * Defines the methods for RSS feed processing
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/includes
 * @author     Satyendra Maurya <contact@satyendramaurya.site>
 */
class AI_News_Scraper_Auto_Blogger_Pro_RSS {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Constructor code
    }

    /**
     * Process RSS feeds.
     *
     * @since    1.0.0
     */
    public function process_rss_feeds() {
        // Get options
        $options = get_option('ai_news_scraper_options', array());
        $rss_settings = isset($options['rss_settings']) ? $options['rss_settings'] : array();
        $feeds = isset($rss_settings['feeds']) ? $rss_settings['feeds'] : array();
        
        if (empty($feeds)) {
            return;
        }
        
        $scheduler = new AI_News_Scraper_Auto_Blogger_Pro_Scheduler();
        $scraper = new AI_News_Scraper_Auto_Blogger_Pro_Scraper();
        
        // Process each feed
        foreach ($feeds as $feed) {
            try {
                $feed_url = $feed['url'];
                $feed_name = isset($feed['name']) ? $feed['name'] : '';
                $keywords = isset($feed['keywords']) ? $feed['keywords'] : '';
                $fetch_limit = isset($feed['fetch_limit']) ? intval($feed['fetch_limit']) : 10;
                
                // Scrape the RSS feed
                $rss_items = $scraper->scrape_from_rss($feed_url, $fetch_limit, $keywords);
                
                if (is_wp_error($rss_items)) {
                    $this->log_action('rss', $feed_url, null, 'error', 'Failed to process RSS feed: ' . $rss_items->get_error_message());
                    continue;
                }
                
                if (empty($rss_items['items'])) {
                    $this->log_action('rss', $feed_url, null, 'success', 'No new items found in RSS feed');
                    continue;
                }
                
                // Get already processed URLs to avoid duplicates
                $processed_urls = $this->get_processed_urls();
                
                // Prepare post data
                $post_data = array(
                    'ai_model' => isset($options['ai_settings']['ai_model']) ? $options['ai_settings']['ai_model'] : 'openai',
                    'language' => isset($options['ai_settings']['language']) ? $options['ai_settings']['language'] : 'english',
                    'tone' => isset($options['ai_settings']['writing_tone']) ? $options['ai_settings']['writing_tone'] : 'default',
                    'auto_internal_linking' => isset($options['post_scheduler']['auto_internal_linking']) ? (bool) $options['post_scheduler']['auto_internal_linking'] : true,
                    'use_scraped_images' => isset($options['image_settings']['use_scraped_images']) ? (bool) $options['image_settings']['use_scraped_images'] : true,
                    'use_ai_images' => isset($options['image_settings']['use_ai_images']) ? (bool) $options['image_settings']['use_ai_images'] : false,
                    'link_to_source' => isset($rss_settings['link_to_source']) ? (bool) $rss_settings['link_to_source'] : true,
                );
                
                // Schedule processing for each item
                $scheduled_count = 0;
                foreach ($rss_items['items'] as $item) {
                    // Skip already processed URLs
                    if (in_array($item['link'], $processed_urls)) {
                        continue;
                    }
                    
                    // Determine post categories
                    $categories = array();
                    if (isset($rss_settings['auto_categorize']) && $rss_settings['auto_categorize']) {
                        // Auto-categorize based on feed name if provided
                        if (!empty($feed_name)) {
                            $category = get_category_by_slug(sanitize_title($feed_name));
                            if ($category) {
                                $categories[] = $category->term_id;
                            } else {
                                // Create the category if it doesn't exist
                                $new_cat_id = wp_create_category($feed_name);
                                if ($new_cat_id) {
                                    $categories[] = $new_cat_id;
                                }
                            }
                        }
                    }
                    
                    // Add categories to post data
                    $post_data['categories'] = $categories;
                    
                    // Calculate schedule time (staggered)
                    $schedule_time = date('Y-m-d H:i:s', strtotime('+' . $scheduled_count * 30 . ' minutes', current_time('timestamp')));
                    
                    // Schedule the post
                    $result = $scheduler->schedule_post($item['link'], 'rss', $post_data, $schedule_time);
                    
                    if (!is_wp_error($result)) {
                        $scheduled_count++;
                        
                        // Store the URL as processed
                        $this->mark_url_as_processed($item['link']);
                    }
                }
                
                $this->log_action('rss', $feed_url, null, 'success', "Processed RSS feed. Scheduled $scheduled_count new items.");
            } catch (Exception $e) {
                $this->log_action('rss', $feed_url, null, 'error', 'Exception while processing RSS feed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Add an RSS feed.
     *
     * @since    1.0.0
     * @param    string    $feed_url    The RSS feed URL.
     * @param    string    $feed_name   Optional. A name for the feed.
     * @param    string    $keywords    Optional. Keywords to filter by.
     * @return   int|WP_Error   The feed ID or error.
     */
    public function add_rss_feed($feed_url, $feed_name = '', $keywords = '') {
        // Check if URL is valid
        if (!filter_var($feed_url, FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_url', 'The provided RSS feed URL is not valid.');
        }
        
        // Test the feed to make sure it's valid
        $scraper = new AI_News_Scraper_Auto_Blogger_Pro_Scraper();
        $test_result = $scraper->test_rss_feed($feed_url);
        
        if (is_wp_error($test_result)) {
            return $test_result;
        }
        
        // Get options
        $options = get_option('ai_news_scraper_options', array());
        $rss_settings = isset($options['rss_settings']) ? $options['rss_settings'] : array();
        $feeds = isset($rss_settings['feeds']) ? $rss_settings['feeds'] : array();
        
        // Check if feed already exists
        foreach ($feeds as $key => $feed) {
            if ($feed['url'] === $feed_url) {
                return new WP_Error('duplicate_feed', 'This RSS feed is already added.');
            }
        }
        
        // Add the new feed
        $feeds[] = array(
            'url' => $feed_url,
            'name' => $feed_name,
            'keywords' => $keywords,
            'fetch_limit' => 10,
            'fetch_frequency' => 'twicedaily',
        );
        
        // Update options
        $rss_settings['feeds'] = $feeds;
        $options['rss_settings'] = $rss_settings;
        update_option('ai_news_scraper_options', $options);
        
        // Log action
        $this->log_action('add_rss', $feed_url, null, 'success', 'Successfully added RSS feed');
        
        return count($feeds) - 1; // Return the index of the new feed
    }

    /**
     * Update an RSS feed.
     *
     * @since    1.0.0
     * @param    int       $feed_index  The index of the feed to update.
     * @param    string    $feed_url    The RSS feed URL.
     * @param    string    $feed_name   Optional. A name for the feed.
     * @param    string    $keywords    Optional. Keywords to filter by.
     * @return   bool|WP_Error   True on success or error.
     */
    public function update_rss_feed($feed_index, $feed_url, $feed_name = '', $keywords = '') {
        // Check if URL is valid
        if (!filter_var($feed_url, FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_url', 'The provided RSS feed URL is not valid.');
        }
        
        // Get options
        $options = get_option('ai_news_scraper_options', array());
        $rss_settings = isset($options['rss_settings']) ? $options['rss_settings'] : array();
        $feeds = isset($rss_settings['feeds']) ? $rss_settings['feeds'] : array();
        
        // Check if feed index exists
        if (!isset($feeds[$feed_index])) {
            return new WP_Error('invalid_feed', 'Feed not found.');
        }
        
        // Update the feed
        $feeds[$feed_index] = array(
            'url' => $feed_url,
            'name' => $feed_name,
            'keywords' => $keywords,
            'fetch_limit' => isset($feeds[$feed_index]['fetch_limit']) ? $feeds[$feed_index]['fetch_limit'] : 10,
            'fetch_frequency' => isset($feeds[$feed_index]['fetch_frequency']) ? $feeds[$feed_index]['fetch_frequency'] : 'twicedaily',
        );
        
        // Update options
        $rss_settings['feeds'] = $feeds;
        $options['rss_settings'] = $rss_settings;
        update_option('ai_news_scraper_options', $options);
        
        // Log action
        $this->log_action('update_rss', $feed_url, null, 'success', 'Successfully updated RSS feed');
        
        return true;
    }

    /**
     * Delete an RSS feed.
     *
     * @since    1.0.0
     * @param    int       $feed_index  The index of the feed to delete.
     * @return   bool|WP_Error   True on success or error.
     */
    public function delete_rss_feed($feed_index) {
        // Get options
        $options = get_option('ai_news_scraper_options', array());
        $rss_settings = isset($options['rss_settings']) ? $options['rss_settings'] : array();
        $feeds = isset($rss_settings['feeds']) ? $rss_settings['feeds'] : array();
        
        // Check if feed index exists
        if (!isset($feeds[$feed_index])) {
            return new WP_Error('invalid_feed', 'Feed not found.');
        }
        
        $feed_url = $feeds[$feed_index]['url'];
        
        // Remove the feed
        unset($feeds[$feed_index]);
        $feeds = array_values($feeds); // Re-index array
        
        // Update options
        $rss_settings['feeds'] = $feeds;
        $options['rss_settings'] = $rss_settings;
        update_option('ai_news_scraper_options', $options);
        
        // Log action
        $this->log_action('delete_rss', $feed_url, null, 'success', 'Successfully deleted RSS feed');
        
        return true;
    }

    /**
     * Get RSS feed statistics.
     *
     * @since    1.0.0
     * @return   array    The RSS feed statistics.
     */
    public function get_rss_stats() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_news_scraper_logs';
        
        // Get today's count
        $today_start = date('Y-m-d 00:00:00', current_time('timestamp'));
        $today_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE action = 'rss' AND status = 'success' AND created_at >= %s",
                $today_start
            )
        );
        
        // Get total count
        $total_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_name WHERE action = 'rss' AND status = 'success'"
        );
        
        // Get last fetch time
        $last_fetch = $wpdb->get_var(
            "SELECT created_at FROM $table_name WHERE action = 'rss' ORDER BY created_at DESC LIMIT 1"
        );
        
        return array(
            'today_count' => $today_count,
            'total_count' => $total_count,
            'last_fetch' => $last_fetch ? date('Y-m-d H:i:s', strtotime($last_fetch)) : 'Never',
        );
    }

    /**
     * Get a list of already processed URLs.
     *
     * @since    1.0.0
     * @return   array    The list of processed URLs.
     */
    private function get_processed_urls() {
        $processed_urls = get_option('ai_news_scraper_processed_urls', array());
        
        // Clean up old URLs (older than 30 days)
        $current_time = current_time('timestamp');
        foreach ($processed_urls as $url => $timestamp) {
            if ($current_time - $timestamp > 30 * DAY_IN_SECONDS) {
                unset($processed_urls[$url]);
            }
        }
        
        update_option('ai_news_scraper_processed_urls', $processed_urls);
        
        return array_keys($processed_urls);
    }

    /**
     * Mark a URL as processed.
     *
     * @since    1.0.0
     * @param    string    $url    The URL to mark as processed.
     */
    private function mark_url_as_processed($url) {
        $processed_urls = get_option('ai_news_scraper_processed_urls', array());
        $processed_urls[$url] = current_time('timestamp');
        update_option('ai_news_scraper_processed_urls', $processed_urls);
    }

    /**
     * Log an action.
     *
     * @since    1.0.0
     * @access   private
     * @param    string    $action     The action that was performed.
     * @param    string    $source_url The source URL if applicable.
     * @param    int       $post_id    The post ID if applicable.
     * @param    string    $status     The status of the action (success or error).
     * @param    string    $message    The message to log.
     */
    private function log_action($action, $source_url = '', $post_id = null, $status = 'success', $message = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_news_scraper_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'action' => $action,
                'source_url' => $source_url,
                'post_id' => $post_id,
                'status' => $status,
                'message' => $message,
            ),
            array('%s', '%s', '%d', '%s', '%s')
        );
    }
}
