<?php
/**
 * Plugin Name: Uptown Trading Chatbot
 * Plugin URI: https://uptowntrading.com/
 * Description: Advanced AI-powered chatbot with SEO optimization, content generation, and trading insights for WordPress.
 * Version: 2.0.0
 * Author: Uptown Trading
 * Author URI: https://uptowntrading.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: uptown-trading-chatbot
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('UTC_PLUGIN_VERSION', '2.0.0');
define('UTC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('UTC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('UTC_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Uptown Trading Chatbot Class
 */
class UptownTradingChatbot {
    
    /**
     * Single instance of the plugin
     */
    private static $instance = null;
    
    /**
     * Plugin components
     */
    private $gemini_api;
    private $keyword_scoring;
    private $content_generator;
    private $image_generator;
    private $seo_optimizer;
    private $chatbot_integration;
    private $database_integration;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize the plugin
     */
    private function init() {
        // Load dependencies
        $this->load_dependencies();
        
        // Initialize components
        $this->init_components();
        
        // Setup hooks
        $this->setup_hooks();
        
        // Load admin interface
        if (is_admin()) {
            $this->load_admin();
        }
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Check if files exist before requiring them
        $required_files = array(
            'includes/class-gemini-api-integration.php',
            'includes/class-keyword-scoring.php',
            'includes/class-gemini-seo-content-generator.php',
            'includes/class-gemini-image-generator.php',
            'includes/class-advanced-seo-optimizer.php',
            'includes/class-trading-chatbot-integration.php',
            'includes/class-poshoq-database-integration.php',
            'includes/faq-endpoint.php'
        );

        foreach ($required_files as $file) {
            $file_path = UTC_PLUGIN_PATH . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                error_log("UTC: Missing required file $file_path");
            }
        }

        // Load compatibility layer
        $compatibility_file = UTC_PLUGIN_PATH . 'includes/compatibility.php';
        if (file_exists($compatibility_file)) {
            require_once $compatibility_file;
        } else {
            // Create basic compatibility if file doesn't exist
            $this->create_basic_compatibility();
        }
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        // Initialize components only if classes exist
        if (class_exists('UTC_Gemini_API_Integration')) {
            $this->gemini_api = new UTC_Gemini_API_Integration();
        } else {
            error_log('UTC: ERROR - UTC_Gemini_API_Integration class not found');
        }

        if (class_exists('UTC_Keyword_Scoring')) {
            $this->keyword_scoring = new UTC_Keyword_Scoring();
        } else {
            error_log('UTC: ERROR - UTC_Keyword_Scoring class not found');
        }

        if (class_exists('UTC_Gemini_SEO_Content_Generator')) {
            $this->content_generator = new UTC_Gemini_SEO_Content_Generator();
        } else {
            error_log('UTC: ERROR - UTC_Gemini_SEO_Content_Generator class not found');
        }

        if (class_exists('UTC_Gemini_Image_Generator')) {
            $this->image_generator = new UTC_Gemini_Image_Generator();
        } else {
            error_log('UTC: ERROR - UTC_Gemini_Image_Generator class not found');
        }

        if (class_exists('UTC_Advanced_SEO_Optimizer')) {
            $this->seo_optimizer = new UTC_Advanced_SEO_Optimizer();
        } else {
            error_log('UTC: ERROR - UTC_Advanced_SEO_Optimizer class not found');
        }

        if (class_exists('UTC_Trading_Chatbot_Integration')) {
            $this->chatbot_integration = new UTC_Trading_Chatbot_Integration();
        } else {
            error_log('UTC: ERROR - UTC_Trading_Chatbot_Integration class not found');
        }

