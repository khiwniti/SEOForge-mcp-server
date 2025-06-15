<?php

/**
 * Admin dashboard functionality
 */
class SEO_Forge_Admin_Dashboard {

    /**
     * Initialize the dashboard
     */
    public function __construct() {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
    }

    /**
     * Add SEO-Forge widgets to WordPress dashboard
     */
    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'seo_forge_stats',
            __('SEO-Forge Statistics', 'seo-forge'),
            array($this, 'display_stats_widget')
        );

        wp_add_dashboard_widget(
            'seo_forge_quick_actions',
            __('SEO Quick Actions', 'seo-forge'),
            array($this, 'display_quick_actions_widget')
        );
    }

    /**
     * Display statistics widget
     */
    public function display_stats_widget() {
        $api = new SEO_Forge_API();
        $connection_status = $api->test_connection();
        ?>
        <div class="seo-forge-dashboard-widget">
            <div class="widget-row">
                <span class="widget-label"><?php _e('API Status:', 'seo-forge'); ?></span>
                <span class="widget-value <?php echo $connection_status['success'] ? 'status-online' : 'status-offline'; ?>">
                    <?php echo $connection_status['success'] ? __('Connected', 'seo-forge') : __('Offline', 'seo-forge'); ?>
                </span>
            </div>
            <div class="widget-row">
                <span class="widget-label"><?php _e('Content Generated:', 'seo-forge'); ?></span>
                <span class="widget-value"><?php echo get_option('seo_forge_content_count', 0); ?></span>
            </div>
            <div class="widget-row">
                <span class="widget-label"><?php _e('Chatbot Conversations:', 'seo-forge'); ?></span>
                <span class="widget-value"><?php echo get_option('seo_forge_chat_count', 0); ?></span>
            </div>
        </div>
        <?php
    }

    /**
     * Display quick actions widget
     */
    public function display_quick_actions_widget() {
        ?>
        <div class="seo-forge-quick-actions">
            <a href="<?php echo admin_url('admin.php?page=seo-forge-content'); ?>" class="button button-primary">
                <i class="fas fa-magic"></i> <?php _e('Generate Content', 'seo-forge'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=seo-forge-settings'); ?>" class="button">
                <i class="fas fa-cog"></i> <?php _e('Settings', 'seo-forge'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=seo-forge-chatbot'); ?>" class="button">
                <i class="fas fa-robot"></i> <?php _e('Chatbot Setup', 'seo-forge'); ?>
            </a>
        </div>
        <?php
    }
}

// Initialize the dashboard
new SEO_Forge_Admin_Dashboard();
