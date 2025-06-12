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

		// Dashboard
		add_submenu_page(
			'seo-forge',
			__( 'Dashboard', 'seo-forge' ),
			__( 'Dashboard', 'seo-forge' ),
			'manage_options',
			'seo-forge',
			[ $this, 'dashboard_page' ]
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
	 * Settings page.
	 */
	public function settings_page() {
		include SEO_FORGE_PATH . 'templates/admin/settings.php';
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