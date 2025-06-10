<?php
/**
 * MCP Client for Universal MCP Plugin
 */
class UMCP_MCP_Client {
    private $server_url;
    private $api_key;
    private $cache_manager;
    private $rate_limiter;

    public function __construct() {
        $this->server_url = get_option('umcp_server_url', '');
        $this->api_key = get_option('umcp_api_key', '');
        $this->cache_manager = new UMCP_Cache_Manager();
        $this->rate_limiter = new UMCP_Rate_Limiter();
    }

    /**
     * Execute MCP tool with parameters and context
     */
    public function execute_tool($tool_name, $parameters = [], $context = []) {
        // Check rate limiting
        if (!$this->rate_limiter->check_rate_limit()) {
            return [
                'success' => false,
                'error' => 'Rate limit exceeded. Please try again later.',
                'code' => 'RATE_LIMIT_EXCEEDED'
            ];
        }

        // Generate cache key
        $cache_key = $this->generate_cache_key($tool_name, $parameters, $context);

        // Check cache first
        if (get_option('umcp_cache_enabled', true)) {
            $cached_result = $this->cache_manager->get($cache_key);
            if ($cached_result !== false) {
                return $cached_result;
            }
        }

        // Prepare request data
        $request_data = [
            'tool_name' => $tool_name,
            'parameters' => $parameters,
            'context' => array_merge($context, [
                'site_url' => get_site_url(),
                'user_id' => get_current_user_id(),
                'timestamp' => time(),
                'wp_version' => get_bloginfo('version'),
                'plugin_version' => UMCP_PLUGIN_VERSION
            ])
        ];

        // Make request to MCP server
        $response = $this->make_secure_request('/mcp-server/execute-tool', $request_data);

        // Log request for analytics
        $this->log_request($tool_name, $parameters, $response);

        // Cache successful responses
        if ($response && isset($response['success']) && $response['success']) {
            $cache_duration = get_option('umcp_cache_duration', 3600);
            $this->cache_manager->set($cache_key, $response, $cache_duration);
        }

        return $response;
    }

    /**
     * Get MCP server status
     */
    public function get_server_status() {
        $cache_key = 'umcp_server_status';
        
        // Check cache first (short cache for status)
        $cached_status = $this->cache_manager->get($cache_key);
        if ($cached_status !== false) {
            return $cached_status;
        }

        $response = $this->make_secure_request('/mcp-server/status');
        
        if ($response) {
            $this->cache_manager->set($cache_key, $response, 300); // 5 minutes cache
        }

        return $response;
    }

    /**
     * Get available tools from MCP server
     */
    public function get_available_tools() {
        $cache_key = 'umcp_available_tools';
        
        $cached_tools = $this->cache_manager->get($cache_key);
        if ($cached_tools !== false) {
            return $cached_tools;
        }

        $response = $this->make_secure_request('/mcp-server/tools');
        
        if ($response) {
            $this->cache_manager->set($cache_key, $response, 1800); // 30 minutes cache
        }

        return $response;
    }

    /**
     * Get supported industries
     */
    public function get_supported_industries() {
        $cache_key = 'umcp_supported_industries';
        
        $cached_industries = $this->cache_manager->get($cache_key);
        if ($cached_industries !== false) {
            return $cached_industries;
        }

        $response = $this->make_secure_request('/mcp-server/industries');
        
        if ($response) {
            $this->cache_manager->set($cache_key, $response, 3600); // 1 hour cache
        }

        return $response;
    }

    /**
     * Get industry templates
     */
    public function get_industry_templates() {
        $cache_key = 'umcp_industry_templates';
        
        $cached_templates = $this->cache_manager->get($cache_key);
        if ($cached_templates !== false) {
            return $cached_templates;
        }

        $response = $this->make_secure_request('/mcp-server/templates');
        
        if ($response) {
            $this->cache_manager->set($cache_key, $response, 1800); // 30 minutes cache
        }

        return $response;
    }

