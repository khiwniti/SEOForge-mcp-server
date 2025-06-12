<?php
/**
 * SEO Forge - Universal SEO WordPress Plugin
 *
 * @package      SEO_FORGE
 * @copyright    Copyright (C) 2024, SEO Forge - Universal SEO Solution
 * @link         https://seoforge.dev
 * @since        1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       SEO Forge
 * Version:           1.0.0
 * Plugin URI:        https://seoforge.dev
 * Description:       Universal SEO WordPress Plugin with AI-powered content generation, SEO analysis, keyword research, and optimization tools. No license required.
 * Author:            SEO Forge Team
 * Author URI:        https://seoforge.dev
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       seo-forge
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Tested up to:      6.4
 * Requires PHP:      7.4
 * Network:           false
 */

defined( 'ABSPATH' ) || exit;

/**
 * SEO_Forge class.
 *
 * @class The main plugin class that holds the entire plugin.
 */
final class SEO_Forge {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * Holds various class instances
	 *
	 * @var array
	 */
	private $container = [];

	/**
	 * The single instance of the class
	 *
	 * @var SEO_Forge
	 */
	protected static $instance = null;

	/**
	 * Main SEO_Forge instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @see seo_forge()
	 * @return SEO_Forge
	 */
	public static function get() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof SEO_Forge ) ) {
			self::$instance = new SEO_Forge();
		}
		return self::$instance;
	}

	/**
	 * Class constructor.
	 */
	private function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Define the plugin constants.
	 */
	private function define_constants() {
		define( 'SEO_FORGE_VERSION', $this->version );
		define( 'SEO_FORGE_FILE', __FILE__ );
		define( 'SEO_FORGE_PATH', dirname( SEO_FORGE_FILE ) . '/' );
		define( 'SEO_FORGE_URL', plugins_url( '', SEO_FORGE_FILE ) . '/' );
		define( 'SEO_FORGE_ASSETS_URL', SEO_FORGE_URL . 'assets/' );
	}

	/**
	 * Include the required files.
	 */
	private function includes() {
		// Core classes
		require_once SEO_FORGE_PATH . 'includes/class-installer.php';
		require_once SEO_FORGE_PATH . 'includes/class-admin.php';
		require_once SEO_FORGE_PATH . 'includes/class-frontend.php';
		require_once SEO_FORGE_PATH . 'includes/class-api.php';
		require_once SEO_FORGE_PATH . 'includes/class-content-generator.php';
		require_once SEO_FORGE_PATH . 'includes/class-seo-analyzer.php';
		require_once SEO_FORGE_PATH . 'includes/class-keyword-research.php';
		require_once SEO_FORGE_PATH . 'includes/class-meta-box.php';
		require_once SEO_FORGE_PATH . 'includes/class-settings.php';
	}

	/**
	 * Initialize WordPress action hooks.
	 */
	private function init_hooks() {
		add_action( 'init', [ $this, 'init' ], 0 );
		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_scripts' ] );
		
		// Activation and deactivation hooks
		register_activation_hook( __FILE__, [ 'SEO_Forge_Installer', 'activate' ] );
		register_deactivation_hook( __FILE__, [ 'SEO_Forge_Installer', 'deactivate' ] );
	}

	/**
	 * Initialize the plugin.
	 */
	public function init() {
		// Initialize localization
		$this->load_textdomain();

		// Initialize core components
		if ( is_admin() ) {
			new SEO_Forge_Admin();
		} else {
			new SEO_Forge_Frontend();
		}

		new SEO_Forge_API();
		new SEO_Forge_Content_Generator();
		new SEO_Forge_SEO_Analyzer();
		new SEO_Forge_Keyword_Research();
		new SEO_Forge_Meta_Box();
		new SEO_Forge_Settings();

		// Loaded action
		do_action( 'seo_forge/loaded' );
	}

	/**
	 * Initialize plugin for localization.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'seo-forge', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Plugins loaded hook.
	 */
	public function plugins_loaded() {
		// Plugin compatibility checks can go here
	}

	/**
	 * Enqueue admin scripts and styles.
	 */
	public function admin_scripts( $hook ) {
		// Main admin CSS
		wp_enqueue_style(
			'seo-forge-admin',
			SEO_FORGE_ASSETS_URL . 'css/admin.css',
			[],
			SEO_FORGE_VERSION
		);

		// Main admin JS
		wp_enqueue_script(
			'seo-forge-admin',
			SEO_FORGE_ASSETS_URL . 'js/admin.js',
			[ 'jquery', 'wp-api' ],
			SEO_FORGE_VERSION,
			true
		);

		// Localize script
		wp_localize_script( 'seo-forge-admin', 'seoForge', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'seo_forge_nonce' ),
			'apiUrl' => get_option( 'seo_forge_api_url', 'https://seoforge-mcp-platform.vercel.app' ),
			'strings' => [
				'generating' => __( 'Generating content...', 'seo-forge' ),
				'analyzing' => __( 'Analyzing SEO...', 'seo-forge' ),
				'researching' => __( 'Researching keywords...', 'seo-forge' ),
				'error' => __( 'An error occurred. Please try again.', 'seo-forge' ),
				'success' => __( 'Operation completed successfully.', 'seo-forge' ),
			]
		] );
	}

	/**
	 * Enqueue frontend scripts and styles.
	 */
	public function frontend_scripts() {
		// Frontend CSS if needed
		wp_enqueue_style(
			'seo-forge-frontend',
			SEO_FORGE_ASSETS_URL . 'css/frontend.css',
			[],
			SEO_FORGE_VERSION
		);
	}
}

/**
 * Returns the main instance of SEO_Forge to prevent the need to use globals.
 *
 * @return SEO_Forge
 */
function seo_forge() {
	return SEO_Forge::get();
}

// Start the plugin
seo_forge();