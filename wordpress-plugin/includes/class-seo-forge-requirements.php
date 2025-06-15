<?php

/**
 * Plugin requirements checker
 *
 * @package    SEO_Forge
 * @subpackage SEO_Forge/includes
 */

/**
 * Plugin requirements checker.
 *
 * This class checks if the server meets the minimum requirements for the plugin.
 */
class SEO_Forge_Requirements {

    /**
     * Check all requirements
     */
    public static function check_requirements() {
        $errors = array();

        // Check PHP version
        if (!self::check_php_version()) {
            $errors[] = sprintf(
                __('SEO-Forge requires PHP version %s or higher. You are running version %s.', 'seo-forge'),
                SEO_FORGE_MIN_PHP_VERSION,
                PHP_VERSION
            );
        }

        // Check WordPress version
        if (!self::check_wp_version()) {
            $errors[] = sprintf(
                __('SEO-Forge requires WordPress version %s or higher. You are running version %s.', 'seo-forge'),
                SEO_FORGE_MIN_WP_VERSION,
                get_bloginfo('version')
            );
        }

        // Check required PHP extensions
        $missing_extensions = self::check_php_extensions();
        if (!empty($missing_extensions)) {
            $errors[] = sprintf(
                __('SEO-Forge requires the following PHP extensions: %s', 'seo-forge'),
                implode(', ', $missing_extensions)
            );
        }

        // Check file permissions
        if (!self::check_file_permissions()) {
            $errors[] = __('SEO-Forge requires write permissions to the uploads directory.', 'seo-forge');
        }

        // Check cURL support
        if (!self::check_curl_support()) {
            $errors[] = __('SEO-Forge requires cURL support for API communication.', 'seo-forge');
        }

        return $errors;
    }

    /**
     * Check PHP version
     */
    private static function check_php_version() {
        return version_compare(PHP_VERSION, SEO_FORGE_MIN_PHP_VERSION, '>=');
    }

    /**
     * Check WordPress version
     */
    private static function check_wp_version() {
        return version_compare(get_bloginfo('version'), SEO_FORGE_MIN_WP_VERSION, '>=');
    }

    /**
     * Check required PHP extensions
     */
    private static function check_php_extensions() {
        $required_extensions = array(
            'curl',
            'json',
            'mbstring',
            'openssl'
        );

        $missing_extensions = array();

        foreach ($required_extensions as $extension) {
            if (!extension_loaded($extension)) {
                $missing_extensions[] = $extension;
            }
        }

        return $missing_extensions;
    }

    /**
     * Check file permissions
     */
    private static function check_file_permissions() {
        $upload_dir = wp_upload_dir();
        return wp_is_writable($upload_dir['basedir']);
    }

    /**
     * Check cURL support
     */
    private static function check_curl_support() {
        return function_exists('curl_init');
    }

    /**
     * Display requirements errors
     */
    public static function display_requirements_error($errors) {
        if (empty($errors)) {
            return;
        }

        $message = '<div class="notice notice-error">';
        $message .= '<p><strong>' . __('SEO-Forge Plugin Requirements Error', 'seo-forge') . '</strong></p>';
        $message .= '<ul>';
        
        foreach ($errors as $error) {
            $message .= '<li>' . esc_html($error) . '</li>';
        }
        
        $message .= '</ul>';
        $message .= '<p>' . __('Please contact your hosting provider to resolve these issues.', 'seo-forge') . '</p>';
        $message .= '</div>';

        echo $message;
    }

    /**
     * Check if plugin can be activated
     */
    public static function can_activate() {
        $errors = self::check_requirements();
        return empty($errors);
    }

    /**
     * Deactivate plugin if requirements are not met
     */
    public static function deactivate_if_requirements_not_met() {
        $errors = self::check_requirements();
        
        if (!empty($errors)) {
            deactivate_plugins(plugin_basename(SEO_FORGE_PLUGIN_FILE));
            
            add_action('admin_notices', function() use ($errors) {
                SEO_Forge_Requirements::display_requirements_error($errors);
            });
            
            return false;
        }
        
        return true;
    }

    /**
     * Get system information
     */
    public static function get_system_info() {
        global $wpdb;

        $info = array(
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'plugin_version' => SEO_FORGE_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'mysql_version' => $wpdb->db_version(),
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'curl_version' => function_exists('curl_version') ? curl_version()['version'] : 'Not available',
            'openssl_version' => defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'Not available',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        );

        return $info;
    }
}
