<?php

/**
 * Define plugin constants
 *
 * @package    SEO_Forge
 * @subpackage SEO_Forge/includes
 */

/**
 * Define plugin constants.
 *
 * This class defines all constants used throughout the plugin.
 */
class SEO_Forge_Constants {

    /**
     * Define all plugin constants
     */
    public static function define_constants() {
        
        // Plugin version
        if (!defined('SEO_FORGE_VERSION')) {
            define('SEO_FORGE_VERSION', '1.0.0');
        }

        // Plugin paths
        if (!defined('SEO_FORGE_PLUGIN_DIR')) {
            define('SEO_FORGE_PLUGIN_DIR', plugin_dir_path(dirname(__FILE__)));
        }

        if (!defined('SEO_FORGE_PLUGIN_URL')) {
            define('SEO_FORGE_PLUGIN_URL', plugin_dir_url(dirname(__FILE__)));
        }

        if (!defined('SEO_FORGE_PLUGIN_FILE')) {
            define('SEO_FORGE_PLUGIN_FILE', dirname(dirname(__FILE__)) . '/seo-forge.php');
        }

        // API settings
        if (!defined('SEO_FORGE_API_BASE_URL')) {
            define('SEO_FORGE_API_BASE_URL', 'https://seoforge-mcp-server.onrender.com');
        }

        if (!defined('SEO_FORGE_API_VERSION')) {
            define('SEO_FORGE_API_VERSION', 'v1');
        }

        if (!defined('SEO_FORGE_API_TIMEOUT')) {
            define('SEO_FORGE_API_TIMEOUT', 30);
        }

        // Database table names
        global $wpdb;
        if (!defined('SEO_FORGE_SETTINGS_TABLE')) {
            define('SEO_FORGE_SETTINGS_TABLE', $wpdb->prefix . 'seo_forge_settings');
        }

        if (!defined('SEO_FORGE_CONVERSATIONS_TABLE')) {
            define('SEO_FORGE_CONVERSATIONS_TABLE', $wpdb->prefix . 'seo_forge_conversations');
        }

        if (!defined('SEO_FORGE_CONTENT_LOG_TABLE')) {
            define('SEO_FORGE_CONTENT_LOG_TABLE', $wpdb->prefix . 'seo_forge_content_log');
        }

        // Plugin options
        if (!defined('SEO_FORGE_OPTION_PREFIX')) {
            define('SEO_FORGE_OPTION_PREFIX', 'seo_forge_');
        }

        // Text domain
        if (!defined('SEO_FORGE_TEXT_DOMAIN')) {
            define('SEO_FORGE_TEXT_DOMAIN', 'seo-forge');
        }

        // Minimum requirements
        if (!defined('SEO_FORGE_MIN_PHP_VERSION')) {
            define('SEO_FORGE_MIN_PHP_VERSION', '7.4');
        }

        if (!defined('SEO_FORGE_MIN_WP_VERSION')) {
            define('SEO_FORGE_MIN_WP_VERSION', '5.0');
        }

        // Debug mode
        if (!defined('SEO_FORGE_DEBUG')) {
            define('SEO_FORGE_DEBUG', false);
        }

        // Cache settings
        if (!defined('SEO_FORGE_CACHE_DURATION')) {
            define('SEO_FORGE_CACHE_DURATION', 3600); // 1 hour
        }

        // Upload directory
        if (!defined('SEO_FORGE_UPLOAD_DIR')) {
            $upload_dir = wp_upload_dir();
            define('SEO_FORGE_UPLOAD_DIR', $upload_dir['basedir'] . '/seo-forge');
        }

        if (!defined('SEO_FORGE_UPLOAD_URL')) {
            $upload_dir = wp_upload_dir();
            define('SEO_FORGE_UPLOAD_URL', $upload_dir['baseurl'] . '/seo-forge');
        }

        // Content limits
        if (!defined('SEO_FORGE_MAX_CONTENT_LENGTH')) {
            define('SEO_FORGE_MAX_CONTENT_LENGTH', 10000);
        }

        if (!defined('SEO_FORGE_MAX_KEYWORDS')) {
            define('SEO_FORGE_MAX_KEYWORDS', 20);
        }

        // Rate limiting
        if (!defined('SEO_FORGE_RATE_LIMIT_REQUESTS')) {
            define('SEO_FORGE_RATE_LIMIT_REQUESTS', 100);
        }

        if (!defined('SEO_FORGE_RATE_LIMIT_WINDOW')) {
            define('SEO_FORGE_RATE_LIMIT_WINDOW', 3600); // 1 hour
        }
    }

    /**
     * Check if all required constants are defined
     */
    public static function validate_constants() {
        $required_constants = array(
            'SEO_FORGE_VERSION',
            'SEO_FORGE_PLUGIN_DIR',
            'SEO_FORGE_PLUGIN_URL',
            'SEO_FORGE_API_BASE_URL',
            'SEO_FORGE_TEXT_DOMAIN'
        );

        foreach ($required_constants as $constant) {
            if (!defined($constant)) {
                wp_die(sprintf('Required constant %s is not defined.', $constant));
            }
        }

        return true;
    }
}
