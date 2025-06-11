<?php
/**
 * Plugin Name: SEOForge MCP
 * Plugin URI: https://github.com/khiwniti/SEOForge-mcp-server
 * Description: WordPress plugin that integrates with SEOForge MCP server for AI-powered SEO content generation and analysis.
 * Version: 1.0.0
 * Author: SEOForge Team
 * License: MIT
 * Text Domain: seoforge-mcp
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SEOFORGE_MCP_VERSION', '1.0.0');
define('SEOFORGE_MCP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SEOFORGE_MCP_PLUGIN_PATH', plugin_dir_path(__FILE__));

class SEOForgeMCP {
    
    private $api_url;
    private $api_key;
    
    public function __construct() {
        $this->api_url = get_option('seoforge_mcp_api_url', 'https://your-domain.vercel.app');
        $this->api_key = get_option('seoforge_mcp_api_key', '');
        
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_seoforge_mcp_request', array($this, 'handle_ajax_request'));
        add_action('wp_ajax_nopriv_seoforge_mcp_request', array($this, 'handle_ajax_request'));
        
        // Add meta boxes for post editing
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_post_meta'));
        
        // Add REST API endpoints
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }
    
    public function init() {
        load_plugin_textdomain('seoforge-mcp', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('SEOForge MCP', 'seoforge-mcp'),
            __('SEOForge MCP', 'seoforge-mcp'),
            'manage_options',
            'seoforge-mcp',
            array($this, 'admin_page'),
            'dashicons-chart-line',
            30
        );
        
        add_submenu_page(
            'seoforge-mcp',
            __('Settings', 'seoforge-mcp'),
            __('Settings', 'seoforge-mcp'),
            'manage_options',
            'seoforge-mcp-settings',
            array($this, 'settings_page')
        );
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script(
            'seoforge-mcp-frontend',
            SEOFORGE_MCP_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            SEOFORGE_MCP_VERSION,
            true
        );
        
        wp_localize_script('seoforge-mcp-frontend', 'seoforge_mcp_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('seoforge_mcp_nonce'),
            'api_url' => $this->api_url
        ));
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'seoforge-mcp') !== false || $hook === 'post.php' || $hook === 'post-new.php') {
            wp_enqueue_script(
                'seoforge-mcp-admin',
                SEOFORGE_MCP_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                SEOFORGE_MCP_VERSION,
                true
            );
            
            wp_enqueue_style(
                'seoforge-mcp-admin',
                SEOFORGE_MCP_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                SEOFORGE_MCP_VERSION
            );
            
            wp_localize_script('seoforge-mcp-admin', 'seoforge_mcp_admin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('seoforge_mcp_nonce'),
                'api_url' => $this->api_url,
                'strings' => array(
                    'generating' => __('Generating content...', 'seoforge-mcp'),
                    'analyzing' => __('Analyzing SEO...', 'seoforge-mcp'),
                    'error' => __('An error occurred. Please try again.', 'seoforge-mcp')
                )
            ));
        }
    }
    
    public function add_meta_boxes() {
        add_meta_box(
            'seoforge-mcp-content-generator',
            __('SEOForge Content Generator', 'seoforge-mcp'),
            array($this, 'content_generator_meta_box'),
            array('post', 'page'),
            'side',
            'high'
        );
        
        add_meta_box(
            'seoforge-mcp-seo-analysis',
            __('SEOForge SEO Analysis', 'seoforge-mcp'),
            array($this, 'seo_analysis_meta_box'),
            array('post', 'page'),
            'side',
            'high'
        );
    }
    
    public function content_generator_meta_box($post) {
        wp_nonce_field('seoforge_mcp_meta_box', 'seoforge_mcp_meta_box_nonce');
        ?>
        <div id="seoforge-content-generator">
            <p>
                <label for="seoforge-keywords"><?php _e('Keywords:', 'seoforge-mcp'); ?></label>
                <input type="text" id="seoforge-keywords" name="seoforge_keywords" 
                       value="<?php echo esc_attr(get_post_meta($post->ID, '_seoforge_keywords', true)); ?>" 
                       placeholder="<?php _e('Enter keywords separated by commas', 'seoforge-mcp'); ?>" />
            </p>
            <p>
                <label for="seoforge-industry"><?php _e('Industry:', 'seoforge-mcp'); ?></label>
                <select id="seoforge-industry" name="seoforge_industry">
                    <option value=""><?php _e('Select Industry', 'seoforge-mcp'); ?></option>
                    <option value="technology"><?php _e('Technology', 'seoforge-mcp'); ?></option>
                    <option value="healthcare"><?php _e('Healthcare', 'seoforge-mcp'); ?></option>
                    <option value="finance"><?php _e('Finance', 'seoforge-mcp'); ?></option>
                    <option value="education"><?php _e('Education', 'seoforge-mcp'); ?></option>
                    <option value="retail"><?php _e('Retail', 'seoforge-mcp'); ?></option>
                </select>
            </p>
            <p>
                <button type="button" id="generate-content-btn" class="button button-primary">
                    <?php _e('Generate Content', 'seoforge-mcp'); ?>
                </button>
                <button type="button" id="generate-suggestions-btn" class="button">
                    <?php _e('Get Suggestions', 'seoforge-mcp'); ?>
                </button>
            </p>
            <div id="seoforge-content-result" style="display:none;">
                <h4><?php _e('Generated Content:', 'seoforge-mcp'); ?></h4>
                <div id="generated-content"></div>
            </div>
        </div>
        <?php
    }
    
    public function seo_analysis_meta_box($post) {
        ?>
        <div id="seoforge-seo-analysis">
            <p>
                <button type="button" id="analyze-seo-btn" class="button button-primary">
                    <?php _e('Analyze SEO', 'seoforge-mcp'); ?>
                </button>
            </p>
            <div id="seoforge-seo-result" style="display:none;">
                <h4><?php _e('SEO Analysis:', 'seoforge-mcp'); ?></h4>
                <div id="seo-analysis-content"></div>
            </div>
        </div>
        <?php
    }
    
    public function save_post_meta($post_id) {
        if (!isset($_POST['seoforge_mcp_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['seoforge_mcp_meta_box_nonce'], 'seoforge_mcp_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        if (isset($_POST['seoforge_keywords'])) {
            update_post_meta($post_id, '_seoforge_keywords', sanitize_text_field($_POST['seoforge_keywords']));
        }
        
        if (isset($_POST['seoforge_industry'])) {
            update_post_meta($post_id, '_seoforge_industry', sanitize_text_field($_POST['seoforge_industry']));
        }
    }
    
    public function handle_ajax_request() {
        check_ajax_referer('seoforge_mcp_nonce', 'nonce');
        
        $action = sanitize_text_field($_POST['seoforge_action']);
        $data = $_POST['data'];
        
        $response = $this->make_api_request($action, $data);
        
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        } else {
            wp_send_json_success($response);
        }
    }
    
    public function register_rest_routes() {
        register_rest_route('seoforge-mcp/v1', '/generate', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_generate_content'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route('seoforge-mcp/v1', '/analyze', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_analyze_seo'),
            'permission_callback' => array($this, 'check_permissions')
        ));
    }
    
    public function check_permissions() {
        return current_user_can('edit_posts');
    }
    
    public function rest_generate_content($request) {
        $params = $request->get_json_params();
        return $this->make_api_request('generate_content', $params);
    }
    
    public function rest_analyze_seo($request) {
        $params = $request->get_json_params();
        return $this->make_api_request('analyze_seo', $params);
    }
    
    private function make_api_request($action, $data) {
        $url = $this->api_url . '/wordpress/plugin';
        
        $headers = array(
            'Content-Type' => 'application/json',
            'X-WordPress-Key' => $this->api_key,
            'X-WordPress-Site' => get_site_url(),
            'X-WordPress-Nonce' => $this->generate_nonce(),
            'X-WordPress-Timestamp' => time()
        );
        
        $body = json_encode(array(
            'action' => $action,
            'data' => $data,
            'site_url' => get_site_url(),
            'user_id' => get_current_user_id()
        ));
        
        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => $body,
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', 'Invalid JSON response');
        }
        
        return $data;
    }
    
    private function generate_nonce() {
        $site_url = get_site_url();
        $timestamp = time();
        return hash('sha256', $site_url . ':' . $timestamp);
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('SEOForge MCP Dashboard', 'seoforge-mcp'); ?></h1>
            <div id="seoforge-dashboard">
                <div class="seoforge-card">
                    <h2><?php _e('Content Generation', 'seoforge-mcp'); ?></h2>
                    <p><?php _e('Generate SEO-optimized content for your posts and pages.', 'seoforge-mcp'); ?></p>
                    <a href="<?php echo admin_url('post-new.php'); ?>" class="button button-primary">
                        <?php _e('Create New Post', 'seoforge-mcp'); ?>
                    </a>
                </div>
                
                <div class="seoforge-card">
                    <h2><?php _e('SEO Analysis', 'seoforge-mcp'); ?></h2>
                    <p><?php _e('Analyze your existing content for SEO optimization.', 'seoforge-mcp'); ?></p>
                    <a href="<?php echo admin_url('edit.php'); ?>" class="button button-primary">
                        <?php _e('View Posts', 'seoforge-mcp'); ?>
                    </a>
                </div>
                
                <div class="seoforge-card">
                    <h2><?php _e('Keyword Research', 'seoforge-mcp'); ?></h2>
                    <p><?php _e('Research keywords for your content strategy.', 'seoforge-mcp'); ?></p>
                    <button id="keyword-research-btn" class="button button-primary">
                        <?php _e('Start Research', 'seoforge-mcp'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function settings_page() {
        if (isset($_POST['submit'])) {
            update_option('seoforge_mcp_api_url', sanitize_url($_POST['api_url']));
            update_option('seoforge_mcp_api_key', sanitize_text_field($_POST['api_key']));
            update_option('seoforge_mcp_default_language', sanitize_text_field($_POST['default_language']));
            update_option('seoforge_mcp_auto_generate', isset($_POST['auto_generate']));
            
            echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'seoforge-mcp') . '</p></div>';
        }
        
        $api_url = get_option('seoforge_mcp_api_url', 'https://your-domain.vercel.app');
        $api_key = get_option('seoforge_mcp_api_key', '');
        $default_language = get_option('seoforge_mcp_default_language', 'en');
        $auto_generate = get_option('seoforge_mcp_auto_generate', false);
        ?>
        <div class="wrap">
            <h1><?php _e('SEOForge MCP Settings', 'seoforge-mcp'); ?></h1>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('API URL', 'seoforge-mcp'); ?></th>
                        <td>
                            <input type="url" name="api_url" value="<?php echo esc_attr($api_url); ?>" class="regular-text" required />
                            <p class="description"><?php _e('The URL of your SEOForge MCP server.', 'seoforge-mcp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('API Key', 'seoforge-mcp'); ?></th>
                        <td>
                            <input type="password" name="api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
                            <p class="description"><?php _e('Your API key for authentication.', 'seoforge-mcp'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Default Language', 'seoforge-mcp'); ?></th>
                        <td>
                            <select name="default_language">
                                <option value="en" <?php selected($default_language, 'en'); ?>><?php _e('English', 'seoforge-mcp'); ?></option>
                                <option value="th" <?php selected($default_language, 'th'); ?>><?php _e('Thai', 'seoforge-mcp'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Auto Generate', 'seoforge-mcp'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="auto_generate" <?php checked($auto_generate); ?> />
                                <?php _e('Automatically generate content suggestions', 'seoforge-mcp'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

// Initialize the plugin
new SEOForgeMCP();

// Activation hook
register_activation_hook(__FILE__, 'seoforge_mcp_activate');
function seoforge_mcp_activate() {
    // Set default options
    add_option('seoforge_mcp_api_url', 'https://your-domain.vercel.app');
    add_option('seoforge_mcp_default_language', 'en');
    add_option('seoforge_mcp_auto_generate', false);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'seoforge_mcp_deactivate');
function seoforge_mcp_deactivate() {
    // Clean up if needed
}
?>
