<?php
/**
 * Plugin Name: SEO Forge - Playground Test
 * Description: SEO Forge plugin for WordPress Playground testing with MCP server integration
 * Version: 1.0.0
 * Author: SEO Forge Team
 * License: MIT
 */

defined('ABSPATH') || exit;

class SEOForgePlaygroundTest {
    
    private $api_url = 'https://seoforge-mcp-platform.vercel.app';
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_seoforge_generate_content', [$this, 'ajax_generate_content']);
        add_action('wp_ajax_seoforge_generate_image', [$this, 'ajax_generate_image']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('wp_head', [$this, 'add_meta_tags']);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'SEO Forge',
            'SEO Forge',
            'manage_options',
            'seo-forge',
            [$this, 'dashboard_page'],
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7v10c0 5.55 3.84 9.74 9 11 5.16-1.26 9-5.45 9-11V7l-10-5z"/><path d="M9 12l2 2 4-4" stroke="white" stroke-width="2" fill="none"/></svg>'),
            30
        );
        
        add_submenu_page(
            'seo-forge',
            'Content Generator',
            'Content Generator',
            'edit_posts',
            'seo-forge-generator',
            [$this, 'generator_page']
        );
    }
    
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'seo-forge') !== false || in_array($hook, ['post.php', 'post-new.php'])) {
            wp_enqueue_style('seo-forge-admin', plugin_dir_url(__FILE__) . 'admin.css', [], '1.0.0');
            wp_enqueue_script('seo-forge-admin', plugin_dir_url(__FILE__) . 'admin.js', ['jquery'], '1.0.0', true);
            
            wp_localize_script('seo-forge-admin', 'seoForgeAjax', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('seoforge_nonce'),
                'apiUrl' => $this->api_url
            ]);
        }
    }
    
    public function add_meta_boxes() {
        add_meta_box(
            'seoforge-content-generator',
            'SEO Forge - Content Generator',
            [$this, 'content_generator_meta_box'],
            ['post', 'page'],
            'side',
            'high'
        );
        
        add_meta_box(
            'seoforge-seo-settings',
            'SEO Forge - SEO Settings',
            [$this, 'seo_settings_meta_box'],
            ['post', 'page'],
            'normal',
            'high'
        );
    }
    
    public function content_generator_meta_box($post) {
        wp_nonce_field('seoforge_meta_box', 'seoforge_meta_box_nonce');
        ?>
        <div class="seoforge-meta-box">
            <div class="seoforge-field">
                <label for="seoforge-keywords">Keywords:</label>
                <input type="text" id="seoforge-keywords" placeholder="Enter keywords (e.g., WordPress SEO, content optimization)" style="width: 100%; margin-bottom: 10px;">
                <small>Enter keywords separated by commas</small>
            </div>
            
            <div class="seoforge-field">
                <label for="seoforge-industry">Industry:</label>
                <select id="seoforge-industry" style="width: 100%; margin-bottom: 10px;">
                    <option value="">Select Industry</option>
                    <option value="technology">Technology</option>
                    <option value="healthcare">Healthcare</option>
                    <option value="finance">Finance</option>
                    <option value="education">Education</option>
                    <option value="retail">Retail</option>
                </select>
            </div>
            
            <div class="seoforge-field">
                <label for="seoforge-content-type">Content Type:</label>
                <select id="seoforge-content-type" style="width: 100%; margin-bottom: 10px;">
                    <option value="blog_post">Blog Post</option>
                    <option value="product_description">Product Description</option>
                    <option value="landing_page">Landing Page</option>
                    <option value="how_to_guide">How-to Guide</option>
                </select>
            </div>
            
            <div class="seoforge-field">
                <label for="seoforge-language">Language:</label>
                <select id="seoforge-language" style="width: 100%; margin-bottom: 10px;">
                    <option value="en">English</option>
                    <option value="th">Thai</option>
                    <option value="es">Spanish</option>
                    <option value="fr">French</option>
                </select>
            </div>
            
            <div class="seoforge-buttons">
                <button type="button" id="generate-content-btn" class="button button-primary" style="margin-right: 10px;">
                    Generate Content
                </button>
                <button type="button" id="generate-image-btn" class="button">
                    Generate Image
                </button>
            </div>
            
            <div id="seoforge-loading" style="display: none; margin: 10px 0;">
                <p>🤖 Generating content with AI... Please wait.</p>
            </div>
            
            <div id="seoforge-result" style="display: none; margin-top: 15px;">
                <h4>Generated Content:</h4>
                <div id="generated-content" style="background: #f9f9f9; padding: 10px; border-radius: 4px; max-height: 200px; overflow-y: auto;"></div>
                <button type="button" id="use-content-btn" class="button button-primary" style="margin-top: 10px;">
                    Use This Content
                </button>
            </div>
            
            <div id="seoforge-image-result" style="display: none; margin-top: 15px;">
                <h4>Generated Image:</h4>
                <div id="generated-image" style="text-align: center;"></div>
                <button type="button" id="use-image-btn" class="button button-primary" style="margin-top: 10px;">
                    Set as Featured Image
                </button>
            </div>
        </div>
        
        <style>
        .seoforge-meta-box {
            padding: 10px 0;
        }
        .seoforge-field {
            margin-bottom: 15px;
        }
        .seoforge-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .seoforge-buttons {
            margin: 15px 0;
        }
        #generated-content {
            font-size: 14px;
            line-height: 1.5;
        }
        #generated-image img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#generate-content-btn').on('click', function() {
                var keywords = $('#seoforge-keywords').val();
                if (!keywords) {
                    alert('Please enter keywords first!');
                    return;
                }
                
                var data = {
                    action: 'seoforge_generate_content',
                    nonce: seoForgeAjax.nonce,
                    keywords: keywords,
                    industry: $('#seoforge-industry').val(),
                    content_type: $('#seoforge-content-type').val(),
                    language: $('#seoforge-language').val()
                };
                
                $('#seoforge-loading').show();
                $('#generate-content-btn').prop('disabled', true).text('Generating...');
                
                $.post(seoForgeAjax.ajaxUrl, data, function(response) {
                    $('#seoforge-loading').hide();
                    $('#generate-content-btn').prop('disabled', false).text('Generate Content');
                    
                    if (response.success) {
                        $('#generated-content').html(response.data.content || response.data);
                        $('#seoforge-result').show();
                        
                        // Auto-fill title if available
                        if (response.data.title && $('#title').length) {
                            $('#title').val(response.data.title);
                        }
                    } else {
                        alert('Error: ' + (response.data || 'Failed to generate content'));
                    }
                }).fail(function() {
                    $('#seoforge-loading').hide();
                    $('#generate-content-btn').prop('disabled', false).text('Generate Content');
                    alert('Network error. Please try again.');
                });
            });
            
            $('#generate-image-btn').on('click', function() {
                var keywords = $('#seoforge-keywords').val();
                if (!keywords) {
                    alert('Please enter keywords first!');
                    return;
                }
                
                var data = {
                    action: 'seoforge_generate_image',
                    nonce: seoForgeAjax.nonce,
                    prompt: 'Professional image related to: ' + keywords,
                    style: 'realistic'
                };
                
                $('#generate-image-btn').prop('disabled', true).text('Generating Image...');
                
                $.post(seoForgeAjax.ajaxUrl, data, function(response) {
                    $('#generate-image-btn').prop('disabled', false).text('Generate Image');
                    
                    if (response.success && response.data.url) {
                        $('#generated-image').html('<img src="' + response.data.url + '" alt="Generated Image">');
                        $('#seoforge-image-result').show();
                    } else {
                        alert('Error: ' + (response.data || 'Failed to generate image'));
                    }
                }).fail(function() {
                    $('#generate-image-btn').prop('disabled', false).text('Generate Image');
                    alert('Network error. Please try again.');
                });
            });
            
            $('#use-content-btn').on('click', function() {
                var content = $('#generated-content').html();
                
                // Insert into WordPress editor
                if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                    tinymce.get('content').setContent(content);
                } else if ($('#content').length) {
                    $('#content').val(content);
                }
                
                alert('Content inserted successfully!');
            });
        });
        </script>
        <?php
    }
    
    public function seo_settings_meta_box($post) {
        $title = get_post_meta($post->ID, '_seoforge_title', true);
        $description = get_post_meta($post->ID, '_seoforge_description', true);
        $keywords = get_post_meta($post->ID, '_seoforge_keywords', true);
        ?>
        <div class="seoforge-seo-settings">
            <table class="form-table">
                <tr>
                    <th><label for="seoforge-title">SEO Title:</label></th>
                    <td>
                        <input type="text" id="seoforge-title" name="seoforge_title" value="<?php echo esc_attr($title); ?>" style="width: 100%;" maxlength="60">
                        <p class="description">Recommended: 50-60 characters</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="seoforge-description">Meta Description:</label></th>
                    <td>
                        <textarea id="seoforge-description" name="seoforge_description" rows="3" style="width: 100%;" maxlength="160"><?php echo esc_textarea($description); ?></textarea>
                        <p class="description">Recommended: 150-160 characters</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="seoforge-keywords-meta">Keywords:</label></th>
                    <td>
                        <input type="text" id="seoforge-keywords-meta" name="seoforge_keywords" value="<?php echo esc_attr($keywords); ?>" style="width: 100%;">
                        <p class="description">Keywords separated by commas</p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    public function ajax_generate_content() {
        check_ajax_referer('seoforge_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $keywords = sanitize_text_field($_POST['keywords'] ?? '');
        $industry = sanitize_text_field($_POST['industry'] ?? '');
        $content_type = sanitize_text_field($_POST['content_type'] ?? 'blog_post');
        $language = sanitize_text_field($_POST['language'] ?? 'en');
        
        if (empty($keywords)) {
            wp_send_json_error('Keywords are required');
        }
        
        // Prepare data for MCP server
        $data = [
            'action' => 'generate_content',
            'data' => [
                'keywords' => $keywords,
                'industry' => $industry,
                'type' => $content_type,
                'language' => $language,
                'tone' => 'professional',
                'length' => 'medium',
                'include_title' => true,
                'include_meta_description' => true
            ],
            'site_url' => get_site_url(),
            'user_id' => get_current_user_id(),
            'version' => '1.0.0'
        ];
        
        $response = $this->make_api_request($data);
        
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }
        
        wp_send_json_success($response);
    }
    
    public function ajax_generate_image() {
        check_ajax_referer('seoforge_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $prompt = sanitize_text_field($_POST['prompt'] ?? '');
        $style = sanitize_text_field($_POST['style'] ?? 'realistic');
        
        if (empty($prompt)) {
            wp_send_json_error('Image prompt is required');
        }
        
        // Prepare data for MCP server
        $data = [
            'action' => 'generate_image',
            'data' => [
                'prompt' => $prompt,
                'style' => $style,
                'size' => '1024x1024',
                'quality' => 'high'
            ],
            'site_url' => get_site_url(),
            'user_id' => get_current_user_id(),
            'version' => '1.0.0'
        ];
        
        $response = $this->make_api_request($data);
        
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }
        
        wp_send_json_success($response);
    }
    
    private function make_api_request($data) {
        $url = $this->api_url . '/wordpress/plugin';
        
        $headers = [
            'Content-Type' => 'application/json',
            'X-WordPress-Site' => get_site_url(),
            'X-WordPress-Timestamp' => time(),
            'User-Agent' => 'SEO Forge WordPress Plugin/1.0.0'
        ];
        
        $body = wp_json_encode($data);
        
        $args = [
            'headers' => $headers,
            'body' => $body,
            'timeout' => 30,
            'method' => 'POST'
        ];
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code !== 200) {
            return new WP_Error('api_error', "API request failed with code: $response_code. Response: $response_body");
        }
        
        $decoded = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', 'Invalid JSON response from server');
        }
        
        return $decoded;
    }
    
    public function add_meta_tags() {
        global $post;
        
        if (is_singular() && $post) {
            $title = get_post_meta($post->ID, '_seoforge_title', true);
            $description = get_post_meta($post->ID, '_seoforge_description', true);
            $keywords = get_post_meta($post->ID, '_seoforge_keywords', true);
            
            if ($title) {
                echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
            }
            
            if ($description) {
                echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
                echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
            }
            
            if ($keywords) {
                echo '<meta name="keywords" content="' . esc_attr($keywords) . '">' . "\n";
            }
            
            echo '<meta name="generator" content="SEO Forge 1.0.0">' . "\n";
        }
    }
    
    public function dashboard_page() {
        ?>
        <div class="wrap">
            <h1>SEO Forge Dashboard</h1>
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>🚀 Welcome to SEO Forge!</h2>
                <p>Universal SEO WordPress Plugin with AI-powered content generation and optimization.</p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">
                    <div style="border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
                        <h3>🤖 Content Generator</h3>
                        <p>Generate SEO-optimized content using AI. Create blog posts, product descriptions, and more.</p>
                        <a href="<?php echo admin_url('admin.php?page=seo-forge-generator'); ?>" class="button button-primary">Generate Content</a>
                    </div>
                    
                    <div style="border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
                        <h3>📝 Create New Post</h3>
                        <p>Create a new post with SEO Forge meta boxes for content generation and optimization.</p>
                        <a href="<?php echo admin_url('post-new.php'); ?>" class="button button-primary">New Post</a>
                    </div>
                    
                    <div style="border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
                        <h3>⚙️ API Status</h3>
                        <p><strong>Server:</strong> <?php echo esc_html($this->api_url); ?></p>
                        <p><strong>Status:</strong> <span style="color: green;">✅ Ready</span></p>
                        <button type="button" id="test-api" class="button">Test Connection</button>
                    </div>
                </div>
                
                <div style="background: #f0f8ff; border: 1px solid #0288d1; border-radius: 6px; padding: 15px; margin: 20px 0;">
                    <h3 style="margin: 0 0 10px; color: #01579b;">🧪 Testing Instructions</h3>
                    <ol style="margin: 0; color: #01579b;">
                        <li>Go to <strong>Posts → Add New</strong> to create a new blog post</li>
                        <li>In the <strong>SEO Forge - Content Generator</strong> meta box (sidebar), enter keywords like "WordPress SEO optimization"</li>
                        <li>Select an industry (e.g., Technology) and content type (Blog Post)</li>
                        <li>Click <strong>"Generate Content"</strong> to create AI-powered content</li>
                        <li>Click <strong>"Generate Image"</strong> to create a featured image</li>
                        <li>Use the generated content and publish your post</li>
                        <li>Check the frontend to see SEO meta tags in action</li>
                    </ol>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#test-api').on('click', function() {
                $(this).prop('disabled', true).text('Testing...');
                
                $.post(ajaxurl, {
                    action: 'seoforge_generate_content',
                    nonce: '<?php echo wp_create_nonce('seoforge_nonce'); ?>',
                    keywords: 'test connection'
                }, function(response) {
                    $('#test-api').prop('disabled', false).text('Test Connection');
                    if (response.success) {
                        alert('✅ API Connection Successful!\n\nYour MCP server is responding correctly.');
                    } else {
                        alert('❌ API Connection Failed:\n\n' + (response.data || 'Unknown error'));
                    }
                }).fail(function() {
                    $('#test-api').prop('disabled', false).text('Test Connection');
                    alert('❌ Network Error:\n\nCould not connect to the server.');
                });
            });
        });
        </script>
        <?php
    }
    
    public function generator_page() {
        ?>
        <div class="wrap">
            <h1>SEO Forge Content Generator</h1>
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>🤖 AI-Powered Content Generation</h2>
                
                <form id="content-generator-form" style="max-width: 600px;">
                    <table class="form-table">
                        <tr>
                            <th><label for="gen-keywords">Keywords:</label></th>
                            <td>
                                <input type="text" id="gen-keywords" placeholder="e.g., WordPress SEO, content optimization" style="width: 100%;" required>
                                <p class="description">Enter keywords separated by commas</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="gen-industry">Industry:</label></th>
                            <td>
                                <select id="gen-industry" style="width: 100%;">
                                    <option value="">Select Industry</option>
                                    <option value="technology">Technology</option>
                                    <option value="healthcare">Healthcare</option>
                                    <option value="finance">Finance</option>
                                    <option value="education">Education</option>
                                    <option value="retail">Retail</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="gen-type">Content Type:</label></th>
                            <td>
                                <select id="gen-type" style="width: 100%;">
                                    <option value="blog_post">Blog Post</option>
                                    <option value="product_description">Product Description</option>
                                    <option value="landing_page">Landing Page</option>
                                    <option value="how_to_guide">How-to Guide</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="gen-language">Language:</label></th>
                            <td>
                                <select id="gen-language" style="width: 100%;">
                                    <option value="en">English</option>
                                    <option value="th">Thai</option>
                                    <option value="es">Spanish</option>
                                    <option value="fr">French</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    
                    <p>
                        <button type="submit" class="button button-primary button-large">
                            🚀 Generate Content & Image
                        </button>
                    </p>
                </form>
                
                <div id="generation-loading" style="display: none; text-align: center; margin: 20px 0;">
                    <p style="font-size: 16px;">🤖 AI is generating your content and image... Please wait.</p>
                    <div style="background: #f0f0f0; height: 4px; border-radius: 2px; overflow: hidden;">
                        <div style="background: #2563eb; height: 100%; width: 0%; animation: progress 3s ease-in-out infinite;"></div>
                    </div>
                </div>
                
                <div id="generation-result" style="display: none; margin-top: 30px;">
                    <h3>📝 Generated Content:</h3>
                    <div id="result-content" style="background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd; margin: 15px 0;"></div>
                    
                    <h3>🎨 Generated Image:</h3>
                    <div id="result-image" style="text-align: center; margin: 15px 0;"></div>
                    
                    <p>
                        <button type="button" id="create-post-btn" class="button button-primary button-large">
                            📄 Create New Post with This Content
                        </button>
                    </p>
                </div>
            </div>
        </div>
        
        <style>
        @keyframes progress {
            0% { width: 0%; }
            50% { width: 70%; }
            100% { width: 100%; }
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#content-generator-form').on('submit', function(e) {
                e.preventDefault();
                
                var keywords = $('#gen-keywords').val();
                if (!keywords) {
                    alert('Please enter keywords!');
                    return;
                }
                
                $('#generation-loading').show();
                $('#generation-result').hide();
                
                // Generate content first
                $.post(ajaxurl, {
                    action: 'seoforge_generate_content',
                    nonce: '<?php echo wp_create_nonce('seoforge_nonce'); ?>',
                    keywords: keywords,
                    industry: $('#gen-industry').val(),
                    content_type: $('#gen-type').val(),
                    language: $('#gen-language').val()
                }, function(contentResponse) {
                    if (contentResponse.success) {
                        $('#result-content').html(contentResponse.data.content || contentResponse.data);
                        
                        // Generate image
                        $.post(ajaxurl, {
                            action: 'seoforge_generate_image',
                            nonce: '<?php echo wp_create_nonce('seoforge_nonce'); ?>',
                            prompt: 'Professional image related to: ' + keywords,
                            style: 'realistic'
                        }, function(imageResponse) {
                            $('#generation-loading').hide();
                            
                            if (imageResponse.success && imageResponse.data.url) {
                                $('#result-image').html('<img src="' + imageResponse.data.url + '" alt="Generated Image" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">');
                            } else {
                                $('#result-image').html('<p style="color: #666;">Image generation failed, but content was created successfully.</p>');
                            }
                            
                            $('#generation-result').show();
                            
                            // Store data for post creation
                            window.generatedData = {
                                title: contentResponse.data.title || keywords + ' - Generated Content',
                                content: contentResponse.data.content || contentResponse.data,
                                image: imageResponse.success ? imageResponse.data.url : null
                            };
                        }).fail(function() {
                            $('#generation-loading').hide();
                            $('#result-image').html('<p style="color: #666;">Image generation failed, but content was created successfully.</p>');
                            $('#generation-result').show();
                        });
                    } else {
                        $('#generation-loading').hide();
                        alert('Content generation failed: ' + (contentResponse.data || 'Unknown error'));
                    }
                }).fail(function() {
                    $('#generation-loading').hide();
                    alert('Network error. Please check your connection and try again.');
                });
            });
            
            $('#create-post-btn').on('click', function() {
                if (window.generatedData) {
                    var url = '<?php echo admin_url('post-new.php'); ?>' + 
                             '?seoforge_title=' + encodeURIComponent(window.generatedData.title) +
                             '&seoforge_content=' + encodeURIComponent(window.generatedData.content);
                    
                    window.open(url, '_blank');
                }
            });
        });
        </script>
        <?php
    }
}

// Initialize the plugin
new SEOForgePlaygroundTest();

// Handle pre-filled content from generator
add_action('admin_footer-post-new.php', function() {
    if (isset($_GET['seoforge_title']) && isset($_GET['seoforge_content'])) {
        $title = sanitize_text_field($_GET['seoforge_title']);
        $content = wp_kses_post($_GET['seoforge_content']);
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Fill title
            $('#title').val(<?php echo wp_json_encode($title); ?>);
            
            // Fill content
            var content = <?php echo wp_json_encode($content); ?>;
            if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                tinymce.get('content').setContent(content);
            } else {
                $('#content').val(content);
            }
            
            // Show success message
            $('<div class="notice notice-success is-dismissible"><p><strong>SEO Forge:</strong> Content has been pre-filled from the generator!</p></div>')
                .insertAfter('.wp-header-end');
        });
        </script>
        <?php
    }
});
?>