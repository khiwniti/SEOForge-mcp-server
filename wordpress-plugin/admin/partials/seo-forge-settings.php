<?php
/**
 * Settings page for SEO-Forge plugin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Handle form submission
if (isset($_POST['submit'])) {
    if (wp_verify_nonce($_POST['seo_forge_settings_nonce'], 'seo_forge_settings')) {
        update_option('seo_forge_api_key', sanitize_text_field($_POST['api_key']));
        update_option('seo_forge_chatbot_enabled', isset($_POST['chatbot_enabled']) ? 1 : 0);
        update_option('seo_forge_language', sanitize_text_field($_POST['language']));
        update_option('seo_forge_chatbot_position', sanitize_text_field($_POST['chatbot_position']));
        update_option('seo_forge_chatbot_color', sanitize_hex_color($_POST['chatbot_color']));

        echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully!', 'seo-forge') . '</p></div>';
    }
}

$api_key = get_option('seo_forge_api_key', '');
$chatbot_enabled = get_option('seo_forge_chatbot_enabled', 1);
$language = get_option('seo_forge_language', 'en');
$chatbot_position = get_option('seo_forge_chatbot_position', 'bottom-right');
$chatbot_color = get_option('seo_forge_chatbot_color', '#007cba');
?>

<div class="wrap seo-forge-settings">
    <h1><i class="fas fa-cog"></i> <?php _e('SEO-Forge Settings', 'seo-forge'); ?></h1>

    <form method="post" action="">
        <?php wp_nonce_field('seo_forge_settings', 'seo_forge_settings_nonce'); ?>

        <div class="settings-sections">
            <div class="settings-section">
                <h2><?php _e('API Configuration', 'seo-forge'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="api_key"><?php _e('API Key', 'seo-forge'); ?></label>
                        </th>
                        <td>
                            <input type="password" id="api_key" name="api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
                            <button type="button" class="button" id="toggle-api-key">
                                <i class="fas fa-eye"></i> <?php _e('Show', 'seo-forge'); ?>
                            </button>
                            <p class="description">
                                <?php _e('Enter your SEO-Forge API key. Leave empty to use default settings.', 'seo-forge'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="language"><?php _e('Default Language', 'seo-forge'); ?></label>
                        </th>
                        <td>
                            <select id="language" name="language">
                                <option value="en" <?php selected($language, 'en'); ?>><?php _e('English', 'seo-forge'); ?></option>
                                <option value="th" <?php selected($language, 'th'); ?>><?php _e('Thai (ไทย)', 'seo-forge'); ?></option>
                            </select>
                            <p class="description">
                                <?php _e('Select the default language for content generation and chatbot responses.', 'seo-forge'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <div class="api-status-check">
                    <button type="button" class="button" id="test-api-connection">
                        <i class="fas fa-check-circle"></i> <?php _e('Test API Connection', 'seo-forge'); ?>
                    </button>
                    <div id="api-status-result"></div>
                </div>
            </div>

            <div class="settings-section">
                <h2><?php _e('Chatbot Settings', 'seo-forge'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="chatbot_enabled"><?php _e('Enable Chatbot', 'seo-forge'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="chatbot_enabled" name="chatbot_enabled" value="1" <?php checked($chatbot_enabled, 1); ?> />
                            <label for="chatbot_enabled"><?php _e('Display chatbot widget on frontend', 'seo-forge'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="chatbot_position"><?php _e('Chatbot Position', 'seo-forge'); ?></label>
                        </th>
                        <td>
                            <select id="chatbot_position" name="chatbot_position">
                                <option value="bottom-right" <?php selected($chatbot_position, 'bottom-right'); ?>><?php _e('Bottom Right', 'seo-forge'); ?></option>
                                <option value="bottom-left" <?php selected($chatbot_position, 'bottom-left'); ?>><?php _e('Bottom Left', 'seo-forge'); ?></option>
                                <option value="top-right" <?php selected($chatbot_position, 'top-right'); ?>><?php _e('Top Right', 'seo-forge'); ?></option>
                                <option value="top-left" <?php selected($chatbot_position, 'top-left'); ?>><?php _e('Top Left', 'seo-forge'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="chatbot_color"><?php _e('Chatbot Color', 'seo-forge'); ?></label>
                        </th>
                        <td>
                            <input type="color" id="chatbot_color" name="chatbot_color" value="<?php echo esc_attr($chatbot_color); ?>" />
                            <p class="description">
                                <?php _e('Choose the primary color for the chatbot widget.', 'seo-forge'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <?php submit_button(__('Save Settings', 'seo-forge')); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle API key visibility
    $('#toggle-api-key').click(function() {
        var input = $('#api_key');
        var button = $(this);
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            button.html('<i class="fas fa-eye-slash"></i> <?php _e('Hide', 'seo-forge'); ?>');
        } else {
            input.attr('type', 'password');
            button.html('<i class="fas fa-eye"></i> <?php _e('Show', 'seo-forge'); ?>');
        }
    });

    // Test API connection
    $('#test-api-connection').click(function() {
        var button = $(this);
        var result = $('#api-status-result');
        
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> <?php _e('Testing...', 'seo-forge'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'seo_forge_test_api',
                nonce: '<?php echo wp_create_nonce('seo_forge_nonce'); ?>',
                api_key: $('#api_key').val()
            },
            success: function(response) {
                if (response.success) {
                    result.html('<div class="notice notice-success inline"><p><i class="fas fa-check-circle"></i> ' + response.data.message + '</p></div>');
                } else {
                    result.html('<div class="notice notice-error inline"><p><i class="fas fa-exclamation-circle"></i> ' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                result.html('<div class="notice notice-error inline"><p><i class="fas fa-exclamation-circle"></i> <?php _e('Connection test failed', 'seo-forge'); ?></p></div>');
            },
            complete: function() {
                button.prop('disabled', false).html('<i class="fas fa-check-circle"></i> <?php _e('Test API Connection', 'seo-forge'); ?>');
            }
        });
    });
});
</script>
