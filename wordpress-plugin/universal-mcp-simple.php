<?php
/**
 * Plugin Name: Universal MCP Server - Simple
 * Plugin URI: https://github.com/khiwniti/SEOForge-mcp-server
 * Description: Simple Universal MCP Server integration for WordPress with AI content generation and chatbot.
 * Version: 3.0.0-simple
 * Author: SEOForge Team
 * License: GPL v2 or later
 * Text Domain: universal-mcp
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('UMCP_SIMPLE_VERSION', '3.0.0-simple');
define('UMCP_SIMPLE_URL', plugin_dir_url(__FILE__));
define('UMCP_SIMPLE_PATH', plugin_dir_path(__FILE__));

/**
 * Main Universal MCP Simple Plugin Class
 */
class UniversalMCPSimple {
    
    private static $instance = null;
    private $mcp_client;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init();
    }
    
    private function init() {
        // Load MCP client
        require_once UMCP_SIMPLE_PATH . 'includes/class-mcp-client.php';
        $this->mcp_client = new UMCP_MCP_Client();
        
        // Setup hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_ajax_umcp_test_connection', array($this, 'ajax_test_connection'));
        add_action('wp_ajax_umcp_generate_content', array($this, 'ajax_generate_content'));
        add_action('wp_ajax_umcp_analyze_seo', array($this, 'ajax_analyze_seo'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'admin_notices'));
        
        // Activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));
    }
    
    public function activate() {
        // Set default options
        add_option('umcp_server_url', 'https://seoforge-mcp-server.onrender.com');
        add_option('umcp_api_key', '');
        
        // Show success notice
        set_transient('umcp_activation_notice', true, 30);
    }
    
    public function admin_notices() {
        if (get_transient('umcp_activation_notice')) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>Universal MCP Plugin activated!</strong> <a href="' . admin_url('admin.php?page=universal-mcp') . '">Configure settings</a></p>';
            echo '</div>';
            delete_transient('umcp_activation_notice');
        }
        
        // Check if server is configured
        $server_url = get_option('umcp_server_url', '');
        if (empty($server_url) && isset($_GET['page']) && $_GET['page'] !== 'universal-mcp') {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>Universal MCP:</strong> Please <a href="' . admin_url('admin.php?page=universal-mcp') . '">configure your server settings</a>.</p>';
            echo '</div>';
        }
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Universal MCP',
            'Universal MCP',
            'manage_options',
            'universal-mcp',
            array($this, 'admin_page'),
            'dashicons-robot',
            30
        );
    }
    
    public function register_settings() {
        register_setting('umcp_settings', 'umcp_server_url');
        register_setting('umcp_settings', 'umcp_api_key');
    }
    
    public function admin_scripts($hook) {
        if ($hook !== 'toplevel_page_universal-mcp') {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'umcp-admin',
            UMCP_SIMPLE_URL . 'assets/js/admin.js',
            array('jquery'),
            UMCP_SIMPLE_VERSION,
            true
        );
        
        wp_enqueue_style(
            'umcp-admin',
            UMCP_SIMPLE_URL . 'assets/css/admin.css',
            array(),
            UMCP_SIMPLE_VERSION
        );
        
        wp_localize_script('umcp-admin', 'umcp_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('umcp_nonce'),
            'server_url' => get_option('umcp_server_url', '')
        ));
    }
    
    public function admin_page() {
        $server_url = get_option('umcp_server_url', 'https://seoforge-mcp-server.onrender.com');
        $api_key = get_option('umcp_api_key', '');
        
        // Handle form submission
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'umcp_settings')) {
            update_option('umcp_server_url', sanitize_url($_POST['umcp_server_url']));
            update_option('umcp_api_key', sanitize_text_field($_POST['umcp_api_key']));
            echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
            $server_url = get_option('umcp_server_url');
            $api_key = get_option('umcp_api_key');
        }
        
        ?>
        <div class="wrap">
            <h1>🤖 Universal MCP Server</h1>
            
            <div class="umcp-admin-page">
                <!-- Server Status -->
                <div class="umcp-card">
                    <h2>🌐 Server Status</h2>
                    <p>Server URL: <strong><?php echo esc_html($server_url); ?></strong></p>
                    <p>Status: <span id="umcp-status" class="umcp-status">Checking...</span></p>
                    <button type="button" id="umcp-test-connection" class="umcp-button">Test Connection</button>
                </div>
                
                <!-- Settings -->
                <div class="umcp-card">
                    <h2>⚙️ Settings</h2>
                    <form method="post" action="">
                        <?php wp_nonce_field('umcp_settings'); ?>
                        
                        <div class="umcp-form-group">
                            <label for="umcp_server_url">Server URL:</label>
                            <input type="url" id="umcp_server_url" name="umcp_server_url" 
                                   value="<?php echo esc_attr($server_url); ?>" 
                                   placeholder="https://seoforge-mcp-server.onrender.com" required>
                            <p class="description">Your Universal MCP Server URL</p>
                        </div>
                        
                        <div class="umcp-form-group">
                            <label for="umcp_api_key">API Key (Optional):</label>
                            <input type="password" id="umcp_api_key" name="umcp_api_key" 
                                   value="<?php echo esc_attr($api_key); ?>" 
                                   placeholder="Your API key (if required)">
                            <p class="description">API key for authentication (if your server requires it)</p>
                        </div>
                        
                        <?php submit_button('Save Settings'); ?>
                    </form>
                </div>
                
                <!-- Content Generator -->
                <div class="umcp-card">
                    <h2>✍️ AI Content Generator</h2>
                    <div class="umcp-form-group">
                        <label for="content-topic">Topic:</label>
                        <input type="text" id="content-topic" placeholder="Enter your topic..." class="regular-text">
                    </div>
                    
                    <div class="umcp-form-group">
                        <label for="content-keywords">Keywords (comma-separated):</label>
                        <input type="text" id="content-keywords" placeholder="keyword1, keyword2, keyword3" class="regular-text">
                    </div>
                    
                    <div class="umcp-form-group">
                        <label for="content-language">Language:</label>
                        <select id="content-language">
                            <option value="en">English</option>
                            <option value="th">Thai</option>
                            <option value="es">Spanish</option>
                            <option value="fr">French</option>
                            <option value="de">German</option>
                        </select>
                    </div>
                    
                    <button type="button" id="generate-content" class="umcp-button">Generate Content</button>
                    
                    <div id="content-result" style="margin-top: 20px; display: none;">
                        <h3>Generated Content:</h3>
                        <div id="content-output" style="background: #f9f9f9; padding: 15px; border-radius: 4px;"></div>
                        <button type="button" id="copy-content" class="umcp-button secondary" style="margin-top: 10px;">Copy to Clipboard</button>
                    </div>
                </div>
                
                <!-- SEO Analyzer -->
                <div class="umcp-card">
                    <h2>📊 SEO Analyzer</h2>
                    <div class="umcp-form-group">
                        <label for="seo-content">Content to Analyze:</label>
                        <textarea id="seo-content" rows="6" placeholder="Paste your content here..." class="large-text"></textarea>
                    </div>
                    
                    <div class="umcp-form-group">
                        <label for="seo-keywords">Target Keywords:</label>
                        <input type="text" id="seo-keywords" placeholder="keyword1, keyword2, keyword3" class="regular-text">
                    </div>
                    
                    <button type="button" id="analyze-seo" class="umcp-button">Analyze SEO</button>
                    
                    <div id="seo-result" style="margin-top: 20px; display: none;">
                        <h3>SEO Analysis:</h3>
                        <div id="seo-output" style="background: #f9f9f9; padding: 15px; border-radius: 4px;"></div>
                    </div>
                </div>
                
                <!-- Chatbot Integration -->
                <div class="umcp-card">
                    <h2>💬 Chatbot Integration</h2>
                    <p>Add this code to your website to embed the AI chatbot:</p>
                    <textarea readonly rows="6" class="large-text" style="font-family: monospace;">&lt;script src="<?php echo esc_html($server_url); ?>/static/chatbot-widget.js"&gt;&lt;/script&gt;
