<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET['action'] so it is indeed a plugin deletion request
 * - Do the actual work of deleting the plugin
 *
 * @link       https://seoforge.com
 * @since      1.0.0
 *
 * @package    SEO_Forge
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Delete plugin data based on user preference
 */
function seo_forge_uninstall_cleanup() {
    global $wpdb;

    // Check if user wants to keep data
    $keep_data = get_option('seo_forge_keep_data_on_uninstall', false);
    
    if ($keep_data) {
        // User wants to keep data, don't delete anything
        return;
    }

    // Delete plugin options
    $options_to_delete = array(
        'seo_forge_api_key',
        'seo_forge_chatbot_enabled',
        'seo_forge_language',
        'seo_forge_chatbot_position',
        'seo_forge_chatbot_color',
        'seo_forge_chatbot_welcome_message',
        'seo_forge_chatbot_placeholder',
        'seo_forge_chatbot_knowledge_base',
        'seo_forge_content_count',
        'seo_forge_chat_count',
        'seo_forge_image_count',
        'seo_forge_recent_activity',
        'seo_forge_keep_data_on_uninstall'
    );

    foreach ($options_to_delete as $option) {
        delete_option($option);
    }

    // Delete multisite options if applicable
    if (is_multisite()) {
        foreach ($options_to_delete as $option) {
            delete_site_option($option);
        }
    }

    // Delete custom database tables
    $table_name = $wpdb->prefix . 'seo_forge_settings';
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");

    // Delete analytics data table if exists
    $analytics_table = $wpdb->prefix . 'seo_forge_analytics';
    $wpdb->query("DROP TABLE IF EXISTS {$analytics_table}");

    // Delete chat history table if exists
    $chat_table = $wpdb->prefix . 'seo_forge_chat_history';
    $wpdb->query("DROP TABLE IF EXISTS {$chat_table}");

    // Delete performance data table if exists
    $performance_table = $wpdb->prefix . 'seo_forge_performance';
    $wpdb->query("DROP TABLE IF EXISTS {$performance_table}");

    // Clean up post meta data
    $wpdb->query(
        "DELETE FROM {$wpdb->postmeta} 
         WHERE meta_key LIKE 'seo_forge_%' 
         OR meta_key LIKE '_seo_forge_%'"
    );

    // Clean up user meta data
    $wpdb->query(
        "DELETE FROM {$wpdb->usermeta} 
         WHERE meta_key LIKE 'seo_forge_%' 
         OR meta_key LIKE '_seo_forge_%'"
    );

    // Clean up term meta data
    if (function_exists('get_term_meta')) {
        $wpdb->query(
            "DELETE FROM {$wpdb->termmeta} 
             WHERE meta_key LIKE 'seo_forge_%' 
             OR meta_key LIKE '_seo_forge_%'"
        );
    }

    // Delete scheduled hooks/cron jobs
    wp_clear_scheduled_hook('seo_forge_daily_cleanup');
    wp_clear_scheduled_hook('seo_forge_weekly_report');
    wp_clear_scheduled_hook('seo_forge_api_health_check');

    // Clear any cached data
    wp_cache_delete('seo_forge_api_status');
    wp_cache_delete('seo_forge_settings');
    wp_cache_delete('seo_forge_stats');

    // Delete transients
    delete_transient('seo_forge_api_connection_status');
    delete_transient('seo_forge_daily_stats');
    delete_transient('seo_forge_keyword_cache');

    // Delete site transients (for multisite)
    delete_site_transient('seo_forge_api_connection_status');
    delete_site_transient('seo_forge_daily_stats');

    // Remove custom capabilities
    $roles = array('administrator', 'editor', 'author');
    foreach ($roles as $role_name) {
        $role = get_role($role_name);
        if ($role) {
            $role->remove_cap('manage_seo_forge');
            $role->remove_cap('use_seo_forge_generator');
            $role->remove_cap('configure_seo_forge_chatbot');
        }
    }

    // Clean up uploaded files in specific directories
    $upload_dir = wp_upload_dir();
    $seo_forge_dir = $upload_dir['basedir'] . '/seo-forge/';
    
    if (is_dir($seo_forge_dir)) {
        seo_forge_delete_directory($seo_forge_dir);
    }

    // Clean up log files
    $log_file = WP_CONTENT_DIR . '/seo-forge-log.txt';
    if (file_exists($log_file)) {
        unlink($log_file);
    }

    // Remove custom rewrite rules
    flush_rewrite_rules();

    // Clear object cache
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
}

/**
 * Recursively delete a directory and its contents
 *
 * @param string $dir Directory path
 * @return bool True on success, false on failure
 */
function seo_forge_delete_directory($dir) {
    if (!is_dir($dir)) {
        return false;
    }

    $files = array_diff(scandir($dir), array('.', '..'));
    
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            seo_forge_delete_directory($path);
        } else {
            unlink($path);
        }
    }
    
    return rmdir($dir);
}

/**
 * Log uninstall activity
 */
function seo_forge_log_uninstall() {
    $log_data = array(
        'timestamp' => current_time('mysql'),
        'site_url' => get_site_url(),
        'admin_email' => get_option('admin_email'),
        'plugin_version' => '1.0.0',
        'wp_version' => get_bloginfo('version'),
        'php_version' => PHP_VERSION,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    );

    // Send anonymous uninstall data (if user opted in)
    $send_usage_data = get_option('seo_forge_send_usage_data', false);
    if ($send_usage_data) {
        wp_remote_post('https://api.seoforge.com/uninstall-feedback', array(
            'body' => json_encode($log_data),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 15,
            'blocking' => false
        ));
    }
}

// Check if this is a network-wide uninstall
if (is_multisite() && isset($_GET['networkwide']) && $_GET['networkwide'] == 1) {
    // Get all blog IDs
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
    
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        seo_forge_uninstall_cleanup();
        restore_current_blog();
    }
} else {
    // Single site uninstall
    seo_forge_uninstall_cleanup();
}

// Log the uninstall (do this last)
seo_forge_log_uninstall();

// Final cleanup - remove any remaining traces
delete_option('seo_forge_version');
delete_option('seo_forge_install_date');
delete_option('seo_forge_activation_time');

// Clear any remaining caches
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// Remove from active plugins list (just in case)
$active_plugins = get_option('active_plugins', array());
$plugin_file = 'seo-forge/seo-forge.php';
$key = array_search($plugin_file, $active_plugins);
if ($key !== false) {
    unset($active_plugins[$key]);
    update_option('active_plugins', $active_plugins);
}

// Network active plugins cleanup
if (is_multisite()) {
    $network_active_plugins = get_site_option('active_sitewide_plugins', array());
    if (isset($network_active_plugins[$plugin_file])) {
        unset($network_active_plugins[$plugin_file]);
        update_site_option('active_sitewide_plugins', $network_active_plugins);
    }
}
