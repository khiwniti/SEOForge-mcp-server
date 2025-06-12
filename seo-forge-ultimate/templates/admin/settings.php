<?php
/**
 * SEO Forge Settings Template
 */

defined( 'ABSPATH' ) || exit;

// Handle form submission
if ( isset( $_POST['submit'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'seo_forge_settings' ) ) {
    update_option( 'seo_forge_api_url', sanitize_url( $_POST['seo_forge_api_url'] ) );
    update_option( 'seo_forge_api_key', sanitize_text_field( $_POST['seo_forge_api_key'] ) );
    update_option( 'seo_forge_enable_content_generator', isset( $_POST['seo_forge_enable_content_generator'] ) );
    update_option( 'seo_forge_enable_seo_analyzer', isset( $_POST['seo_forge_enable_seo_analyzer'] ) );
    update_option( 'seo_forge_enable_keyword_research', isset( $_POST['seo_forge_enable_keyword_research'] ) );
    update_option( 'seo_forge_auto_generate_meta', isset( $_POST['seo_forge_auto_generate_meta'] ) );
    update_option( 'seo_forge_default_language', sanitize_text_field( $_POST['seo_forge_default_language'] ) );
    update_option( 'seo_forge_default_country', sanitize_text_field( $_POST['seo_forge_default_country'] ) );
    
    echo '<div class="notice notice-success"><p>' . __( 'Settings saved successfully!', 'seo-forge' ) . '</p></div>';
}
?>

<div class="wrap">
    <h1><?php _e( 'SEO Forge Settings', 'seo-forge' ); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field( 'seo_forge_settings' ); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e( 'API URL', 'seo-forge' ); ?></th>
                <td>
                    <input type="url" name="seo_forge_api_url" value="<?php echo esc_attr( get_option( 'seo_forge_api_url', 'https://seoforge-mcp-platform.vercel.app' ) ); ?>" class="regular-text" />
                    <p class="description"><?php _e( 'The URL of your SEO Forge MCP server', 'seo-forge' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e( 'API Key', 'seo-forge' ); ?></th>
                <td>
                    <input type="password" name="seo_forge_api_key" value="<?php echo esc_attr( get_option( 'seo_forge_api_key' ) ); ?>" class="regular-text" />
                    <p class="description"><?php _e( 'Your API key for authentication (optional but recommended)', 'seo-forge' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e( 'Features', 'seo-forge' ); ?></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox" name="seo_forge_enable_content_generator" <?php checked( get_option( 'seo_forge_enable_content_generator', true ) ); ?> />
                            <?php _e( 'Enable Content Generator', 'seo-forge' ); ?>
                        </label><br>
                        
                        <label>
                            <input type="checkbox" name="seo_forge_enable_seo_analyzer" <?php checked( get_option( 'seo_forge_enable_seo_analyzer', true ) ); ?> />
                            <?php _e( 'Enable SEO Analyzer', 'seo-forge' ); ?>
                        </label><br>
                        
                        <label>
                            <input type="checkbox" name="seo_forge_enable_keyword_research" <?php checked( get_option( 'seo_forge_enable_keyword_research', true ) ); ?> />
                            <?php _e( 'Enable Keyword Research', 'seo-forge' ); ?>
                        </label><br>
                        
                        <label>
                            <input type="checkbox" name="seo_forge_auto_generate_meta" <?php checked( get_option( 'seo_forge_auto_generate_meta', false ) ); ?> />
                            <?php _e( 'Auto-generate meta descriptions', 'seo-forge' ); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e( 'Default Language', 'seo-forge' ); ?></th>
                <td>
                    <select name="seo_forge_default_language">
                        <?php foreach ( SEO_Forge_Content_Generator::get_languages() as $key => $label ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, get_option( 'seo_forge_default_language', 'en' ) ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e( 'Default Country', 'seo-forge' ); ?></th>
                <td>
                    <select name="seo_forge_default_country">
                        <?php foreach ( SEO_Forge_Keyword_Research::get_countries() as $key => $label ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, get_option( 'seo_forge_default_country', 'US' ) ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" class="button-primary" value="<?php _e( 'Save Settings', 'seo-forge' ); ?>" />
            <button type="button" class="button seo-forge-test-connection">
                <?php _e( 'Test Connection', 'seo-forge' ); ?>
            </button>
        </p>
    </form>
    
    <div class="seo-forge-results" style="display: none;"></div>
</div>