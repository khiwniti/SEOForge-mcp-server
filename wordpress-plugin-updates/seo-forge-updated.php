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
 * Plugin Name:       SEO Forge Ultimate - MCP Server Edition
 * Version:           1.7.0
 * Plugin URI:        https://seoforge.dev
 * Description:       Universal SEO WordPress Plugin with AI-powered content generation, Flux image generation, SEO analysis, keyword research, and optimization tools. Updated for Express MCP Server with unified architecture, enhanced performance, and better reliability. No license required.
 * Author:            SEO Forge Team
 * Author URI:        https://seoforge.dev
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       seo-forge-ultimate
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Tested up to:      6.4
 * Requires PHP:      7.4
 * Network:           false
 */

defined( 'ABSPATH' ) || exit;

// Prevent conflicts with other versions
if ( class_exists( 'SEO_Forge_Ultimate' ) ) {
	return;
}

/**
 * SEO_Forge_Ultimate class.
 *
 * @class The main plugin class that holds the entire plugin.
 */
final class SEO_Forge_Ultimate {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	public $version = '1.7.0';

	/**
	 * Holds various class instances
	 *
	 * @var array
	 */
	private $container = [];

	/**
	 * The single instance of the class
	 *
	 * @var SEO_Forge_Ultimate
	 */
	protected static $instance = null;

	/**
	 * Main SEO_Forge_Ultimate instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @see seo_forge_ultimate()
	 * @return SEO_Forge_Ultimate
	 */
	public static function get() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof SEO_Forge_Ultimate ) ) {
			self::$instance = new SEO_Forge_Ultimate();
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
		define( 'SEO_FORGE_ULTIMATE_VERSION', $this->version );
		define( 'SEO_FORGE_ULTIMATE_FILE', __FILE__ );
		define( 'SEO_FORGE_ULTIMATE_PATH', dirname( SEO_FORGE_ULTIMATE_FILE ) . '/' );
		define( 'SEO_FORGE_ULTIMATE_URL', plugins_url( '', SEO_FORGE_ULTIMATE_FILE ) . '/' );
		define( 'SEO_FORGE_ULTIMATE_ASSETS_URL', SEO_FORGE_ULTIMATE_URL . 'assets/' );
		
		// Backward compatibility constants (only if not already defined)
		if ( ! defined( 'SEO_FORGE_VERSION' ) ) {
			define( 'SEO_FORGE_VERSION', $this->version );
		}
		if ( ! defined( 'SEO_FORGE_FILE' ) ) {
			define( 'SEO_FORGE_FILE', __FILE__ );
		}
		if ( ! defined( 'SEO_FORGE_PATH' ) ) {
			define( 'SEO_FORGE_PATH', dirname( SEO_FORGE_FILE ) . '/' );
		}
		if ( ! defined( 'SEO_FORGE_URL' ) ) {
			define( 'SEO_FORGE_URL', plugins_url( '', SEO_FORGE_FILE ) . '/' );
		}
		if ( ! defined( 'SEO_FORGE_ASSETS_URL' ) ) {
			define( 'SEO_FORGE_ASSETS_URL', SEO_FORGE_URL . 'assets/' );
		}
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
		require_once SEO_FORGE_PATH . 'includes/class-chatbot.php';
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
		$this->chatbot = new SEO_Forge_Chatbot();

		// Force chatbot initialization check
		add_action( 'wp_loaded', [ $this, 'ensure_chatbot_initialization' ] );

		// Loaded action
		do_action( 'seo_forge/loaded' );
	}

	/**
	 * Ensure chatbot is properly initialized.
	 */
	public function ensure_chatbot_initialization() {
		// Check if chatbot settings exist
		$settings = get_option( 'seo_forge_chatbot_settings', false );
		
		if ( false === $settings ) {
			// Force initialization
			update_option( 'seo_forge_chatbot_force_init', true );
			
			// Log for debugging
			error_log( 'SEO Forge: Chatbot settings missing, forcing initialization' );
		}
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

		// Localize script with updated default API URL for Express MCP Server
		wp_localize_script( 'seo-forge-admin', 'seoForge', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'seo_forge_nonce' ),
			'apiUrl' => get_option( 'seo_forge_api_url', 'http://localhost:8000' ), // Updated for Express MCP Server
			'strings' => [
				'generating' => __( 'Generating content...', 'seo-forge' ),
				'analyzing' => __( 'Analyzing SEO...', 'seo-forge' ),
				'researching' => __( 'Researching keywords...', 'seo-forge' ),
				'error' => __( 'An error occurred. Please try again.', 'seo-forge' ),
				'success' => __( 'Operation completed successfully.', 'seo-forge' ),
				'connecting' => __( 'Testing connection to MCP server...', 'seo-forge' ),
				'connected' => __( 'Successfully connected to MCP server!', 'seo-forge' ),
				'connection_failed' => __( 'Failed to connect to MCP server. Please check your settings.', 'seo-forge' ),
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
 * Returns the main instance of SEO_Forge_Ultimate to prevent the need to use globals.
 *
 * @return SEO_Forge_Ultimate
 */
function seo_forge_ultimate() {
	return SEO_Forge_Ultimate::get();
}

// Start the plugin
seo_forge_ultimate();