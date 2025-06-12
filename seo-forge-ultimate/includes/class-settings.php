<?php
/**
 * SEO Forge Settings
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;

/**
 * SEO_Forge_Settings class.
 */
class SEO_Forge_Settings {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Settings are handled in the admin class
	}

	/**
	 * Get default settings.
	 */
	public static function get_defaults() {
		return [
			'seo_forge_api_url' => 'https://seoforge-mcp-platform.vercel.app',
			'seo_forge_api_key' => '',
			'seo_forge_enable_content_generator' => true,
			'seo_forge_enable_seo_analyzer' => true,
			'seo_forge_enable_keyword_research' => true,
			'seo_forge_auto_generate_meta' => false,
			'seo_forge_default_language' => 'en',
			'seo_forge_default_country' => 'US',
		];
	}
}