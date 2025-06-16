<?php
/**
 * Provide a admin area view for the plugin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$api = new SEO_Forge_API();
$connection_status = $api->test_connection();
?>

<div class="wrap seo-forge-admin">
    <h1 class="wp-heading-inline">
        <i class="fas fa-chart-line"></i>
        <?php _e('SEO-Forge Dashboard', 'seo-forge'); ?>
    </h1>

    <div class="seo-forge-admin-header">
        <div class="welcome-panel">
            <div class="welcome-panel-content">
                <h2><?php _e('Welcome to SEO-Forge!', 'seo-forge'); ?></h2>
                <p class="about-description">
                    <?php _e('Your comprehensive WordPress SEO plugin with AI-powered content generation and intelligent customer service chatbot.', 'seo-forge'); ?>
                </p>
                <div class="welcome-panel-column-container">
                    <div class="welcome-panel-column">
                        <h3><?php _e('Get Started', 'seo-forge'); ?></h3>
                        <a class="button button-primary button-hero" href="<?php echo admin_url('admin.php?page=seo-forge-settings'); ?>">
                            <?php _e('Configure Settings', 'seo-forge'); ?>
                        </a>
                    </div>
                    <div class="welcome-panel-column">
                        <h3><?php _e('Generate Content', 'seo-forge'); ?></h3>
                        <a class="button button-secondary" href="<?php echo admin_url('admin.php?page=seo-forge-content'); ?>">
                            <?php _e('Create SEO Content', 'seo-forge'); ?>
                        </a>
                    </div>
                    <div class="welcome-panel-column">
                        <h3><?php _e('Setup Chatbot', 'seo-forge'); ?></h3>
                        <a class="button button-secondary" href="<?php echo admin_url('admin.php?page=seo-forge-chatbot'); ?>">
                            <?php _e('Configure Chatbot', 'seo-forge'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="seo-forge-dashboard-grid">
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-server"></i> <?php _e('API Status', 'seo-forge'); ?></h3>
            </div>
            <div class="card-content">
                <div class="status-indicator <?php echo $connection_status['success'] ? 'status-online' : 'status-offline'; ?>">
                    <span class="status-dot"></span>
                    <?php echo $connection_status['success'] ? __('Connected', 'seo-forge') : __('Disconnected', 'seo-forge'); ?>
                </div>
                <?php if (!$connection_status['success']): ?>
                    <p class="error-message">
                        <?php _e('Unable to connect to SEO-Forge API. Please check your settings.', 'seo-forge'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-chart-bar"></i> <?php _e('Statistics', 'seo-forge'); ?></h3>
            </div>
            <div class="card-content">
                <div class="stat-item">
                    <span class="stat-number"><?php echo get_option('seo_forge_content_count', 0); ?></span>
                    <span class="stat-label"><?php _e('Content Generated', 'seo-forge'); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo get_option('seo_forge_chat_count', 0); ?></span>
                    <span class="stat-label"><?php _e('Chat Conversations', 'seo-forge'); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo get_option('seo_forge_image_count', 0); ?></span>
                    <span class="stat-label"><?php _e('Images Generated', 'seo-forge'); ?></span>
                </div>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-tools"></i> <?php _e('Quick Actions', 'seo-forge'); ?></h3>
            </div>
            <div class="card-content">
                <div class="quick-actions">
                    <a href="<?php echo admin_url('admin.php?page=seo-forge-content'); ?>" class="quick-action-btn">
                        <i class="fas fa-magic"></i>
                        <span><?php _e('Generate Content', 'seo-forge'); ?></span>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=seo-forge-chatbot'); ?>" class="quick-action-btn">
                        <i class="fas fa-robot"></i>
                        <span><?php _e('Setup Chatbot', 'seo-forge'); ?></span>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=seo-forge-settings'); ?>" class="quick-action-btn">
                        <i class="fas fa-cog"></i>
                        <span><?php _e('Settings', 'seo-forge'); ?></span>
                    </a>
                </div>
            </div>
        </div>

        <div class="dashboard-card full-width">
            <div class="card-header">
                <h3><i class="fas fa-newspaper"></i> <?php _e('Recent Activity', 'seo-forge'); ?></h3>
            </div>
            <div class="card-content">
                <div class="activity-log">
                    <?php
                    $recent_activity = get_option('seo_forge_recent_activity', array());
                    if (empty($recent_activity)): ?>
                        <p class="no-activity"><?php _e('No recent activity. Start by generating some content!', 'seo-forge'); ?></p>
                    <?php else: ?>
                        <ul class="activity-list">
                            <?php foreach (array_slice($recent_activity, 0, 5) as $activity): ?>
                                <li class="activity-item">
                                    <span class="activity-time"><?php echo human_time_diff($activity['time'], current_time('timestamp')); ?> <?php _e('ago', 'seo-forge'); ?></span>
                                    <span class="activity-description"><?php echo esc_html($activity['description']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
