<?php
/**
 * SEO Forge Meta Box
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;

/**
 * SEO_Forge_Meta_Box class.
 */
class SEO_Forge_Meta_Box {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_meta_box' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Add meta boxes.
	 */
	public function add_meta_boxes() {
		$post_types = get_post_types( [ 'public' => true ] );

		foreach ( $post_types as $post_type ) {
			// SEO Forge main meta box
			add_meta_box(
				'seo-forge-meta-box',
				__( 'SEO Forge', 'seo-forge' ),
				[ $this, 'render_meta_box' ],
				$post_type,
				'normal',
				'high'
			);

			// Content Generator meta box
			add_meta_box(
				'seo-forge-content-generator',
				__( 'SEO Forge - Content Generator', 'seo-forge' ),
				[ $this, 'render_content_generator_meta_box' ],
				$post_type,
				'side',
				'default'
			);

			// SEO Analysis meta box
			add_meta_box(
				'seo-forge-seo-analysis',
				__( 'SEO Forge - SEO Analysis', 'seo-forge' ),
				[ $this, 'render_seo_analysis_meta_box' ],
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Enqueue scripts for meta boxes.
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'seo-forge-meta-box',
			SEO_FORGE_ASSETS_URL . 'js/meta-box.js',
			[ 'jquery', 'seo-forge-admin' ],
			SEO_FORGE_VERSION,
			true
		);

		wp_enqueue_style(
			'seo-forge-meta-box',
			SEO_FORGE_ASSETS_URL . 'css/meta-box.css',
			[ 'seo-forge-admin' ],
			SEO_FORGE_VERSION
		);
	}

	/**
	 * Render main meta box.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'seo_forge_meta_box', 'seo_forge_meta_box_nonce' );

		$focus_keyword = get_post_meta( $post->ID, '_seo_forge_focus_keyword', true );
		$meta_title = get_post_meta( $post->ID, '_seo_forge_meta_title', true );
		$meta_description = get_post_meta( $post->ID, '_seo_forge_meta_description', true );
		$seo_score = get_post_meta( $post->ID, '_seo_forge_seo_score', true );

		include SEO_FORGE_PATH . 'templates/meta-box/main.php';
	}

	/**
	 * Render content generator meta box.
	 */
	public function render_content_generator_meta_box( $post ) {
		include SEO_FORGE_PATH . 'templates/meta-box/content-generator.php';
	}

	/**
	 * Render SEO analysis meta box.
	 */
	public function render_seo_analysis_meta_box( $post ) {
		$analysis_data = get_post_meta( $post->ID, '_seo_forge_analysis_data', true );
		$last_analysis = get_post_meta( $post->ID, '_seo_forge_last_analysis', true );

		include SEO_FORGE_PATH . 'templates/meta-box/seo-analysis.php';
	}

	/**
	 * Save meta box data.
	 */
	public function save_meta_box( $post_id ) {
		// Check if nonce is valid
		if ( ! isset( $_POST['seo_forge_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['seo_forge_meta_box_nonce'], 'seo_forge_meta_box' ) ) {
			return;
		}

		// Check if user has permission to edit post
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Don't save on autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Save meta fields
		$fields = [
			'_seo_forge_focus_keyword',
			'_seo_forge_meta_title',
			'_seo_forge_meta_description',
		];

		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
			}
		}
	}
}