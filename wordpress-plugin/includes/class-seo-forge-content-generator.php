<?php

/**
 * Content generation functionality for SEO-Forge plugin
 */
class SEO_Forge_Content_Generator {

    /**
     * The API handler
     */
    private $api;

    /**
     * Initialize the class
     */
    public function __construct() {
        $this->api = new SEO_Forge_API();
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('wp_ajax_seo_forge_generate_content', array($this, 'generate_content'));
        add_action('wp_ajax_seo_forge_generate_title', array($this, 'generate_title'));
        add_action('wp_ajax_seo_forge_generate_meta', array($this, 'generate_meta_description'));
        add_action('wp_ajax_seo_forge_generate_image', array($this, 'generate_image'));
        add_action('wp_ajax_seo_forge_analyze_seo', array($this, 'analyze_seo'));
        add_action('wp_ajax_seo_forge_get_keywords', array($this, 'get_keyword_suggestions'));
    }

    /**
     * Handle content generation AJAX request
     */
    public function generate_content() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'seo_forge_nonce')) {
            wp_die('Security check failed');
        }

        $topic = sanitize_text_field($_POST['topic']);
        $keywords = isset($_POST['keywords']) ? array_map('sanitize_text_field', $_POST['keywords']) : array();
        $word_count = intval($_POST['word_count']) ?: 1000;

        $response = $this->api->generate_blog_content($topic, $keywords, $word_count);

        if ($response['success']) {
            wp_send_json_success($response['data']);
        } else {
            wp_send_json_error(array(
                'message' => $response['error'] ?? __('Failed to generate content', 'seo-forge')
            ));
        }
    }

    /**
     * Handle title generation AJAX request
     */
    public function generate_title() {
        if (!wp_verify_nonce($_POST['nonce'], 'seo_forge_nonce')) {
            wp_die('Security check failed');
        }

        $content = sanitize_textarea_field($_POST['content']);
        $keyword = sanitize_text_field($_POST['keyword']);

        $response = $this->api->generate_seo_title($content, $keyword);

        if ($response['success']) {
            wp_send_json_success($response['data']);
        } else {
            wp_send_json_error(array(
                'message' => $response['error'] ?? __('Failed to generate title', 'seo-forge')
            ));
        }
    }

    /**
     * Handle meta description generation AJAX request
     */
    public function generate_meta_description() {
        if (!wp_verify_nonce($_POST['nonce'], 'seo_forge_nonce')) {
            wp_die('Security check failed');
        }

        $content = sanitize_textarea_field($_POST['content']);
        $keyword = sanitize_text_field($_POST['keyword']);

        $response = $this->api->generate_meta_description($content, $keyword);

        if ($response['success']) {
            wp_send_json_success($response['data']);
        } else {
            wp_send_json_error(array(
                'message' => $response['error'] ?? __('Failed to generate meta description', 'seo-forge')
            ));
        }
    }

    /**
     * Handle image generation AJAX request
     */
    public function generate_image() {
        if (!wp_verify_nonce($_POST['nonce'], 'seo_forge_nonce')) {
            wp_die('Security check failed');
        }

        $prompt = sanitize_text_field($_POST['prompt']);
        $style = sanitize_text_field($_POST['style']) ?: 'realistic';

        $response = $this->api->generate_image($prompt, $style);

        if ($response['success']) {
            wp_send_json_success($response['data']);
        } else {
            wp_send_json_error(array(
                'message' => $response['error'] ?? __('Failed to generate image', 'seo-forge')
            ));
        }
    }

    /**
     * Handle SEO analysis AJAX request
     */
    public function analyze_seo() {
        if (!wp_verify_nonce($_POST['nonce'], 'seo_forge_nonce')) {
            wp_die('Security check failed');
        }

        $content = sanitize_textarea_field($_POST['content']);
        $keyword = sanitize_text_field($_POST['keyword']);

        $response = $this->api->analyze_seo($content, $keyword);

        if ($response['success']) {
            wp_send_json_success($response['data']);
        } else {
            wp_send_json_error(array(
                'message' => $response['error'] ?? __('Failed to analyze SEO', 'seo-forge')
            ));
        }
    }

    /**
     * Handle keyword suggestions AJAX request
     */
    public function get_keyword_suggestions() {
        if (!wp_verify_nonce($_POST['nonce'], 'seo_forge_nonce')) {
            wp_die('Security check failed');
        }

        $topic = sanitize_text_field($_POST['topic']);

        $response = $this->api->get_keyword_suggestions($topic);

        if ($response['success']) {
            wp_send_json_success($response['data']);
        } else {
            wp_send_json_error(array(
                'message' => $response['error'] ?? __('Failed to get keyword suggestions', 'seo-forge')
            ));
        }
    }
}
