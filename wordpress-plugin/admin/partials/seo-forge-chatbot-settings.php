<?php
/**
 * Chatbot settings page for SEO-Forge plugin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Handle form submission
if (isset($_POST['submit'])) {
    if (wp_verify_nonce($_POST['seo_forge_chatbot_nonce'], 'seo_forge_chatbot_settings')) {
        update_option('seo_forge_chatbot_enabled', isset($_POST['chatbot_enabled']) ? 1 : 0);
        update_option('seo_forge_chatbot_position', sanitize_text_field($_POST['chatbot_position']));
        update_option('seo_forge_chatbot_color', sanitize_hex_color($_POST['chatbot_color']));
        update_option('seo_forge_chatbot_welcome_message', sanitize_textarea_field($_POST['welcome_message']));
        update_option('seo_forge_chatbot_placeholder', sanitize_text_field($_POST['placeholder_text']));
        update_option('seo_forge_chatbot_knowledge_base', sanitize_textarea_field($_POST['knowledge_base']));

        echo '<div class="notice notice-success is-dismissible"><p>' . __('Chatbot settings saved successfully!', 'seo-forge') . '</p></div>';
    }
}

$chatbot_enabled = get_option('seo_forge_chatbot_enabled', 1);
$chatbot_position = get_option('seo_forge_chatbot_position', 'bottom-right');
$chatbot_color = get_option('seo_forge_chatbot_color', '#007cba');
$welcome_message = get_option('seo_forge_chatbot_welcome_message', __('Hello! I\'m your SEO assistant. How can I help you optimize your content today?', 'seo-forge'));
$placeholder_text = get_option('seo_forge_chatbot_placeholder', __('Type your message...', 'seo-forge'));
$knowledge_base = get_option('seo_forge_chatbot_knowledge_base', '');
?>

<div class="wrap seo-forge-chatbot-settings">
    <h1><i class="fas fa-robot"></i> <?php _e('Chatbot Configuration', 'seo-forge'); ?></h1>

    <div class="chatbot-settings-container">
        <div class="settings-main">
            <form method="post" action="">
                <?php wp_nonce_field('seo_forge_chatbot_settings', 'seo_forge_chatbot_nonce'); ?>

                <div class="settings-section">
                    <h2><?php _e('General Settings', 'seo-forge'); ?></h2>
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
                                <label for="chatbot_position"><?php _e('Position', 'seo-forge'); ?></label>
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
                                <label for="chatbot_color"><?php _e('Primary Color', 'seo-forge'); ?></label>
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

                <div class="settings-section">
                    <h2><?php _e('Messages & Content', 'seo-forge'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="welcome_message"><?php _e('Welcome Message', 'seo-forge'); ?></label>
                            </th>
                            <td>
                                <textarea id="welcome_message" name="welcome_message" rows="3" class="large-text"><?php echo esc_textarea($welcome_message); ?></textarea>
                                <p class="description">
                                    <?php _e('The first message displayed when users open the chatbot.', 'seo-forge'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="placeholder_text"><?php _e('Input Placeholder', 'seo-forge'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="placeholder_text" name="placeholder_text" value="<?php echo esc_attr($placeholder_text); ?>" class="regular-text" />
                                <p class="description">
                                    <?php _e('Placeholder text for the message input field.', 'seo-forge'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="settings-section">
                    <h2><?php _e('Knowledge Base', 'seo-forge'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="knowledge_base"><?php _e('Custom Knowledge Base', 'seo-forge'); ?></label>
                            </th>
                            <td>
                                <textarea id="knowledge_base" name="knowledge_base" rows="10" class="large-text" placeholder="<?php _e('Enter information about your business, services, FAQ, etc. This will help the chatbot provide more relevant responses.', 'seo-forge'); ?>"><?php echo esc_textarea($knowledge_base); ?></textarea>
                                <p class="description">
                                    <?php _e('Add custom information that the chatbot should know about your business. This includes services, products, company info, FAQ, etc.', 'seo-forge'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php submit_button(__('Save Chatbot Settings', 'seo-forge')); ?>
            </form>
        </div>

        <div class="chatbot-preview">
            <h3><?php _e('Live Preview', 'seo-forge'); ?></h3>
            <div class="preview-container">
                <div id="chatbot-preview" class="chatbot-preview-widget">
                    <div class="preview-chatbot-toggle" style="background-color: <?php echo esc_attr($chatbot_color); ?>;">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <div class="preview-chatbot-window" style="display: none;">
                        <div class="preview-chatbot-header" style="background-color: <?php echo esc_attr($chatbot_color); ?>;">
                            <h4><?php _e('SEO Assistant', 'seo-forge'); ?></h4>
                            <button class="preview-chatbot-close">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="preview-chatbot-messages">
                            <div class="preview-chatbot-message bot-message">
                                <div class="message-content">
                                    <?php echo esc_html($welcome_message); ?>
                                </div>
                            </div>
                        </div>
                        <div class="preview-chatbot-input-container">
                            <input type="text" placeholder="<?php echo esc_attr($placeholder_text); ?>" readonly>
                            <button style="background-color: <?php echo esc_attr($chatbot_color); ?>;">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <p class="preview-note">
                <?php _e('This is how the chatbot will appear on your website.', 'seo-forge'); ?>
            </p>

            <div class="chatbot-test">
                <h4><?php _e('Test Chatbot', 'seo-forge'); ?></h4>
                <div class="test-conversation">
                    <div id="test-messages" class="test-messages"></div>
                    <div class="test-input-container">
                        <input type="text" id="test-message-input" placeholder="<?php _e('Type a test message...', 'seo-forge'); ?>">
                        <button id="send-test-message" class="button button-primary">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Update preview when settings change
    function updatePreview() {
        var color = $('#chatbot_color').val();
        var welcomeMessage = $('#welcome_message').val();
        var placeholder = $('#placeholder_text').val();
        
        $('.preview-chatbot-toggle').css('background-color', color);
        $('.preview-chatbot-header').css('background-color', color);
        $('.preview-chatbot-input-container button').css('background-color', color);
        $('.message-content').text(welcomeMessage);
        $('.preview-chatbot-input-container input').attr('placeholder', placeholder);
    }

    $('#chatbot_color, #welcome_message, #placeholder_text').on('change input', updatePreview);

    // Preview toggle functionality
    $('.preview-chatbot-toggle').click(function() {
        $('.preview-chatbot-window').toggle();
    });

    $('.preview-chatbot-close').click(function() {
        $('.preview-chatbot-window').hide();
    });

    // Test chatbot functionality
    $('#send-test-message, #test-message-input').on('click keypress', function(e) {
        if (e.type === 'click' || e.which === 13) {
            var message = $('#test-message-input').val().trim();
            if (message === '') return;

            // Add user message
            $('#test-messages').append(
                '<div class="test-message user-message">' +
                '<strong><?php _e('You:', 'seo-forge'); ?></strong> ' + message +
                '</div>'
            );

            $('#test-message-input').val('');

            // Add loading message
            $('#test-messages').append(
                '<div class="test-message bot-message loading">' +
                '<strong><?php _e('Assistant:', 'seo-forge'); ?></strong> <i class="fas fa-spinner fa-spin"></i> <?php _e('Thinking...', 'seo-forge'); ?>' +
                '</div>'
            );

            // Scroll to bottom
            $('#test-messages').scrollTop($('#test-messages')[0].scrollHeight);

            // Send AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'seo_forge_chatbot_message',
                    message: message,
                    nonce: '<?php echo wp_create_nonce('seo_forge_chatbot_nonce'); ?>'
                },
                success: function(response) {
                    $('.test-message.loading').remove();
                    if (response.success) {
                        $('#test-messages').append(
                            '<div class="test-message bot-message">' +
                            '<strong><?php _e('Assistant:', 'seo-forge'); ?></strong> ' + response.data.message +
                            '</div>'
                        );
                    } else {
                        $('#test-messages').append(
                            '<div class="test-message bot-message error">' +
                            '<strong><?php _e('Assistant:', 'seo-forge'); ?></strong> <?php _e('Sorry, I encountered an error. Please try again.', 'seo-forge'); ?>' +
                            '</div>'
                        );
                    }
                    $('#test-messages').scrollTop($('#test-messages')[0].scrollHeight);
                },
                error: function() {
                    $('.test-message.loading').remove();
                    $('#test-messages').append(
                        '<div class="test-message bot-message error">' +
                        '<strong><?php _e('Assistant:', 'seo-forge'); ?></strong> <?php _e('Connection error. Please check your API settings.', 'seo-forge'); ?>' +
                        '</div>'
                    );
                    $('#test-messages').scrollTop($('#test-messages')[0].scrollHeight);
                }
            });
        }
    });
});
</script>
