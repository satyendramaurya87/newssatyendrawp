<?php
/**
 * The scheduler functionality of the plugin.
 *
 * @link       https://satyendramaurya.site
 * @since      1.0.0
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/includes
 */

/**
 * The scheduler functionality of the plugin.
 *
 * Defines the methods for scheduling posts
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/includes
 * @author     Satyendra Maurya <contact@satyendramaurya.site>
 */
class AI_News_Scraper_Auto_Blogger_Pro_Scheduler {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Constructor code
    }

    /**
     * Add custom cron schedules.
     *
     * @since    1.0.0
     * @param    array    $schedules    Current cron schedules.
     * @return   array    Modified cron schedules.
     */
    public function add_cron_schedules($schedules) {
        $schedules['every_30_minutes'] = array(
            'interval' => 30 * MINUTE_IN_SECONDS,
            'display'  => __('Every 30 Minutes', 'ai-news-scraper-auto-blogger-pro'),
        );
        
        $schedules['every_2_hours'] = array(
            'interval' => 2 * HOUR_IN_SECONDS,
            'display'  => __('Every 2 Hours', 'ai-news-scraper-auto-blogger-pro'),
        );
        
        $schedules['every_6_hours'] = array(
            'interval' => 6 * HOUR_IN_SECONDS,
            'display'  => __('Every 6 Hours', 'ai-news-scraper-auto-blogger-pro'),
        );
        
        $schedules['every_12_hours'] = array(
            'interval' => 12 * HOUR_IN_SECONDS,
            'display'  => __('Every 12 Hours', 'ai-news-scraper-auto-blogger-pro'),
        );
        
        return $schedules;
    }

    /**
     * Schedule cron events.
     *
     * @since    1.0.0
     */
    public function schedule_events() {
        // Schedule processing of scheduled posts
        if (!wp_next_scheduled('ai_news_scraper_process_scheduled_posts')) {
            wp_schedule_event(time(), 'hourly', 'ai_news_scraper_process_scheduled_posts');
        }
        
        // Schedule RSS feed processing
        if (!wp_next_scheduled('ai_news_scraper_process_rss_feeds')) {
            wp_schedule_event(time(), 'twicedaily', 'ai_news_scraper_process_rss_feeds');
        }
    }

    /**
     * Clear scheduled cron events.
     *
     * @since    1.0.0
     */
    public function clear_scheduled_events() {
        wp_clear_scheduled_hook('ai_news_scraper_process_scheduled_posts');
        wp_clear_scheduled_hook('ai_news_scraper_process_rss_feeds');
    }

    /**
     * Process scheduled posts.
     *
     * @since    1.0.0
     */
    public function process_scheduled_posts() {
        global $wpdb;
        
        // Get posts that are scheduled in our custom table
        $table_name = $wpdb->prefix . 'ai_news_scraper_scheduled_posts';
        $current_time = current_time('mysql');
        
        $scheduled_posts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE scheduled_time <= %s AND status = 'pending' ORDER BY scheduled_time ASC LIMIT 5",
                $current_time
            )
        );
        
        if (empty($scheduled_posts)) {
            return;
        }
        
        // Process each scheduled post
        foreach ($scheduled_posts as $scheduled_post) {
            $this->process_single_scheduled_post($scheduled_post);
        }
    }

    /**
     * Process a single scheduled post.
     *
     * @since    1.0.0
     * @param    object    $scheduled_post    The scheduled post data.
     */
    private function process_single_scheduled_post($scheduled_post) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_news_scraper_scheduled_posts';
        
        // Update status to processing
        $wpdb->update(
            $table_name,
            array('status' => 'processing'),
            array('id' => $scheduled_post->id),
            array('%s'),
            array('%d')
        );
        
        try {
            // Get post data
            $post_data = maybe_unserialize($scheduled_post->post_data);
            
            if (empty($post_data)) {
                throw new Exception('Invalid post data.');
            }
            
            // Process according to source type
            if ($scheduled_post->source_type === 'url') {
                $this->process_scheduled_url($scheduled_post->source_url, $post_data);
            } elseif ($scheduled_post->source_type === 'rss') {
                $this->process_scheduled_rss_item($scheduled_post->source_url, $post_data);
            } else {
                throw new Exception('Unknown source type: ' . $scheduled_post->source_type);
            }
            
            // Update status to completed
            $wpdb->update(
                $table_name,
                array(
                    'status' => 'completed',
                    'completed_time' => current_time('mysql')
                ),
                array('id' => $scheduled_post->id),
                array('%s', '%s'),
                array('%d')
            );
            
            // Log successful processing
            $this->log_action('process_scheduled', $scheduled_post->source_url, null, 'success', 'Successfully processed scheduled post');
        } catch (Exception $e) {
            // Update status to failed
            $wpdb->update(
                $table_name,
                array(
                    'status' => 'failed',
                    'completed_time' => current_time('mysql')
                ),
                array('id' => $scheduled_post->id),
                array('%s', '%s'),
                array('%d')
            );
            
            // Log error
            $this->log_action('process_scheduled', $scheduled_post->source_url, null, 'error', 'Failed to process scheduled post: ' . $e->getMessage());
        }
    }

    /**
     * Process a scheduled URL to create a post.
     *
     * @since    1.0.0
     * @param    string    $url         The URL to scrape.
     * @param    array     $post_data   Additional post data.
     * @return   int|WP_Error   The post ID or error.
     */
    private function process_scheduled_url($url, $post_data) {
        // Get scraper and AI instances
        $scraper = new AI_News_Scraper_Auto_Blogger_Pro_Scraper();
        $ai = new AI_News_Scraper_Auto_Blogger_Pro_AI();
        
        // Scrape the article
        $scraped_data = $scraper->scrape_article($url);
        
        if (is_wp_error($scraped_data)) {
            throw new Exception($scraped_data->get_error_message());
        }
        
        // Generate AI content
        $ai_model = isset($post_data['ai_model']) ? $post_data['ai_model'] : 'openai';
        $language = isset($post_data['language']) ? $post_data['language'] : 'english';
        $tone = isset($post_data['tone']) ? $post_data['tone'] : 'default';
        
        $ai_content = $ai->generate_content($scraped_data['content'], $scraped_data['title'], $ai_model, $language, $tone);
        
        if (is_wp_error($ai_content)) {
            throw new Exception($ai_content->get_error_message());
        }
        
        // Create post
        return $this->create_post($ai_content, $post_data, $scraped_data);
    }

    /**
     * Process a scheduled RSS item to create a post.
     *
     * @since    1.0.0
     * @param    string    $url         The RSS item URL.
     * @param    array     $post_data   Additional post data.
     * @return   int|WP_Error   The post ID or error.
     */
    private function process_scheduled_rss_item($url, $post_data) {
        // Get scraper and AI instances
        $scraper = new AI_News_Scraper_Auto_Blogger_Pro_Scraper();
        $ai = new AI_News_Scraper_Auto_Blogger_Pro_AI();
        
        // Scrape the article
        $scraped_data = $scraper->scrape_article($url);
        
        if (is_wp_error($scraped_data)) {
            throw new Exception($scraped_data->get_error_message());
        }
        
        // Generate AI content
        $ai_model = isset($post_data['ai_model']) ? $post_data['ai_model'] : 'openai';
        $language = isset($post_data['language']) ? $post_data['language'] : 'english';
        $tone = isset($post_data['tone']) ? $post_data['tone'] : 'default';
        
        $ai_content = $ai->generate_content($scraped_data['content'], $scraped_data['title'], $ai_model, $language, $tone);
        
        if (is_wp_error($ai_content)) {
            throw new Exception($ai_content->get_error_message());
        }
        
        // Create post
        return $this->create_post($ai_content, $post_data, $scraped_data);
    }

    /**
     * Create a post from AI-generated content.
     *
     * @since    1.0.0
     * @param    array    $ai_content    The AI-generated content.
     * @param    array    $post_data     Additional post data.
     * @param    array    $scraped_data  The original scraped data.
     * @return   int|WP_Error   The post ID or error.
     */
    private function create_post($ai_content, $post_data, $scraped_data) {
        // Get AI instance for internal linking
        $ai = new AI_News_Scraper_Auto_Blogger_Pro_AI();
        
        // Add internal links if enabled
        $content = $ai_content['content'];
        if (isset($post_data['auto_internal_linking']) && $post_data['auto_internal_linking']) {
            $content = $ai->generate_internal_links($content);
        }
        
        // Prepare post data
        $post_args = array(
            'post_title'    => $ai_content['title'],
            'post_content'  => $content,
            'post_status'   => 'publish',
            'post_author'   => isset($post_data['author_id']) ? $post_data['author_id'] : 1, // Default to admin
            'post_category' => isset($post_data['categories']) ? $post_data['categories'] : array(),
            'tags_input'    => isset($post_data['tags']) ? $post_data['tags'] : array(),
        );
        
        // Insert the post
        $post_id = wp_insert_post($post_args);
        
        if (is_wp_error($post_id)) {
            throw new Exception($post_id->get_error_message());
        }
        
        // Set featured image if available
        if (!empty($scraped_data['images']) && isset($post_data['use_scraped_images']) && $post_data['use_scraped_images']) {
            $this->set_featured_image($post_id, $scraped_data['images'][0]['url']);
        } elseif (isset($post_data['use_ai_images']) && $post_data['use_ai_images']) {
            // Generate and set AI image
            $prompt = isset($post_data['image_prompt']) ? $post_data['image_prompt'] : $ai_content['title'];
            $image_style = isset($post_data['image_style']) ? $post_data['image_style'] : 'digital-art';
            
            $image_result = $ai->generate_image($prompt, 'dall-e', $image_style);
            
            if (!is_wp_error($image_result) && isset($image_result['url'])) {
                $this->set_featured_image($post_id, $image_result['url']);
            }
        }
        
        // Add source link if enabled
        if (isset($post_data['link_to_source']) && $post_data['link_to_source'] && !empty($scraped_data['url'])) {
            $source_link = '<p class="source-link">Source: <a href="' . esc_url($scraped_data['url']) . '" target="_blank" rel="nofollow">' . esc_html($scraped_data['url']) . '</a></p>';
            $updated_content = $content . $source_link;
            
            wp_update_post(array(
                'ID' => $post_id,
                'post_content' => $updated_content,
            ));
        }
        
        // Log successful post creation
        $this->log_action('publish', $scraped_data['url'], $post_id, 'success', 'Successfully published post from scheduled job');
        
        return $post_id;
    }

    /**
     * Set the featured image for a post.
     *
     * @since    1.0.0
     * @param    int      $post_id            The ID of the post to set the featured image for.
     * @param    string   $featured_image_url The URL of the featured image.
     * @return   int|bool                     The attachment ID or false on failure.
     */
    private function set_featured_image($post_id, $featured_image_url) {
        // Download image from URL
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($featured_image_url);
        $filename = basename($featured_image_url);
        
        if (wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }
        
        file_put_contents($file, $image_data);
        
        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
        $attach_id = wp_insert_attachment($attachment, $file, $post_id);
        
        if (!is_wp_error($attach_id)) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $file);
            wp_update_attachment_metadata($attach_id, $attach_data);
            set_post_thumbnail($post_id, $attach_id);
            return $attach_id;
        }
        
        return false;
    }

    /**
     * Schedule a post for future processing.
     *
     * @since    1.0.0
     * @param    string    $source_url    The URL to scrape.
     * @param    string    $source_type   The source type (url or rss).
     * @param    array     $post_data     Additional post data.
     * @param    string    $schedule_time The time to schedule the post for.
     * @return   int|WP_Error   The scheduled post ID or error.
     */
    public function schedule_post($source_url, $source_type = 'url', $post_data = array(), $schedule_time = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_news_scraper_scheduled_posts';
        
        // Check if URL is valid
        if (!filter_var($source_url, FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_url', 'The provided URL is not valid.');
        }
        
        // If no schedule time provided, schedule for now
        if (empty($schedule_time)) {
            $schedule_time = current_time('mysql');
        }
        
        // Insert scheduled post
        $wpdb->insert(
            $table_name,
            array(
                'source_url' => $source_url,
                'source_type' => $source_type,
                'post_data' => maybe_serialize($post_data),
                'scheduled_time' => $schedule_time,
                'created_time' => current_time('mysql'),
                'status' => 'pending',
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($wpdb->last_error) {
            return new WP_Error('db_error', 'Failed to schedule post: ' . $wpdb->last_error);
        }
        
        $scheduled_id = $wpdb->insert_id;
        
        // Log successful scheduling
        $this->log_action('schedule', $source_url, null, 'success', 'Successfully scheduled post for ' . $schedule_time);
        
        return $scheduled_id;
    }

    /**
     * Create bulk schedule for multiple posts.
     *
     * @since    1.0.0
     * @param    array     $urls           Array of URLs to schedule.
     * @param    array     $post_data      Additional post data.
     * @param    string    $start_time     The start time for scheduling.
     * @param    int       $interval       The interval in minutes between posts.
     * @param    bool      $randomize      Whether to randomize the intervals.
     * @return   array|WP_Error   The scheduled post IDs or error.
     */
    public function create_bulk_schedule($urls, $post_data = array(), $start_time = '', $interval = 60, $randomize = true) {
        // Validate URLs
        $valid_urls = array();
        foreach ($urls as $url) {
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $valid_urls[] = $url;
            }
        }
        
        if (empty($valid_urls)) {
            return new WP_Error('no_valid_urls', 'No valid URLs provided.');
        }
        
        // If no start time provided, use current time
        if (empty($start_time)) {
            $start_time = current_time('mysql');
        } else {
            // Convert to MySQL format if timestamp
            if (is_numeric($start_time)) {
                $start_time = date('Y-m-d H:i:s', $start_time);
            }
        }
        
        $start_timestamp = strtotime($start_time);
        if (!$start_timestamp) {
            return new WP_Error('invalid_time', 'Invalid start time format.');
        }
        
        // Schedule each URL
        $scheduled_ids = array();
        $current_timestamp = $start_timestamp;
        
        foreach ($valid_urls as $url) {
            // Calculate schedule time
            if ($randomize) {
                // Add random minutes (±15 minutes)
                $random_minutes = rand(-15, 15);
                $schedule_timestamp = $current_timestamp + ($random_minutes * 60);
            } else {
                $schedule_timestamp = $current_timestamp;
            }
            
            $schedule_time = date('Y-m-d H:i:s', $schedule_timestamp);
            
            // Schedule the post
            $result = $this->schedule_post($url, 'url', $post_data, $schedule_time);
            
            if (!is_wp_error($result)) {
                $scheduled_ids[] = $result;
            }
            
            // Increment the timestamp for the next post
            $current_timestamp += ($interval * 60);
        }
        
        if (empty($scheduled_ids)) {
            return new WP_Error('scheduling_failed', 'Failed to schedule any posts.');
        }
        
        return $scheduled_ids;
    }

    /**
     * Delete a scheduled post.
     *
     * @since    1.0.0
     * @param    int    $schedule_id    The ID of the scheduled post.
     * @return   bool|WP_Error   True on success or error.
     */
    public function delete_scheduled_post($schedule_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_news_scraper_scheduled_posts';
        
        // Check if schedule exists
        $scheduled_post = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $schedule_id)
        );
        
        if (!$scheduled_post) {
            return new WP_Error('not_found', 'Scheduled post not found.');
        }
        
        // Delete the scheduled post
        $result = $wpdb->delete(
            $table_name,
            array('id' => $schedule_id),
            array('%d')
        );
        
        if (!$result) {
            return new WP_Error('delete_failed', 'Failed to delete scheduled post: ' . $wpdb->last_error);
        }
        
        // Log successful deletion
        $this->log_action('delete_schedule', $scheduled_post->source_url, null, 'success', 'Successfully deleted scheduled post');
        
        return true;
    }

    /**
     * Get all scheduled posts.
     *
     * @since    1.0.0
     * @param    int       $limit    Optional. Maximum number of posts to retrieve.
     * @param    string    $status   Optional. Filter by status.
     * @return   array     The scheduled posts.
     */
    public function get_scheduled_posts($limit = 100, $status = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_news_scraper_scheduled_posts';
        
        $sql = "SELECT * FROM $table_name";
        $args = array();
        
        if (!empty($status)) {
            $sql .= " WHERE status = %s";
            $args[] = $status;
        }
        
        $sql .= " ORDER BY scheduled_time ASC LIMIT %d";
        $args[] = $limit;
        
        if (!empty($args)) {
            $sql = $wpdb->prepare($sql, $args);
        }
        
        return $wpdb->get_results($sql);
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

    /**
     * Create the scheduled posts table in the WordPress database if it doesn't exist.
     *
     * @since    1.0.0
     */
    public static function create_scheduled_posts_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_news_scraper_scheduled_posts';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            source_url varchar(255) NOT NULL,
            source_type varchar(20) NOT NULL DEFAULT 'url',
            post_data longtext,
            scheduled_time datetime DEFAULT NULL,
            created_time datetime DEFAULT CURRENT_TIMESTAMP,
            completed_time datetime DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}

// Create the scheduled posts table on plugin activation
add_action('plugins_loaded', array('AI_News_Scraper_Auto_Blogger_Pro_Scheduler', 'create_scheduled_posts_table'));
