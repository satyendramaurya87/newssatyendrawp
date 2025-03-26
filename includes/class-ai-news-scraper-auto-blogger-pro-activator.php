<?php
/**
 * Fired during plugin activation
 *
 * @link       https://satyendramaurya.site
 * @since      1.0.0
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/includes
 * @author     Satyendra Maurya <contact@satyendramaurya.site>
 */
class AI_News_Scraper_Auto_Blogger_Pro_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Create plugin options with default values
        $default_options = array(
            'scraper_settings' => array(
                'visual_selector_enabled' => true,
                'auto_scraping_enabled' => false,
            ),
            'ai_settings' => array(
                'ai_model' => 'openai',
                'language' => 'english',
                'writing_tone' => 'default',
                'social_media_embed' => true,
            ),
            'post_scheduler' => array(
                'bulk_posting_enabled' => false,
                'post_schedule' => 'hourly',
                'randomize_schedule' => true,
                'auto_internal_linking' => true,
            ),
            'rss_settings' => array(
                'feeds' => array(),
                'rewrite_enabled' => true,
                'keyword_filter' => '',
            ),
            'image_settings' => array(
                'use_scraped_images' => true,
                'use_ai_images' => false,
            ),
        );

        // Add options to the WordPress database
        add_option('ai_news_scraper_options', $default_options);

        // Create necessary database tables for storing logs
        self::create_log_table();

        // Fire action to set up scheduled tasks
        do_action('ai_news_scraper_auto_blogger_pro_activate');
    }

    /**
     * Create the log table in the WordPress database
     */
    private static function create_log_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_news_scraper_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            action varchar(50) NOT NULL,
            source_url varchar(255) DEFAULT '',
            post_id bigint(20) DEFAULT NULL,
            status varchar(20) NOT NULL,
            message text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
