<?php
/**
 * SEO Forge Dashboard Template
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap seo-forge-dashboard">
    <h1><?php _e( 'SEO Forge Dashboard', 'seo-forge' ); ?></h1>
    
    <div class="seo-forge-status">
        <div class="seo-forge-status-indicator <?php echo get_option( 'seo_forge_api_url' ) ? 'connected' : ''; ?>"></div>
        <span><?php _e( 'API Status:', 'seo-forge' ); ?></span>
        <strong><?php echo get_option( 'seo_forge_api_url' ) ? __( 'Connected', 'seo-forge' ) : __( 'Not Connected', 'seo-forge' ); ?></strong>
        <button type="button" class="seo-forge-button secondary seo-forge-test-connection">
            <?php _e( 'Test Connection', 'seo-forge' ); ?>
        </button>
    </div>

    <div class="seo-forge-cards">
        <div class="seo-forge-card">
            <h3><?php _e( 'Content Generator', 'seo-forge' ); ?></h3>
            <p><?php _e( 'Generate SEO-optimized content using AI. Create blog posts, product descriptions, and more with just a few keywords.', 'seo-forge' ); ?></p>
            <a href="<?php echo admin_url( 'admin.php?page=seo-forge-content' ); ?>" class="seo-forge-button">
                <?php _e( 'Generate Content', 'seo-forge' ); ?>
            </a>
        </div>

        <div class="seo-forge-card">
            <h3><?php _e( 'SEO Analyzer', 'seo-forge' ); ?></h3>
            <p><?php _e( 'Analyze your content for SEO optimization. Get detailed reports and actionable recommendations to improve your rankings.', 'seo-forge' ); ?></p>
            <a href="<?php echo admin_url( 'admin.php?page=seo-forge-analyzer' ); ?>" class="seo-forge-button">
                <?php _e( 'Analyze SEO', 'seo-forge' ); ?>
            </a>
        </div>

        <div class="seo-forge-card">
            <h3><?php _e( 'Keyword Research', 'seo-forge' ); ?></h3>
            <p><?php _e( 'Discover high-value keywords for your content strategy. Find search volume, difficulty, and competition data.', 'seo-forge' ); ?></p>
            <a href="<?php echo admin_url( 'admin.php?page=seo-forge-keywords' ); ?>" class="seo-forge-button">
                <?php _e( 'Research Keywords', 'seo-forge' ); ?>
            </a>
        </div>

        <div class="seo-forge-card">
            <h3><?php _e( 'Settings', 'seo-forge' ); ?></h3>
            <p><?php _e( 'Configure your SEO Forge plugin settings, API connection, and preferences.', 'seo-forge' ); ?></p>
            <a href="<?php echo admin_url( 'admin.php?page=seo-forge-settings' ); ?>" class="seo-forge-button">
                <?php _e( 'Configure Settings', 'seo-forge' ); ?>
            </a>
        </div>
    </div>

    <div class="seo-forge-results" style="display: none;"></div>
</div>