    /**
     * Test connection to MCP server
     */
    public function test_connection() {
        if (empty($this->server_url) || empty($this->api_key)) {
            return [
                'success' => false,
                'message' => 'MCP server URL or API key not configured',
                'code' => 'CONFIGURATION_MISSING'
            ];
        }

        $start_time = microtime(true);
        $response = $this->make_secure_request('/mcp-server/status');
        $response_time = round((microtime(true) - $start_time) * 1000, 2);

        if ($response && isset($response['status']) && $response['status'] === 'active') {
            return [
                'success' => true,
                'message' => 'Connection successful',
                'response_time' => $response_time . 'ms',
                'server_info' => $response
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to connect to MCP server',
            'response_time' => $response_time . 'ms',
            'code' => 'CONNECTION_FAILED'
        ];
    }

    /**
     * Make secure request to MCP server
     */
    private function make_secure_request($endpoint, $data = null) {
        if (empty($this->server_url) || empty($this->api_key)) {
            return [
                'success' => false,
                'error' => 'MCP server not configured',
                'code' => 'CONFIGURATION_MISSING'
            ];
        }

        $url = rtrim($this->server_url, '/') . $endpoint;
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->api_key,
            'X-Plugin-Version' => UMCP_PLUGIN_VERSION,
            'X-Site-Hash' => $this->generate_site_hash(),
            'User-Agent' => 'Universal-MCP-Plugin/' . UMCP_PLUGIN_VERSION
        ];

        $args = [
            'method' => $data ? 'POST' : 'GET',
            'headers' => $headers,
            'timeout' => 30,
            'sslverify' => true,
            'body' => $data ? wp_json_encode($data) : null
        ];

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            error_log('UMCP Client Error: ' . $response->get_error_message());
            return [
                'success' => false,
                'error' => $response->get_error_message(),
                'code' => 'REQUEST_ERROR'
            ];
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($response_code !== 200) {
            error_log('UMCP Client HTTP Error: ' . $response_code . ' - ' . $body);
            return [
                'success' => false,
                'error' => 'HTTP Error: ' . $response_code,
                'code' => 'HTTP_ERROR_' . $response_code
            ];
        }

        $decoded = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('UMCP Client JSON Error: ' . json_last_error_msg());
            return [
                'success' => false,
                'error' => 'Invalid JSON response',
                'code' => 'JSON_ERROR'
            ];
        }

        return $decoded;
    }

    /**
     * Generate cache key for request
     */
    private function generate_cache_key($tool_name, $parameters, $context) {
        $key_data = [
            'tool' => $tool_name,
            'params' => $parameters,
            'context' => array_intersect_key($context, array_flip(['industry', 'language'])),
            'site' => get_site_url()
        ];
        
        return 'umcp_' . md5(wp_json_encode($key_data));
    }

    /**
     * Generate site hash for security
     */
    private function generate_site_hash() {
        return hash('sha256', get_site_url() . $this->api_key . wp_salt());
    }

    /**
     * Log request for analytics
     */
    private function log_request($tool_name, $parameters, $response) {
        if (!get_option('umcp_debug_mode', false)) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'umcp_requests';

        $execution_time = isset($response['execution_time']) ? $response['execution_time'] : 0;
        $success = isset($response['success']) ? $response['success'] : false;

        $wpdb->insert(
            $table_name,
            [
                'user_id' => get_current_user_id(),
                'tool_name' => $tool_name,
                'parameters' => wp_json_encode($parameters),
                'response' => wp_json_encode($response),
                'industry' => get_option('umcp_current_industry', 'general'),
                'execution_time' => $execution_time,
                'success' => $success ? 1 : 0,
                'created_at' => current_time('mysql')
            ],
            [
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%f',
                '%d',
                '%s'
            ]
        );
    }

    /**
     * Get request analytics
     */
    public function get_request_analytics($days = 7) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'umcp_requests';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                tool_name,
                industry,
                COUNT(*) as total_requests,
                SUM(success) as successful_requests,
                AVG(execution_time) as avg_execution_time,
                DATE(created_at) as request_date
            FROM $table_name 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY tool_name, industry, DATE(created_at)
            ORDER BY request_date DESC, total_requests DESC",
            $days
        ));

        return $results;
    }

    /**
     * Clear cache
     */
    public function clear_cache() {
        return $this->cache_manager->clear_all();
    }

    /**
     * Update server configuration
     */
    public function update_server_config($server_url, $api_key) {
        $this->server_url = $server_url;
        $this->api_key = $api_key;
        
        update_option('umcp_server_url', $server_url);
        update_option('umcp_api_key', $api_key);
        
        // Clear cache when configuration changes
        $this->clear_cache();
        
        return true;
    }
}

/**
 * Cache Manager for MCP Client
 */
class UMCP_Cache_Manager {
    private $cache_prefix = 'umcp_cache_';

    public function get($key) {
        return get_transient($this->cache_prefix . $key);
    }

    public function set($key, $value, $expiration = 3600) {
        return set_transient($this->cache_prefix . $key, $value, $expiration);
    }

    public function delete($key) {
        return delete_transient($this->cache_prefix . $key);
    }

    public function clear_all() {
        global $wpdb;
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_' . $this->cache_prefix . '%'
        ));
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_timeout_' . $this->cache_prefix . '%'
        ));
        
        return true;
    }
}

/**
 * Rate Limiter for MCP Client
 */
class UMCP_Rate_Limiter {
    private $rate_limit_key = 'umcp_rate_limit_';

    public function check_rate_limit($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $limit = get_option('umcp_rate_limit', 100); // requests per hour
        $key = $this->rate_limit_key . $user_id;
        
        $current_count = get_transient($key);
        
        if ($current_count === false) {
            // First request in this hour
            set_transient($key, 1, HOUR_IN_SECONDS);
            return true;
        }
        
        if ($current_count >= $limit) {
            return false;
        }
        
        // Increment counter
        set_transient($key, $current_count + 1, HOUR_IN_SECONDS);
        return true;
    }

    public function get_remaining_requests($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $limit = get_option('umcp_rate_limit', 100);
        $key = $this->rate_limit_key . $user_id;
        $current_count = get_transient($key);
        
        return max(0, $limit - ($current_count ?: 0));
    }
}
