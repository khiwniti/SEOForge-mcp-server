<?php
/**
 * WordPress-Style Editor Class
 * 
 * Provides WordPress-style editing interface with AI image generation
 * 
 * @package UniversalMCP
 * @since 3.0.0-enhanced
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * UMCP WordPress Editor Class
 */
class UMCP_WordPress_Editor {
    
    /**
     * MCP Client instance
     */
    private $mcp_client;
    
    /**
     * Image Generator instance
     */
    private $image_generator;
    
    /**
     * Constructor
     */
    public function __construct($mcp_client, $image_generator) {
        $this->mcp_client = $mcp_client;
        $this->image_generator = $image_generator;
        
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Add WordPress-style editor page
        add_action('admin_menu', array($this, 'add_editor_page'));
        
        // Enqueue editor scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_editor_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_umcp_editor_generate_content', array($this, 'ajax_generate_content'));
        add_action('wp_ajax_umcp_editor_save_draft', array($this, 'ajax_save_draft'));
        add_action('wp_ajax_umcp_editor_publish_post', array($this, 'ajax_publish_post'));
        
        // Add editor button to post list
        add_filter('post_row_actions', array($this, 'add_editor_row_action'), 10, 2);
        add_filter('page_row_actions', array($this, 'add_editor_row_action'), 10, 2);
    }
    
    /**
     * Add WordPress-style editor page to admin menu
     */
    public function add_editor_page() {
        add_submenu_page(
            'universal-mcp',
            __('WordPress-Style Editor', 'universal-mcp'),
            __('AI Blog Editor', 'universal-mcp'),
            'edit_posts',
            'umcp-wordpress-editor',
            array($this, 'render_editor_page')
        );
    }
    
