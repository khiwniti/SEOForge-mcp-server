<?php
/**
 * SEO Forge Content Generator Meta Box Template
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="seo-forge-meta-box">
    <div class="seo-forge-form-group">
        <label for="meta-keywords"><?php _e( 'Keywords', 'seo-forge' ); ?></label>
        <input type="text" name="meta-keywords" id="meta-keywords" placeholder="<?php _e( 'digital marketing, SEO tips', 'seo-forge' ); ?>" />
        <div class="description"><?php _e( 'Keywords to focus on for content generation', 'seo-forge' ); ?></div>
    </div>

    <div class="seo-forge-form-group">
        <label for="meta-industry"><?php _e( 'Industry', 'seo-forge' ); ?></label>
        <select name="meta-industry" id="meta-industry">
            <option value=""><?php _e( 'Select Industry', 'seo-forge' ); ?></option>
            <?php foreach ( SEO_Forge_Content_Generator::get_industries() as $key => $label ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
        </select>
        <div class="description"><?php _e( 'Industry context for better content generation', 'seo-forge' ); ?></div>
    </div>

    <div class="seo-forge-form-group">
        <label for="meta-content-type"><?php _e( 'Content Type', 'seo-forge' ); ?></label>
        <select name="meta-content-type" id="meta-content-type">
            <?php foreach ( SEO_Forge_Content_Generator::get_content_types() as $key => $label ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="seo-forge-form-group">
        <button type="button" class="seo-forge-button seo-forge-generate-content">
            <?php _e( 'Generate Content', 'seo-forge' ); ?>
        </button>
        <button type="button" class="seo-forge-button secondary">
            <?php _e( 'Get Suggestions', 'seo-forge' ); ?>
        </button>
    </div>

    <div class="seo-forge-content-results" style="display: none;">
        <h4><?php _e( 'Generated Content', 'seo-forge' ); ?></h4>
        <!-- Results will be populated by JavaScript -->
    </div>
</div>