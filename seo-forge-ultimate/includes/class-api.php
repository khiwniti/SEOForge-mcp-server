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
		$this->api_url = get_option( 'seo_forge_api_url', 'https://work-1-mhngrhjpizklxmsi.prod-runtime.all-hands.dev' );
		$this->api_key = get_option( 'seo_forge_api_key', '' );

		add_action( 'wp_ajax_seo_forge_test_connection', [ $this, 'test_connection' ] );
		add_action( 'wp_ajax_seo_forge_generate_content', [ $this, 'generate_content' ] );
		add_action( 'wp_ajax_seo_forge_analyze_seo', [ $this, 'analyze_seo' ] );
		add_action( 'wp_ajax_seo_forge_research_keywords', [ $this, 'research_keywords' ] );
		add_action( 'wp_ajax_seo_forge_generate_flux_image', [ $this, 'generate_flux_image' ] );
		add_action( 'wp_ajax_seo_forge_generate_flux_batch', [ $this, 'generate_flux_batch' ] );
		add_action( 'wp_ajax_seo_forge_get_server_status', [ $this, 'get_server_status' ] );
		
		// Add nopriv actions for non-logged-in users (if needed)
		add_action( 'wp_ajax_nopriv_seo_forge_test_connection', [ $this, 'test_connection' ] );
		add_action( 'wp_ajax_nopriv_seo_forge_generate_content', [ $this, 'generate_content' ] );
		add_action( 'wp_ajax_nopriv_seo_forge_analyze_seo', [ $this, 'analyze_seo' ] );
		add_action( 'wp_ajax_nopriv_seo_forge_research_keywords', [ $this, 'research_keywords' ] );
		add_action( 'wp_ajax_nopriv_seo_forge_generate_flux_image', [ $this, 'generate_flux_image' ] );
		add_action( 'wp_ajax_nopriv_seo_forge_generate_flux_batch', [ $this, 'generate_flux_batch' ] );
		add_action( 'wp_ajax_nopriv_seo_forge_get_server_status', [ $this, 'get_server_status' ] );
	}

	/**
	 * Test API connection.
	 */
	public function test_connection() {
		// Enhanced nonce verification with better error handling
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'seo_forge_nonce' ) ) {
			wp_send_json_error( [
				'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-forge' )
			] );
		}

		// Check if user has proper capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [
				'message' => __( 'You do not have permission to perform this action.', 'seo-forge' )
			] );
		}

		$response = $this->make_request( '/api/status' );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [
				'message' => __( 'Connection failed: ', 'seo-forge' ) . $response->get_error_message()
			] );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		
		// Handle different response codes
		if ( $response_code === 403 ) {
			wp_send_json_error( [
				'message' => __( 'Access denied. Please check your API key and server configuration.', 'seo-forge' )
			] );
		} elseif ( $response_code === 404 ) {
			wp_send_json_error( [
				'message' => __( 'API endpoint not found. Please check your server URL.', 'seo-forge' )
			] );
		} elseif ( $response_code !== 200 ) {
			wp_send_json_error( [
				'message' => sprintf( __( 'Server returned error code %d. Please try again later.', 'seo-forge' ), $response_code )
			] );
		}

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

		$response = $this->make_request( '/api/generate-content', [
			'keywords' => explode(',', $keywords),
			'industry' => $industry,
			'content_type' => $content_type,
			'language' => $language,
			'include_images' => true,
			'image_count' => 2
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

		$response = $this->make_request( '/api/analyze-seo', [
			'content' => $content,
			'url' => $url,
			'keywords' => explode(',', $focus_keyword),
			'language' => 'en'
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

		$response = $this->make_request( '/api/research-keywords', [
			'seed_keywords' => explode(',', $seed_keywords),
			'language' => $language,
			'location' => $country,
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
	 * Generate Flux image via API.
	 */
	public function generate_flux_image() {
		check_ajax_referer( 'seo_forge_nonce', 'nonce' );

		$prompt = sanitize_text_field( $_POST['prompt'] ?? '' );
		$model = sanitize_text_field( $_POST['model'] ?? 'flux-schnell' );
		$style = sanitize_text_field( $_POST['style'] ?? 'professional' );
		$width = intval( $_POST['width'] ?? 1024 );
		$height = intval( $_POST['height'] ?? 1024 );

		if ( empty( $prompt ) ) {
			wp_send_json_error( [
				'message' => __( 'Prompt is required for image generation.', 'seo-forge' )
			] );
		}

		$response = $this->make_request( '/api/generate-flux-image', [
			'prompt' => $prompt,
			'model' => $model,
			'style' => $style,
			'width' => $width,
			'height' => $height,
			'enhance_prompt' => true
		] );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [
				'message' => __( 'Image generation failed: ', 'seo-forge' ) . $response->get_error_message()
			] );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['image_url'] ) ) {
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( [
				'message' => __( 'Invalid response from image generation API.', 'seo-forge' )
			] );
		}
	}

	/**
	 * Generate Flux batch images via API.
	 */
	public function generate_flux_batch() {
		check_ajax_referer( 'seo_forge_nonce', 'nonce' );

		$prompts = array_map( 'sanitize_text_field', $_POST['prompts'] ?? [] );
		$model = sanitize_text_field( $_POST['model'] ?? 'flux-schnell' );
		$style = sanitize_text_field( $_POST['style'] ?? 'professional' );

		if ( empty( $prompts ) ) {
			wp_send_json_error( [
				'message' => __( 'Prompts are required for batch image generation.', 'seo-forge' )
			] );
		}

		$response = $this->make_request( '/api/generate-flux-batch', [
			'prompts' => $prompts,
			'model' => $model,
			'style' => $style
		] );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [
				'message' => __( 'Batch image generation failed: ', 'seo-forge' ) . $response->get_error_message()
			] );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['images'] ) ) {
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( [
				'message' => __( 'Invalid response from batch image generation API.', 'seo-forge' )
			] );
		}
	}

	/**
	 * Get server status via API.
	 */
	public function get_server_status() {
		check_ajax_referer( 'seo_forge_nonce', 'nonce' );

		$response = $this->make_request( '/api/status' );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [
				'message' => __( 'Failed to get server status: ', 'seo-forge' ) . $response->get_error_message()
			] );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		wp_send_json_success( $data );
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
				'Accept'       => 'application/json',
				'X-Requested-With' => 'XMLHttpRequest',
			],
			'body'    => json_encode( $data ),
			'timeout' => 60,
			'sslverify' => false, // For development environments
			'redirection' => 5,
		];

		if ( ! empty( $this->api_key ) ) {
			$args['headers']['Authorization'] = 'Bearer ' . $this->api_key;
		}

		// Add debug logging
		error_log( 'SEO Forge API Request: ' . $url );
		error_log( 'SEO Forge API Data: ' . json_encode( $data ) );

		$response = wp_remote_request( $url, $args );

		// Log response for debugging
		if ( is_wp_error( $response ) ) {
			error_log( 'SEO Forge API Error: ' . $response->get_error_message() );
		} else {
			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );
			
			error_log( 'SEO Forge API Response Code: ' . $response_code );
			
			// Check for malformed response
			if ( empty( $response_body ) || strpos( $response_body, 'Missing header/body separator' ) !== false ) {
				error_log( 'SEO Forge API Error: Missing header/body separator' );
				return new WP_Error( 'malformed_response', 'Server returned malformed response' );
			}
			
			// Validate JSON response
			$decoded = json_decode( $response_body, true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				error_log( 'SEO Forge API Error: Invalid JSON response - ' . json_last_error_msg() );
				error_log( 'SEO Forge API Raw Response: ' . substr( $response_body, 0, 500 ) );
			}
		}

		return $response;
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