&lt;script&gt;
  UMCPChatbot.init({
    serverUrl: '<?php echo esc_html($server_url); ?>',
    companyName: 'Your Company Name',
    primaryColor: '#667eea'
  });
&lt;/script&gt;</textarea>
                    <button type="button" onclick="this.previousElementSibling.select(); document.execCommand('copy');" class="umcp-button secondary">Copy Code</button>
                </div>
                
                <!-- Documentation -->
                <div class="umcp-card">
                    <h2>📚 Documentation</h2>
                    <p>Available API endpoints:</p>
                    <ul>
                        <li><strong>Content Generation:</strong> <code>/universal-mcp/generate-content</code></li>
                        <li><strong>Image Generation:</strong> <code>/universal-mcp/generate-image</code></li>
                        <li><strong>SEO Analysis:</strong> <code>/universal-mcp/analyze-seo</code></li>
                        <li><strong>Chatbot:</strong> <code>/universal-mcp/chatbot</code></li>
                        <li><strong>Blog with Images:</strong> <code>/universal-mcp/generate-blog-with-images</code></li>
                    </ul>
                    
                    <p><strong>Live API:</strong> <a href="<?php echo esc_html($server_url); ?>" target="_blank"><?php echo esc_html($server_url); ?></a></p>
                    <p><strong>GitHub:</strong> <a href="https://github.com/khiwniti/SEOForge-mcp-server" target="_blank">SEOForge MCP Server</a></p>
                </div>
            </div>
        </div>
        
        <style>
        .umcp-admin-page { max-width: 1200px; }
        .umcp-card { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin-bottom: 20px; }
        .umcp-card h2 { margin-top: 0; }
        .umcp-form-group { margin-bottom: 15px; }
        .umcp-form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .umcp-button { background: #0073aa; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .umcp-button:hover { background: #005a87; color: #fff; }
        .umcp-button.secondary { background: #6c757d; }
        .umcp-button.secondary:hover { background: #545b62; }
        .umcp-status { padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .umcp-status.online { background: #d4edda; color: #155724; }
        .umcp-status.offline { background: #f8d7da; color: #721c24; }
        .umcp-status.checking { background: #fff3cd; color: #856404; }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Test connection on page load
            testConnection();
            
            // Test connection button
            $('#umcp-test-connection').click(testConnection);
            
            // Generate content
            $('#generate-content').click(function() {
                var topic = $('#content-topic').val();
                var keywords = $('#content-keywords').val().split(',').map(k => k.trim()).filter(k => k);
                var language = $('#content-language').val();
                
                if (!topic) {
                    alert('Please enter a topic');
                    return;
                }
                
                $(this).prop('disabled', true).text('Generating...');
                
                $.post(umcp_ajax.ajax_url, {
                    action: 'umcp_generate_content',
                    nonce: umcp_ajax.nonce,
                    topic: topic,
                    keywords: keywords,
                    language: language
                }, function(response) {
                    $('#generate-content').prop('disabled', false).text('Generate Content');
                    
                    if (response.success) {
                        $('#content-output').html('<h4>' + response.data.content.title + '</h4><p>' + response.data.content.body.replace(/\n/g, '</p><p>') + '</p>');
                        $('#content-result').show();
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                    }
                });
            });
            
            // Analyze SEO
            $('#analyze-seo').click(function() {
                var content = $('#seo-content').val();
                var keywords = $('#seo-keywords').val().split(',').map(k => k.trim()).filter(k => k);
                
                if (!content) {
                    alert('Please enter content to analyze');
                    return;
                }
                
                $(this).prop('disabled', true).text('Analyzing...');
                
                $.post(umcp_ajax.ajax_url, {
                    action: 'umcp_analyze_seo',
                    nonce: umcp_ajax.nonce,
                    content: content,
                    keywords: keywords
                }, function(response) {
                    $('#analyze-seo').prop('disabled', false).text('Analyze SEO');
                    
                    if (response.success) {
                        var analysis = response.data.analysis;
                        var html = '<h4>SEO Score: ' + analysis.seo_score + '/100</h4>';
                        html += '<p><strong>Word Count:</strong> ' + analysis.word_count + '</p>';
                        html += '<p><strong>Readability:</strong> ' + analysis.readability_score + '/100</p>';
                        html += '<h5>Recommendations:</h5><ul>';
                        analysis.recommendations.forEach(function(rec) {
                            html += '<li>' + rec + '</li>';
                        });
                        html += '</ul>';
                        
                        $('#seo-output').html(html);
                        $('#seo-result').show();
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                    }
                });
            });
            
            // Copy content
            $('#copy-content').click(function() {
                var content = $('#content-output').text();
                navigator.clipboard.writeText(content).then(function() {
                    alert('Content copied to clipboard!');
                });
            });
            
            function testConnection() {
                $('#umcp-status').removeClass('online offline').addClass('checking').text('Checking...');
                
                $.post(umcp_ajax.ajax_url, {
                    action: 'umcp_test_connection',
                    nonce: umcp_ajax.nonce
                }, function(response) {
                    if (response.success) {
                        $('#umcp-status').removeClass('checking offline').addClass('online').text('Online ✅');
                    } else {
                        $('#umcp-status').removeClass('checking online').addClass('offline').text('Offline ❌');
                    }
                }).fail(function() {
                    $('#umcp-status').removeClass('checking online').addClass('offline').text('Offline ❌');
                });
            }
        });
        </script>
        <?php
    }
    
    public function ajax_test_connection() {
        check_ajax_referer('umcp_nonce', 'nonce');
        
        if ($this->mcp_client) {
            $result = $this->mcp_client->test_connection();
            wp_send_json($result);
        }
        
        wp_send_json_error('MCP client not available');
    }
    
    public function ajax_generate_content() {
        check_ajax_referer('umcp_nonce', 'nonce');
        
        $topic = sanitize_text_field($_POST['topic'] ?? '');
        $keywords = array_map('sanitize_text_field', $_POST['keywords'] ?? []);
        $language = sanitize_text_field($_POST['language'] ?? 'en');
        
        if ($this->mcp_client) {
            $result = $this->mcp_client->generate_content($topic, $keywords, $language);
            wp_send_json($result);
        }
        
        wp_send_json_error('MCP client not available');
    }
    
    public function ajax_analyze_seo() {
        check_ajax_referer('umcp_nonce', 'nonce');
        
        $content = wp_kses_post($_POST['content'] ?? '');
        $keywords = array_map('sanitize_text_field', $_POST['keywords'] ?? []);
        $language = sanitize_text_field($_POST['language'] ?? 'en');
        
        if ($this->mcp_client) {
            $result = $this->mcp_client->analyze_seo($content, $keywords, $language);
            wp_send_json($result);
        }
        
        wp_send_json_error('MCP client not available');
    }
}

// Initialize the plugin
UniversalMCPSimple::get_instance();