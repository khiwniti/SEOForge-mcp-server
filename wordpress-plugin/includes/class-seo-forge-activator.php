<?php

/**
 * Fired during plugin activation
 *
 * @package    SEO_Forge
 * @subpackage SEO_Forge/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class SEO_Forge_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     */
    public static function activate() {
        // Create necessary database tables
        global $wpdb;

        $table_name = $wpdb->prefix . 'seo_forge_settings';
        
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            setting_name tinytext NOT NULL,
            setting_value longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY setting_name (setting_name(191))
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Create chatbot conversations table
        $conversations_table = $wpdb->prefix . 'seo_forge_conversations';
        
        $sql_conversations = "CREATE TABLE $conversations_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL,
            user_message longtext,
            bot_response longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY session_id (session_id)
        ) $charset_collate;";

        dbDelta($sql_conversations);

        // Create content generation log table
        $content_table = $wpdb->prefix . 'seo_forge_content_log';
        
        $sql_content = "CREATE TABLE $content_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20),
            content_type varchar(50),
            generated_content longtext,
            keywords text,
            language varchar(10),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY post_id (post_id)
        ) $charset_collate;";

        dbDelta($sql_content);

        // Set default options
        add_option('seo_forge_api_key', '');
        add_option('seo_forge_chatbot_enabled', 1);
        add_option('seo_forge_language', 'en');
        add_option('seo_forge_content_count', 0);
        add_option('seo_forge_chat_count', 0);
        add_option('seo_forge_version', SEO_FORGE_VERSION);
        add_option('seo_forge_activation_date', current_time('mysql'));

        // Create upload directory for generated images
        $upload_dir = wp_upload_dir();
        $seo_forge_dir = $upload_dir['basedir'] . '/seo-forge';
        
        if (!file_exists($seo_forge_dir)) {
            wp_mkdir_p($seo_forge_dir);
        }

        // Set activation flag
        set_transient('seo_forge_activation_notice', true, 30);

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
