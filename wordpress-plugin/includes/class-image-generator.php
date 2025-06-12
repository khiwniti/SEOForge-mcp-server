<?php
/**
 * AI Image Generator Class
 * 
 * Handles AI image generation integration with Universal MCP Server
 * 
 * @package UniversalMCP
 * @since 3.0.0-enhanced
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * UMCP Image Generator Class
 */
class UMCP_Image_Generator {
    
    /**
     * MCP Client instance
     */
    private $mcp_client;
    
    /**
     * Image generation settings
     */
    private $settings;
    
    /**
     * Constructor
     */
    public function __construct($mcp_client) {
        $this->mcp_client = $mcp_client;
        $this->settings = get_option('umcp_image_settings', $this->get_default_settings());
        
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('wp_ajax_umcp_generate_image', array($this, 'ajax_generate_image'));
        add_action('wp_ajax_umcp_generate_blog_with_images', array($this, 'ajax_generate_blog_with_images'));
        add_action('wp_ajax_umcp_get_image_gallery', array($this, 'ajax_get_image_gallery'));
        
        // Add image generation to post editor
        add_action('add_meta_boxes', array($this, 'add_image_generation_metabox'));
        add_action('save_post', array($this, 'save_image_generation_data'));
        
        // Enqueue scripts for image generation
        add_action('admin_enqueue_scripts', array($this, 'enqueue_image_scripts'));
    }
    
    /**
     * Get default image generation settings
     */
    private function get_default_settings() {
        return array(
            'default_style' => 'professional',
            'default_size' => '1024x1024',
            'default_count' => 3,
            'auto_generate' => true,
            'save_to_media_library' => true,
            'image_quality' => 'high',
            'providers' => array('dalle', 'stable_diffusion', 'midjourney')
        );
    }
    
