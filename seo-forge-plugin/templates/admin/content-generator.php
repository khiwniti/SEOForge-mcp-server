<?php
/**
 * SEO Forge Content Generator Template
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap">
    <h1><?php _e( 'Content Generator', 'seo-forge' ); ?></h1>
    
    <div class="seo-forge-form">
        <div class="seo-forge-form-group">
            <label for="keywords"><?php _e( 'Keywords', 'seo-forge' ); ?></label>
            <input type="text" name="keywords" id="keywords" placeholder="<?php _e( 'Enter keywords separated by commas', 'seo-forge' ); ?>" />
            <div class="description"><?php _e( 'Keywords to focus on for content generation', 'seo-forge' ); ?></div>
        </div>

        <div class="seo-forge-form-group">
            <label for="industry"><?php _e( 'Industry', 'seo-forge' ); ?></label>
            <select name="industry" id="industry">
                <option value=""><?php _e( 'Select Industry', 'seo-forge' ); ?></option>
                <?php foreach ( SEO_Forge_Content_Generator::get_industries() as $key => $label ) : ?>
                    <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
                <?php endforeach; ?>
            </select>
            <div class="description"><?php _e( 'Industry context for better content generation', 'seo-forge' ); ?></div>
        </div>

        <div class="seo-forge-form-group">
            <label for="content_type"><?php _e( 'Content Type', 'seo-forge' ); ?></label>
            <select name="content_type" id="content_type">
                <?php foreach ( SEO_Forge_Content_Generator::get_content_types() as $key => $label ) : ?>
                    <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
                <?php endforeach; ?>
            </select>
            <div class="description"><?php _e( 'Type of content to generate', 'seo-forge' ); ?></div>
        </div>

        <div class="seo-forge-form-group">
            <label for="language"><?php _e( 'Language', 'seo-forge' ); ?></label>
            <select name="language" id="language">
                <?php foreach ( SEO_Forge_Content_Generator::get_languages() as $key => $label ) : ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, get_option( 'seo_forge_default_language', 'en' ) ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="description"><?php _e( 'Language for content generation', 'seo-forge' ); ?></div>
        </div>

        <div class="seo-forge-form-group">
            <button type="button" class="seo-forge-button seo-forge-generate-content">
                <?php _e( 'Generate Content', 'seo-forge' ); ?>
            </button>
            <button type="button" class="seo-forge-button secondary">
                <?php _e( 'Get Suggestions', 'seo-forge' ); ?>
            </button>
        </div>
    </div>

    <div class="seo-forge-content-results" style="display: none;">
        <h3><?php _e( 'Generated Content', 'seo-forge' ); ?></h3>
        <!-- Results will be populated by JavaScript -->
    </div>

    <div class="seo-forge-results" style="display: none;"></div>
</div>

<script>
jQuery(document).ready(function($) {
    // Auto-populate keywords from URL parameters if available
    const urlParams = new URLSearchParams(window.location.search);
    const keywords = urlParams.get('keywords');
    if (keywords) {
        $('#keywords').val(decodeURIComponent(keywords));
    }
});
</script>