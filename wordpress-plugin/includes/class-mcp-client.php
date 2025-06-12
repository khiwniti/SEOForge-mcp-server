<?php
/**
 * MCP Client for Universal MCP Plugin
 * Enhanced with comprehensive API support and error handling
 */

if (!defined('ABSPATH')) {
    exit;
}

class UMCP_MCP_Client {
    private $server_url;
    private $api_key;
    private $timeout;
    private $last_error;

    public function __construct() {
        // Try production server first, fallback to localhost for development
        $default_servers = [
            'https://seoforge-mcp-server.onrender.com',
            'https://universal-mcp-server.onrender.com',
            'https://seo-forge-mcp-server-645x.vercel.app',
            'http://localhost:8083',
            'http://127.0.0.1:8083'
        ];
        
        $configured_url = get_option('umcp_server_url', '');
        if (!empty($configured_url)) {
            $this->server_url = $configured_url;
        } else {
            // Auto-detect working server
            $this->server_url = $this->find_working_server($default_servers);
            if ($this->server_url) {
                update_option('umcp_server_url', $this->server_url);
            } else {
                $this->server_url = $default_servers[0]; // Default to production
            }
        }
        
        $this->api_key = get_option('umcp_api_key', '');
        $this->timeout = 30;
        $this->last_error = null;
    }
    
    /**
     * Find working server from list
     */
    private function find_working_server($servers) {
        foreach ($servers as $server) {
            $test_url = rtrim($server, '/') . '/';
            $response = wp_remote_get($test_url, array('timeout' => 5));
            
            if (!is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);
                if ($data && isset($data['status'])) {
                    return $server;
                }
            }
        }
        return null;
    }

    /**
     * Test server connection
     */
    public function test_connection() {
        // Try multiple endpoints to find working API
        $endpoints = ['/', '/universal-mcp/status', '/health'];
        
        foreach ($endpoints as $endpoint) {
            $response = $this->make_request('GET', $endpoint);
            if ($response !== false && (isset($response['status']) || isset($response['success']))) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Generate content using MCP server
     */
    public function generate_content($params) {
        $endpoint = '/universal-mcp/generate-content';
        return $this->make_request('POST', $endpoint, $params);
    }
    
    /**
     * Generate blog with images
     */
    public function generate_blog_with_images($params) {
        $endpoint = '/universal-mcp/generate-blog-with-images';
        return $this->make_request('POST', $endpoint, $params);
    }
    
    /**
     * Generate single image
     */
    public function generate_image($params) {
        $endpoint = '/universal-mcp/generate-image';
        return $this->make_request('POST', $endpoint, $params);
    }
    
    /**
     * Analyze SEO
     */
    public function analyze_seo($params) {
        $endpoint = '/universal-mcp/analyze-seo';
        return $this->make_request('POST', $endpoint, $params);
    }
    
    /**
     * Analyze website
     */
    public function analyze_website($params) {
        $endpoint = '/universal-mcp/analyze-website';
        return $this->make_request('POST', $endpoint, $params);
    }
    
    /**
     * Get server status
     */
    public function get_status() {
        $endpoint = '/universal-mcp/status';
        return $this->make_request('GET', $endpoint);
    }
    
    /**
     * Make HTTP request to MCP server
     */
    private function make_request($method, $endpoint, $data = null) {
        try {
            $url = rtrim($this->server_url, '/') . $endpoint;
            
            $args = array(
                'method' => $method,
                'timeout' => $this->timeout,
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'WordPress-UMCP-Plugin/' . UMCP_PLUGIN_VERSION
                )
            );
            
            if ($this->api_key) {
                $args['headers']['Authorization'] = 'Bearer ' . $this->api_key;
            }
            
            if ($data && $method === 'POST') {
                $args['body'] = wp_json_encode($data);
            }
            
            $response = wp_remote_request($url, $args);
            
            if (is_wp_error($response)) {
                $this->last_error = $response->get_error_message();
                error_log('UMCP API Error: ' . $this->last_error);
                return false;
            }
            
            $status_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            
            if ($status_code >= 200 && $status_code < 300) {
                $decoded = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                } else {
                    $this->last_error = 'Invalid JSON response';
                    return false;
                }
            } else {
                $this->last_error = "HTTP Error: $status_code";
                error_log("UMCP API HTTP Error: $status_code - $body");
                return false;
            }
            
        } catch (Exception $e) {
            $this->last_error = $e->getMessage();
            error_log('UMCP API Exception: ' . $this->last_error);
            return false;
        }
    }
    
    /**
     * Get last error message
     */
    public function get_last_error() {
        return $this->last_error;
    }
    
    /**
     * Set server URL
     */
    public function set_server_url($url) {
        $this->server_url = $url;
        update_option('umcp_server_url', $url);
    }
    
    /**
     * Set API key
     */
    public function set_api_key($key) {
        $this->api_key = $key;
        update_option('umcp_api_key', $key);
    }
    
    /**
     * Execute MCP tool with parameters and context (legacy support)
     */
    public function execute_tool($tool_name, $parameters = [], $context = []) {
        switch ($tool_name) {
            case 'generate_content':
                return $this->generate_content($parameters);
            case 'generate_blog_with_images':
                return $this->generate_blog_with_images($parameters);
            case 'generate_image':
                return $this->generate_image($parameters);
            case 'analyze_seo':
                return $this->analyze_seo($parameters);
            case 'analyze_website':
                return $this->analyze_website($parameters);
            default:
                $this->last_error = "Unknown tool: $tool_name";
                return false;
        }
    }
}
