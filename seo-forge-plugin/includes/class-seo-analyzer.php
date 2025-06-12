<?php
/**
 * SEO Forge SEO Analyzer
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;

/**
 * SEO_Forge_SEO_Analyzer class.
 */
class SEO_Forge_SEO_Analyzer {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// AJAX actions are handled in the API class
	}

	/**
	 * Get SEO checklist items.
	 */
	public static function get_seo_checklist() {
		return [
			'title_length' => [
				'label' => __( 'SEO Title (recommended: 50-60 characters)', 'seo-forge' ),
				'description' => __( 'Title should be between 50-60 characters for optimal display in search results.', 'seo-forge' ),
			],
			'meta_description' => [
				'label' => __( 'Meta Description (recommended: 150-160 characters)', 'seo-forge' ),
				'description' => __( 'Meta description should be between 150-160 characters to avoid truncation.', 'seo-forge' ),
			],
			'focus_keyword' => [
				'label' => __( 'Focus Keyword Set', 'seo-forge' ),
				'description' => __( 'A focus keyword helps search engines understand your content topic.', 'seo-forge' ),
			],
			'content_length' => [
				'label' => __( 'Content Length (recommended: 300+ words)', 'seo-forge' ),
				'description' => __( 'Longer content typically performs better in search results.', 'seo-forge' ),
			],
			'heading_structure' => [
				'label' => __( 'Proper Heading Structure (H1, H2, H3)', 'seo-forge' ),
				'description' => __( 'Use headings to structure your content hierarchically.', 'seo-forge' ),
			],
			'images_alt_text' => [
				'label' => __( 'Images with Alt Text', 'seo-forge' ),
				'description' => __( 'Alt text helps search engines understand image content.', 'seo-forge' ),
			],
			'internal_links' => [
				'label' => __( 'Internal Links', 'seo-forge' ),
				'description' => __( 'Internal links help distribute page authority and improve navigation.', 'seo-forge' ),
			],
			'external_links' => [
				'label' => __( 'External Links', 'seo-forge' ),
				'description' => __( 'Quality external links can add credibility to your content.', 'seo-forge' ),
			],
		];
	}

	/**
	 * Analyze content locally (basic analysis).
	 */
	public static function analyze_content_basic( $content, $focus_keyword = '' ) {
		$analysis = [
			'score' => 0,
			'issues' => [],
			'recommendations' => [],
			'checklist' => [],
		];

		$word_count = str_word_count( strip_tags( $content ) );
		$checklist = self::get_seo_checklist();

		// Content length check
		if ( $word_count >= 300 ) {
			$analysis['checklist']['content_length'] = 'pass';
			$analysis['score'] += 15;
		} else {
			$analysis['checklist']['content_length'] = 'fail';
			$analysis['issues'][] = sprintf( __( 'Content is too short (%d words). Recommended: 300+ words.', 'seo-forge' ), $word_count );
			$analysis['recommendations'][] = __( 'Expand your content to at least 300 words for better SEO performance.', 'seo-forge' );
		}

		// Heading structure check
		$h1_count = substr_count( $content, '<h1' );
		$h2_count = substr_count( $content, '<h2' );
		$h3_count = substr_count( $content, '<h3' );

		if ( $h1_count === 1 && ( $h2_count > 0 || $h3_count > 0 ) ) {
			$analysis['checklist']['heading_structure'] = 'pass';
			$analysis['score'] += 15;
		} else {
			$analysis['checklist']['heading_structure'] = 'fail';
			if ( $h1_count === 0 ) {
				$analysis['issues'][] = __( 'No H1 heading found.', 'seo-forge' );
			} elseif ( $h1_count > 1 ) {
				$analysis['issues'][] = __( 'Multiple H1 headings found. Use only one H1 per page.', 'seo-forge' );
			}
			$analysis['recommendations'][] = __( 'Use proper heading structure: one H1, followed by H2s and H3s.', 'seo-forge' );
		}

		// Images with alt text check
		$img_count = substr_count( $content, '<img' );
		$alt_count = substr_count( $content, 'alt=' );

		if ( $img_count === 0 || $alt_count === $img_count ) {
			$analysis['checklist']['images_alt_text'] = 'pass';
			$analysis['score'] += 10;
		} else {
			$analysis['checklist']['images_alt_text'] = 'fail';
			$analysis['issues'][] = sprintf( __( '%d images missing alt text.', 'seo-forge' ), $img_count - $alt_count );
			$analysis['recommendations'][] = __( 'Add descriptive alt text to all images.', 'seo-forge' );
		}

		// Focus keyword check
		if ( ! empty( $focus_keyword ) ) {
			$analysis['checklist']['focus_keyword'] = 'pass';
			$analysis['score'] += 10;

			// Check keyword density
			$keyword_count = substr_count( strtolower( strip_tags( $content ) ), strtolower( $focus_keyword ) );
			$keyword_density = ( $keyword_count / $word_count ) * 100;

			if ( $keyword_density < 0.5 ) {
				$analysis['issues'][] = sprintf( __( 'Focus keyword density is low (%.1f%%). Consider using it more naturally.', 'seo-forge' ), $keyword_density );
			} elseif ( $keyword_density > 3 ) {
				$analysis['issues'][] = sprintf( __( 'Focus keyword density is high (%.1f%%). Avoid keyword stuffing.', 'seo-forge' ), $keyword_density );
			}
		} else {
			$analysis['checklist']['focus_keyword'] = 'fail';
			$analysis['recommendations'][] = __( 'Set a focus keyword to optimize your content.', 'seo-forge' );
		}

		// Internal links check
		$internal_links = substr_count( $content, home_url() );
		if ( $internal_links >= 2 ) {
			$analysis['checklist']['internal_links'] = 'pass';
			$analysis['score'] += 10;
		} else {
			$analysis['checklist']['internal_links'] = 'fail';
			$analysis['recommendations'][] = __( 'Add 2-3 relevant internal links to improve site structure.', 'seo-forge' );
		}

		// External links check
		$external_links = substr_count( $content, 'http' ) - substr_count( $content, home_url() );
		if ( $external_links >= 1 ) {
			$analysis['checklist']['external_links'] = 'pass';
			$analysis['score'] += 5;
		} else {
			$analysis['checklist']['external_links'] = 'warning';
			$analysis['recommendations'][] = __( 'Consider adding 1-2 quality external links for credibility.', 'seo-forge' );
		}

		return $analysis;
	}
}