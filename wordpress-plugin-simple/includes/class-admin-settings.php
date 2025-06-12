<?php
/**
 * Admin Settings Page for Universal MCP Plugin
 * Handles plugin configuration and settings management
 */

if (!defined('ABSPATH')) {
    exit;
}

class UMCP_Admin_Settings {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add settings page to admin menu
     */
    public function add_settings_page() {
        add_options_page(
            'Universal MCP Settings',
            'Universal MCP',
            'manage_options',
            'umcp-settings',
            array($this, 'settings_page_content')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        // Server Configuration
        register_setting('umcp_settings_group', 'umcp_server_url');
        register_setting('umcp_settings_group', 'umcp_api_key');
        register_setting('umcp_settings_group', 'umcp_timeout');
        
        // Content Generation Settings
        register_setting('umcp_settings_group', 'umcp_default_language');
        register_setting('umcp_settings_group', 'umcp_default_tone');
        register_setting('umcp_settings_group', 'umcp_default_length');
        register_setting('umcp_settings_group', 'umcp_default_industry');
        register_setting('umcp_settings_group', 'umcp_auto_publish');
        register_setting('umcp_settings_group', 'umcp_default_post_status');
        register_setting('umcp_settings_group', 'umcp_default_post_category');
        register_setting('umcp_settings_group', 'umcp_default_post_author');
        
        // Image Generation Settings
        register_setting('umcp_settings_group', 'umcp_default_image_style');
        register_setting('umcp_settings_group', 'umcp_default_image_size');
        register_setting('umcp_settings_group', 'umcp_enable_ai_enhancement');
        register_setting('umcp_settings_group', 'umcp_auto_add_images');
        
        // Draft Management Settings
        register_setting('umcp_settings_group', 'umcp_draft_retention_days');
        register_setting('umcp_settings_group', 'umcp_auto_cleanup_drafts');
        
        // Advanced Settings
        register_setting('umcp_settings_group', 'umcp_enable_logging');
        register_setting('umcp_settings_group', 'umcp_cache_duration');
        register_setting('umcp_settings_group', 'umcp_rate_limit');
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'settings_page_umcp-settings') {
            return;
        }
        
        wp_enqueue_script('umcp-admin-settings', plugin_dir_url(__FILE__) . '../assets/admin-settings.js', array('jquery'), '1.0.0', true);
        wp_enqueue_style('umcp-admin-settings', plugin_dir_url(__FILE__) . '../assets/admin-settings.css', array(), '1.0.0');
        
        wp_localize_script('umcp-admin-settings', 'umcp_settings_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('umcp_settings_nonce')
        ));
    }
    
    /**
     * Settings page content
     */
    public function settings_page_content() {
        ?>
        <div class="wrap">
            <h1>🚀 Universal MCP Settings</h1>
            
            <?php if (isset($_GET['settings-updated'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p>✅ Settings saved successfully!</p>
                </div>
            <?php endif; ?>
            
            <div class="umcp-settings-container">
                <form method="post" action="options.php">
                    <?php settings_fields('umcp_settings_group'); ?>
                    
                    <!-- Server Configuration -->
                    <div class="umcp-settings-section">
                        <h2>🌐 Server Configuration</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="umcp_server_url">Server URL</label>
                                </th>
                                <td>
                                    <input type="url" 
                                           id="umcp_server_url" 
                                           name="umcp_server_url" 
                                           value="<?php echo esc_attr(get_option('umcp_server_url', 'https://seoforge-mcp-server.onrender.com')); ?>" 
                                           class="regular-text" 
                                           placeholder="https://your-mcp-server.com" />
                                    <p class="description">URL of your MCP server. Leave empty for auto-detection.</p>
                                    <button type="button" id="test-connection" class="button">🔍 Test Connection</button>
                                    <span id="connection-status" class="umcp-status"></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="umcp_api_key">API Key</label>
                                </th>
                                <td>
                                    <input type="password" 
                                           id="umcp_api_key" 
                                           name="umcp_api_key" 
                                           value="<?php echo esc_attr(get_option('umcp_api_key', '')); ?>" 
                                           class="regular-text" 
                                           placeholder="Optional API key" />
                                    <p class="description">Optional API key for authenticated requests.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="umcp_timeout">Request Timeout</label>
                                </th>
                                <td>
                                    <input type="number" 
                                           id="umcp_timeout" 
                                           name="umcp_timeout" 
                                           value="<?php echo esc_attr(get_option('umcp_timeout', '30')); ?>" 
                                           min="10" 
                                           max="120" 
                                           class="small-text" /> seconds
                                    <p class="description">Maximum time to wait for server response (10-120 seconds).</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Content Generation Defaults -->
                    <div class="umcp-settings-section">
                        <h2>📝 Content Generation Defaults</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="umcp_default_language">Default Language</label>
                                </th>
                                <td>
                                    <select id="umcp_default_language" name="umcp_default_language">
                                        <?php
                                        $languages = array(
                                            'en' => 'English',
                                            'th' => 'Thai (ไทย)',
                                            'es' => 'Spanish (Español)',
                                            'fr' => 'French (Français)',
                                            'de' => 'German (Deutsch)',
                                            'it' => 'Italian (Italiano)',
                                            'pt' => 'Portuguese (Português)',
                                            'ja' => 'Japanese (日本語)',
                                            'ko' => 'Korean (한국어)',
                                            'zh' => 'Chinese (中文)'
                                        );
                                        $selected_lang = get_option('umcp_default_language', 'en');
                                        foreach ($languages as $code => $name) {
                                            echo '<option value="' . esc_attr($code) . '"' . selected($selected_lang, $code, false) . '>' . esc_html($name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="umcp_default_tone">Default Tone</label>
                                </th>
                                <td>
                                    <select id="umcp_default_tone" name="umcp_default_tone">
                                        <?php
                                        $tones = array(
                                            'professional' => 'Professional',
                                            'casual' => 'Casual',
                                            'formal' => 'Formal',
                                            'friendly' => 'Friendly',
                                            'technical' => 'Technical',
                                            'conversational' => 'Conversational',
                                            'authoritative' => 'Authoritative'
                                        );
                                        $selected_tone = get_option('umcp_default_tone', 'professional');
                                        foreach ($tones as $value => $label) {
                                            echo '<option value="' . esc_attr($value) . '"' . selected($selected_tone, $value, false) . '>' . esc_html($label) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="umcp_default_length">Default Length</label>
                                </th>
                                <td>
                                    <select id="umcp_default_length" name="umcp_default_length">
                                        <?php
                                        $lengths = array(
                                            'short' => 'Short (300-500 words)',
                                            'medium' => 'Medium (500-1000 words)',
                                            'long' => 'Long (1000+ words)'
                                        );
                                        $selected_length = get_option('umcp_default_length', 'medium');
                                        foreach ($lengths as $value => $label) {
                                            echo '<option value="' . esc_attr($value) . '"' . selected($selected_length, $value, false) . '>' . esc_html($label) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="umcp_default_industry">Default Industry</label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="umcp_default_industry" 
                                           name="umcp_default_industry" 
                                           value="<?php echo esc_attr(get_option('umcp_default_industry', 'general')); ?>" 
                                           class="regular-text" 
                                           placeholder="e.g., technology, healthcare, finance" />
                                    <p class="description">Default industry context for content generation.</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Post Publishing Settings -->
                    <div class="umcp-settings-section">
                        <h2>📄 Post Publishing Settings</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="umcp_auto_publish">Auto Publish</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               id="umcp_auto_publish" 
                                               name="umcp_auto_publish" 
                                               value="1" 
                                               <?php checked(get_option('umcp_auto_publish', '0'), '1'); ?> />
                                        Automatically publish generated content as posts
                                    </label>
                                    <p class="description">When disabled, content will be saved as drafts in the drafts table.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="umcp_default_post_status">Default Post Status</label>
                                </th>
                                <td>
                                    <select id="umcp_default_post_status" name="umcp_default_post_status">
                                        <?php
                                        $statuses = array(
                                            'draft' => 'Draft',
                                            'pending' => 'Pending Review',
                                            'private' => 'Private',
                                            'publish' => 'Published'
                                        );
                                        $selected_status = get_option('umcp_default_post_status', 'draft');
                                        foreach ($statuses as $value => $label) {
                                            echo '<option value="' . esc_attr($value) . '"' . selected($selected_status, $value, false) . '>' . esc_html($label) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="umcp_default_post_category">Default Category</label>
                                </th>
                                <td>
                                    <?php
                                    wp_dropdown_categories(array(
                                        'name' => 'umcp_default_post_category',
                                        'id' => 'umcp_default_post_category',
                                        'selected' => get_option('umcp_default_post_category', '1'),
                                        'show_option_none' => 'Select Category',
                                        'option_none_value' => '0'
                                    ));
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="umcp_default_post_author">Default Author</label>
                                </th>
                                <td>
                                    <?php
                                    wp_dropdown_users(array(
                                        'name' => 'umcp_default_post_author',
                                        'id' => 'umcp_default_post_author',
                                        'selected' => get_option('umcp_default_post_author', get_current_user_id()),
                                        'show_option_none' => 'Current User',
                                        'option_none_value' => '0'
                                    ));
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Image Generation Settings -->
                    <div class="umcp-settings-section">
                        <h2>🎨 Image Generation Settings</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="umcp_default_image_style">Default Image Style</label>
                                </th>
                                <td>
                                    <select id="umcp_default_image_style" name="umcp_default_image_style">
                                        <?php
                                        $styles = array(
                                            'professional' => 'Professional',
                                            'artistic' => 'Artistic',
                                            'minimalist' => 'Minimalist',
                                            'commercial' => 'Commercial',
                                            'realistic' => 'Realistic',
                                            'illustration' => 'Illustration',
                                            'modern' => 'Modern'
                                        );
                                        $selected_style = get_option('umcp_default_image_style', 'professional');
                                        foreach ($styles as $value => $label) {
                                            echo '<option value="' . esc_attr($value) . '"' . selected($selected_style, $value, false) . '>' . esc_html($label) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="umcp_default_image_size">Default Image Size</label>
                                </th>
                                <td>
                                    <select id="umcp_default_image_size" name="umcp_default_image_size">
                                        <?php
                                        $sizes = array(
                                            '512x512' => 'Square (512x512)',
                                            '1024x1024' => 'Large Square (1024x1024)',
                                            '1024x768' => 'Landscape (1024x768)',
                                            '768x1024' => 'Portrait (768x1024)',
                                            '1920x1080' => 'HD Landscape (1920x1080)',
                                            '1080x1920' => 'HD Portrait (1080x1920)'
                                        );
                                        $selected_size = get_option('umcp_default_image_size', '1024x1024');
                                        foreach ($sizes as $value => $label) {
                                            echo '<option value="' . esc_attr($value) . '"' . selected($selected_size, $value, false) . '>' . esc_html($label) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="umcp_enable_ai_enhancement">AI Prompt Enhancement</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               id="umcp_enable_ai_enhancement" 
                                               name="umcp_enable_ai_enhancement" 
                                               value="1" 
                                               <?php checked(get_option('umcp_enable_ai_enhancement', '1'), '1'); ?> />
                                        Use AI to enhance image prompts for better results
                                    </label>
                                    <p class="description">AI will automatically improve image prompts with technical details.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="umcp_auto_add_images">Auto Add Images</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               id="umcp_auto_add_images" 
                                               name="umcp_auto_add_images" 
                                               value="1" 
                                               <?php checked(get_option('umcp_auto_add_images', '1'), '1'); ?> />
                                        Automatically generate and add images to content
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Draft Management -->
                    <div class="umcp-settings-section">
                        <h2>📋 Draft Management</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="umcp_draft_retention_days">Draft Retention</label>
                                </th>
                                <td>
                                    <input type="number" 
                                           id="umcp_draft_retention_days" 
                                           name="umcp_draft_retention_days" 
                                           value="<?php echo esc_attr(get_option('umcp_draft_retention_days', '30')); ?>" 
                                           min="1" 
                                           max="365" 
                                           class="small-text" /> days
                                    <p class="description">How long to keep drafts before auto-cleanup (1-365 days).</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="umcp_auto_cleanup_drafts">Auto Cleanup</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               id="umcp_auto_cleanup_drafts" 
                                               name="umcp_auto_cleanup_drafts" 
                                               value="1" 
                                               <?php checked(get_option('umcp_auto_cleanup_drafts', '1'), '1'); ?> />
                                        Automatically delete old drafts after retention period
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Advanced Settings -->
                    <div class="umcp-settings-section">
                        <h2>⚙️ Advanced Settings</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="umcp_enable_logging">Enable Logging</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               id="umcp_enable_logging" 
                                               name="umcp_enable_logging" 
                                               value="1" 
                                               <?php checked(get_option('umcp_enable_logging', '0'), '1'); ?> />
                                        Log API requests and responses for debugging
                                    </label>
                                    <p class="description">Logs will be stored in wp-content/uploads/umcp-logs/</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="umcp_cache_duration">Cache Duration</label>
                                </th>
                                <td>
                                    <input type="number" 
                                           id="umcp_cache_duration" 
                                           name="umcp_cache_duration" 
                                           value="<?php echo esc_attr(get_option('umcp_cache_duration', '3600')); ?>" 
                                           min="0" 
                                           max="86400" 
                                           class="small-text" /> seconds
                                    <p class="description">How long to cache API responses (0 to disable, max 24 hours).</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="umcp_rate_limit">Rate Limit</label>
                                </th>
                                <td>
                                    <input type="number" 
                                           id="umcp_rate_limit" 
                                           name="umcp_rate_limit" 
                                           value="<?php echo esc_attr(get_option('umcp_rate_limit', '60')); ?>" 
                                           min="1" 
                                           max="1000" 
                                           class="small-text" /> requests per hour
                                    <p class="description">Maximum API requests per hour (1-1000).</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <?php submit_button('💾 Save Settings', 'primary', 'submit', true, array('id' => 'umcp-save-settings')); ?>
                </form>
                
                <!-- Quick Actions -->
                <div class="umcp-settings-section">
                    <h2>🚀 Quick Actions</h2>
                    <div class="umcp-quick-actions">
                        <button type="button" id="clear-cache" class="button">🗑️ Clear Cache</button>
                        <button type="button" id="test-all-endpoints" class="button">🔍 Test All Endpoints</button>
                        <button type="button" id="export-settings" class="button">📤 Export Settings</button>
                        <button type="button" id="import-settings" class="button">📥 Import Settings</button>
                        <input type="file" id="import-file" style="display: none;" accept=".json">
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .umcp-settings-container {
            max-width: 1000px;
        }
        
        .umcp-settings-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            margin: 20px 0;
            padding: 20px;
        }
        
        .umcp-settings-section h2 {
            margin-top: 0;
            color: #23282d;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .umcp-quick-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .umcp-status {
            margin-left: 10px;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .umcp-status.success {
            background: #d4edda;
            color: #155724;
        }
        
        .umcp-status.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .umcp-status.checking {
            background: #fff3cd;
            color: #856404;
        }
        
        #umcp-save-settings {
            font-size: 16px;
            height: auto;
            padding: 12px 24px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Test connection
            $('#test-connection').click(function() {
                var $status = $('#connection-status');
                var serverUrl = $('#umcp_server_url').val() || 'https://seoforge-mcp-server.onrender.com';
                
                $status.removeClass('success error').addClass('checking').text('🔍 Testing...');
                
                $.ajax({
                    url: serverUrl + '/',
                    method: 'GET',
                    timeout: 10000,
                    success: function(response) {
                        $status.removeClass('checking error').addClass('success').text('✅ Connected');
                    },
                    error: function() {
                        $status.removeClass('checking success').addClass('error').text('❌ Failed');
                    }
                });
            });
            
            // Auto-test connection when URL changes
            $('#umcp_server_url').on('blur', function() {
                if ($(this).val()) {
                    $('#test-connection').click();
                }
            });
            
            // Clear cache
            $('#clear-cache').click(function() {
                $.post(umcp_settings_ajax.ajax_url, {
                    action: 'umcp_clear_cache',
                    nonce: umcp_settings_ajax.nonce
                }, function(response) {
                    alert(response.success ? '✅ Cache cleared!' : '❌ Error: ' + response.data);
                });
            });
            
            // Test all endpoints
            $('#test-all-endpoints').click(function() {
                var $btn = $(this);
                $btn.prop('disabled', true).text('🔍 Testing...');
                
                $.post(umcp_settings_ajax.ajax_url, {
                    action: 'umcp_test_endpoints',
                    nonce: umcp_settings_ajax.nonce
                }, function(response) {
                    $btn.prop('disabled', false).text('🔍 Test All Endpoints');
                    
                    if (response.success) {
                        var results = response.data;
                        var message = 'Endpoint Test Results:\n\n';
                        for (var endpoint in results) {
                            message += endpoint + ': ' + (results[endpoint] ? '✅ OK' : '❌ Failed') + '\n';
                        }
                        alert(message);
                    } else {
                        alert('❌ Test failed: ' + response.data);
                    }
                });
            });
            
            // Export settings
            $('#export-settings').click(function() {
                $.post(umcp_settings_ajax.ajax_url, {
                    action: 'umcp_export_settings',
                    nonce: umcp_settings_ajax.nonce
                }, function(response) {
                    if (response.success) {
                        var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(response.data, null, 2));
                        var downloadAnchorNode = document.createElement('a');
                        downloadAnchorNode.setAttribute("href", dataStr);
                        downloadAnchorNode.setAttribute("download", "umcp-settings-" + new Date().toISOString().split('T')[0] + ".json");
                        document.body.appendChild(downloadAnchorNode);
                        downloadAnchorNode.click();
                        downloadAnchorNode.remove();
                    } else {
                        alert('❌ Export failed: ' + response.data);
                    }
                });
            });
            
            // Import settings
            $('#import-settings').click(function() {
                $('#import-file').click();
            });
            
            $('#import-file').change(function() {
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        try {
                            var settings = JSON.parse(e.target.result);
                            
                            $.post(umcp_settings_ajax.ajax_url, {
                                action: 'umcp_import_settings',
                                nonce: umcp_settings_ajax.nonce,
                                settings: settings
                            }, function(response) {
                                if (response.success) {
                                    alert('✅ Settings imported successfully! Reloading page...');
                                    location.reload();
                                } else {
                                    alert('❌ Import failed: ' + response.data);
                                }
                            });
                        } catch (error) {
                            alert('❌ Invalid JSON file');
                        }
                    };
                    reader.readAsText(file);
                }
            });
            
            // Auto-test connection on page load
            $('#test-connection').click();
        });
        </script>
        <?php
    }
}