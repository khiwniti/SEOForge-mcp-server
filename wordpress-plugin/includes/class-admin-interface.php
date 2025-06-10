<?php
/**
 * Admin Interface for Universal MCP Plugin
 */
class UMCP_Admin_Interface {
    private $mcp_client;
    private $plugin_slug = 'universal-mcp';

    public function __construct($mcp_client) {
        $this->mcp_client = $mcp_client;
    }

    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_umcp_test_connection', array($this, 'ajax_test_connection'));
        add_action('wp_ajax_umcp_get_server_status', array($this, 'ajax_get_server_status'));
    }

    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            'Universal MCP',
            'Universal MCP',
            'manage_options',
            $this->plugin_slug,
            array($this, 'dashboard_page'),
            'dashicons-networking',
            30
        );

        // Dashboard submenu
        add_submenu_page(
            $this->plugin_slug,
            'Dashboard',
            'Dashboard',
            'manage_options',
            $this->plugin_slug,
            array($this, 'dashboard_page')
        );

        // Content Generator submenu
        add_submenu_page(
            $this->plugin_slug,
            'Content Generator',
            'Content Generator',
            'edit_posts',
            $this->plugin_slug . '-content',
            array($this, 'content_generator_page')
        );

        // SEO Analyzer submenu
        add_submenu_page(
            $this->plugin_slug,
            'SEO Analyzer',
            'SEO Analyzer',
            'edit_posts',
            $this->plugin_slug . '-seo',
            array($this, 'seo_analyzer_page')
        );

        // Industry Tools submenu
        add_submenu_page(
            $this->plugin_slug,
            'Industry Tools',
            'Industry Tools',
            'edit_posts',
            $this->plugin_slug . '-industry',
            array($this, 'industry_tools_page')
        );

        // Analytics submenu
        add_submenu_page(
            $this->plugin_slug,
            'Analytics',
            'Analytics',
            'manage_options',
            $this->plugin_slug . '-analytics',
            array($this, 'analytics_page')
        );

        // Settings submenu
        add_submenu_page(
            $this->plugin_slug,
            'Settings',
            'Settings',
            'manage_options',
            $this->plugin_slug . '-settings',
            array($this, 'settings_page')
        );
    }

    public function register_settings() {
        // MCP Server Settings
        register_setting('umcp_settings', 'umcp_server_url');
        register_setting('umcp_settings', 'umcp_api_key');
        register_setting('umcp_settings', 'umcp_current_industry');
        register_setting('umcp_settings', 'umcp_default_language');
        register_setting('umcp_settings', 'umcp_cache_enabled');
        register_setting('umcp_settings', 'umcp_cache_duration');
        register_setting('umcp_settings', 'umcp_rate_limit');
        register_setting('umcp_settings', 'umcp_debug_mode');

        // Settings sections
        add_settings_section(
            'umcp_server_section',
            'MCP Server Configuration',
            array($this, 'server_section_callback'),
            'umcp_settings'
        );

        add_settings_section(
            'umcp_general_section',
            'General Settings',
            array($this, 'general_section_callback'),
            'umcp_settings'
        );

        add_settings_section(
            'umcp_performance_section',
            'Performance Settings',
            array($this, 'performance_section_callback'),
            'umcp_settings'
        );

        // Settings fields
        add_settings_field(
            'umcp_server_url',
            'MCP Server URL',
            array($this, 'server_url_field'),
            'umcp_settings',
            'umcp_server_section'
        );

        add_settings_field(
            'umcp_api_key',
            'API Key',
            array($this, 'api_key_field'),
            'umcp_settings',
            'umcp_server_section'
        );

        add_settings_field(
            'umcp_current_industry',
            'Default Industry',
            array($this, 'industry_field'),
            'umcp_settings',
            'umcp_general_section'
        );

        add_settings_field(
            'umcp_default_language',
            'Default Language',
            array($this, 'language_field'),
            'umcp_settings',
            'umcp_general_section'
        );

        add_settings_field(
            'umcp_cache_enabled',
            'Enable Caching',
            array($this, 'cache_enabled_field'),
            'umcp_settings',
            'umcp_performance_section'
        );

        add_settings_field(
            'umcp_cache_duration',
            'Cache Duration (seconds)',
            array($this, 'cache_duration_field'),
            'umcp_settings',
            'umcp_performance_section'
        );

        add_settings_field(
            'umcp_rate_limit',
            'Rate Limit (requests/hour)',
            array($this, 'rate_limit_field'),
            'umcp_settings',
            'umcp_performance_section'
        );

        add_settings_field(
            'umcp_debug_mode',
            'Debug Mode',
            array($this, 'debug_mode_field'),
            'umcp_settings',
            'umcp_performance_section'
        );
    }

    public function dashboard_page() {
        $server_status = $this->mcp_client->get_server_status();
        $analytics = $this->mcp_client->get_request_analytics(7);
        ?>
        <div class="wrap">
            <h1>Universal MCP Dashboard</h1>
            
            <div class="umcp-dashboard">
                <!-- Server Status Card -->
                <div class="umcp-card">
                    <h2>Server Status</h2>
                    <div class="umcp-status-indicator <?php echo $server_status && $server_status['status'] === 'active' ? 'active' : 'inactive'; ?>">
                        <?php echo $server_status && $server_status['status'] === 'active' ? 'Online' : 'Offline'; ?>
                    </div>
                    <?php if ($server_status): ?>
                        <p><strong>Version:</strong> <?php echo esc_html($server_status['version']); ?></p>
                        <p><strong>Available Tools:</strong> <?php echo count($server_status['available_tools']); ?></p>
                        <p><strong>Supported Industries:</strong> <?php echo count($server_status['supported_industries']); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="umcp-card">
                    <h2>Quick Actions</h2>
                    <div class="umcp-quick-actions">
                        <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_slug . '-content'); ?>" class="button button-primary">
                            Generate Content
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=' . $this->plugin_slug . '-seo'); ?>" class="button button-secondary">
                            Analyze SEO
                        </a>
                        <button id="umcp-test-connection" class="button">Test Connection</button>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="umcp-card umcp-full-width">
                    <h2>Recent Activity</h2>
                    <div id="umcp-recent-activity">
                        <?php if ($analytics): ?>
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th>Tool</th>
                                        <th>Industry</th>
                                        <th>Requests</th>
                                        <th>Success Rate</th>
                                        <th>Avg Time</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($analytics as $row): ?>
                                        <tr>
                                            <td><?php echo esc_html($row->tool_name); ?></td>
                                            <td><?php echo esc_html($row->industry); ?></td>
                                            <td><?php echo esc_html($row->total_requests); ?></td>
                                            <td><?php echo round(($row->successful_requests / $row->total_requests) * 100, 1); ?>%</td>
                                            <td><?php echo round($row->avg_execution_time, 2); ?>s</td>
                                            <td><?php echo esc_html($row->request_date); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No recent activity found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#umcp-test-connection').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).text('Testing...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'umcp_test_connection',
                        nonce: umcp_admin_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Connection successful!\nResponse time: ' + response.data.response_time);
                        } else {
                            alert('Connection failed: ' + response.data.message);
                        }
                    },
                    error: function() {
                        alert('Connection test failed');
                    },
                    complete: function() {
                        button.prop('disabled', false).text('Test Connection');
                    }
                });
            });
        });
        </script>
        <?php
    }

    public function content_generator_page() {
        ?>
        <div class="wrap">
            <h1>Content Generator</h1>
            
            <div class="umcp-content-generator">
                <form id="umcp-content-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row">Content Type</th>
                            <td>
                                <select name="content_type" id="content_type">
                                    <option value="blog_post">Blog Post</option>
                                    <option value="product_description">Product Description</option>
                                    <option value="category_page">Category Page</option>
                                    <option value="landing_page">Landing Page</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Topic</th>
                            <td>
                                <input type="text" name="topic" id="topic" class="regular-text" placeholder="Enter your topic..." required>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Keywords</th>
                            <td>
                                <textarea name="keywords" id="keywords" rows="3" class="large-text" placeholder="Enter keywords separated by commas..."></textarea>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Industry</th>
                            <td>
                                <select name="industry" id="industry">
                                    <option value="general">General</option>
                                    <option value="ecommerce">E-commerce</option>
                                    <option value="healthcare">Healthcare</option>
                                    <option value="technology">Technology</option>
                                    <option value="finance">Finance</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Language</th>
                            <td>
                                <select name="language" id="language">
                                    <option value="en">English</option>
                                    <option value="th">Thai</option>
                                    <option value="dual">Dual (English + Thai)</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary">Generate Content</button>
                    </p>
                </form>

                <div id="umcp-content-result" style="display: none;">
                    <h2>Generated Content</h2>
                    <div id="umcp-content-output"></div>
                    <p>
                        <button id="umcp-insert-content" class="button button-secondary">Insert into Post</button>
                        <button id="umcp-copy-content" class="button">Copy to Clipboard</button>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }

    public function seo_analyzer_page() {
        ?>
        <div class="wrap">
            <h1>SEO Analyzer</h1>
            
            <div class="umcp-seo-analyzer">
                <form id="umcp-seo-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row">Analysis Type</th>
                            <td>
                                <label><input type="radio" name="analysis_type" value="url" checked> URL Analysis</label><br>
                                <label><input type="radio" name="analysis_type" value="content"> Content Analysis</label>
                            </td>
                        </tr>
                        <tr id="url_field">
                            <th scope="row">URL</th>
                            <td>
                                <input type="url" name="url" id="url" class="regular-text" placeholder="https://example.com">
                            </td>
                        </tr>
                        <tr id="content_field" style="display: none;">
                            <th scope="row">Content</th>
                            <td>
                                <textarea name="content" id="content" rows="10" class="large-text" placeholder="Paste your content here..."></textarea>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Target Keywords</th>
                            <td>
                                <textarea name="keywords" id="seo_keywords" rows="3" class="large-text" placeholder="Enter target keywords separated by commas..."></textarea>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary">Analyze SEO</button>
                    </p>
                </form>

                <div id="umcp-seo-result" style="display: none;">
                    <h2>SEO Analysis Results</h2>
                    <div id="umcp-seo-output"></div>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('input[name="analysis_type"]').on('change', function() {
                if ($(this).val() === 'url') {
                    $('#url_field').show();
                    $('#content_field').hide();
                } else {
                    $('#url_field').hide();
                    $('#content_field').show();
                }
            });
        });
        </script>
        <?php
    }

    public function industry_tools_page() {
        ?>
        <div class="wrap">
            <h1>Industry Tools</h1>
            <p>Industry-specific tools and templates will be displayed here based on your selected industry.</p>
        </div>
        <?php
    }

    public function analytics_page() {
        $analytics = $this->mcp_client->get_request_analytics(30);
        ?>
        <div class="wrap">
            <h1>Analytics</h1>
            
            <div class="umcp-analytics">
                <!-- Analytics dashboard content -->
                <div class="umcp-stats-grid">
                    <div class="umcp-stat-card">
                        <h3>Total Requests</h3>
                        <div class="umcp-stat-number">
                            <?php echo $analytics ? array_sum(array_column($analytics, 'total_requests')) : 0; ?>
                        </div>
                    </div>
                    
                    <div class="umcp-stat-card">
                        <h3>Success Rate</h3>
                        <div class="umcp-stat-number">
                            <?php 
                            if ($analytics) {
                                $total = array_sum(array_column($analytics, 'total_requests'));
                                $successful = array_sum(array_column($analytics, 'successful_requests'));
                                echo $total > 0 ? round(($successful / $total) * 100, 1) . '%' : '0%';
                            } else {
                                echo '0%';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Universal MCP Settings</h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('umcp_settings');
                do_settings_sections('umcp_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    // Settings field callbacks
    public function server_section_callback() {
        echo '<p>Configure your MCP server connection settings.</p>';
    }

    public function general_section_callback() {
        echo '<p>General plugin settings and preferences.</p>';
    }

    public function performance_section_callback() {
        echo '<p>Performance and optimization settings.</p>';
    }

    public function server_url_field() {
        $value = get_option('umcp_server_url', '');
        echo '<input type="url" name="umcp_server_url" value="' . esc_attr($value) . '" class="regular-text" placeholder="http://localhost:3000" />';
        echo '<p class="description">URL of your MCP server instance.</p>';
    }

    public function api_key_field() {
        $value = get_option('umcp_api_key', '');
        echo '<input type="password" name="umcp_api_key" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">API key for authenticating with the MCP server.</p>';
    }

    public function industry_field() {
        $value = get_option('umcp_current_industry', 'general');
        $industries = array(
            'general' => 'General',
            'ecommerce' => 'E-commerce',
            'healthcare' => 'Healthcare',
            'technology' => 'Technology',
            'finance' => 'Finance',
            'education' => 'Education',
            'real_estate' => 'Real Estate',
            'automotive' => 'Automotive'
        );
        
        echo '<select name="umcp_current_industry">';
        foreach ($industries as $key => $label) {
            echo '<option value="' . esc_attr($key) . '"' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }

    public function language_field() {
        $value = get_option('umcp_default_language', 'en');
        $languages = array(
            'en' => 'English',
            'th' => 'Thai',
            'dual' => 'Dual (English + Thai)'
        );
        
        echo '<select name="umcp_default_language">';
        foreach ($languages as $key => $label) {
            echo '<option value="' . esc_attr($key) . '"' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }

    public function cache_enabled_field() {
        $value = get_option('umcp_cache_enabled', true);
        echo '<input type="checkbox" name="umcp_cache_enabled" value="1"' . checked($value, true, false) . ' />';
        echo '<p class="description">Enable caching to improve performance.</p>';
    }

    public function cache_duration_field() {
        $value = get_option('umcp_cache_duration', 3600);
        echo '<input type="number" name="umcp_cache_duration" value="' . esc_attr($value) . '" min="60" max="86400" />';
        echo '<p class="description">Cache duration in seconds (60-86400).</p>';
    }

    public function rate_limit_field() {
        $value = get_option('umcp_rate_limit', 100);
        echo '<input type="number" name="umcp_rate_limit" value="' . esc_attr($value) . '" min="10" max="1000" />';
        echo '<p class="description">Maximum requests per hour per user.</p>';
    }

    public function debug_mode_field() {
        $value = get_option('umcp_debug_mode', false);
        echo '<input type="checkbox" name="umcp_debug_mode" value="1"' . checked($value, true, false) . ' />';
        echo '<p class="description">Enable debug mode for troubleshooting.</p>';
    }

    // AJAX handlers
    public function ajax_test_connection() {
        check_ajax_referer('umcp_admin_nonce', 'nonce');
        
        $result = $this->mcp_client->test_connection();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    public function ajax_get_server_status() {
        check_ajax_referer('umcp_admin_nonce', 'nonce');
        
        $status = $this->mcp_client->get_server_status();
        
        if ($status) {
            wp_send_json_success($status);
        } else {
            wp_send_json_error('Failed to get server status');
        }
    }
}
