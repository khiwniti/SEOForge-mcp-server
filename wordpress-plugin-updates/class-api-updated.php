<?php
/**
 * SEO Forge API Handler - Updated for Express MCP Server
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;

/**
 * SEO_Forge_API class - Updated to work with Express MCP Server.
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
		// Updated default URL to point to our Express MCP server
		$this->api_url = get_option( 'seo_forge_api_url', 'http://localhost:8000' );
		$this->api_key = get_option( 'seo_forge_api_key', 'dev-api-key-1' );

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

		// Use the new health endpoint
		$response = $this->make_request( '/health', [], 'GET' );

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

		// Use the new blog generator endpoint with proper data structure
		$response = $this->make_request( '/api/blog-generator/generate', [
			'topic' => $industry . ' ' . $keywords,
			'keywords' => explode(',', $keywords),
			'language' => $language,
			'tone' => 'professional',
			'length' => 'medium'
		] );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [
				'message' => __( 'Content generation failed: ', 'seo-forge' ) . $response->get_error_message()
			] );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Handle the new MCP response format
		if ( isset( $data['success'] ) && $data['success'] && isset( $data['result'] ) ) {
			// Transform the response to match the expected format
			$transformed_data = [
				'content' => $data['result']['content'] ?? '',
				'title' => $data['result']['title'] ?? '',
				'meta_description' => $data['result']['meta_description'] ?? '',
				'keywords' => $data['result']['keywords'] ?? [],
				'images' => $data['result']['images'] ?? []
			];
			wp_send_json_success( $transformed_data );
		} else {
			$error_message = $data['error'] ?? __( 'Invalid response from content generation API.', 'seo-forge' );
			wp_send_json_error( [
				'message' => $error_message
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

		// Use the new SEO analyzer endpoint
		$request_data = [];
		if ( ! empty( $url ) ) {
			$request_data['url'] = $url;
		}
		if ( ! empty( $content ) ) {
			$request_data['content'] = $content;
		}
		if ( ! empty( $focus_keyword ) ) {
			$request_data['keywords'] = explode(',', $focus_keyword);
		}

		$response = $this->make_request( '/api/seo-analyzer/analyze', $request_data );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [
				'message' => __( 'SEO analysis failed: ', 'seo-forge' ) . $response->get_error_message()
			] );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Handle the new MCP response format
		if ( isset( $data['success'] ) && $data['success'] && isset( $data['result'] ) ) {
			// Transform the response to match the expected format
			$result = $data['result'];
			$transformed_data = [
				'score' => $result['overall_score'] ?? 0,
				'scores' => $result['scores'] ?? [],
				'recommendations' => $result['recommendations'] ?? [],
				'keyword_analysis' => $result['keyword_analysis'] ?? [],
				'competitor_analysis' => $result['competitor_analysis'] ?? []
			];
			wp_send_json_success( $transformed_data );
		} else {
			$error_message = $data['error'] ?? __( 'Invalid response from SEO analysis API.', 'seo-forge' );
			wp_send_json_error( [
				'message' => $error_message
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

		// Use the new keyword research endpoint
		$response = $this->make_request( '/api/keyword-research/analyze', [
			'seed_keywords' => explode(',', $seed_keywords),
			'language' => $language,
			'market' => strtolower( $country ),
			'competition_level' => 'medium'
		] );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [
				'message' => __( 'Keyword research failed: ', 'seo-forge' ) . $response->get_error_message()
			] );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Handle the new MCP response format
		if ( isset( $data['success'] ) && $data['success'] && isset( $data['result'] ) ) {
			$result = $data['result'];
			$keywords = $result['keywords'] ?? [];
			
			// Cache keywords in database
			$this->cache_keywords( $keywords, $language, $country );
			
			wp_send_json_success( [
				'keywords' => $keywords,
				'total_count' => count( $keywords ),
				'analysis' => $result['analysis'] ?? []
			] );
		} else {
			$error_message = $data['error'] ?? __( 'Invalid response from keyword research API.', 'seo-forge' );
			wp_send_json_error( [
				'message' => $error_message
			] );
		}
	}

	/**
	 * Generate Flux image via API.
	 */
	public function generate_flux_image() {
		check_ajax_referer( 'seo_forge_nonce', 'nonce' );

		$prompt = sanitize_text_field( $_POST['prompt'] ?? '' );
		$model = sanitize_text_field( $_POST['model'] ?? 'flux' );
		$style = sanitize_text_field( $_POST['style'] ?? 'professional' );
		$width = intval( $_POST['width'] ?? 1024 );
		$height = intval( $_POST['height'] ?? 1024 );

		if ( empty( $prompt ) ) {
			wp_send_json_error( [
				'message' => __( 'Prompt is required for image generation.', 'seo-forge' )
			] );
		}

		// Determine size format
		$size = $width . 'x' . $height;
		if ( ! in_array( $size, ['512x512', '1024x1024', '1024x768'] ) ) {
			$size = '1024x1024';
		}

		// Use the new image generation endpoint
		$response = $this->make_request( '/api/flux-image-gen/generate', [
			'prompt' => $prompt,
			'model' => $model,
			'style' => $style,
			'size' => $size
		] );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [
				'message' => __( 'Image generation failed: ', 'seo-forge' ) . $response->get_error_message()
			] );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Handle the new MCP response format
		if ( isset( $data['success'] ) && $data['success'] && isset( $data['result'] ) ) {
			$result = $data['result'];
			wp_send_json_success( [
				'image_url' => $result['image_url'] ?? '',
				'image_data' => $result['image_data'] ?? '',
				'metadata' => $result['metadata'] ?? []
			] );
		} else {
			$error_message = $data['error'] ?? __( 'Invalid response from image generation API.', 'seo-forge' );
			wp_send_json_error( [
				'message' => $error_message
			] );
		}
	}

	/**
	 * Generate Flux batch images via API.
	 */
	public function generate_flux_batch() {
		check_ajax_referer( 'seo_forge_nonce', 'nonce' );

		$prompts = array_map( 'sanitize_text_field', $_POST['prompts'] ?? [] );
		$model = sanitize_text_field( $_POST['model'] ?? 'flux' );
		$style = sanitize_text_field( $_POST['style'] ?? 'professional' );

		if ( empty( $prompts ) ) {
			wp_send_json_error( [
				'message' => __( 'Prompts are required for batch image generation.', 'seo-forge' )
			] );
		}

		// For batch generation, we'll call the single image endpoint multiple times
		// since our MCP server doesn't have a specific batch endpoint
		$images = [];
		$errors = [];

		foreach ( $prompts as $prompt ) {
			$response = $this->make_request( '/api/flux-image-gen/generate', [
				'prompt' => $prompt,
				'model' => $model,
				'style' => $style,
				'size' => '1024x1024'
			] );

			if ( is_wp_error( $response ) ) {
				$errors[] = $response->get_error_message();
				continue;
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( isset( $data['success'] ) && $data['success'] && isset( $data['result'] ) ) {
				$images[] = [
					'prompt' => $prompt,
					'image_url' => $data['result']['image_url'] ?? '',
					'image_data' => $data['result']['image_data'] ?? '',
					'metadata' => $data['result']['metadata'] ?? []
				];
			} else {
				$errors[] = $data['error'] ?? 'Unknown error for prompt: ' . $prompt;
			}
		}

		if ( ! empty( $images ) ) {
			wp_send_json_success( [
				'images' => $images,
				'total_generated' => count( $images ),
				'errors' => $errors
			] );
		} else {
			wp_send_json_error( [
				'message' => __( 'Batch image generation failed for all prompts.', 'seo-forge' ),
				'errors' => $errors
			] );
		}
	}

	/**
	 * Get server status via API.
	 */
	public function get_server_status() {
		check_ajax_referer( 'seo_forge_nonce', 'nonce' );

		// Use the new health endpoint
		$response = $this->make_request( '/health', [], 'GET' );

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
	 * @param string $method   HTTP method.
	 * @return array|WP_Error
	 */
	private function make_request( $endpoint, $data = [], $method = 'POST' ) {
		$url = rtrim( $this->api_url, '/' ) . $endpoint;

		$args = [
			'method'  => $method,
			'headers' => [
				'Content-Type' => 'application/json',
				'User-Agent'   => 'SEO-Forge-WordPress-Plugin/' . SEO_FORGE_VERSION,
				'Accept'       => 'application/json',
				'X-Requested-With' => 'XMLHttpRequest',
			],
			'timeout' => 60,
			'sslverify' => false, // For development environments
			'redirection' => 5,
		];

		// Add API key authentication
		if ( ! empty( $this->api_key ) ) {
			$args['headers']['X-API-Key'] = $this->api_key;
		}

		// Add body for POST requests
		if ( $method === 'POST' && ! empty( $data ) ) {
			$args['body'] = json_encode( $data );
		}

		// Add debug logging
		error_log( 'SEO Forge API Request: ' . $url );
		error_log( 'SEO Forge API Method: ' . $method );
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
			// Handle different keyword data structures
			$keyword = '';
			$search_volume = 0;
			$difficulty = 0;
			$cpc = 0.00;

			if ( is_string( $keyword_data ) ) {
				$keyword = $keyword_data;
			} elseif ( is_array( $keyword_data ) ) {
				$keyword = $keyword_data['keyword'] ?? $keyword_data['text'] ?? '';
				$search_volume = $keyword_data['search_volume'] ?? $keyword_data['volume'] ?? 0;
				$difficulty = $keyword_data['difficulty'] ?? $keyword_data['competition'] ?? 0;
				$cpc = $keyword_data['cpc'] ?? $keyword_data['cost_per_click'] ?? 0.00;
			}

			if ( ! empty( $keyword ) ) {
				$wpdb->replace(
					$table_name,
					[
						'keyword'       => $keyword,
						'search_volume' => $search_volume,
						'difficulty'    => $difficulty,
						'cpc'           => $cpc,
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
}