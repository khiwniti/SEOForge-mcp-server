<?php
/**
 * SEO Forge Frontend
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;

/**
 * SEO_Forge_Frontend class.
 */
class SEO_Forge_Frontend {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_head', [ $this, 'output_meta_tags' ], 1 );
		add_filter( 'document_title_parts', [ $this, 'filter_title' ] );
		add_action( 'wp_head', [ $this, 'output_schema' ], 99 );
	}

	/**
	 * Output meta tags.
	 */
	public function output_meta_tags() {
		if ( is_singular() ) {
			global $post;

			$meta_title = get_post_meta( $post->ID, '_seo_forge_meta_title', true );
			$meta_description = get_post_meta( $post->ID, '_seo_forge_meta_description', true );
			$focus_keyword = get_post_meta( $post->ID, '_seo_forge_focus_keyword', true );

			// Meta description
			if ( ! empty( $meta_description ) ) {
				echo '<meta name="description" content="' . esc_attr( $meta_description ) . '">' . "\n";
			}

			// Focus keyword as meta keyword (for legacy support)
			if ( ! empty( $focus_keyword ) ) {
				echo '<meta name="keywords" content="' . esc_attr( $focus_keyword ) . '">' . "\n";
			}

			// Open Graph tags
			$this->output_og_tags( $post );

			// Twitter Card tags
			$this->output_twitter_tags( $post );
		}
	}

	/**
	 * Filter document title.
	 */
	public function filter_title( $title_parts ) {
		if ( is_singular() ) {
			global $post;

			$meta_title = get_post_meta( $post->ID, '_seo_forge_meta_title', true );

			if ( ! empty( $meta_title ) ) {
				$title_parts['title'] = $meta_title;
			}
		}

		return $title_parts;
	}

	/**
	 * Output Open Graph tags.
	 */
	private function output_og_tags( $post ) {
		$meta_title = get_post_meta( $post->ID, '_seo_forge_meta_title', true );
		$meta_description = get_post_meta( $post->ID, '_seo_forge_meta_description', true );

		$title = ! empty( $meta_title ) ? $meta_title : get_the_title( $post );
		$description = ! empty( $meta_description ) ? $meta_description : wp_trim_words( get_the_excerpt( $post ), 20 );

		echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
		echo '<meta property="og:url" content="' . esc_url( get_permalink( $post ) ) . '">' . "\n";
		echo '<meta property="og:type" content="article">' . "\n";

		// Featured image
		if ( has_post_thumbnail( $post ) ) {
			$image_url = get_the_post_thumbnail_url( $post, 'large' );
			echo '<meta property="og:image" content="' . esc_url( $image_url ) . '">' . "\n";
		}
	}

	/**
	 * Output Twitter Card tags.
	 */
	private function output_twitter_tags( $post ) {
		$meta_title = get_post_meta( $post->ID, '_seo_forge_meta_title', true );
		$meta_description = get_post_meta( $post->ID, '_seo_forge_meta_description', true );

		$title = ! empty( $meta_title ) ? $meta_title : get_the_title( $post );
		$description = ! empty( $meta_description ) ? $meta_description : wp_trim_words( get_the_excerpt( $post ), 20 );

		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
		echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";

		// Featured image
		if ( has_post_thumbnail( $post ) ) {
			$image_url = get_the_post_thumbnail_url( $post, 'large' );
			echo '<meta name="twitter:image" content="' . esc_url( $image_url ) . '">' . "\n";
		}
	}

	/**
	 * Output structured data schema.
	 */
	public function output_schema() {
		if ( is_singular() ) {
			global $post;

			$schema = [
				'@context' => 'https://schema.org',
				'@type' => 'Article',
				'headline' => get_the_title( $post ),
				'description' => wp_trim_words( get_the_excerpt( $post ), 20 ),
				'url' => get_permalink( $post ),
				'datePublished' => get_the_date( 'c', $post ),
				'dateModified' => get_the_modified_date( 'c', $post ),
				'author' => [
					'@type' => 'Person',
					'name' => get_the_author_meta( 'display_name', $post->post_author ),
				],
				'publisher' => [
					'@type' => 'Organization',
					'name' => get_bloginfo( 'name' ),
					'url' => home_url(),
				],
			];

			// Add featured image if available
			if ( has_post_thumbnail( $post ) ) {
				$image_url = get_the_post_thumbnail_url( $post, 'large' );
				$schema['image'] = $image_url;
			}

			echo '<script type="application/ld+json">' . json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
		}
	}
}