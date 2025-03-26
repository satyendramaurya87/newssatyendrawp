<?php
/**
 * Plugin Name:       AI News Scraper & Auto Blogger Pro
 * Plugin URI:        https://satyendramaurya.site/plugins/ai-news-scraper-auto-blogger-pro
 * Description:       A fully automated AI-powered news scraper & auto-blogging plugin for WordPress that fetches & rewrites articles using Multipage Visual Scraper, AI-generated content, auto-categories, auto-tags, auto-images, and scheduled posting.
 * Version:           1.0.0
 * Author:            Satyendra Maurya
 * Author URI:        https://satyendramaurya.site/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ai-news-scraper-auto-blogger-pro
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Current plugin version.
 */
define('AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_VERSION', '1.0.0');

/**
 * Plugin base path.
 */
define('AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_PATH', plugin_dir_path(__FILE__));

/**
 * Plugin base URL.
 */
define('AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_URL', plugin_dir_url(__FILE__));

/**
 * API endpoint for the Python Flask API.
 */
define('AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_API_URL', 'http://localhost:5000');

/**
 * The code that runs during plugin activation.
 */
function activate_ai_news_scraper_auto_blogger_pro() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-ai-news-scraper-auto-blogger-pro-activator.php';
    AI_News_Scraper_Auto_Blogger_Pro_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_ai_news_scraper_auto_blogger_pro() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-ai-news-scraper-auto-blogger-pro-deactivator.php';
    AI_News_Scraper_Auto_Blogger_Pro_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_ai_news_scraper_auto_blogger_pro');
register_deactivation_hook(__FILE__, 'deactivate_ai_news_scraper_auto_blogger_pro');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-ai-news-scraper-auto-blogger-pro.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_ai_news_scraper_auto_blogger_pro() {
    $plugin = new AI_News_Scraper_Auto_Blogger_Pro();
    $plugin->run();
}

run_ai_news_scraper_auto_blogger_pro();

/**
 * Add the Developer Credit to the footer with encryption
 * 
 * This is encrypted to prevent removal of the credit as per requirement
 */
function ai_news_scraper_auto_blogger_pro_add_footer_credit() {
    $credit = base64_decode('RGV2ZWxvcGVkIGJ5IFNhdHllbmRyYSBNYXVyeWEgfCBXZWJzaXRlOiBzYXR5ZW5kcmFtYXVyeWEuc2l0ZQ==');
    echo '<div class="ai-news-scraper-auto-blogger-pro-footer-credit" style="text-align: center; padding: 10px; margin-top: 20px;">' . $credit . '</div>';
}
add_action('wp_footer', 'ai_news_scraper_auto_blogger_pro_add_footer_credit');
add_action('admin_footer', 'ai_news_scraper_auto_blogger_pro_add_footer_credit');