    /**
     * Render WordPress-style editor page
     */
    public function render_editor_page() {
        $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        $post = $post_id ? get_post($post_id) : null;
        ?>
        <div class="wrap umcp-wordpress-editor">
            <h1 class="wp-heading-inline">
                <?php echo $post ? __('Edit Post with AI', 'universal-mcp') : __('Create New Post with AI', 'universal-mcp'); ?>
            </h1>
            
            <?php if ($post): ?>
                <a href="<?php echo admin_url('post.php?post=' . $post_id . '&action=edit'); ?>" class="page-title-action">
                    <?php _e('Switch to Classic Editor', 'universal-mcp'); ?>
                </a>
            <?php endif; ?>
            
            <hr class="wp-header-end">
            
            <div class="umcp-editor-container">
                <div class="umcp-editor-sidebar">
                    <div class="umcp-editor-panel">
                        <h3><?php _e('Content Generation', 'universal-mcp'); ?></h3>
                        
                        <div class="umcp-form-group">
                            <label for="umcp-post-title"><?php _e('Post Title:', 'universal-mcp'); ?></label>
                            <input type="text" id="umcp-post-title" class="umcp-form-control" 
                                   value="<?php echo $post ? esc_attr($post->post_title) : ''; ?>"
                                   placeholder="<?php _e('Enter your post title...', 'universal-mcp'); ?>">
                        </div>
                        
                        <div class="umcp-form-group">
                            <label for="umcp-keywords"><?php _e('Keywords (Thai/English):', 'universal-mcp'); ?></label>
                            <textarea id="umcp-keywords" class="umcp-form-control" rows="3" 
                                      placeholder="<?php _e('กระดาษมวนขายส่ง, rolling papers, wholesale...', 'universal-mcp'); ?>"></textarea>
                        </div>
                        
                        <div class="umcp-form-row">
                            <div class="umcp-form-group">
                                <label for="umcp-language"><?php _e('Language:', 'universal-mcp'); ?></label>
                                <select id="umcp-language" class="umcp-form-control">
                                    <option value="th"><?php _e('Thai (ไทย)', 'universal-mcp'); ?></option>
                                    <option value="en"><?php _e('English', 'universal-mcp'); ?></option>
                                    <option value="es"><?php _e('Spanish', 'universal-mcp'); ?></option>
                                    <option value="fr"><?php _e('French', 'universal-mcp'); ?></option>
                                    <option value="de"><?php _e('German', 'universal-mcp'); ?></option>
                                </select>
                            </div>
                            
                            <div class="umcp-form-group">
                                <label for="umcp-industry"><?php _e('Industry:', 'universal-mcp'); ?></label>
                                <select id="umcp-industry" class="umcp-form-control">
                                    <option value="ecommerce"><?php _e('E-commerce', 'universal-mcp'); ?></option>
                                    <option value="healthcare"><?php _e('Healthcare', 'universal-mcp'); ?></option>
                                    <option value="finance"><?php _e('Finance', 'universal-mcp'); ?></option>
                                    <option value="technology"><?php _e('Technology', 'universal-mcp'); ?></option>
                                    <option value="education"><?php _e('Education', 'universal-mcp'); ?></option>
                                    <option value="general"><?php _e('General', 'universal-mcp'); ?></option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="umcp-form-group">
                            <label for="umcp-tone"><?php _e('Tone:', 'universal-mcp'); ?></label>
                            <select id="umcp-tone" class="umcp-form-control">
                                <option value="professional"><?php _e('Professional', 'universal-mcp'); ?></option>
                                <option value="casual"><?php _e('Casual', 'universal-mcp'); ?></option>
                                <option value="friendly"><?php _e('Friendly', 'universal-mcp'); ?></option>
                                <option value="authoritative"><?php _e('Authoritative', 'universal-mcp'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="umcp-editor-panel">
                        <h3><?php _e('AI Image Generation', 'universal-mcp'); ?></h3>
                        
                        <div class="umcp-form-group">
                            <label>
                                <input type="checkbox" id="umcp-include-images" checked>
                                <?php _e('Generate AI Images', 'universal-mcp'); ?>
                            </label>
                        </div>
                        
                        <div class="umcp-form-row">
                            <div class="umcp-form-group">
                                <label for="umcp-image-count"><?php _e('Image Count:', 'universal-mcp'); ?></label>
                                <select id="umcp-image-count" class="umcp-form-control">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3" selected>3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                            </div>
                            
                            <div class="umcp-form-group">
                                <label for="umcp-image-style"><?php _e('Image Style:', 'universal-mcp'); ?></label>
                                <select id="umcp-image-style" class="umcp-form-control">
                                    <option value="professional"><?php _e('Professional', 'universal-mcp'); ?></option>
                                    <option value="artistic"><?php _e('Artistic', 'universal-mcp'); ?></option>
                                    <option value="minimalist"><?php _e('Minimalist', 'universal-mcp'); ?></option>
                                    <option value="commercial"><?php _e('Commercial', 'universal-mcp'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="umcp-editor-actions">
                        <button type="button" id="umcp-generate-content" class="button button-primary button-large">
                            <?php _e('🤖 Generate Content with Images', 'universal-mcp'); ?>
                        </button>
                        
                        <button type="button" id="umcp-save-draft" class="button button-secondary">
                            <?php _e('💾 Save Draft', 'universal-mcp'); ?>
                        </button>
                        
                        <button type="button" id="umcp-publish-post" class="button button-secondary">
                            <?php _e('📤 Publish Post', 'universal-mcp'); ?>
                        </button>
                    </div>
                </div>
                
                <div class="umcp-editor-main">
                    <div class="umcp-editor-toolbar">
                        <div class="umcp-toolbar-group">
                            <button type="button" class="button" id="umcp-preview-mode">
                                <?php _e('👁️ Preview', 'universal-mcp'); ?>
                            </button>
                            <button type="button" class="button" id="umcp-edit-mode">
                                <?php _e('✏️ Edit', 'universal-mcp'); ?>
                            </button>
                            <button type="button" class="button" id="umcp-seo-analysis">
                                <?php _e('📊 SEO Analysis', 'universal-mcp'); ?>
                            </button>
                        </div>
                        
                        <div class="umcp-toolbar-group">
                            <span class="umcp-word-count">
                                <?php _e('Words:', 'universal-mcp'); ?> <span id="umcp-word-count">0</span>
                            </span>
                            <span class="umcp-seo-score">
                                <?php _e('SEO Score:', 'universal-mcp'); ?> <span id="umcp-seo-score">--</span>
                            </span>
                        </div>
                    </div>
                    
                    <div class="umcp-editor-content">
                        <div id="umcp-content-editor" class="umcp-content-area">
                            <?php
                            wp_editor(
                                $post ? $post->post_content : '',
                                'umcp_post_content',
                                array(
                                    'textarea_name' => 'umcp_post_content',
                                    'textarea_rows' => 20,
                                    'teeny' => false,
                                    'media_buttons' => true,
                                    'tinymce' => array(
                                        'toolbar1' => 'formatselect,bold,italic,underline,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
                                        'toolbar2' => 'styleselect,fontselect,fontsizeselect,forecolor,backcolor,indent,outdent,undo,redo,wp_help'
                                    )
                                )
                            );
                            ?>
                        </div>
                        
                        <div id="umcp-content-preview" class="umcp-content-area" style="display: none;">
                            <div class="umcp-preview-content"></div>
                        </div>
                        
                        <div id="umcp-seo-panel" class="umcp-content-area" style="display: none;">
                            <div class="umcp-seo-analysis">
                                <h3><?php _e('SEO Analysis', 'universal-mcp'); ?></h3>
                                <div id="umcp-seo-results"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="umcp-image-gallery-container">
                        <h3><?php _e('Generated Images', 'universal-mcp'); ?></h3>
                        <div id="umcp-generated-images" class="umcp-image-gallery"></div>
                    </div>
                </div>
            </div>
            
            <div id="umcp-loading-overlay" class="umcp-loading-overlay" style="display: none;">
                <div class="umcp-loading-content">
                    <div class="spinner is-active"></div>
                    <h3><?php _e('AI is working its magic...', 'universal-mcp'); ?></h3>
                    <p id="umcp-loading-message"><?php _e('Generating your content with AI images', 'universal-mcp'); ?></p>
                </div>
            </div>
        </div>
        
        <input type="hidden" id="umcp-post-id" value="<?php echo $post_id; ?>">
        <input type="hidden" id="umcp-nonce" value="<?php echo wp_create_nonce('umcp_editor_nonce'); ?>">
        <?php
    }
    
    /**
     * Enqueue editor assets
     */
    public function enqueue_editor_assets($hook) {
        if ($hook !== 'universal-mcp_page_umcp-wordpress-editor') {
            return;
        }
        
        // Enqueue WordPress editor
        wp_enqueue_editor();
        
        // Enqueue custom editor scripts
        wp_enqueue_script(
            'umcp-wordpress-editor',
            UMCP_PLUGIN_URL . 'assets/js/wordpress-editor.js',
            array('jquery', 'wp-tinymce'),
            UMCP_PLUGIN_VERSION,
            true
        );
        
        wp_localize_script('umcp-wordpress-editor', 'umcp_editor_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('umcp_editor_nonce'),
            'strings' => array(
                'generating' => __('Generating content with AI images...', 'universal-mcp'),
                'saving' => __('Saving draft...', 'universal-mcp'),
                'publishing' => __('Publishing post...', 'universal-mcp'),
                'error' => __('An error occurred. Please try again.', 'universal-mcp'),
                'success' => __('Operation completed successfully!', 'universal-mcp')
            )
        ));
        
        // Enqueue custom editor styles
        wp_enqueue_style(
            'umcp-wordpress-editor',
            UMCP_PLUGIN_URL . 'assets/css/wordpress-editor.css',
            array(),
            UMCP_PLUGIN_VERSION
        );
    }
    
    /**
     * Add editor row action to post list
     */
    public function add_editor_row_action($actions, $post) {
        if (current_user_can('edit_post', $post->ID)) {
            $actions['umcp_ai_editor'] = sprintf(
                '<a href="%s">%s</a>',
                admin_url('admin.php?page=umcp-wordpress-editor&post=' . $post->ID),
                __('AI Editor', 'universal-mcp')
            );
        }
        
        return $actions;
    }
    
    /**
     * AJAX handler for content generation
     */
    public function ajax_generate_content() {
        check_ajax_referer('umcp_editor_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('Insufficient permissions', 'universal-mcp'));
        }
        
        $topic = sanitize_text_field($_POST['topic'] ?? '');
        $keywords = array_filter(array_map('trim', explode(',', $_POST['keywords'] ?? '')));
        $language = sanitize_text_field($_POST['language'] ?? 'en');
        $industry = sanitize_text_field($_POST['industry'] ?? 'general');
        $tone = sanitize_text_field($_POST['tone'] ?? 'professional');
        $include_images = filter_var($_POST['include_images'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $image_count = intval($_POST['image_count'] ?? 3);
        $image_style = sanitize_text_field($_POST['image_style'] ?? 'professional');
        
        if (empty($topic)) {
            wp_send_json_error(__('Please provide a topic', 'universal-mcp'));
        }
        
        $options = array(
            'language' => $language,
            'industry' => $industry,
            'tone' => $tone,
            'include_images' => $include_images,
            'image_count' => $image_count,
            'image_style' => $image_style
        );
        
        $result = $this->image_generator->generate_blog_with_images($topic, $keywords, $options);
        
        if ($result) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error(__('Failed to generate content', 'universal-mcp'));
        }
    }
    
    /**
     * AJAX handler for saving draft
     */
    public function ajax_save_draft() {
        check_ajax_referer('umcp_editor_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('Insufficient permissions', 'universal-mcp'));
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $title = sanitize_text_field($_POST['title'] ?? '');
        $content = wp_kses_post($_POST['content'] ?? '');
        $images = $_POST['images'] ?? array();
        
        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'draft',
            'post_type' => 'post'
        );
        
        if ($post_id) {
            $post_data['ID'] = $post_id;
            $result = wp_update_post($post_data);
        } else {
            $result = wp_insert_post($post_data);
        }
        
        if ($result && !is_wp_error($result)) {
            // Save generated images
            if (!empty($images)) {
                update_post_meta($result, '_umcp_generated_images', $images);
            }
            
            wp_send_json_success(array(
                'post_id' => $result,
                'edit_url' => admin_url('admin.php?page=umcp-wordpress-editor&post=' . $result)
            ));
        } else {
            wp_send_json_error(__('Failed to save draft', 'universal-mcp'));
        }
    }
    
    /**
     * AJAX handler for publishing post
     */
    public function ajax_publish_post() {
        check_ajax_referer('umcp_editor_nonce', 'nonce');
        
        if (!current_user_can('publish_posts')) {
            wp_die(__('Insufficient permissions', 'universal-mcp'));
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $title = sanitize_text_field($_POST['title'] ?? '');
        $content = wp_kses_post($_POST['content'] ?? '');
        $images = $_POST['images'] ?? array();
        
        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'post'
        );
        
        if ($post_id) {
            $post_data['ID'] = $post_id;
            $result = wp_update_post($post_data);
        } else {
            $result = wp_insert_post($post_data);
        }
        
        if ($result && !is_wp_error($result)) {
            // Save generated images
            if (!empty($images)) {
                update_post_meta($result, '_umcp_generated_images', $images);
            }
            
            wp_send_json_success(array(
                'post_id' => $result,
                'post_url' => get_permalink($result),
                'edit_url' => admin_url('post.php?post=' . $result . '&action=edit')
            ));
        } else {
            wp_send_json_error(__('Failed to publish post', 'universal-mcp'));
        }
    }
}