        if (class_exists('UTC_Poshoq_Database_Integration')) {
            $this->database_integration = new UTC_Poshoq_Database_Integration();
        } else {
            error_log('UTC: ERROR - UTC_Poshoq_Database_Integration class not found');
        }
    }
    
    /**
     * Setup WordPress hooks
     */
    private function setup_hooks() {
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // WordPress hooks
        add_action('init', array($this, 'init_plugin'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // AJAX hooks
        $this->setup_ajax_hooks();

        // Shortcodes
        add_shortcode('uptown_chatbot', array($this, 'chatbot_shortcode'));

        // Frontend chatbot
        add_action('wp_footer', array($this, 'render_chatbot'));

        // Admin notices
        add_action('admin_notices', array($this, 'admin_notices'));
    }
    
    /**
     * Setup AJAX hooks
     */
    private function setup_ajax_hooks() {
        // Main content generation
        add_action('wp_ajax_utc_generate_content', array($this, 'ajax_generate_content'));
        add_action('wp_ajax_nopriv_utc_generate_content', array($this, 'ajax_generate_content'));
        
        // Keyword analysis
        add_action('wp_ajax_utc_analyze_keyword', array($this, 'ajax_analyze_keyword'));
        add_action('wp_ajax_nopriv_utc_analyze_keyword', array($this, 'ajax_analyze_keyword'));
        
        // API connection testing
        add_action('wp_ajax_utc_test_connection', array($this, 'ajax_test_connection'));
        
        // Language switching
        add_action('wp_ajax_utc_update_language', array($this, 'ajax_update_language'));
    }
    
    /**
     * Load admin interface
     */
    private function load_admin() {
        require_once UTC_PLUGIN_PATH . 'admin/admin-menu.php';
        new UTC_Admin_Menu();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables if needed
        $this->create_database_tables();

        // Set default options
        $this->set_default_options();

        // Set success notice
        set_transient('utc_show_success_notice', true, 30);

        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up temporary data
        $this->cleanup_temp_data();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Initialize plugin after WordPress is loaded
     */
    public function init_plugin() {
        // Load text domain for translations
        load_plugin_textdomain('uptown-trading-chatbot', false, dirname(UTC_PLUGIN_BASENAME) . '/languages');

        // Initialize chatbot
        if ($this->chatbot_integration) {
            $this->chatbot_integration->init_chatbot();
        }
    }

    /**
     * Display admin notices
     */
    public function admin_notices() {
        // Check if core files are missing
        $missing_files = array();
        $required_files = array(
            'includes/class-gemini-api-integration.php',
            'includes/class-keyword-scoring.php',
            'includes/class-gemini-seo-content-generator.php',
            'includes/class-advanced-seo-optimizer.php'
        );

        foreach ($required_files as $file) {
            if (!file_exists(UTC_PLUGIN_PATH . $file)) {
                $missing_files[] = $file;
            }
        }

        if (!empty($missing_files)) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>UptownTrading Chatbot:</strong> Some plugin files are missing. The plugin is running in limited mode.</p>';
            echo '<p>Missing files: ' . implode(', ', $missing_files) . '</p>';
            echo '<p>Please reinstall the plugin or contact support.</p>';
            echo '</div>';
        }

        // Show success notice if all files are present
        if (empty($missing_files) && get_transient('utc_show_success_notice')) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>UptownTrading Chatbot:</strong> Plugin activated successfully! All features are available.</p>';
            echo '<p><a href="' . admin_url('admin.php?page=uptown-trading-chatbot') . '">Configure your settings</a></p>';
            echo '</div>';
            delete_transient('utc_show_success_notice');
        }
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'utc-chatbot-js',
            UTC_PLUGIN_URL . 'assets/js/chatbot.js',
            array('jquery'),
            UTC_PLUGIN_VERSION,
            true
        );
        
        wp_enqueue_style(
            'utc-chatbot-css',
            UTC_PLUGIN_URL . 'assets/css/chatbot.css',
            array(),
            UTC_PLUGIN_VERSION
        );
        
        // Localize script for AJAX
        wp_localize_script('utc-chatbot-js', 'utc_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('utc_nonce'),
            'plugin_url' => UTC_PLUGIN_URL
        ));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'uptown-trading') === false) {
            return;
        }
        
        wp_enqueue_script(
            'utc-admin-js',
            UTC_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker'),
            UTC_PLUGIN_VERSION,
            true
        );
        
        wp_enqueue_style(
            'utc-admin-css',
            UTC_PLUGIN_URL . 'assets/css/admin.css',
            array('wp-color-picker'),
            UTC_PLUGIN_VERSION
        );
        
        // Localize admin script
        wp_localize_script('utc-admin-js', 'utc_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('utc_admin_nonce'),
            'plugin_url' => UTC_PLUGIN_URL
        ));
    }
    
    /**
     * Chatbot shortcode
     */
    public function chatbot_shortcode($atts) {
        $atts = shortcode_atts(array(
            'theme' => 'default',
            'position' => 'bottom-right',
            'size' => 'medium'
        ), $atts, 'uptown_chatbot');

        if ($this->chatbot_integration) {
            return $this->chatbot_integration->render_chatbot($atts);
        }

        // Return basic chatbot placeholder
        return '<div class="utc-chatbot-placeholder" style="background: #f8f9fa; border: 2px dashed #dee2e6; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0;">
            <h4 style="color: #667eea; margin: 0 0 10px 0;">🌿 UptownTrading Cannabis Assistant</h4>
            <p style="margin: 0 0 10px 0; color: #6c757d;">Specialized chatbot for cannabis accessories in Thailand</p>
            <p style="margin: 0; font-size: 14px; color: #6c757d;">Complete plugin installation to activate full chatbot functionality</p>
        </div>';
    }

    /**
     * Render basic chatbot widget in footer
     */
    public function render_chatbot() {
        if (!is_admin() && get_option('utc_chatbot_enabled', true)) {
            echo '<div id="utc-chatbot-widget" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; max-width: 300px;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); cursor: pointer;" onclick="this.style.display=\'none\'">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <h4 style="margin: 0; font-size: 16px;">🌿 UptownTrading</h4>
                        <span style="font-size: 18px; cursor: pointer;">&times;</span>
                    </div>
                    <p style="margin: 0; font-size: 14px; line-height: 1.4;">Cannabis accessories expert for Thailand</p>
                    <p style="margin: 5px 0 0 0; font-size: 12px; opacity: 0.8;">Complete installation to activate full chatbot</p>
                </div>
            </div>';
        }
    }
    
    /**
     * AJAX handler for content generation
     */
    public function ajax_generate_content() {
        check_ajax_referer('utc_nonce', 'nonce');
        
        $content_type = sanitize_text_field($_POST['content_type'] ?? '');
        $topic = sanitize_text_field($_POST['topic'] ?? '');
        $keywords = sanitize_text_field($_POST['keywords'] ?? '');
        
        if ($this->content_generator) {
            $result = $this->content_generator->generate_seo_content($content_type, $topic, $keywords);
            wp_send_json_success($result);
        }
        
        wp_send_json_error('Content generator not available');
    }
    
    /**
     * AJAX handler for keyword analysis
     */
    public function ajax_analyze_keyword() {
        check_ajax_referer('utc_nonce', 'nonce');
        
        $keyword = sanitize_text_field($_POST['keyword'] ?? '');
        
        if ($this->keyword_scoring && $keyword) {
            $result = $this->keyword_scoring->calculate_keyword_score($keyword);
            wp_send_json_success($result);
        }
        
        wp_send_json_error('Invalid keyword or scoring not available');
    }
    
    /**
     * AJAX handler for API connection testing
     */
    public function ajax_test_connection() {
        check_ajax_referer('utc_admin_nonce', 'nonce');
        
        if ($this->gemini_api) {
            $result = $this->gemini_api->test_connection();
            wp_send_json($result);
        }
        
        wp_send_json_error('API integration not available');
    }
    
    /**
     * AJAX handler for language switching
     */
    public function ajax_update_language() {
        check_ajax_referer('utc_admin_nonce', 'nonce');
        
        $language = sanitize_text_field($_POST['language'] ?? '');
        
        if ($language) {
            update_option('utc_language', $language);
            wp_send_json_success('Language updated');
        }
        
        wp_send_json_error('Invalid language');
    }
    
    /**
     * Create database tables
     */
    private function create_database_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Conversations table
        $table_name = $wpdb->prefix . 'utc_conversations';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            session_id varchar(255) NOT NULL,
            message text NOT NULL,
            response text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Keyword scores table
        $table_name = $wpdb->prefix . 'utc_keyword_scores';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            keyword varchar(255) NOT NULL,
            score decimal(5,2) NOT NULL,
            difficulty decimal(5,2) NOT NULL,
            volume int(11) DEFAULT NULL,
            competition decimal(3,2) DEFAULT NULL,
            analyzed_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY keyword (keyword)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $defaults = array(
            'utc_gemini_api_key' => '',
            'utc_language' => 'en',
            'utc_chatbot_enabled' => true,
            'utc_chatbot_theme' => 'default',
            'utc_seo_enabled' => true,
            'utc_analytics_enabled' => true
        );
        
        foreach ($defaults as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
    
    /**
     * Clean up temporary data
     */
    private function cleanup_temp_data() {
        // Clean up any temporary files or cache
        wp_cache_flush();
    }

    /**
     * Create basic compatibility if compatibility.php is missing
     */
    private function create_basic_compatibility() {
        // Create stub classes if main classes don't exist
        if (!class_exists('UTC_Gemini_API_Integration')) {
            class UTC_Gemini_API_Integration {
                public function __construct() {
                    error_log('UTC: Using stub UTC_Gemini_API_Integration class');
                }

                public function make_gemini_request($prompt) {
                    return array('success' => false, 'message' => 'Gemini API not available');
                }

                public function test_connection() {
                    return array('success' => false, 'message' => 'API integration not available');
                }
            }
        }

        if (!class_exists('UTC_Keyword_Scoring')) {
            class UTC_Keyword_Scoring {
                public function __construct() {
                    error_log('UTC: Using stub UTC_Keyword_Scoring class');
                }

                public function calculate_keyword_score($keyword, $options = array()) {
                    return array('success' => false, 'message' => 'Keyword scoring not available');
                }

                public function enhanced_keyword_analysis($keyword, $options = array()) {
                    return array('success' => false, 'message' => 'Enhanced keyword analysis not available');
                }
            }
        }

        if (!class_exists('UTC_Gemini_SEO_Content_Generator')) {
            class UTC_Gemini_SEO_Content_Generator {
                public function __construct() {
                    error_log('UTC: Using stub UTC_Gemini_SEO_Content_Generator class');
                }

                public function generate_blog_post($topic, $keywords, $options = array()) {
                    return array('success' => false, 'message' => 'Content generator not available');
                }

                public function generate_professional_blog_post($topic, $keywords, $options = array()) {
                    return array('success' => false, 'message' => 'Professional content generator not available');
                }

                public function generate_cannabis_product_description($product_data) {
                    return array('success' => false, 'message' => 'Cannabis product description generator not available');
                }
            }
        }

        if (!class_exists('UTC_Advanced_SEO_Optimizer')) {
            class UTC_Advanced_SEO_Optimizer {
                public function __construct() {
                    error_log('UTC: Using stub UTC_Advanced_SEO_Optimizer class');
                }

                public function comprehensive_site_audit($domain = '') {
                    return array('success' => false, 'message' => 'SEO optimizer not available');
                }

                public function optimize_product_pages($product_data) {
                    return array('success' => false, 'message' => 'Product optimization not available');
                }
            }
        }

        if (!class_exists('UTC_Trading_Chatbot_Integration')) {
            class UTC_Trading_Chatbot_Integration {
                public function __construct() {
                    error_log('UTC: Using stub UTC_Trading_Chatbot_Integration class');
                }

                public function init_chatbot() {
                    return false;
                }

                public function render_chatbot($atts) {
                    return '<div class="utc-chatbot-error">Chatbot not available - please check plugin installation</div>';
                }
            }
        }

        if (!class_exists('UTC_Gemini_Image_Generator')) {
            class UTC_Gemini_Image_Generator {
                public function __construct() {
                    error_log('UTC: Using stub UTC_Gemini_Image_Generator class');
                }

                public function generate_image($prompt, $options = array()) {
                    return array('success' => false, 'message' => 'Image generator not available');
                }
            }
        }

        if (!class_exists('UTC_Poshoq_Database_Integration')) {
            class UTC_Poshoq_Database_Integration {
                public function __construct() {
                    error_log('UTC: Using stub UTC_Poshoq_Database_Integration class');
                }

                public function sync_products() {
                    return array('success' => false, 'message' => 'Database integration not available');
                }
            }
        }

        // Backward compatibility aliases
        if (!class_exists('Uptown_Trading_Chatbot_Integration')) {
            class_alias('UTC_Trading_Chatbot_Integration', 'Uptown_Trading_Chatbot_Integration');
        }

        if (!class_exists('UTC_Enhanced_SEO_Blog_Generator')) {
            class_alias('UTC_Gemini_SEO_Content_Generator', 'UTC_Enhanced_SEO_Blog_Generator');
        }

        if (!class_exists('UTC_Professional_SEO_Blog_Generator')) {
            class_alias('UTC_Gemini_SEO_Content_Generator', 'UTC_Professional_SEO_Blog_Generator');
        }

        if (!class_exists('UTC_Enhanced_Keyword_Scoring')) {
            class_alias('UTC_Keyword_Scoring', 'UTC_Enhanced_Keyword_Scoring');
        }
    }
}

// Initialize the plugin
function uptown_trading_chatbot_init() {
    return UptownTradingChatbot::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'uptown_trading_chatbot_init');
