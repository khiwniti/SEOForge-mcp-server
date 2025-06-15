<?php

/**
 * Fired during plugin deactivation
 *
 * @package    SEO_Forge
 * @subpackage SEO_Forge/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 */
class SEO_Forge_Deactivator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     */
    public static function deactivate() {
        // Clear any scheduled events
        wp_clear_scheduled_hook('seo_forge_cleanup_logs');
        wp_clear_scheduled_hook('seo_forge_update_stats');

        // Clear transients
        delete_transient('seo_forge_api_status');
        delete_transient('seo_forge_activation_notice');

        // Flush rewrite rules
        flush_rewrite_rules();

        // Log deactivation
        if (get_option('seo_forge_log_events', false)) {
            error_log('SEO-Forge plugin deactivated at ' . current_time('mysql'));
        }
    }
}
