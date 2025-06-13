<?php

/**
 * API communication class for SEO-Forge plugin
 */
class SEO_Forge_API {

    /**
     * The base URL for the API
     */
    private $api_base_url;

    /**
     * The API key for authentication
     */
    private $api_key;

    /**
     * Initialize the class
     */
    public function __construct() {
        $this->api_base_url = SEO_FORGE_API_BASE_URL;
        $this->api_key = get_option('seo_forge_api_key', '');
    }

    /**
     * Make a GET request to the API
     */
    private function make_request($endpoint, $method = 'GET', $data = null) {
        $url = $this->api_base_url . $endpoint;
        
        $args = array(
            'method' => $method,
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key
            )
        );

        if ($data && ($method === 'POST' || $method === 'PUT')) {
            $args['body'] = json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => $response->get_error_message()
            );
        }

        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        $status_code = wp_remote_retrieve_response_code($response);

        return array(
            'success' => $status_code >= 200 && $status_code < 300,
            'status_code' => $status_code,
            'data' => $decoded
        );
    }

    /**
     * Test API connection
     */
    public function test_connection() {
        return $this->make_request('/universal-mcp/status');
    }

    /**
     * Generate blog content with images
     */
    public function generate_blog_content($topic, $keywords = array(), $word_count = 1000) {
        $length = 'medium';
        if ($word_count <= 500) {
            $length = 'short';
        } elseif ($word_count >= 1500) {
            $length = 'long';
        }

        $data = array(
            'content_type' => 'blog_post',
            'topic' => $topic,
            'keywords' => $keywords,
            'length' => $length,
            'language' => get_option('seo_forge_language', 'en'),
            'tone' => 'professional',
            'industry' => 'general',
            'include_images' => true,
            'image_count' => 3,
            'image_style' => 'professional'
        );

        return $this->make_request('/universal-mcp/generate-blog-with-images', 'POST', $data);
    }

    /**
     * Generate content from keywords only
     */
    public function generate_from_keywords($keywords, $language = 'en', $tone = 'professional') {
        $params = array(
            'language' => $language,
            'tone' => $tone,
            'length' => 'medium',
            'industry' => 'general',
            'include_images' => true
        );

        $query_string = http_build_query($params);
        return $this->make_request('/universal-mcp/generate-from-keywords?' . $query_string, 'POST', $keywords);
    }

    /**
     * Generate image for content
     */
    public function generate_image($prompt, $style = 'professional') {
        $data = array(
            'prompt' => $prompt,
            'style' => $style,
            'size' => '1024x1024',
            'count' => 1
        );

        return $this->make_request('/universal-mcp/generate-image', 'POST', $data);
    }

    /**
     * Generate high-quality Flux image
     */
    public function generate_flux_image($prompt, $model = 'flux-schnell') {
        $data = array(
            'prompt' => $prompt,
            'model' => $model,
            'width' => 1024,
            'height' => 1024,
            'steps' => 4,
            'guidance' => 3.5
        );

        return $this->make_request('/universal-mcp/generate-flux-image', 'POST', $data);
    }

    /**
     * Analyze content for SEO
     */
    public function analyze_seo($content, $target_keyword = '') {
        $data = array(
            'content' => $content,
            'target_keyword' => $target_keyword,
            'url' => get_site_url()
        );

        return $this->make_request('/universal-mcp/analyze-seo', 'POST', $data);
    }

    /**
     * Analyze website
     */
    public function analyze_website($url, $analysis_type = 'simple') {
        $data = array(
            'url' => $url,
            'analysis_type' => $analysis_type
        );

        return $this->make_request('/universal-mcp/analyze-website', 'POST', $data);
    }

    /**
     * Generate chatbot response
     */
    public function generate_chatbot_response($message, $context = array()) {
        $data = array(
            'message' => $message,
            'context' => $context,
            'language' => get_option('seo_forge_language', 'en'),
            'website_url' => get_site_url()
        );

        return $this->make_request('/universal-mcp/chatbot', 'POST', $data);
    }

    /**
     * Get available Flux models
     */
    public function get_flux_models() {
        return $this->make_request('/universal-mcp/flux-models');
    }

    /**
     * Enhance prompt for Flux generation
     */
    public function enhance_flux_prompt($prompt) {
        $data = array(
            'prompt' => $prompt
        );

        return $this->make_request('/universal-mcp/enhance-flux-prompt', 'POST', $data);
    }
}
