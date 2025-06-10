<?php
/**
 * Plugin Name: Universal MCP Server Integration
 * Plugin URI: https://universal-mcp.com/
 * Description: Universal Model Context Protocol server integration for AI-powered content generation, SEO optimization, and industry-specific tools.
 * Version: 1.0.0
 * Author: Universal MCP Platform
 * Author URI: https://universal-mcp.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: universal-mcp
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
define('UMCP_PLUGIN_VERSION', '1.0.0');
define('UMCP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('UMCP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('UMCP_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Universal MCP Plugin Class
 */
class UniversalMCPPlugin {
    
    /**
     * Single instance of the plugin
     */
    private static $instance = null;
    
    /**
     * Plugin components
     */
    private $mcp_client;
    private $content_generator;
    private $seo_optimizer;
    private $industry_manager;
    private $admin_interface;
    
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
        $required_files = array(
            'includes/class-mcp-client.php',
            'includes/class-content-generator.php',
            'includes/class-seo-optimizer.php',
            'includes/class-industry-manager.php',
            'includes/class-admin-interface.php',
            'includes/class-shortcodes.php',
            'includes/class-rest-api.php'
        );

        foreach ($required_files as $file) {
            $file_path = UMCP_PLUGIN_PATH . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                error_log("UMCP: Missing required file $file_path");
            }
        }
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        if (class_exists('UMCP_MCP_Client')) {
            $this->mcp_client = new UMCP_MCP_Client();
        }

        if (class_exists('UMCP_Content_Generator')) {
            $this->content_generator = new UMCP_Content_Generator($this->mcp_client);
        }

        if (class_exists('UMCP_SEO_Optimizer')) {
            $this->seo_optimizer = new UMCP_SEO_Optimizer($this->mcp_client);
        }

        if (class_exists('UMCP_Industry_Manager')) {
            $this->industry_manager = new UMCP_Industry_Manager($this->mcp_client);
        }

        if (class_exists('UMCP_Admin_Interface')) {
            $this->admin_interface = new UMCP_Admin_Interface($this->mcp_client);
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
        add_shortcode('umcp_content_generator', array($this, 'content_generator_shortcode'));
        add_shortcode('umcp_seo_analyzer', array($this, 'seo_analyzer_shortcode'));
        add_shortcode('umcp_industry_tools', array($this, 'industry_tools_shortcode'));

        // REST API
        add_action('rest_api_init', array($this, 'register_rest_routes'));

        // Admin notices
        add_action('admin_notices', array($this, 'admin_notices'));
    }
    
    /**
     * Setup AJAX hooks
     */
    private function setup_ajax_hooks() {
        // Content generation
        add_action('wp_ajax_umcp_generate_content', array($this, 'ajax_generate_content'));
        add_action('wp_ajax_nopriv_umcp_generate_content', array($this, 'ajax_generate_content'));
        
        // SEO analysis
        add_action('wp_ajax_umcp_analyze_seo', array($this, 'ajax_analyze_seo'));
        add_action('wp_ajax_nopriv_umcp_analyze_seo', array($this, 'ajax_analyze_seo'));
        
        // Industry tools
        add_action('wp_ajax_umcp_execute_industry_tool', array($this, 'ajax_execute_industry_tool'));
        
        // MCP server connection testing
        add_action('wp_ajax_umcp_test_connection', array($this, 'ajax_test_connection'));
        
        // Industry switching
        add_action('wp_ajax_umcp_switch_industry', array($this, 'ajax_switch_industry'));
    }
    
    /**
     * Load admin interface
     */
    private function load_admin() {
        if ($this->admin_interface) {
            $this->admin_interface->init();
        }
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
        set_transient('umcp_show_success_notice', true, 30);

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
        load_plugin_textdomain('universal-mcp', false, dirname(UMCP_PLUGIN_BASENAME) . '/languages');

        // Initialize REST API
        if (class_exists('UMCP_REST_API')) {
            new UMCP_REST_API($this->mcp_client);
        }

        // Initialize shortcodes
        if (class_exists('UMCP_Shortcodes')) {
            new UMCP_Shortcodes($this->mcp_client);
        }
    }

    /**
     * Display admin notices
     */
    public function admin_notices() {
        // Check MCP server connection
        if (!$this->is_mcp_server_configured()) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>Universal MCP Plugin:</strong> MCP server is not configured. Please configure your server settings.</p>';
            echo '<p><a href="' . admin_url('admin.php?page=universal-mcp-settings') . '">Configure Settings</a></p>';
            echo '</div>';
        }

        // Show success notice
        if (get_transient('umcp_show_success_notice')) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>Universal MCP Plugin:</strong> Plugin activated successfully! All features are available.</p>';
            echo '<p><a href="' . admin_url('admin.php?page=universal-mcp') . '">Get Started</a></p>';
            echo '</div>';
            delete_transient('umcp_show_success_notice');
        }
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'umcp-frontend-js',
            UMCP_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            UMCP_PLUGIN_VERSION,
            true
        );
        
        wp_enqueue_style(
            'umcp-frontend-css',
            UMCP_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            UMCP_PLUGIN_VERSION
        );
        
        // Localize script for AJAX
        wp_localize_script('umcp-frontend-js', 'umcp_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('umcp_nonce'),
            'plugin_url' => UMCP_PLUGIN_URL,
            'mcp_server_url' => get_option('umcp_server_url', ''),
            'current_industry' => get_option('umcp_current_industry', 'general')
        ));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'universal-mcp') === false) {
            return;
        }
        
        wp_enqueue_script(
            'umcp-admin-js',
            UMCP_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker'),
            UMCP_PLUGIN_VERSION,
            true
        );
        
        wp_enqueue_style(
            'umcp-admin-css',
            UMCP_PLUGIN_URL . 'assets/css/admin.css',
            array('wp-color-picker'),
            UMCP_PLUGIN_VERSION
        );
        
        // Localize admin script
        wp_localize_script('umcp-admin-js', 'umcp_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('umcp_admin_nonce'),
            'plugin_url' => UMCP_PLUGIN_URL,
            'mcp_server_url' => get_option('umcp_server_url', ''),
            'api_key' => get_option('umcp_api_key', '')
        ));
    }
    
    /**
     * Content generator shortcode
     */
    public function content_generator_shortcode($atts) {
        $atts = shortcode_atts(array(
            'industry' => get_option('umcp_current_industry', 'general'),
            'content_type' => 'blog_post',
            'language' => 'en',
            'show_form' => 'true'
        ), $atts, 'umcp_content_generator');

        if ($this->content_generator) {
            return $this->content_generator->render_shortcode($atts);
        }

        return '<div class="umcp-error">Content generator not available</div>';
    }

    /**
     * SEO analyzer shortcode
     */
    public function seo_analyzer_shortcode($atts) {
        $atts = shortcode_atts(array(
            'industry' => get_option('umcp_current_industry', 'general'),
            'show_form' => 'true'
        ), $atts, 'umcp_seo_analyzer');

        if ($this->seo_optimizer) {
            return $this->seo_optimizer->render_shortcode($atts);
        }

        return '<div class="umcp-error">SEO analyzer not available</div>';
    }

    /**
     * Industry tools shortcode
     */
    public function industry_tools_shortcode($atts) {
        $atts = shortcode_atts(array(
            'industry' => get_option('umcp_current_industry', 'general'),
            'tools' => 'all'
        ), $atts, 'umcp_industry_tools');

        if ($this->industry_manager) {
            return $this->industry_manager->render_shortcode($atts);
        }

        return '<div class="umcp-error">Industry tools not available</div>';
    }
    
    /**
     * AJAX handler for content generation
     */
    public function ajax_generate_content() {
        check_ajax_referer('umcp_nonce', 'nonce');
        
        $content_type = sanitize_text_field($_POST['content_type'] ?? 'blog_post');
        $topic = sanitize_text_field($_POST['topic'] ?? '');
        $keywords = array_map('sanitize_text_field', $_POST['keywords'] ?? []);
        $industry = sanitize_text_field($_POST['industry'] ?? 'general');
        $language = sanitize_text_field($_POST['language'] ?? 'en');
        
        if ($this->content_generator) {
            $result = $this->content_generator->generate_content($content_type, $topic, $keywords, $industry, $language);
            wp_send_json_success($result);
        }
        
        wp_send_json_error('Content generator not available');
    }
    
    /**
     * AJAX handler for SEO analysis
     */
    public function ajax_analyze_seo() {
        check_ajax_referer('umcp_nonce', 'nonce');
        
        $url = esc_url_raw($_POST['url'] ?? '');
        $content = wp_kses_post($_POST['content'] ?? '');
        $keywords = array_map('sanitize_text_field', $_POST['keywords'] ?? []);
        $industry = sanitize_text_field($_POST['industry'] ?? 'general');
        
        if ($this->seo_optimizer) {
            $result = $this->seo_optimizer->analyze_seo($url, $content, $keywords, $industry);
            wp_send_json_success($result);
        }
        
        wp_send_json_error('SEO optimizer not available');
    }
    
    /**
     * AJAX handler for industry tool execution
     */
    public function ajax_execute_industry_tool() {
        check_ajax_referer('umcp_admin_nonce', 'nonce');
        
        $tool_name = sanitize_text_field($_POST['tool_name'] ?? '');
        $parameters = $_POST['parameters'] ?? [];
        $industry = sanitize_text_field($_POST['industry'] ?? 'general');
        
        if ($this->industry_manager) {
            $result = $this->industry_manager->execute_tool($tool_name, $parameters, $industry);
            wp_send_json_success($result);
        }
        
        wp_send_json_error('Industry manager not available');
    }
    
    /**
     * AJAX handler for MCP server connection testing
     */
    public function ajax_test_connection() {
        check_ajax_referer('umcp_admin_nonce', 'nonce');
        
        if ($this->mcp_client) {
            $result = $this->mcp_client->test_connection();
            wp_send_json($result);
        }
        
        wp_send_json_error('MCP client not available');
    }
    
    /**
     * AJAX handler for industry switching
     */
    public function ajax_switch_industry() {
        check_ajax_referer('umcp_admin_nonce', 'nonce');
        
        $industry = sanitize_text_field($_POST['industry'] ?? 'general');
        
        if ($industry) {
            update_option('umcp_current_industry', $industry);
            wp_send_json_success('Industry switched to ' . $industry);
        }
        
        wp_send_json_error('Invalid industry');
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('universal-mcp/v1', '/generate-content', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_generate_content'),
            'permission_callback' => array($this, 'rest_permission_check')
        ));

        register_rest_route('universal-mcp/v1', '/analyze-seo', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_analyze_seo'),
            'permission_callback' => array($this, 'rest_permission_check')
        ));

        register_rest_route('universal-mcp/v1', '/industry-tools/(?P<tool>[a-zA-Z0-9_-]+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_execute_industry_tool'),
            'permission_callback' => array($this, 'rest_permission_check')
        ));
    }

    /**
     * REST API permission check
     */
    public function rest_permission_check() {
        return current_user_can('edit_posts');
    }

    /**
     * REST API content generation endpoint
     */
    public function rest_generate_content($request) {
        $parameters = $request->get_json_params();
        
        if ($this->content_generator) {
            return $this->content_generator->generate_content(
                $parameters['content_type'] ?? 'blog_post',
                $parameters['topic'] ?? '',
                $parameters['keywords'] ?? [],
                $parameters['industry'] ?? 'general',
                $parameters['language'] ?? 'en'
            );
        }
        
        return new WP_Error('not_available', 'Content generator not available', array('status' => 503));
    }

    /**
     * REST API SEO analysis endpoint
     */
    public function rest_analyze_seo($request) {
        $parameters = $request->get_json_params();
        
        if ($this->seo_optimizer) {
            return $this->seo_optimizer->analyze_seo(
                $parameters['url'] ?? '',
                $parameters['content'] ?? '',
                $parameters['keywords'] ?? [],
                $parameters['industry'] ?? 'general'
            );
        }
        
        return new WP_Error('not_available', 'SEO optimizer not available', array('status' => 503));
    }

    /**
     * REST API industry tool execution endpoint
     */
    public function rest_execute_industry_tool($request) {
        $tool_name = $request['tool'];
        $parameters = $request->get_json_params();
        
        if ($this->industry_manager) {
            return $this->industry_manager->execute_tool(
                $tool_name,
                $parameters['parameters'] ?? [],
                $parameters['industry'] ?? 'general'
            );
        }
        
        return new WP_Error('not_available', 'Industry manager not available', array('status' => 503));
    }
    
    /**
     * Create database tables
     */
    private function create_database_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // MCP requests log table
        $table_name = $wpdb->prefix . 'umcp_requests';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            tool_name varchar(100) NOT NULL,
            parameters text NOT NULL,
            response text NOT NULL,
            industry varchar(50) DEFAULT 'general',
            execution_time float DEFAULT 0,
            success tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY tool_name (tool_name),
            KEY user_id (user_id),
            KEY industry (industry)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Industry templates table
        $table_name = $wpdb->prefix . 'umcp_industry_templates';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            industry varchar(50) NOT NULL,
            template_data text NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY industry (industry),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $defaults = array(
            'umcp_server_url' => '',
            'umcp_api_key' => '',
            'umcp_current_industry' => 'general',
            'umcp_default_language' => 'en',
            'umcp_cache_enabled' => true,
            'umcp_cache_duration' => 3600,
            'umcp_rate_limit' => 100,
            'umcp_debug_mode' => false
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
        
        // Clean up old request logs (older than 30 days)
        global $wpdb;
        $table_name = $wpdb->prefix . 'umcp_requests';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
        ));
    }

    /**
     * Check if MCP server is configured
     */
    private function is_mcp_server_configured() {
        $server_url = get_option('umcp_server_url', '');
        $api_key = get_option('umcp_api_key', '');
        
        return !empty($server_url) && !empty($api_key);
    }
}

// Initialize the plugin
function universal_mcp_plugin_init() {
    return UniversalMCPPlugin::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'universal_mcp_plugin_init');
