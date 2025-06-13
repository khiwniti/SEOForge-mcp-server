<?php
/**
 * SEO-Forge Plugin Demo Interface
 * This file demonstrates the WordPress plugin interface outside of WordPress
 */

// Simulate WordPress environment for demo purposes
if (!defined('WPINC')) {
    define('WPINC', true);
}

// Define WordPress-like functions for demo
if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('_e')) {
    function _e($text, $domain = 'default') {
        echo $text;
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_textarea')) {
    function esc_textarea($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '') {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action) {
        return md5($action . 'nonce_salt');
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        $options = [
            'seo_forge_api_key' => '',
            'seo_forge_chatbot_enabled' => 1,
            'seo_forge_language' => 'en',
            'seo_forge_chatbot_position' => 'bottom-right',
            'seo_forge_chatbot_color' => '#007cba',
            'seo_forge_chatbot_welcome_message' => "Hello! I'm your SEO assistant. How can I help you optimize your content today?",
            'seo_forge_chatbot_placeholder' => 'Type your message...',
            'seo_forge_chatbot_knowledge_base' => '',
            'seo_forge_content_count' => 42,
            'seo_forge_chat_count' => 156,
            'seo_forge_image_count' => 28,
            'seo_forge_recent_activity' => []
        ];
        return isset($options[$option]) ? $options[$option] : $default;
    }
}

if (!function_exists('selected')) {
    function selected($selected, $current = true, $echo = true) {
        $result = selected_helper($selected, $current);
        if ($echo) {
            echo $result;
        }
        return $result;
    }
}

if (!function_exists('selected_helper')) {
    function selected_helper($selected, $current) {
        if ($selected == $current) {
            return ' selected="selected"';
        }
        return '';
    }
}

if (!function_exists('checked')) {
    function checked($checked, $current = true, $echo = true) {
        $result = checked_helper($checked, $current);
        if ($echo) {
            echo $result;
        }
        return $result;
    }
}

if (!function_exists('checked_helper')) {
    function checked_helper($checked, $current) {
        if ($checked == $current) {
            return ' checked="checked"';
        }
        return '';
    }
}

if (!function_exists('sanitize_hex_color')) {
    function sanitize_hex_color($color) {
        if ('' === $color) {
            return '';
        }
        
        if (preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color)) {
            return $color;
        }
        
        return '';
    }
}

if (!function_exists('submit_button')) {
    function submit_button($text = 'Save Changes', $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = null) {
        $button = '<input type="submit" name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" class="button button-' . esc_attr($type) . '" value="' . esc_attr($text) . '"' . $other_attributes . ' />';
        
        if ($wrap) {
            $button = '<p class="submit">' . $button . '</p>';
        }
        
        echo $button;
    }
}

if (!function_exists('wp_nonce_field')) {
    function wp_nonce_field($action = -1, $name = "_wpnonce", $referer = true, $echo = true) {
        $nonce = wp_create_nonce($action);
        $nonce_field = '<input type="hidden" id="' . $name . '" name="' . $name . '" value="' . $nonce . '" />';
        
        if ($referer) {
            $nonce_field .= wp_referer_field(false);
        }
        
        if ($echo) {
            echo $nonce_field;
        }
        
        return $nonce_field;
    }
}

if (!function_exists('wp_referer_field')) {
    function wp_referer_field($echo = true) {
        $referer_field = '<input type="hidden" name="_wp_http_referer" value="' . esc_attr($_SERVER['REQUEST_URI'] ?? '') . '" />';
        
        if ($echo) {
            echo $referer_field;
        }
        
        return $referer_field;
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1) {
        return true; // For demo purposes
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return htmlspecialchars(strip_tags($str), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('sanitize_textarea_field')) {
    function sanitize_textarea_field($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value, $autoload = null) {
        return true; // For demo purposes
    }
}

if (!function_exists('wp_remote_request')) {
    function wp_remote_request($url, $args = array()) {
        $ch = curl_init();
        
        $defaults = array(
            'method' => 'GET',
            'timeout' => 30,
            'headers' => array(),
            'body' => null
        );
        
        $args = array_merge($defaults, $args);
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $args['timeout'],
            CURLOPT_CUSTOMREQUEST => $args['method'],
            CURLOPT_HTTPHEADER => $args['headers'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true
        ));
        
        if ($args['body'] && in_array($args['method'], array('POST', 'PUT', 'PATCH'))) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $args['body']);
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            return new WP_Error('http_request_failed', $error);
        }
        
        return array(
            'response' => array('code' => $http_code),
            'body' => $response
        );
    }
}

if (!function_exists('wp_remote_retrieve_body')) {
    function wp_remote_retrieve_body($response) {
        if (is_wp_error($response)) {
            return '';
        }
        return isset($response['body']) ? $response['body'] : '';
    }
}

if (!function_exists('wp_remote_retrieve_response_code')) {
    function wp_remote_retrieve_response_code($response) {
        if (is_wp_error($response)) {
            return '';
        }
        return isset($response['response']['code']) ? $response['response']['code'] : 0;
    }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) {
        return ($thing instanceof WP_Error);
    }
}

if (!function_exists('get_site_url')) {
    function get_site_url() {
        return 'https://example.com'; // Demo URL
    }
}

if (!class_exists('WP_Error')) {
    class WP_Error {
        public $errors = array();
        public $error_data = array();
        
        public function __construct($code = '', $message = '', $data = '') {
            if (empty($code)) {
                return;
            }
            
            $this->errors[$code][] = $message;
            
            if (!empty($data)) {
                $this->error_data[$code] = $data;
            }
        }
        
        public function get_error_message($code = '') {
            if (empty($code)) {
                $code = $this->get_error_code();
            }
            
            if (isset($this->errors[$code])) {
                return $this->errors[$code][0];
            }
            
            return '';
        }
        
        public function get_error_code() {
            $codes = array_keys($this->errors);
            
            if (empty($codes)) {
                return '';
            }
            
            return $codes[0];
        }
    }
}

