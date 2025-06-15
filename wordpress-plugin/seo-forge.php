<?php
/**
 * Plugin Name: SEO-Forge
 * Plugin URI: https://seoforge.com
 * Description: A comprehensive WordPress SEO plugin with AI-powered content generation and intelligent customer service chatbot
 * Version: 1.0.0
 * Author: SEO-Forge Team
 * License: GPL v2 or later
 * Text Domain: seo-forge
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Define plugin constants
 */
require_once plugin_dir_path(__FILE__) . 'includes/class-seo-forge-constants.php';
SEO_Forge_Constants::define_constants();
SEO_Forge_Constants::validate_constants();

/**
 * Check plugin requirements
 */
require_once plugin_dir_path(__FILE__) . 'includes/class-seo-forge-requirements.php';
if (!SEO_Forge_Requirements::deactivate_if_requirements_not_met()) {
    return;
}

/**
 * The code that runs during plugin activation.
 */
function activate_seo_forge() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-seo-forge-activator.php';
    SEO_Forge_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_seo_forge() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-seo-forge-deactivator.php';
    SEO_Forge_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_seo_forge');
register_deactivation_hook(__FILE__, 'deactivate_seo_forge');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-seo-forge.php';

/**
 * Begins execution of the plugin.
 */
function run_seo_forge() {
    error_log('SEO-Forge: Plugin initialization started');
    $plugin = new SEO_Forge();
    $plugin->run();
    error_log('SEO-Forge: Plugin initialization completed');
}

/**
 * Simple admin menu hook as backup
 */
function seo_forge_simple_admin_menu() {
    error_log('SEO-Forge: Simple admin menu hook called');
    add_menu_page(
        'SEO-Forge',
        'SEO-Forge',
        'manage_options',
        'seo-forge-simple',
        'seo_forge_simple_admin_page',
        'dashicons-chart-line',
        30
    );
}

function seo_forge_simple_admin_page() {
    echo '<div class="wrap"><h1>SEO-Forge</h1><p>Plugin is working! This is a simple test page.</p></div>';
}

// Hook both methods
add_action('admin_menu', 'seo_forge_simple_admin_menu');
run_seo_forge();
