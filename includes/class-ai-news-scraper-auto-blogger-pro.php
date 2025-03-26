<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      1.0.0
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/includes
 * @author     Satyendra Maurya <contact@satyendramaurya.site>
 */
class AI_News_Scraper_Auto_Blogger_Pro {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      AI_News_Scraper_Auto_Blogger_Pro_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_VERSION')) {
            $this->version = AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'ai-news-scraper-auto-blogger-pro';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_cron_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - AI_News_Scraper_Auto_Blogger_Pro_Loader. Orchestrates the hooks of the plugin.
     * - AI_News_Scraper_Auto_Blogger_Pro_i18n. Defines internationalization functionality.
     * - AI_News_Scraper_Auto_Blogger_Pro_Admin. Defines all hooks for the admin area.
     * - AI_News_Scraper_Auto_Blogger_Pro_Scraper. Handles web scraping functionality.
     * - AI_News_Scraper_Auto_Blogger_Pro_AI. Handles AI content generation.
     * - AI_News_Scraper_Auto_Blogger_Pro_Scheduler. Handles scheduling of posts.
     * - AI_News_Scraper_Auto_Blogger_Pro_RSS. Handles RSS feed processing.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_PATH . 'includes/class-ai-news-scraper-auto-blogger-pro-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_PATH . 'includes/class-ai-news-scraper-auto-blogger-pro-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_PATH . 'admin/class-ai-news-scraper-auto-blogger-pro-admin.php';

        /**
         * The class responsible for handling web scraping functionality.
         */
        require_once AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_PATH . 'includes/class-ai-news-scraper-auto-blogger-pro-scraper.php';

        /**
         * The class responsible for handling AI content generation.
         */
        require_once AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_PATH . 'includes/class-ai-news-scraper-auto-blogger-pro-ai.php';

        /**
         * The class responsible for handling scheduling of posts.
         */
        require_once AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_PATH . 'includes/class-ai-news-scraper-auto-blogger-pro-scheduler.php';

        /**
         * The class responsible for handling RSS feed processing.
         */
        require_once AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_PATH . 'includes/class-ai-news-scraper-auto-blogger-pro-rss.php';

        $this->loader = new AI_News_Scraper_Auto_Blogger_Pro_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the AI_News_Scraper_Auto_Blogger_Pro_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new AI_News_Scraper_Auto_Blogger_Pro_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new AI_News_Scraper_Auto_Blogger_Pro_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        
        // Add AJAX actions
        $this->loader->add_action('wp_ajax_scrape_article', $plugin_admin, 'ajax_scrape_article');
        $this->loader->add_action('wp_ajax_generate_content', $plugin_admin, 'ajax_generate_content');
        $this->loader->add_action('wp_ajax_publish_post', $plugin_admin, 'ajax_publish_post');
        $this->loader->add_action('wp_ajax_schedule_post', $plugin_admin, 'ajax_schedule_post');
        $this->loader->add_action('wp_ajax_add_rss_feed', $plugin_admin, 'ajax_add_rss_feed');
        $this->loader->add_action('wp_ajax_get_logs', $plugin_admin, 'ajax_get_logs');
        $this->loader->add_action('wp_ajax_save_settings', $plugin_admin, 'ajax_save_settings');
    }

    /**
     * Register all of the hooks related to the cron jobs for scheduled tasks
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_cron_hooks() {
        $scraper = new AI_News_Scraper_Auto_Blogger_Pro_Scraper();
        $ai = new AI_News_Scraper_Auto_Blogger_Pro_AI();
        $scheduler = new AI_News_Scraper_Auto_Blogger_Pro_Scheduler();
        $rss = new AI_News_Scraper_Auto_Blogger_Pro_RSS();

        // Register the cron schedules
        $this->loader->add_filter('cron_schedules', $scheduler, 'add_cron_schedules');
        
        // Register cron hooks
        $this->loader->add_action('ai_news_scraper_process_scheduled_posts', $scheduler, 'process_scheduled_posts');
        $this->loader->add_action('ai_news_scraper_process_rss_feeds', $rss, 'process_rss_feeds');
        
        // Register activation/deactivation hooks for cron
        $this->loader->add_action('ai_news_scraper_auto_blogger_pro_activate', $scheduler, 'schedule_events');
        $this->loader->add_action('ai_news_scraper_auto_blogger_pro_deactivate', $scheduler, 'clear_scheduled_events');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    AI_News_Scraper_Auto_Blogger_Pro_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}

/**
 * The class responsible for orchestrating the actions and filters of the
 * core plugin.
 */
class AI_News_Scraper_Auto_Blogger_Pro_Loader {

    /**
     * The array of actions registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
     */
    protected $actions;

    /**
     * The array of filters registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
     */
    protected $filters;

    /**
     * Initialize the collections used to maintain the actions and filters.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
    }

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @param    string               $hook             The name of the WordPress action that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the action is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @param    string               $hook             The name of the WordPress filter that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the filter is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * A utility function that is used to register the actions and hooks into a single
     * collection.
     *
     * @since    1.0.0
     * @access   private
     * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
     * @param    string               $hook             The name of the WordPress filter that is being registered.
     * @param    object               $component        A reference to the instance of the object on which the filter is defined.
     * @param    string               $callback         The name of the function definition on the $component.
     * @param    int                  $priority         The priority at which the function should be fired.
     * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
     * @return   array                                  The collection of actions and filters registered with WordPress.
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    /**
     * Register the filters and actions with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }

        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }
    }
}

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/includes
 * @author     Satyendra Maurya <contact@satyendramaurya.site>
 */
class AI_News_Scraper_Auto_Blogger_Pro_i18n {
    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'ai-news-scraper-auto-blogger-pro',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
