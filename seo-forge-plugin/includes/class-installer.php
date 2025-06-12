<?php
/**
 * SEO Forge Installer
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;

/**
 * SEO_Forge_Installer class.
 */
class SEO_Forge_Installer {

	/**
	 * Plugin activation hook.
	 */
	public static function activate() {
		// Create database tables if needed
		self::create_tables();
		
		// Set default options
		self::set_default_options();
		
		// Flush rewrite rules
		flush_rewrite_rules();
		
		// Set activation flag
		update_option( 'seo_forge_activated', true );
	}

	/**
	 * Plugin deactivation hook.
	 */
	public static function deactivate() {
		// Flush rewrite rules
		flush_rewrite_rules();
		
		// Remove activation flag
		delete_option( 'seo_forge_activated' );
	}

	/**
	 * Create database tables.
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// SEO Analysis table
		$table_name = $wpdb->prefix . 'seo_forge_analysis';
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			post_id bigint(20) NOT NULL,
			analysis_data longtext NOT NULL,
			score int(3) DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY post_id (post_id)
		) $charset_collate;";

		// Keyword Research table
		$table_name_keywords = $wpdb->prefix . 'seo_forge_keywords';
		$sql_keywords = "CREATE TABLE $table_name_keywords (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			keyword varchar(255) NOT NULL,
			search_volume int(11) DEFAULT 0,
			difficulty int(3) DEFAULT 0,
			cpc decimal(10,2) DEFAULT 0.00,
			language varchar(10) DEFAULT 'en',
			country varchar(10) DEFAULT 'US',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY keyword_lang_country (keyword, language, country)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		dbDelta( $sql_keywords );
	}

	/**
	 * Set default plugin options.
	 */
	private static function set_default_options() {
		$defaults = [
			'seo_forge_api_url' => 'https://seoforge-mcp-platform.vercel.app',
			'seo_forge_api_key' => '',
			'seo_forge_enable_content_generator' => true,
			'seo_forge_enable_seo_analyzer' => true,
			'seo_forge_enable_keyword_research' => true,
			'seo_forge_auto_generate_meta' => false,
			'seo_forge_default_language' => 'en',
			'seo_forge_default_country' => 'US',
		];

		foreach ( $defaults as $option => $value ) {
			if ( false === get_option( $option ) ) {
				add_option( $option, $value );
			}
		}
	}
}