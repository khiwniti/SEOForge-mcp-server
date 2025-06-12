<?php
/**
 * SEO Forge Content Generator
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;

/**
 * SEO_Forge_Content_Generator class.
 */
class SEO_Forge_Content_Generator {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// AJAX actions are handled in the API class
	}

	/**
	 * Get content types.
	 */
	public static function get_content_types() {
		return [
			'blog_post' => __( 'Blog Post', 'seo-forge' ),
			'product_description' => __( 'Product Description', 'seo-forge' ),
			'landing_page' => __( 'Landing Page', 'seo-forge' ),
			'how_to_guide' => __( 'How-to Guide', 'seo-forge' ),
			'news_article' => __( 'News Article', 'seo-forge' ),
			'social_media_post' => __( 'Social Media Post', 'seo-forge' ),
		];
	}

	/**
	 * Get industries.
	 */
	public static function get_industries() {
		return [
			'technology' => __( 'Technology', 'seo-forge' ),
			'healthcare' => __( 'Healthcare', 'seo-forge' ),
			'finance' => __( 'Finance', 'seo-forge' ),
			'education' => __( 'Education', 'seo-forge' ),
			'retail' => __( 'Retail', 'seo-forge' ),
			'real_estate' => __( 'Real Estate', 'seo-forge' ),
			'automotive' => __( 'Automotive', 'seo-forge' ),
			'food_beverage' => __( 'Food & Beverage', 'seo-forge' ),
			'travel' => __( 'Travel', 'seo-forge' ),
			'fitness' => __( 'Fitness', 'seo-forge' ),
			'fashion' => __( 'Fashion', 'seo-forge' ),
			'entertainment' => __( 'Entertainment', 'seo-forge' ),
		];
	}

	/**
	 * Get supported languages.
	 */
	public static function get_languages() {
		return [
			'en' => __( 'English', 'seo-forge' ),
			'th' => __( 'Thai', 'seo-forge' ),
			'es' => __( 'Spanish', 'seo-forge' ),
			'fr' => __( 'French', 'seo-forge' ),
			'de' => __( 'German', 'seo-forge' ),
			'it' => __( 'Italian', 'seo-forge' ),
			'pt' => __( 'Portuguese', 'seo-forge' ),
			'ru' => __( 'Russian', 'seo-forge' ),
			'ja' => __( 'Japanese', 'seo-forge' ),
			'ko' => __( 'Korean', 'seo-forge' ),
			'zh' => __( 'Chinese', 'seo-forge' ),
		];
	}
}