    /**
     * Generate AI image
     */
    public function generate_image($prompt, $style = 'professional', $size = '1024x1024', $count = 1) {
        try {
            $endpoint = '/universal-mcp/generate-image';
            $data = array(
                'prompt' => sanitize_text_field($prompt),
                'style' => sanitize_text_field($style),
                'size' => sanitize_text_field($size),
                'count' => intval($count)
            );
            
            $response = $this->mcp_client->make_request($endpoint, $data, 'POST');
            
            if ($response && isset($response['success']) && $response['success']) {
                // Save image to WordPress media library if enabled
                if ($this->settings['save_to_media_library']) {
                    $response['image'] = $this->save_to_media_library($response['image']);
                }
                
                return $response;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log('UMCP Image Generation Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate blog content with images
     */
    public function generate_blog_with_images($topic, $keywords = array(), $options = array()) {
        try {
            $endpoint = '/universal-mcp/generate-blog-with-images';
            
            $default_options = array(
                'content_type' => 'blog_post',
                'tone' => 'professional',
                'length' => 'comprehensive',
                'industry' => 'general',
                'language' => 'en',
                'include_images' => true,
                'image_count' => $this->settings['default_count'],
                'image_style' => $this->settings['default_style']
            );
            
            $options = wp_parse_args($options, $default_options);
            
            $data = array(
                'topic' => sanitize_text_field($topic),
                'keywords' => array_map('sanitize_text_field', $keywords),
                'website_url' => get_site_url(),
                'content_type' => $options['content_type'],
                'tone' => $options['tone'],
                'length' => $options['length'],
                'industry' => $options['industry'],
                'language' => $options['language'],
                'include_images' => $options['include_images'],
                'image_count' => intval($options['image_count']),
                'image_style' => $options['image_style']
            );
            
            $response = $this->mcp_client->make_request($endpoint, $data, 'POST');
            
            if ($response && isset($response['success']) && $response['success']) {
                // Save images to WordPress media library
                if ($this->settings['save_to_media_library'] && isset($response['images'])) {
                    foreach ($response['images'] as &$image) {
                        $image = $this->save_to_media_library($image);
                    }
                }
                
                return $response;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log('UMCP Blog with Images Generation Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Save generated image to WordPress media library
     */
    private function save_to_media_library($image_data) {
        if (!isset($image_data['url']) || !isset($image_data['filename'])) {
            return $image_data;
        }
        
        try {
            // Get image from MCP server
            $image_url = $this->mcp_client->get_server_url() . $image_data['url'];
            $image_content = wp_remote_get($image_url);
            
            if (is_wp_error($image_content)) {
                return $image_data;
            }
            
            $image_body = wp_remote_retrieve_body($image_content);
            
            // Upload to WordPress
            $upload_dir = wp_upload_dir();
            $filename = 'umcp-' . $image_data['filename'];
            $file_path = $upload_dir['path'] . '/' . $filename;
            
            file_put_contents($file_path, $image_body);
            
            // Create attachment
            $attachment = array(
                'guid' => $upload_dir['url'] . '/' . $filename,
                'post_mime_type' => 'image/png',
                'post_title' => sanitize_file_name($image_data['prompt']),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            $attachment_id = wp_insert_attachment($attachment, $file_path);
            
            if (!is_wp_error($attachment_id)) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
                wp_update_attachment_metadata($attachment_id, $attachment_data);
                
                // Update image data with WordPress info
                $image_data['wordpress_id'] = $attachment_id;
                $image_data['wordpress_url'] = wp_get_attachment_url($attachment_id);
                $image_data['wordpress_path'] = $file_path;
            }
            
        } catch (Exception $e) {
            error_log('UMCP Media Library Save Error: ' . $e->getMessage());
        }
        
        return $image_data;
    }
    
    /**
     * Add image generation metabox to post editor
     */
    public function add_image_generation_metabox() {
        add_meta_box(
            'umcp-image-generator',
            __('AI Image Generation', 'universal-mcp'),
            array($this, 'render_image_generation_metabox'),
            array('post', 'page'),
            'side',
            'high'
        );
    }
    
    /**
     * Render image generation metabox
     */
    public function render_image_generation_metabox($post) {
        wp_nonce_field('umcp_image_generation', 'umcp_image_generation_nonce');
        
        $generated_images = get_post_meta($post->ID, '_umcp_generated_images', true);
        ?>
        <div id="umcp-image-generator">
            <div class="umcp-image-controls">
                <p>
                    <label for="umcp-image-prompt"><?php _e('Image Prompt:', 'universal-mcp'); ?></label>
                    <textarea id="umcp-image-prompt" name="umcp_image_prompt" rows="3" style="width: 100%;" placeholder="<?php _e('Describe the image you want to generate...', 'universal-mcp'); ?>"></textarea>
                </p>
                
                <p>
                    <label for="umcp-image-style"><?php _e('Style:', 'universal-mcp'); ?></label>
                    <select id="umcp-image-style" name="umcp_image_style" style="width: 100%;">
                        <option value="professional"><?php _e('Professional', 'universal-mcp'); ?></option>
                        <option value="artistic"><?php _e('Artistic', 'universal-mcp'); ?></option>
                        <option value="minimalist"><?php _e('Minimalist', 'universal-mcp'); ?></option>
                        <option value="commercial"><?php _e('Commercial', 'universal-mcp'); ?></option>
                    </select>
                </p>
                
                <p>
                    <label for="umcp-image-count"><?php _e('Number of Images:', 'universal-mcp'); ?></label>
                    <select id="umcp-image-count" name="umcp_image_count" style="width: 100%;">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3" selected>3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </p>
                
                <p>
                    <button type="button" id="umcp-generate-images" class="button button-primary" style="width: 100%;">
                        <?php _e('Generate AI Images', 'universal-mcp'); ?>
                    </button>
                </p>
            </div>
            
            <div id="umcp-image-gallery" class="umcp-image-gallery">
                <?php if ($generated_images): ?>
                    <?php foreach ($generated_images as $image): ?>
                        <div class="umcp-image-item">
                            <img src="<?php echo esc_url($image['wordpress_url'] ?? $image['url']); ?>" alt="<?php echo esc_attr($image['prompt']); ?>" style="width: 100%; height: auto; margin-bottom: 10px;">
                            <p style="font-size: 12px; color: #666;"><?php echo esc_html($image['prompt']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div id="umcp-image-loading" style="display: none; text-align: center; padding: 20px;">
                <div class="spinner is-active"></div>
                <p><?php _e('Generating AI images...', 'universal-mcp'); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Save image generation data
     */
    public function save_image_generation_data($post_id) {
        if (!isset($_POST['umcp_image_generation_nonce']) || 
            !wp_verify_nonce($_POST['umcp_image_generation_nonce'], 'umcp_image_generation')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save image generation settings
        if (isset($_POST['umcp_generated_images'])) {
            update_post_meta($post_id, '_umcp_generated_images', $_POST['umcp_generated_images']);
        }
    }
    
    /**
     * Enqueue image generation scripts
     */
    public function enqueue_image_scripts($hook) {
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }
        
        wp_enqueue_script(
            'umcp-image-generator',
            UMCP_PLUGIN_URL . 'assets/js/image-generator.js',
            array('jquery'),
            UMCP_PLUGIN_VERSION,
            true
        );
        
        wp_localize_script('umcp-image-generator', 'umcp_image_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('umcp_image_nonce'),
            'strings' => array(
                'generating' => __('Generating AI images...', 'universal-mcp'),
                'error' => __('Error generating images. Please try again.', 'universal-mcp'),
                'success' => __('Images generated successfully!', 'universal-mcp')
            )
        ));
        
        wp_enqueue_style(
            'umcp-image-generator',
            UMCP_PLUGIN_URL . 'assets/css/image-generator.css',
            array(),
            UMCP_PLUGIN_VERSION
        );
    }
    
    /**
     * AJAX handler for image generation
     */
    public function ajax_generate_image() {
        check_ajax_referer('umcp_image_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('Insufficient permissions', 'universal-mcp'));
        }
        
        $prompt = sanitize_text_field($_POST['prompt'] ?? '');
        $style = sanitize_text_field($_POST['style'] ?? 'professional');
        $size = sanitize_text_field($_POST['size'] ?? '1024x1024');
        $count = intval($_POST['count'] ?? 1);
        
        if (empty($prompt)) {
            wp_send_json_error(__('Please provide an image prompt', 'universal-mcp'));
        }
        
        $result = $this->generate_image($prompt, $style, $size, $count);
        
        if ($result) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error(__('Failed to generate image', 'universal-mcp'));
        }
    }
    
    /**
     * AJAX handler for blog with images generation
     */
    public function ajax_generate_blog_with_images() {
        check_ajax_referer('umcp_image_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('Insufficient permissions', 'universal-mcp'));
        }
        
        $topic = sanitize_text_field($_POST['topic'] ?? '');
        $keywords = array_map('sanitize_text_field', $_POST['keywords'] ?? array());
        $options = $_POST['options'] ?? array();
        
        if (empty($topic)) {
            wp_send_json_error(__('Please provide a topic', 'universal-mcp'));
        }
        
        $result = $this->generate_blog_with_images($topic, $keywords, $options);
        
        if ($result) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error(__('Failed to generate blog with images', 'universal-mcp'));
        }
    }
    
    /**
     * AJAX handler for getting image gallery
     */
    public function ajax_get_image_gallery() {
        check_ajax_referer('umcp_image_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('Insufficient permissions', 'universal-mcp'));
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $images = get_post_meta($post_id, '_umcp_generated_images', true);
        
        wp_send_json_success($images ?: array());
    }
    
    /**
     * Get image generation settings
     */
    public function get_settings() {
        return $this->settings;
    }
    
    /**
     * Update image generation settings
     */
    public function update_settings($new_settings) {
        $this->settings = wp_parse_args($new_settings, $this->settings);
        update_option('umcp_image_settings', $this->settings);
    }
}