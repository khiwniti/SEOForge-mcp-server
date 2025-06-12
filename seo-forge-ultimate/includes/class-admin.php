<?php
/**
 * SEO Forge Admin
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;

/**
 * SEO_Forge_Admin class.
 */
class SEO_Forge_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
	}

	/**
	 * Add admin menu.
	 */
	public function admin_menu() {
		// Main menu
		add_menu_page(
			__( 'SEO Forge', 'seo-forge' ),
			__( 'SEO Forge', 'seo-forge' ),
			'manage_options',
			'seo-forge',
			[ $this, 'dashboard_page' ],
			'data:image/svg+xml;base64,' . base64_encode( $this->get_menu_icon() ),
			30
		);

		// Content Generator
		add_submenu_page(
			'seo-forge',
			__( 'Content Generator', 'seo-forge' ),
			__( 'Content Generator', 'seo-forge' ),
			'edit_posts',
			'seo-forge-content',
			[ $this, 'content_generator_page' ]
		);

		// SEO Analyzer
		add_submenu_page(
			'seo-forge',
			__( 'SEO Analyzer', 'seo-forge' ),
			__( 'SEO Analyzer', 'seo-forge' ),
			'edit_posts',
			'seo-forge-analyzer',
			[ $this, 'seo_analyzer_page' ]
		);

		// Keyword Research
		add_submenu_page(
			'seo-forge',
			__( 'Keyword Research', 'seo-forge' ),
			__( 'Keyword Research', 'seo-forge' ),
			'edit_posts',
			'seo-forge-keywords',
			[ $this, 'keyword_research_page' ]
		);

		// Image Generator
		add_submenu_page(
			'seo-forge',
			__( 'Image Generator', 'seo-forge' ),
			__( 'Image Generator', 'seo-forge' ),
			'edit_posts',
			'seo-forge-images',
			[ $this, 'image_generator_page' ]
		);

		// Site Analysis
		add_submenu_page(
			'seo-forge',
			__( 'Site Analysis', 'seo-forge' ),
			__( 'Site Analysis', 'seo-forge' ),
			'edit_posts',
			'seo-forge-site-analysis',
			[ $this, 'site_analysis_page' ]
		);

		// Rank Tracker
		add_submenu_page(
			'seo-forge',
			__( 'Rank Tracker', 'seo-forge' ),
			__( 'Rank Tracker', 'seo-forge' ),
			'edit_posts',
			'seo-forge-rank-tracker',
			[ $this, 'rank_tracker_page' ]
		);

		// Schema Generator
		add_submenu_page(
			'seo-forge',
			__( 'Schema Generator', 'seo-forge' ),
			__( 'Schema Generator', 'seo-forge' ),
			'edit_posts',
			'seo-forge-schema',
			[ $this, 'schema_generator_page' ]
		);

		// Local SEO
		add_submenu_page(
			'seo-forge',
			__( 'Local SEO', 'seo-forge' ),
			__( 'Local SEO', 'seo-forge' ),
			'manage_options',
			'seo-forge-local-seo',
			[ $this, 'local_seo_page' ]
		);

		// Analytics
		add_submenu_page(
			'seo-forge',
			__( 'Analytics', 'seo-forge' ),
			__( 'Analytics', 'seo-forge' ),
			'manage_options',
			'seo-forge-analytics',
			[ $this, 'analytics_page' ]
		);

		// AI Chatbot
		add_submenu_page(
			'seo-forge',
			__( 'AI Chatbot', 'seo-forge' ),
			__( 'AI Chatbot', 'seo-forge' ),
			'manage_options',
			'seo-forge-chatbot',
			[ $this, 'chatbot_page' ]
		);

		// Settings
		add_submenu_page(
			'seo-forge',
			__( 'Settings', 'seo-forge' ),
			__( 'Settings', 'seo-forge' ),
			'manage_options',
			'seo-forge-settings',
			[ $this, 'settings_page' ]
		);
	}

	/**
	 * Admin init.
	 */
	public function admin_init() {
		// Register settings
		register_setting( 'seo_forge_settings', 'seo_forge_api_url' );
		register_setting( 'seo_forge_settings', 'seo_forge_api_key' );
		register_setting( 'seo_forge_settings', 'seo_forge_enable_content_generator' );
		register_setting( 'seo_forge_settings', 'seo_forge_enable_seo_analyzer' );
		register_setting( 'seo_forge_settings', 'seo_forge_enable_keyword_research' );
		register_setting( 'seo_forge_settings', 'seo_forge_auto_generate_meta' );
		register_setting( 'seo_forge_settings', 'seo_forge_default_language' );
		register_setting( 'seo_forge_settings', 'seo_forge_default_country' );
	}

	/**
	 * Admin notices.
	 */
	public function admin_notices() {
		if ( get_option( 'seo_forge_activated' ) ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php _e( 'SEO Forge has been activated successfully! Configure your settings to get started.', 'seo-forge' ); ?></p>
			</div>
			<?php
			delete_option( 'seo_forge_activated' );
		}
	}

	/**
	 * Dashboard page.
	 */
	public function dashboard_page() {
		include SEO_FORGE_PATH . 'templates/admin/dashboard.php';
	}

	/**
	 * Content generator page.
	 */
	public function content_generator_page() {
		include SEO_FORGE_PATH . 'templates/admin/content-generator.php';
	}

	/**
	 * SEO analyzer page.
	 */
	public function seo_analyzer_page() {
		include SEO_FORGE_PATH . 'templates/admin/seo-analyzer.php';
	}

	/**
	 * Keyword research page.
	 */
	public function keyword_research_page() {
		include SEO_FORGE_PATH . 'templates/admin/keyword-research.php';
	}

	/**
	 * Image generator page.
	 */
	public function image_generator_page() {
		include SEO_FORGE_PATH . 'templates/admin/image-generator.php';
	}

	/**
	 * Site analysis page.
	 */
	public function site_analysis_page() {
		include SEO_FORGE_PATH . 'templates/admin/site-analysis.php';
	}

	/**
	 * Rank tracker page.
	 */
	public function rank_tracker_page() {
		include SEO_FORGE_PATH . 'templates/admin/rank-tracker.php';
	}

	/**
	 * Schema generator page.
	 */
	public function schema_generator_page() {
		include SEO_FORGE_PATH . 'templates/admin/schema-generator.php';
	}

	/**
	 * Local SEO page.
	 */
	public function local_seo_page() {
		include SEO_FORGE_PATH . 'templates/admin/local-seo.php';
	}

	/**
	 * Analytics page.
	 */
	public function analytics_page() {
		include SEO_FORGE_PATH . 'templates/admin/analytics.php';
	}

	/**
	 * Chatbot page.
	 */
	public function chatbot_page() {
		include SEO_FORGE_PATH . 'templates/admin/chatbot.php';
	}

	/**
	 * Settings page.
	 */
	public function settings_page() {
		include SEO_FORGE_PATH . 'templates/admin/settings.php';
	}

	/**
	 * Enqueue admin scripts and styles.
	 */
	public function admin_enqueue_scripts( $hook ) {
		// Only load on SEO Forge admin pages
		if ( strpos( $hook, 'seo-forge' ) === false ) {
			return;
		}

		// Enqueue admin CSS
		wp_enqueue_style(
			'seo-forge-admin',
			SEO_FORGE_URL . 'assets/css/admin.css',
			[],
			SEO_FORGE_VERSION
		);

		// Enqueue admin JS
		wp_enqueue_script(
			'seo-forge-admin',
			SEO_FORGE_URL . 'assets/js/admin.js',
			[ 'jquery' ],
			SEO_FORGE_VERSION,
			true
		);

		// Localize script for AJAX
		wp_localize_script(
			'seo-forge-admin',
			'seoForge',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'seo_forge_nonce' ),
				'strings' => [
					'loading'           => __( 'Loading...', 'seo-forge' ),
					'error'             => __( 'An error occurred. Please try again.', 'seo-forge' ),
					'success'           => __( 'Operation completed successfully!', 'seo-forge' ),
					'confirm_delete'    => __( 'Are you sure you want to delete this item?', 'seo-forge' ),
					'generating'        => __( 'Generating...', 'seo-forge' ),
					'analyzing'         => __( 'Analyzing...', 'seo-forge' ),
					'researching'       => __( 'Researching...', 'seo-forge' ),
					'processing'        => __( 'Processing...', 'seo-forge' ),
				]
			]
		);
	}

	/**
	 * Get menu icon SVG.
	 */
	private function get_menu_icon() {
		return '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M10 2L12.5 7.5L18 8L14 12L15 18L10 15.5L5 18L6 12L2 8L7.5 7.5L10 2Z" fill="currentColor"/>
		</svg>';
	}
}