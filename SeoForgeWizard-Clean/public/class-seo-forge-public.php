<?php

/**
 * The public-facing functionality of the plugin.
 */
class SEO_Forge_Public {

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/seo-forge-public.css', array(), $this->version, 'all');
        wp_enqueue_style('seo-forge-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/seo-forge-public.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->plugin_name . '-chatbot', plugin_dir_url(__FILE__) . 'js/seo-forge-chatbot.js', array('jquery'), $this->version, false);
        
        wp_localize_script($this->plugin_name . '-chatbot', 'seoForgeChatbot', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('seo_forge_chatbot_nonce'),
            'enabled' => get_option('seo_forge_chatbot_enabled', 1),
            'position' => get_option('seo_forge_chatbot_position', 'bottom-right'),
            'color' => get_option('seo_forge_chatbot_color', '#007cba'),
            'welcome_message' => get_option('seo_forge_chatbot_welcome_message', __('Hello! I\'m your SEO assistant. How can I help you optimize your content today?', 'seo-forge')),
            'placeholder' => get_option('seo_forge_chatbot_placeholder', __('Type your message...', 'seo-forge')),
            'strings' => array(
                'seo_assistant' => __('SEO Assistant', 'seo-forge'),
                'thinking' => __('Thinking...', 'seo-forge'),
                'error_message' => __('Sorry, I encountered an error. Please try again.', 'seo-forge'),
                'network_error' => __('Network error. Please check your connection.', 'seo-forge')
            )
        ));
    }

    /**
     * Add chatbot widget to footer
     */
    public function add_chatbot_widget() {
        if (!get_option('seo_forge_chatbot_enabled', 1)) {
            return;
        }

        $chatbot = new SEO_Forge_Chatbot();
        echo $chatbot->get_chatbot_widget();
    }
}
