<?php

/**
 * AJAX functionality for the plugin
 *
 * @package    SEO_Forge
 * @subpackage SEO_Forge/includes
 */

/**
 * AJAX functionality for the plugin.
 *
 * Handles all AJAX requests from the admin interface.
 */
class SEO_Forge_Ajax {

    /**
     * Initialize the AJAX hooks
     */
    public function __construct() {
        add_action('wp_ajax_seo_forge_generate_content', array($this, 'generate_content'));
        add_action('wp_ajax_seo_forge_generate_title', array($this, 'generate_title'));
        add_action('wp_ajax_seo_forge_generate_meta', array($this, 'generate_meta'));
        add_action('wp_ajax_seo_forge_generate_image', array($this, 'generate_image'));
        add_action('wp_ajax_seo_forge_analyze_seo', array($this, 'analyze_seo'));
        add_action('wp_ajax_seo_forge_get_keywords', array($this, 'get_keywords'));
        add_action('wp_ajax_seo_forge_save_image', array($this, 'save_image'));
        add_action('wp_ajax_seo_forge_test_api', array($this, 'test_api'));
    }

    /**
     * Verify nonce for security
     */
    private function verify_nonce() {
        if (!wp_verify_nonce($_POST['nonce'], 'seo_forge_nonce')) {
            wp_die('Security check failed');
        }
    }

    /**
     * Generate blog content
     */
    public function generate_content() {
        $this->verify_nonce();

        $topic = sanitize_text_field($_POST['topic']);
        $keywords = array_map('sanitize_text_field', $_POST['keywords']);
        $word_count = intval($_POST['word_count']);

        $content_generator = new SEO_Forge_Content_Generator();
        $result = $content_generator->generate_blog_content($topic, $keywords, $word_count);

        if ($result['success']) {
            // Update content count
            $count = get_option('seo_forge_content_count', 0);
            update_option('seo_forge_content_count', $count + 1);
        }

        wp_send_json($result);
    }

    /**
     * Generate title
     */
    public function generate_title() {
        $this->verify_nonce();

        $content = sanitize_textarea_field($_POST['content']);
        $keyword = sanitize_text_field($_POST['keyword']);

        $content_generator = new SEO_Forge_Content_Generator();
        $result = $content_generator->generate_title($content, $keyword);

        wp_send_json($result);
    }

    /**
     * Generate meta description
     */
    public function generate_meta() {
        $this->verify_nonce();

        $content = sanitize_textarea_field($_POST['content']);
        $keyword = sanitize_text_field($_POST['keyword']);

        $content_generator = new SEO_Forge_Content_Generator();
        $result = $content_generator->generate_meta_description($content, $keyword);

        wp_send_json($result);
    }

    /**
     * Generate image
     */
    public function generate_image() {
        $this->verify_nonce();

        $prompt = sanitize_text_field($_POST['prompt']);
        $style = sanitize_text_field($_POST['style']);

        $content_generator = new SEO_Forge_Content_Generator();
        $result = $content_generator->generate_image($prompt, $style);

        wp_send_json($result);
    }

    /**
     * Analyze SEO
     */
    public function analyze_seo() {
        $this->verify_nonce();

        $content = sanitize_textarea_field($_POST['content']);
        $keyword = sanitize_text_field($_POST['keyword']);

        $content_generator = new SEO_Forge_Content_Generator();
        $result = $content_generator->analyze_seo($content, $keyword);

        wp_send_json($result);
    }

    /**
     * Get keyword suggestions
     */
    public function get_keywords() {
        $this->verify_nonce();

        $topic = sanitize_text_field($_POST['topic']);

        $content_generator = new SEO_Forge_Content_Generator();
        $result = $content_generator->get_keyword_suggestions($topic);

        wp_send_json($result);
    }

    /**
     * Save image to media library
     */
    public function save_image() {
        $this->verify_nonce();

        $image_url = esc_url_raw($_POST['image_url']);

        // Download and save image to media library
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($image_url);
        $filename = 'seo-forge-generated-' . time() . '.jpg';
        $file = $upload_dir['path'] . '/' . $filename;

        if (file_put_contents($file, $image_data)) {
            $attachment = array(
                'post_mime_type' => 'image/jpeg',
                'post_title' => 'SEO-Forge Generated Image',
                'post_content' => '',
                'post_status' => 'inherit'
            );

            $attach_id = wp_insert_attachment($attachment, $file);
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $file);
            wp_update_attachment_metadata($attach_id, $attach_data);

            wp_send_json_success(array('attachment_id' => $attach_id));
        } else {
            wp_send_json_error(array('message' => 'Failed to save image'));
        }
    }

    /**
     * Test API connection
     */
    public function test_api() {
        $this->verify_nonce();

        $api = new SEO_Forge_API();
        $result = $api->test_connection();

        wp_send_json($result);
    }
}

// Initialize AJAX handlers
new SEO_Forge_Ajax();
