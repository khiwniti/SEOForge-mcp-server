<?php
/**
 * SEO Forge Keyword Research
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;

/**
 * SEO_Forge_Keyword_Research class.
 */
class SEO_Forge_Keyword_Research {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// AJAX actions are handled in the API class
	}

	/**
	 * Get supported countries.
	 */
	public static function get_countries() {
		return [
			'US' => __( 'United States', 'seo-forge' ),
			'TH' => __( 'Thailand', 'seo-forge' ),
			'GB' => __( 'United Kingdom', 'seo-forge' ),
			'CA' => __( 'Canada', 'seo-forge' ),
			'AU' => __( 'Australia', 'seo-forge' ),
			'DE' => __( 'Germany', 'seo-forge' ),
			'FR' => __( 'France', 'seo-forge' ),
			'ES' => __( 'Spain', 'seo-forge' ),
			'IT' => __( 'Italy', 'seo-forge' ),
			'BR' => __( 'Brazil', 'seo-forge' ),
			'MX' => __( 'Mexico', 'seo-forge' ),
			'JP' => __( 'Japan', 'seo-forge' ),
			'KR' => __( 'South Korea', 'seo-forge' ),
			'CN' => __( 'China', 'seo-forge' ),
			'IN' => __( 'India', 'seo-forge' ),
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

	/**
	 * Get cached keywords from database.
	 */
	public static function get_cached_keywords( $seed_keywords, $language = 'en', $country = 'US', $limit = 50 ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'seo_forge_keywords';
		$keywords_array = array_map( 'trim', explode( ',', $seed_keywords ) );

		$placeholders = implode( ',', array_fill( 0, count( $keywords_array ), '%s' ) );
		$query_params = array_merge( $keywords_array, [ $language, $country, $limit ] );

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_name 
				WHERE keyword IN ($placeholders) 
				AND language = %s 
				AND country = %s 
				ORDER BY search_volume DESC 
				LIMIT %d",
				...$query_params
			),
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Get keyword difficulty color class.
	 */
	public static function get_difficulty_class( $difficulty ) {
		if ( $difficulty <= 30 ) {
			return 'easy';
		} elseif ( $difficulty <= 60 ) {
			return 'medium';
		} else {
			return 'hard';
		}
	}

	/**
	 * Format search volume.
	 */
	public static function format_search_volume( $volume ) {
		if ( $volume >= 1000000 ) {
			return round( $volume / 1000000, 1 ) . 'M';
		} elseif ( $volume >= 1000 ) {
			return round( $volume / 1000, 1 ) . 'K';
		}
		return number_format( $volume );
	}
}