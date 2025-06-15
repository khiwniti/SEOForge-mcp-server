<?php

/**
 * Chatbot functionality for SEO-Forge plugin
 */
class SEO_Forge_Chatbot {

    /**
     * The API handler
     */
    private $api;

    /**
     * Initialize the class
     */
    public function __construct() {
        $this->api = new SEO_Forge_API();
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('wp_ajax_seo_forge_chatbot_message', array($this, 'handle_chatbot_message'));
        add_action('wp_ajax_nopriv_seo_forge_chatbot_message', array($this, 'handle_chatbot_message'));
    }

    /**
     * Handle chatbot AJAX requests
     */
    public function handle_chatbot_message() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'seo_forge_chatbot_nonce')) {
            wp_die('Security check failed');
        }

        $message = sanitize_text_field($_POST['message']);
        $context = isset($_POST['context']) ? $_POST['context'] : array();

        // Get chatbot response from API
        $response = $this->api->generate_chatbot_response($message, $context);

        if ($response['success']) {
            wp_send_json_success(array(
                'message' => $response['data']['response'],
                'context' => $response['data']['context'] ?? array()
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Sorry, I encountered an error. Please try again.', 'seo-forge')
            ));
        }
    }

    /**
     * Get chatbot widget HTML
     */
    public function get_chatbot_widget() {
        if (!get_option('seo_forge_chatbot_enabled', 1)) {
            return '';
        }

        ob_start();
        ?>
        <div id="seo-forge-chatbot" class="seo-forge-chatbot-container">
            <div id="seo-forge-chatbot-toggle" class="seo-forge-chatbot-toggle">
                <i class="fas fa-comment-dots"></i>
            </div>
            <div id="seo-forge-chatbot-window" class="seo-forge-chatbot-window" style="display: none;">
                <div class="seo-forge-chatbot-header">
                    <h4><?php _e('SEO Assistant', 'seo-forge'); ?></h4>
                    <button id="seo-forge-chatbot-close" class="seo-forge-chatbot-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="seo-forge-chatbot-messages" class="seo-forge-chatbot-messages">
                    <div class="seo-forge-chatbot-message bot-message">
                        <div class="message-content">
                            <?php _e('Hello! I\'m your SEO assistant. How can I help you optimize your content today?', 'seo-forge'); ?>
                        </div>
                    </div>
                </div>
                <div class="seo-forge-chatbot-input-container">
                    <input type="text" id="seo-forge-chatbot-input" placeholder="<?php _e('Type your message...', 'seo-forge'); ?>">
                    <button id="seo-forge-chatbot-send">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
