<?php
/**
 * SEO Forge API Handler
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;

/**
 * SEO_Forge_API class.
 */
class SEO_Forge_API {

	/**
	 * API base URL.
	 *
	 * @var string
	 */
	private $api_url;

	/**
	 * API key.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->api_url = get_option( 'seo_forge_api_url', 'https://seoforge-mcp-platform.vercel.app' );
		$this->api_key = get_option( 'seo_forge_api_key', '' );

		add_action( 'wp_ajax_seo_forge_test_connection', [ $this, 'test_connection' ] );
		add_action( 'wp_ajax_seo_forge_generate_content', [ $this, 'generate_content' ] );
		add_action( 'wp_ajax_seo_forge_analyze_seo', [ $this, 'analyze_seo' ] );
		add_action( 'wp_ajax_seo_forge_research_keywords', [ $this, 'research_keywords' ] );
	}

	/**
	 * Test API connection.
	 */
	public function test_connection() {
		check_ajax_referer( 'seo_forge_nonce', 'nonce' );

		$response = $this->make_request( '/api/health' );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [
				'message' => __( 'Connection failed: ', 'seo-forge' ) . $response->get_error_message()
			] );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['status'] ) && $data['status'] === 'healthy' ) {
			wp_send_json_success( [
				'message' => __( 'Connection successful!', 'seo-forge' ),
				'server_info' => $data
			] );
		} else {
			wp_send_json_error( [
				'message' => __( 'Server responded but status is not healthy.', 'seo-forge' )
			] );
		}
	}

	/**
	 * Generate content via API.
	 */
	public function generate_content() {
		check_ajax_referer( 'seo_forge_nonce', 'nonce' );

		$keywords = sanitize_text_field( $_POST['keywords'] ?? '' );
		$industry = sanitize_text_field( $_POST['industry'] ?? '' );
		$content_type = sanitize_text_field( $_POST['content_type'] ?? 'blog_post' );
		$language = sanitize_text_field( $_POST['language'] ?? 'en' );

		if ( empty( $keywords ) ) {
			wp_send_json_error( [
				'message' => __( 'Keywords are required.', 'seo-forge' )
			] );
		}

		$response = $this->make_request( '/api/content/generate', [
			'keywords' => $keywords,
			'industry' => $industry,
			'content_type' => $content_type,
			'language' => $language
		] );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [
				'message' => __( 'Content generation failed: ', 'seo-forge' ) . $response->get_error_message()
			] );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['content'] ) ) {
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( [
				'message' => __( 'Invalid response from content generation API.', 'seo-forge' )
			] );
		}
	}

	/**
	 * Analyze SEO via API.
	 */
	public function analyze_seo() {
		check_ajax_referer( 'seo_forge_nonce', 'nonce' );

		$content = wp_kses_post( $_POST['content'] ?? '' );
		$url = esc_url_raw( $_POST['url'] ?? '' );
		$focus_keyword = sanitize_text_field( $_POST['focus_keyword'] ?? '' );

		if ( empty( $content ) && empty( $url ) ) {
			wp_send_json_error( [
				'message' => __( 'Content or URL is required for analysis.', 'seo-forge' )
			] );
		}

		$response = $this->make_request( '/api/seo/analyze', [
			'content' => $content,
			'url' => $url,
			'focus_keyword' => $focus_keyword
		] );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [
				'message' => __( 'SEO analysis failed: ', 'seo-forge' ) . $response->get_error_message()
			] );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['score'] ) ) {
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( [
				'message' => __( 'Invalid response from SEO analysis API.', 'seo-forge' )
			] );
		}
	}

	/**
	 * Research keywords via API.
	 */
	public function research_keywords() {
		check_ajax_referer( 'seo_forge_nonce', 'nonce' );

		$seed_keywords = sanitize_text_field( $_POST['seed_keywords'] ?? '' );
		$language = sanitize_text_field( $_POST['language'] ?? 'en' );
		$country = sanitize_text_field( $_POST['country'] ?? 'US' );
		$limit = intval( $_POST['limit'] ?? 50 );

		if ( empty( $seed_keywords ) ) {
			wp_send_json_error( [
				'message' => __( 'Seed keywords are required.', 'seo-forge' )
			] );
		}

		$response = $this->make_request( '/api/keywords/research', [
			'seed_keywords' => $seed_keywords,
			'language' => $language,
			'country' => $country,
			'limit' => $limit
		] );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [
				'message' => __( 'Keyword research failed: ', 'seo-forge' ) . $response->get_error_message()
			] );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['keywords'] ) ) {
			// Cache keywords in database
			$this->cache_keywords( $data['keywords'], $language, $country );
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( [
				'message' => __( 'Invalid response from keyword research API.', 'seo-forge' )
			] );
		}
	}

	/**
	 * Make API request.
	 *
	 * @param string $endpoint API endpoint.
	 * @param array  $data     Request data.
	 * @return array|WP_Error
	 */
	private function make_request( $endpoint, $data = [] ) {
		$url = rtrim( $this->api_url, '/' ) . $endpoint;

		$args = [
			'method'  => 'POST',
			'headers' => [
				'Content-Type' => 'application/json',
				'User-Agent'   => 'SEO-Forge-WordPress-Plugin/' . SEO_FORGE_VERSION,
			],
			'body'    => json_encode( $data ),
			'timeout' => 30,
		];

		if ( ! empty( $this->api_key ) ) {
			$args['headers']['Authorization'] = 'Bearer ' . $this->api_key;
		}

		return wp_remote_request( $url, $args );
	}

	/**
	 * Cache keywords in database.
	 *
	 * @param array  $keywords Keywords data.
	 * @param string $language Language code.
	 * @param string $country  Country code.
	 */
	private function cache_keywords( $keywords, $language, $country ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'seo_forge_keywords';

		foreach ( $keywords as $keyword_data ) {
			$wpdb->replace(
				$table_name,
				[
					'keyword'       => $keyword_data['keyword'],
					'search_volume' => $keyword_data['search_volume'] ?? 0,
					'difficulty'    => $keyword_data['difficulty'] ?? 0,
					'cpc'           => $keyword_data['cpc'] ?? 0.00,
					'language'      => $language,
					'country'       => $country,
				],
				[
					'%s', // keyword
					'%d', // search_volume
					'%d', // difficulty
					'%f', // cpc
					'%s', // language
					'%s', // country
				]
			);
		}
	}
}