<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://satyendramaurya.site
 * @since      1.0.0
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks for
 * enqueuing the admin-specific stylesheet and JavaScript.
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/admin
 * @author     Satyendra Maurya <contact@satyendramaurya.site>
 */
class AI_News_Scraper_Auto_Blogger_Pro_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        // Bootstrap CSS
        wp_enqueue_style('bootstrap', 'https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css', array(), $this->version, 'all');
        
        // Font Awesome
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0', 'all');
        
        // Plugin specific CSS
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ai-news-scraper-auto-blogger-pro-admin.css', array('bootstrap'), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Bootstrap JS
        wp_enqueue_script('bootstrap-bundle', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', false);
        
        // Plugin specific JS
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ai-news-scraper-auto-blogger-pro-admin.js', array('jquery', 'bootstrap-bundle'), $this->version, false);
        
        // Localize script to pass PHP variables to JavaScript
        wp_localize_script($this->plugin_name, 'aiNewsScraperParams', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai_news_scraper_nonce'),
        ));
    }

    /**
     * Add plugin admin menu
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        // Main menu
        add_menu_page(
            'AI News Scraper & Auto Blogger Pro',
            'AI News Scraper',
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_page'),
            'dashicons-rss',
            26
        );

        // Submenu pages
        add_submenu_page(
            $this->plugin_name,
            'Scraper Settings',
            'Scraper Settings',
            'manage_options',
            $this->plugin_name . '-scraper',
            array($this, 'display_scraper_settings_page')
        );

        add_submenu_page(
            $this->plugin_name,
            'AI Settings',
            'AI Settings',
            'manage_options',
            $this->plugin_name . '-ai',
            array($this, 'display_ai_settings_page')
        );

        add_submenu_page(
            $this->plugin_name,
            'Post Scheduler',
            'Post Scheduler',
            'manage_options',
            $this->plugin_name . '-scheduler',
            array($this, 'display_post_scheduler_page')
        );

        add_submenu_page(
            $this->plugin_name,
            'RSS Settings',
            'RSS Settings',
            'manage_options',
            $this->plugin_name . '-rss',
            array($this, 'display_rss_settings_page')
        );

        add_submenu_page(
            $this->plugin_name,
            'Image Settings',
            'Image Settings',
            'manage_options',
            $this->plugin_name . '-images',
            array($this, 'display_image_settings_page')
        );

        add_submenu_page(
            $this->plugin_name,
            'Logs & History',
            'Logs & History',
            'manage_options',
            $this->plugin_name . '-logs',
            array($this, 'display_logs_history_page')
        );
    }

    /**
     * Display the main plugin admin page
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        include_once('partials/ai-news-scraper-auto-blogger-pro-admin-display.php');
    }

    /**
     * Display the scraper settings page
     *
     * @since    1.0.0
     */
    public function display_scraper_settings_page() {
        include_once('partials/scraper-settings.php');
    }

    /**
     * Display the AI settings page
     *
     * @since    1.0.0
     */
    public function display_ai_settings_page() {
        include_once('partials/ai-settings.php');
    }

    /**
     * Display the post scheduler page
     *
     * @since    1.0.0
     */
    public function display_post_scheduler_page() {
        include_once('partials/post-scheduler.php');
    }

    /**
     * Display the RSS settings page
     *
     * @since    1.0.0
     */
    public function display_rss_settings_page() {
        include_once('partials/rss-settings.php');
    }

    /**
     * Display the image settings page
     *
     * @since    1.0.0
     */
    public function display_image_settings_page() {
        include_once('partials/image-settings.php');
    }

    /**
     * Display the logs & history page
     *
     * @since    1.0.0
     */
    public function display_logs_history_page() {
        include_once('partials/logs-history.php');
    }

    /**
     * Handle AJAX request to scrape an article
     *
     * @since    1.0.0
     */
    public function ajax_scrape_article() {
        // Check nonce for security
        check_ajax_referer('ai_news_scraper_nonce', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have sufficient permissions to perform this action.'));
            return;
        }
        
        // Get the URL from the AJAX request
        $url = isset($_POST['url']) ? sanitize_text_field($_POST['url']) : '';
        
        if (empty($url)) {
            wp_send_json_error(array('message' => 'URL is required.'));
            return;
        }
        
        // Get the scraper instance
        $scraper = new AI_News_Scraper_Auto_Blogger_Pro_Scraper();
        
        // Attempt to scrape the article
        $result = $scraper->scrape_article($url);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        } else {
            // Log successful scrape
            $this->log_action('scrape', $url, null, 'success', 'Successfully scraped article');
            
            wp_send_json_success($result);
        }
    }

    /**
     * Handle AJAX request to generate content using AI
     *
     * @since    1.0.0
     */
    public function ajax_generate_content() {
        // Check nonce for security
        check_ajax_referer('ai_news_scraper_nonce', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have sufficient permissions to perform this action.'));
            return;
        }
        
        // Get the content data from the AJAX request
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $ai_model = isset($_POST['ai_model']) ? sanitize_text_field($_POST['ai_model']) : 'openai';
        $language = isset($_POST['language']) ? sanitize_text_field($_POST['language']) : 'english';
        $tone = isset($_POST['tone']) ? sanitize_text_field($_POST['tone']) : 'default';
        
        if (empty($content)) {
            wp_send_json_error(array('message' => 'Content is required.'));
            return;
        }
        
        // Get the AI instance
        $ai = new AI_News_Scraper_Auto_Blogger_Pro_AI();
        
        // Attempt to generate content
        $result = $ai->generate_content($content, $title, $ai_model, $language, $tone);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        } else {
            // Log successful content generation
            $this->log_action('generate', '', null, 'success', 'Successfully generated AI content');
            
            wp_send_json_success($result);
        }
    }

    /**
     * Handle AJAX request to publish a post
     *
     * @since    1.0.0
     */
    public function ajax_publish_post() {
        // Check nonce for security
        check_ajax_referer('ai_news_scraper_nonce', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have sufficient permissions to perform this action.'));
            return;
        }
        
        // Get the post data from the AJAX request
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        $categories = isset($_POST['categories']) ? array_map('intval', $_POST['categories']) : array();
        $tags = isset($_POST['tags']) ? array_map('sanitize_text_field', $_POST['tags']) : array();
        $featured_image_url = isset($_POST['featured_image']) ? esc_url_raw($_POST['featured_image']) : '';
        
        if (empty($title) || empty($content)) {
            wp_send_json_error(array('message' => 'Title and content are required.'));
            return;
        }
        
        // Create post arguments
        $post_args = array(
            'post_title'    => $title,
            'post_content'  => $content,
            'post_status'   => 'publish',
            'post_author'   => get_current_user_id(),
            'post_category' => $categories,
            'tags_input'    => $tags,
        );
        
        // Insert the post
        $post_id = wp_insert_post($post_args);
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => $post_id->get_error_message()));
            return;
        }
        
        // Set featured image if provided
        if (!empty($featured_image_url)) {
            $this->set_featured_image($post_id, $featured_image_url);
        }
        
        // Log successful post creation
        $this->log_action('publish', '', $post_id, 'success', 'Successfully published post');
        
        wp_send_json_success(array(
            'post_id' => $post_id,
            'post_url' => get_permalink($post_id),
            'message' => 'Post published successfully!'
        ));
    }

    /**
     * Handle AJAX request to schedule a post
     *
     * @since    1.0.0
     */
    public function ajax_schedule_post() {
        // Check nonce for security
        check_ajax_referer('ai_news_scraper_nonce', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have sufficient permissions to perform this action.'));
            return;
        }
        
        // Get the post data from the AJAX request
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        $categories = isset($_POST['categories']) ? array_map('intval', $_POST['categories']) : array();
        $tags = isset($_POST['tags']) ? array_map('sanitize_text_field', $_POST['tags']) : array();
        $featured_image_url = isset($_POST['featured_image']) ? esc_url_raw($_POST['featured_image']) : '';
        $schedule_date = isset($_POST['schedule_date']) ? sanitize_text_field($_POST['schedule_date']) : '';
        
        if (empty($title) || empty($content) || empty($schedule_date)) {
            wp_send_json_error(array('message' => 'Title, content, and schedule date are required.'));
            return;
        }
        
        // Convert schedule date to GMT
        $schedule_date_gmt = get_gmt_from_date($schedule_date);
        
        // Create post arguments
        $post_args = array(
            'post_title'    => $title,
            'post_content'  => $content,
            'post_status'   => 'future',
            'post_author'   => get_current_user_id(),
            'post_category' => $categories,
            'tags_input'    => $tags,
            'post_date'     => $schedule_date,
            'post_date_gmt' => $schedule_date_gmt,
        );
        
        // Insert the post
        $post_id = wp_insert_post($post_args);
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => $post_id->get_error_message()));
            return;
        }
        
        // Set featured image if provided
        if (!empty($featured_image_url)) {
            $this->set_featured_image($post_id, $featured_image_url);
        }
        
        // Log successful post scheduling
        $this->log_action('schedule', '', $post_id, 'success', 'Successfully scheduled post for ' . $schedule_date);
        
        wp_send_json_success(array(
            'post_id' => $post_id,
            'post_url' => get_permalink($post_id),
            'schedule_date' => $schedule_date,
            'message' => 'Post scheduled successfully for ' . $schedule_date
        ));
    }

    /**
     * Handle AJAX request to add an RSS feed
     *
     * @since    1.0.0
     */
    public function ajax_add_rss_feed() {
        // Check nonce for security
        check_ajax_referer('ai_news_scraper_nonce', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have sufficient permissions to perform this action.'));
            return;
        }
        
        // Get the RSS feed URL from the AJAX request
        $feed_url = isset($_POST['feed_url']) ? esc_url_raw($_POST['feed_url']) : '';
        $feed_name = isset($_POST['feed_name']) ? sanitize_text_field($_POST['feed_name']) : '';
        $keywords = isset($_POST['keywords']) ? sanitize_text_field($_POST['keywords']) : '';
        
        if (empty($feed_url)) {
            wp_send_json_error(array('message' => 'Feed URL is required.'));
            return;
        }
        
        // Get the RSS instance
        $rss = new AI_News_Scraper_Auto_Blogger_Pro_RSS();
        
        // Attempt to add the RSS feed
        $result = $rss->add_rss_feed($feed_url, $feed_name, $keywords);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        } else {
            // Log successful feed addition
            $this->log_action('add_rss', $feed_url, null, 'success', 'Successfully added RSS feed');
            
            wp_send_json_success(array(
                'feed_id' => $result,
                'message' => 'RSS feed added successfully!'
            ));
        }
    }

    /**
     * Handle AJAX request to get logs
     *
     * @since    1.0.0
     */
    public function ajax_get_logs() {
        // Check nonce for security
        check_ajax_referer('ai_news_scraper_nonce', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have sufficient permissions to perform this action.'));
            return;
        }
        
        // Get pagination parameters
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
        
        // Get the logs
        global $wpdb;
        $table_name = $wpdb->prefix . 'ai_news_scraper_logs';
        
        $total_query = "SELECT COUNT(*) FROM $table_name";
        $total = $wpdb->get_var($total_query);
        
        $offset = ($page - 1) * $per_page;
        
        $logs_query = $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        );
        
        $logs = $wpdb->get_results($logs_query);
        
        wp_send_json_success(array(
            'logs' => $logs,
            'total' => $total,
            'pages' => ceil($total / $per_page),
            'current_page' => $page
        ));
    }

    /**
     * Handle AJAX request to save settings
     *
     * @since    1.0.0
     */
    public function ajax_save_settings() {
        // Check nonce for security
        check_ajax_referer('ai_news_scraper_nonce', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have sufficient permissions to perform this action.'));
            return;
        }
        
        // Get the settings data from the AJAX request
        $settings_type = isset($_POST['settings_type']) ? sanitize_text_field($_POST['settings_type']) : '';
        $settings = isset($_POST['settings']) ? $_POST['settings'] : array();
        
        if (empty($settings_type)) {
            wp_send_json_error(array('message' => 'Settings type is required.'));
            return;
        }
        
        // Get current options
        $options = get_option('ai_news_scraper_options', array());
        
        // Update the specific settings type
        switch ($settings_type) {
            case 'scraper':
                $options['scraper_settings'] = array(
                    'visual_selector_enabled' => isset($settings['visual_selector_enabled']) ? (bool)$settings['visual_selector_enabled'] : false,
                    'auto_scraping_enabled' => isset($settings['auto_scraping_enabled']) ? (bool)$settings['auto_scraping_enabled'] : false,
                );
                break;
                
            case 'ai':
                $options['ai_settings'] = array(
                    'ai_model' => isset($settings['ai_model']) ? sanitize_text_field($settings['ai_model']) : 'openai',
                    'language' => isset($settings['language']) ? sanitize_text_field($settings['language']) : 'english',
                    'writing_tone' => isset($settings['writing_tone']) ? sanitize_text_field($settings['writing_tone']) : 'default',
                    'social_media_embed' => isset($settings['social_media_embed']) ? (bool)$settings['social_media_embed'] : true,
                    'api_keys' => array(
                        'openai' => isset($settings['api_keys']['openai']) ? sanitize_text_field($settings['api_keys']['openai']) : '',
                        'gemini' => isset($settings['api_keys']['gemini']) ? sanitize_text_field($settings['api_keys']['gemini']) : '',
                        'claude' => isset($settings['api_keys']['claude']) ? sanitize_text_field($settings['api_keys']['claude']) : '',
                        'deepseek' => isset($settings['api_keys']['deepseek']) ? sanitize_text_field($settings['api_keys']['deepseek']) : '',
                    ),
                );
                break;
                
            case 'scheduler':
                $options['post_scheduler'] = array(
                    'bulk_posting_enabled' => isset($settings['bulk_posting_enabled']) ? (bool)$settings['bulk_posting_enabled'] : false,
                    'post_schedule' => isset($settings['post_schedule']) ? sanitize_text_field($settings['post_schedule']) : 'hourly',
                    'randomize_schedule' => isset($settings['randomize_schedule']) ? (bool)$settings['randomize_schedule'] : true,
                    'auto_internal_linking' => isset($settings['auto_internal_linking']) ? (bool)$settings['auto_internal_linking'] : true,
                );
                break;
                
            case 'rss':
                // RSS feeds are handled separately in add_rss_feed method
                $options['rss_settings'] = array(
                    'rewrite_enabled' => isset($settings['rewrite_enabled']) ? (bool)$settings['rewrite_enabled'] : true,
                    'keyword_filter' => isset($settings['keyword_filter']) ? sanitize_text_field($settings['keyword_filter']) : '',
                );
                break;
                
            case 'image':
                $options['image_settings'] = array(
                    'use_scraped_images' => isset($settings['use_scraped_images']) ? (bool)$settings['use_scraped_images'] : true,
                    'use_ai_images' => isset($settings['use_ai_images']) ? (bool)$settings['use_ai_images'] : false,
                );
                break;
        }
        
        // Save the updated options
        update_option('ai_news_scraper_options', $options);
        
        // Log successful settings update
        $this->log_action('settings', '', null, 'success', 'Successfully updated ' . $settings_type . ' settings');
        
        wp_send_json_success(array(
            'message' => ucfirst($settings_type) . ' settings saved successfully!'
        ));
    }

    /**
     * Set the featured image for a post
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
     * Log an action to the database
     *
     * @since    1.0.0
     * @param    string   $action     The action that was performed.
     * @param    string   $source_url The source URL if applicable.
     * @param    int      $post_id    The post ID if applicable.
     * @param    string   $status     The status of the action (success or error).
     * @param    string   $message    The message to log.
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