// Mock API class for demo
if (!class_exists('SEO_Forge_API')) {
    class SEO_Forge_API {
        public function test_connection() {
            return array(
                'success' => true,
                'data' => array('message' => 'API connection successful')
            );
        }
    }
}

// Get the current page
$page = $_GET['page'] ?? 'dashboard';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO-Forge WordPress Plugin Demo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin/css/seo-forge-admin.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            background: #f1f1f1;
        }
        
        .demo-header {
            background: #23282d;
            color: white;
            padding: 15px 20px;
            border-bottom: 4px solid #007cba;
        }
        
        .demo-header h1 {
            margin: 0;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .demo-nav {
            background: #32373c;
            padding: 0;
            border-bottom: 1px solid #464b50;
        }
        
        .demo-nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }
        
        .demo-nav li {
            margin: 0;
        }
        
        .demo-nav a {
            display: block;
            padding: 15px 20px;
            color: #eee;
            text-decoration: none;
            transition: background-color 0.3s;
            border-bottom: 3px solid transparent;
        }
        
        .demo-nav a:hover,
        .demo-nav a.active {
            background: #007cba;
            border-bottom-color: #005a87;
        }
        
        .demo-nav i {
            margin-right: 8px;
        }
        
        .demo-content {
            padding: 20px;
            min-height: calc(100vh - 120px);
        }
        
        .demo-notice {
            background: #e7f3ff;
            border: 1px solid #007cba;
            border-left: 4px solid #007cba;
            padding: 12px;
            margin: 0 0 20px;
            border-radius: 4px;
        }
        
        .demo-notice p {
            margin: 0;
            color: #0073aa;
        }
        
        .wrap {
            max-width: none;
        }
        
        .wp-heading-inline {
            display: inline-block;
            margin-right: 5px;
        }
        
        .button {
            display: inline-block;
            text-decoration: none;
            font-size: 13px;
            line-height: 2.15384615;
            min-height: 30px;
            margin: 0;
            padding: 0 10px;
            cursor: pointer;
            border-width: 1px;
            border-style: solid;
            border-radius: 3px;
            white-space: nowrap;
            box-sizing: border-box;
            background: #f3f5f6;
            border-color: #8c8f94;
            color: #2c3338;
        }
        
        .button-primary {
            background: #007cba;
            border-color: #007cba;
            color: #fff;
        }
        
        .button:hover {
            background: #f0f0f1;
            border-color: #8c8f94;
            color: #2c3338;
        }
        
        .button-primary:hover {
            background: #005a87;
            border-color: #005a87;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="demo-header">
        <h1>
            <i class="fas fa-chart-line"></i>
            SEO-Forge WordPress Plugin Demo
        </h1>
    </div>
    
    <nav class="demo-nav">
        <ul>
            <li><a href="?page=dashboard" class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a></li>
            <li><a href="?page=content" class="<?php echo $page === 'content' ? 'active' : ''; ?>">
                <i class="fas fa-magic"></i> Content Generator
            </a></li>
            <li><a href="?page=chatbot" class="<?php echo $page === 'chatbot' ? 'active' : ''; ?>">
                <i class="fas fa-robot"></i> Chatbot
            </a></li>
            <li><a href="?page=settings" class="<?php echo $page === 'settings' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Settings
            </a></li>
        </ul>
    </nav>
    
    <div class="demo-content">
        <div class="demo-notice">
            <p><strong>Demo Mode:</strong> This is a demonstration of the SEO-Forge WordPress plugin interface. The actual functionality requires WordPress and your FastAPI server integration.</p>
        </div>
        
        <?php
        switch ($page) {
            case 'dashboard':
                include 'admin/partials/seo-forge-admin-display.php';
                break;
            case 'content':
                include 'admin/partials/seo-forge-content-generator.php';
                break;
            case 'chatbot':
                include 'admin/partials/seo-forge-chatbot-settings.php';
                break;
            case 'settings':
                include 'admin/partials/seo-forge-settings.php';
                break;
            default:
                include 'admin/partials/seo-forge-admin-display.php';
                break;
        }
        ?>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Mock WordPress admin AJAX
        var ajaxurl = '/admin-ajax.php';
        var seoForgeAjax = {
            ajaxurl: ajaxurl,
            nonce: '<?php echo wp_create_nonce('seo_forge_nonce'); ?>'
        };
        
        // Mock AJAX responses for demo
        jQuery(document).ready(function($) {
            // Override AJAX calls for demo
            var originalAjax = $.ajax;
            $.ajax = function(options) {
                if (options.url === ajaxurl) {
                    setTimeout(function() {
                        var mockResponse = {
                            success: true,
                            data: {
                                message: 'Demo response - API integration would work here with your FastAPI server',
                                content: 'This is a sample generated content. In the real plugin, this would be generated by your AI API.',
                                title: 'AI-Generated SEO Title Example',
                                meta_description: 'This is an example meta description that would be generated by your AI service.',
                                keywords: ['seo', 'content', 'optimization', 'ai', 'wordpress'],
                                image_url: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkdlbmVyYXRlZCBJbWFnZSBQbGFjZWhvbGRlcjwvdGV4dD48L3N2Zz4='
                            }
                        };
                        
                        if (options.success) {
                            options.success(mockResponse);
                        }
                    }, 1000);
                } else {
                    return originalAjax.call(this, options);
                }
            };
        });
    </script>
    <script src="admin/js/seo-forge-admin.js"></script>
</body>
</html>