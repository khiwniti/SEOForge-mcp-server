<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    SEO_Forge
 * @subpackage SEO_Forge/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
class SEO_Forge_Admin {

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
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version     The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Debug: Log that the admin class is being instantiated
        error_log('SEO-Forge: Admin class instantiated with plugin_name: ' . $plugin_name . ', version: ' . $version);
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/seo-forge-admin.css', array(), $this->version, 'all');
        wp_enqueue_style('seo-forge-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0');
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/seo-forge-admin.js', array('jquery'), $this->version, false);
        wp_localize_script($this->plugin_name, 'seoForgeAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('seo_forge_nonce')
        ));
    }

    /**
     * Add an options page under the Settings submenu
     */
    public function add_plugin_admin_menu() {
        // Debug: Log that this function is being called
        error_log('SEO-Forge: add_plugin_admin_menu called with plugin_name: ' . $this->plugin_name);

        $hook = add_menu_page(
            __('SEO-Forge', 'seo-forge'),
            __('SEO-Forge', 'seo-forge'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_setup_page'),
            'dashicons-chart-line',
            30
        );

        // Debug: Log the hook result
        error_log('SEO-Forge: Menu hook result: ' . $hook);

        add_submenu_page(
            $this->plugin_name,
            __('Dashboard', 'seo-forge'),
            __('Dashboard', 'seo-forge'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_setup_page')
        );

        add_submenu_page(
            $this->plugin_name,
            __('Content Generator', 'seo-forge'),
            __('Content Generator', 'seo-forge'),
            'manage_options',
            $this->plugin_name . '-content',
            array($this, 'display_content_generator_page')
        );

        add_submenu_page(
            $this->plugin_name,
            __('Chatbot', 'seo-forge'),
            __('Chatbot', 'seo-forge'),
            'manage_options',
            $this->plugin_name . '-chatbot',
            array($this, 'display_chatbot_page')
        );

        add_submenu_page(
            $this->plugin_name,
            __('Settings', 'seo-forge'),
            __('Settings', 'seo-forge'),
            'manage_options',
            $this->plugin_name . '-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Render the settings page for this plugin.
     */
    public function display_plugin_setup_page() {
        include_once 'partials/seo-forge-admin-display.php';
    }

    /**
     * Render the content generator page.
     */
    public function display_content_generator_page() {
        include_once 'partials/seo-forge-content-generator.php';
    }

    /**
     * Render the chatbot settings page.
     */
    public function display_chatbot_page() {
        include_once 'partials/seo-forge-chatbot-settings.php';
    }

    /**
     * Render the settings page.
     */
    public function display_settings_page() {
        include_once 'partials/seo-forge-settings.php';
    }

    /**
     * Update all the settings field
     */
    public function options_update() {
        register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
    }

    /**
     * Validate all the plugin fields
     */
    public function validate($input) {
        $valid = array();

        $valid['api_key'] = sanitize_text_field($input['api_key']);
        $valid['chatbot_enabled'] = (isset($input['chatbot_enabled']) && !empty($input['chatbot_enabled'])) ? 1 : 0;
        $valid['language'] = sanitize_text_field($input['language']);

        return $valid;
    }
}
