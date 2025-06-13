<?php

/**
 * The file that defines the core plugin class
 */
class SEO_Forge {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct() {
        if (defined('SEO_FORGE_VERSION')) {
            $this->version = SEO_FORGE_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'seo-forge';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-seo-forge-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-seo-forge-i18n.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-seo-forge-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-seo-forge-api.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-seo-forge-chatbot.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-seo-forge-content-generator.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-seo-forge-ajax.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-seo-forge-public.php';

        $this->loader = new SEO_Forge_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     */
    private function set_locale() {
        $plugin_i18n = new SEO_Forge_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     */
    private function define_admin_hooks() {
        error_log('SEO-Forge: define_admin_hooks called');

        $plugin_admin = new SEO_Forge_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'options_update');

        error_log('SEO-Forge: Admin hooks registered');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     */
    private function define_public_hooks() {
        $plugin_public = new SEO_Forge_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('wp_footer', $plugin_public, 'add_chatbot_widget');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }


}
