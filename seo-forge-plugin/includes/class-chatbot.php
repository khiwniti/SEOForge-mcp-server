<?php
/**
 * SEO Forge Chatbot
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;

/**
 * SEO_Forge_Chatbot class.
 */
class SEO_Forge_Chatbot {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_footer', [ $this, 'render_chatbot' ] );
		add_action( 'wp_ajax_seo_forge_chatbot_query', [ $this, 'handle_chatbot_query' ] );
		add_action( 'wp_ajax_nopriv_seo_forge_chatbot_query', [ $this, 'handle_chatbot_query' ] );
		add_action( 'wp_ajax_seo_forge_chatbot_feedback', [ $this, 'handle_chatbot_feedback' ] );
		add_action( 'wp_ajax_nopriv_seo_forge_chatbot_feedback', [ $this, 'handle_chatbot_feedback' ] );
		add_action( 'admin_menu', [ $this, 'add_chatbot_admin_menu' ] );
	}

	/**
	 * Add chatbot admin menu.
	 */
	public function add_chatbot_admin_menu() {
		add_submenu_page(
			'seo-forge',
			__( 'AI Chatbot', 'seo-forge' ),
			__( 'AI Chatbot', 'seo-forge' ),
			'manage_options',
			'seo-forge-chatbot',
			[ $this, 'chatbot_admin_page' ]
		);
	}

	/**
	 * Enqueue chatbot scripts and styles.
	 */
	public function enqueue_scripts() {
		if ( ! $this->should_show_chatbot() ) {
			return;
		}

		wp_enqueue_style(
			'seo-forge-chatbot',
			SEO_FORGE_URL . 'assets/css/chatbot.css',
			[],
			SEO_FORGE_VERSION
		);

		wp_enqueue_script(
			'seo-forge-chatbot',
			SEO_FORGE_URL . 'assets/js/chatbot.js',
			[ 'jquery' ],
			SEO_FORGE_VERSION,
			true
		);

		wp_localize_script(
			'seo-forge-chatbot',
			'seoForgeChatbot',
			[
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'seo_forge_chatbot_nonce' ),
				'settings'   => $this->get_chatbot_settings(),
				'strings'    => [
					'welcome'           => __( 'Hi! I\'m your SEO assistant. How can I help you today?', 'seo-forge' ),
					'placeholder'       => __( 'Ask me about SEO, content, keywords...', 'seo-forge' ),
					'send'              => __( 'Send', 'seo-forge' ),
					'thinking'          => __( 'Thinking...', 'seo-forge' ),
					'error'             => __( 'Sorry, I encountered an error. Please try again.', 'seo-forge' ),
					'minimize'          => __( 'Minimize', 'seo-forge' ),
					'maximize'          => __( 'Maximize', 'seo-forge' ),
					'close'             => __( 'Close', 'seo-forge' ),
					'helpful'           => __( 'Was this helpful?', 'seo-forge' ),
					'yes'               => __( 'Yes', 'seo-forge' ),
					'no'                => __( 'No', 'seo-forge' ),
					'feedback_thanks'   => __( 'Thank you for your feedback!', 'seo-forge' ),
					'clear_chat'        => __( 'Clear Chat', 'seo-forge' ),
					'export_chat'       => __( 'Export Chat', 'seo-forge' ),
				],
				'knowledge_base'     => $this->get_knowledge_base(),
				'quick_actions'      => $this->get_quick_actions(),
			]
		);
	}

	/**
	 * Check if chatbot should be shown.
	 */
	private function should_show_chatbot() {
		$settings = get_option( 'seo_forge_chatbot_settings', [] );
		
		if ( empty( $settings['enabled'] ) ) {
			return false;
		}

		// Check page restrictions
		if ( ! empty( $settings['pages'] ) ) {
			$current_page = get_queried_object_id();
			$allowed_pages = array_map( 'intval', $settings['pages'] );
			
			if ( ! in_array( $current_page, $allowed_pages ) ) {
				return false;
			}
		}

		// Check user role restrictions
		if ( ! empty( $settings['user_roles'] ) ) {
			$user = wp_get_current_user();
			$user_roles = $user->roles;
			$allowed_roles = $settings['user_roles'];
			
			if ( ! array_intersect( $user_roles, $allowed_roles ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get chatbot settings.
	 */
	private function get_chatbot_settings() {
		$defaults = [
			'enabled'           => true,
			'position'          => 'bottom-right',
			'theme'             => 'auto',
			'primary_color'     => '#0073aa',
			'secondary_color'   => '#f8f9fa',
			'text_color'        => '#333333',
			'bot_avatar'        => SEO_FORGE_URL . 'assets/images/bot-avatar.svg',
			'user_avatar'       => '',
			'welcome_message'   => __( 'Hi! I\'m your SEO assistant. How can I help you today?', 'seo-forge' ),
			'offline_message'   => __( 'I\'m currently offline. Please leave a message and I\'ll get back to you.', 'seo-forge' ),
			'max_messages'      => 50,
			'typing_delay'      => 1000,
			'auto_open'         => false,
			'sound_enabled'     => true,
			'show_branding'     => true,
		];

		return wp_parse_args( get_option( 'seo_forge_chatbot_settings', [] ), $defaults );
	}

	/**
	 * Get knowledge base data.
	 */
	private function get_knowledge_base() {
		return [
			'seo_basics' => [
				'title' => __( 'SEO Basics', 'seo-forge' ),
				'topics' => [
					'what_is_seo' => [
						'question' => __( 'What is SEO?', 'seo-forge' ),
						'answer' => __( 'SEO (Search Engine Optimization) is the practice of optimizing your website to increase its visibility and ranking in search engine results pages (SERPs). It involves various techniques to make your site more attractive to search engines like Google.', 'seo-forge' ),
						'related' => [ 'on_page_seo', 'off_page_seo', 'technical_seo' ]
					],
					'on_page_seo' => [
						'question' => __( 'What is On-Page SEO?', 'seo-forge' ),
						'answer' => __( 'On-Page SEO refers to optimizing individual web pages to rank higher in search engines. This includes optimizing content, HTML tags (title, meta description, headers), images, and internal linking structure.', 'seo-forge' ),
						'related' => [ 'meta_tags', 'keyword_optimization', 'content_optimization' ]
					],
					'off_page_seo' => [
						'question' => __( 'What is Off-Page SEO?', 'seo-forge' ),
						'answer' => __( 'Off-Page SEO involves activities outside your website that impact your search rankings. This primarily includes building high-quality backlinks, social media marketing, and building domain authority.', 'seo-forge' ),
						'related' => [ 'link_building', 'social_signals', 'domain_authority' ]
					],
					'technical_seo' => [
						'question' => __( 'What is Technical SEO?', 'seo-forge' ),
						'answer' => __( 'Technical SEO focuses on optimizing your website\'s technical aspects to help search engines crawl and index your site effectively. This includes site speed, mobile-friendliness, SSL certificates, and structured data.', 'seo-forge' ),
						'related' => [ 'site_speed', 'mobile_optimization', 'schema_markup' ]
					]
				]
			],
			'keyword_research' => [
				'title' => __( 'Keyword Research', 'seo-forge' ),
				'topics' => [
					'keyword_types' => [
						'question' => __( 'What are the different types of keywords?', 'seo-forge' ),
						'answer' => __( 'Keywords can be categorized as: 1) Short-tail (1-2 words, high volume, high competition), 2) Long-tail (3+ words, lower volume, lower competition), 3) Branded (include your brand name), 4) Commercial (buying intent), 5) Informational (seeking information).', 'seo-forge' ),
						'related' => [ 'keyword_difficulty', 'search_volume', 'keyword_intent' ]
					],
					'keyword_research_tools' => [
						'question' => __( 'What tools can I use for keyword research?', 'seo-forge' ),
						'answer' => __( 'Popular keyword research tools include: Google Keyword Planner, SEMrush, Ahrefs, Moz Keyword Explorer, Ubersuggest, and our built-in SEO Forge keyword research tool. Each offers unique features for finding and analyzing keywords.', 'seo-forge' ),
						'related' => [ 'keyword_analysis', 'competitor_keywords', 'keyword_tracking' ]
					],
					'keyword_difficulty' => [
						'question' => __( 'How do I assess keyword difficulty?', 'seo-forge' ),
						'answer' => __( 'Keyword difficulty is measured by analyzing the competition for a keyword. Factors include: domain authority of ranking pages, content quality, backlink profiles, and search volume. Use our keyword research tool to get difficulty scores.', 'seo-forge' ),
						'related' => [ 'competitor_analysis', 'serp_analysis', 'keyword_opportunity' ]
					]
				]
			],
			'content_optimization' => [
				'title' => __( 'Content Optimization', 'seo-forge' ),
				'topics' => [
					'content_quality' => [
						'question' => __( 'What makes high-quality SEO content?', 'seo-forge' ),
						'answer' => __( 'High-quality SEO content is: 1) Original and valuable to users, 2) Well-researched and comprehensive, 3) Properly structured with headers, 4) Optimized for target keywords naturally, 5) Includes relevant images and media, 6) Provides clear answers to user queries.', 'seo-forge' ),
						'related' => [ 'content_length', 'readability', 'user_intent' ]
					],
					'meta_optimization' => [
						'question' => __( 'How do I optimize meta tags?', 'seo-forge' ),
						'answer' => __( 'Meta tag optimization includes: 1) Title tags (50-60 characters, include primary keyword), 2) Meta descriptions (150-160 characters, compelling and descriptive), 3) Header tags (H1, H2, H3 for structure), 4) Alt text for images. Use our SEO analyzer for optimization suggestions.', 'seo-forge' ),
						'related' => [ 'title_optimization', 'meta_description', 'header_tags' ]
					],
					'internal_linking' => [
						'question' => __( 'Why is internal linking important?', 'seo-forge' ),
						'answer' => __( 'Internal linking helps: 1) Distribute page authority throughout your site, 2) Help search engines discover and index pages, 3) Improve user navigation and experience, 4) Establish information hierarchy, 5) Increase time on site and reduce bounce rate.', 'seo-forge' ),
						'related' => [ 'anchor_text', 'link_structure', 'page_authority' ]
					]
				]
			],
			'technical_seo' => [
				'title' => __( 'Technical SEO', 'seo-forge' ),
				'topics' => [
					'site_speed' => [
						'question' => __( 'How can I improve my site speed?', 'seo-forge' ),
						'answer' => __( 'Improve site speed by: 1) Optimizing images (compress, use WebP format), 2) Minifying CSS/JS files, 3) Using a CDN, 4) Enabling browser caching, 5) Choosing fast hosting, 6) Reducing HTTP requests, 7) Using our site analysis tool to identify issues.', 'seo-forge' ),
						'related' => [ 'core_web_vitals', 'mobile_speed', 'caching' ]
					],
					'mobile_optimization' => [
						'question' => __( 'How do I optimize for mobile SEO?', 'seo-forge' ),
						'answer' => __( 'Mobile optimization includes: 1) Responsive design that adapts to all screen sizes, 2) Fast loading times on mobile, 3) Touch-friendly navigation, 4) Readable text without zooming, 5) Optimized images for mobile, 6) Google Mobile-Friendly Test compliance.', 'seo-forge' ),
						'related' => [ 'responsive_design', 'mobile_usability', 'amp_pages' ]
					],
					'schema_markup' => [
						'question' => __( 'What is Schema markup and why use it?', 'seo-forge' ),
						'answer' => __( 'Schema markup is structured data that helps search engines understand your content better. It can result in rich snippets, improved click-through rates, and better search visibility. Use our Schema Generator to create markup for articles, products, reviews, and more.', 'seo-forge' ),
						'related' => [ 'structured_data', 'rich_snippets', 'json_ld' ]
					]
				]
			],
			'local_seo' => [
				'title' => __( 'Local SEO', 'seo-forge' ),
				'topics' => [
					'google_my_business' => [
						'question' => __( 'How do I optimize Google My Business?', 'seo-forge' ),
						'answer' => __( 'Optimize GMB by: 1) Claiming and verifying your listing, 2) Complete all profile information, 3) Add high-quality photos, 4) Collect and respond to reviews, 5) Post regular updates, 6) Use relevant categories, 7) Keep hours and contact info updated.', 'seo-forge' ),
						'related' => [ 'local_citations', 'online_reviews', 'local_keywords' ]
					],
					'local_citations' => [
						'question' => __( 'What are local citations and why are they important?', 'seo-forge' ),
						'answer' => __( 'Local citations are online mentions of your business name, address, and phone number (NAP). They help establish credibility and improve local search rankings. Ensure consistency across all platforms. Use our citation scanner to find and manage your listings.', 'seo-forge' ),
						'related' => [ 'nap_consistency', 'directory_listings', 'local_authority' ]
					],
					'local_content' => [
						'question' => __( 'How do I create local SEO content?', 'seo-forge' ),
						'answer' => __( 'Create local content by: 1) Including location-specific keywords, 2) Writing about local events and news, 3) Creating location pages for multiple areas, 4) Featuring local customer testimonials, 5) Partnering with local businesses, 6) Using local schema markup.', 'seo-forge' ),
						'related' => [ 'location_pages', 'local_keywords', 'geo_targeting' ]
					]
				]
			],
			'analytics_tracking' => [
				'title' => __( 'Analytics & Tracking', 'seo-forge' ),
				'topics' => [
					'google_analytics' => [
						'question' => __( 'How do I set up Google Analytics for SEO?', 'seo-forge' ),
						'answer' => __( 'Set up GA4 by: 1) Creating a Google Analytics account, 2) Installing the tracking code, 3) Setting up goals and conversions, 4) Linking with Google Search Console, 5) Creating custom reports for SEO metrics, 6) Using our analytics integration for easier setup.', 'seo-forge' ),
						'related' => [ 'conversion_tracking', 'goal_setup', 'custom_reports' ]
					],
					'search_console' => [
						'question' => __( 'What is Google Search Console and how do I use it?', 'seo-forge' ),
						'answer' => __( 'Google Search Console is a free tool that helps monitor your site\'s search performance. It shows: search queries, click-through rates, indexing issues, mobile usability problems, and security issues. Connect it with our plugin for integrated reporting.', 'seo-forge' ),
						'related' => [ 'search_performance', 'indexing_issues', 'mobile_usability' ]
					],
					'kpi_tracking' => [
						'question' => __( 'What SEO KPIs should I track?', 'seo-forge' ),
						'answer' => __( 'Important SEO KPIs include: 1) Organic traffic growth, 2) Keyword rankings, 3) Click-through rates, 4) Bounce rate, 5) Page load speed, 6) Backlink profile, 7) Conversion rates from organic traffic. Use our analytics dashboard to monitor these metrics.', 'seo-forge' ),
						'related' => [ 'traffic_analysis', 'ranking_tracking', 'conversion_optimization' ]
					]
				]
			]
		];
	}

	/**
	 * Get quick actions for chatbot.
	 */
	private function get_quick_actions() {
		return [
			[
				'label' => __( 'Analyze my page', 'seo-forge' ),
				'action' => 'analyze_page',
				'icon' => 'dashicons-search'
			],
			[
				'label' => __( 'Research keywords', 'seo-forge' ),
				'action' => 'research_keywords',
				'icon' => 'dashicons-admin-network'
			],
			[
				'label' => __( 'Generate content', 'seo-forge' ),
				'action' => 'generate_content',
				'icon' => 'dashicons-edit'
			],
			[
				'label' => __( 'Create images', 'seo-forge' ),
				'action' => 'generate_images',
				'icon' => 'dashicons-format-image'
			],
			[
				'label' => __( 'Check site health', 'seo-forge' ),
				'action' => 'site_health',
				'icon' => 'dashicons-heart'
			],
			[
				'label' => __( 'Local SEO help', 'seo-forge' ),
				'action' => 'local_seo',
				'icon' => 'dashicons-location'
			]
		];
	}

	/**
	 * Handle chatbot query.
	 */
	public function handle_chatbot_query() {
		check_ajax_referer( 'seo_forge_chatbot_nonce', 'nonce' );

		$query = sanitize_text_field( $_POST['query'] ?? '' );
		$context = sanitize_text_field( $_POST['context'] ?? '' );
		$action_type = sanitize_text_field( $_POST['action_type'] ?? 'chat' );

		if ( empty( $query ) ) {
			wp_send_json_error( [
				'message' => __( 'Please enter a question.', 'seo-forge' )
			] );
		}

		// Process the query based on type
		switch ( $action_type ) {
			case 'quick_action':
				$response = $this->handle_quick_action( $query, $context );
				break;
			case 'knowledge_search':
				$response = $this->search_knowledge_base( $query );
				break;
			default:
				$response = $this->process_ai_query( $query, $context );
				break;
		}

		// Log the interaction
		$this->log_chatbot_interaction( $query, $response, $action_type );

		wp_send_json_success( $response );
	}

	/**
	 * Handle quick actions.
	 */
	private function handle_quick_action( $action, $context ) {
		switch ( $action ) {
			case 'analyze_page':
				return $this->analyze_current_page( $context );
			case 'research_keywords':
				return $this->suggest_keyword_research( $context );
			case 'generate_content':
				return $this->suggest_content_generation( $context );
			case 'generate_images':
				return $this->suggest_image_generation( $context );
			case 'site_health':
				return $this->check_site_health();
			case 'local_seo':
				return $this->provide_local_seo_tips();
			default:
				return [
					'message' => __( 'I\'m not sure how to help with that action.', 'seo-forge' ),
					'type' => 'error'
				];
		}
	}

	/**
	 * Search knowledge base.
	 */
	private function search_knowledge_base( $query ) {
		$knowledge_base = $this->get_knowledge_base();
		$results = [];
		$query_lower = strtolower( $query );

		foreach ( $knowledge_base as $category_key => $category ) {
			foreach ( $category['topics'] as $topic_key => $topic ) {
				$question_lower = strtolower( $topic['question'] );
				$answer_lower = strtolower( $topic['answer'] );

				// Simple keyword matching
				if ( strpos( $question_lower, $query_lower ) !== false || 
					 strpos( $answer_lower, $query_lower ) !== false ) {
					$results[] = [
						'category' => $category['title'],
						'question' => $topic['question'],
						'answer' => $topic['answer'],
						'related' => $topic['related'] ?? []
					];
				}
			}
		}

		if ( empty( $results ) ) {
			return [
				'message' => __( 'I couldn\'t find specific information about that. Let me help you with some general SEO guidance or try rephrasing your question.', 'seo-forge' ),
				'type' => 'info',
				'suggestions' => [
					__( 'How do I improve my SEO?', 'seo-forge' ),
					__( 'What is keyword research?', 'seo-forge' ),
					__( 'How do I optimize my content?', 'seo-forge' )
				]
			];
		}

		// Return the best match
		$best_match = $results[0];
		return [
			'message' => $best_match['answer'],
			'type' => 'knowledge',
			'category' => $best_match['category'],
			'question' => $best_match['question'],
			'related' => $best_match['related'],
			'additional_results' => array_slice( $results, 1, 2 )
		];
	}

	/**
	 * Process AI query using API.
	 */
	private function process_ai_query( $query, $context ) {
		$api_url = get_option( 'seo_forge_api_url', '' );
		
		if ( empty( $api_url ) ) {
			return $this->search_knowledge_base( $query );
		}

		// Prepare context for AI
		$ai_context = $this->prepare_ai_context( $context );
		
		$response = wp_remote_post( $api_url . '/api/chatbot-query', [
			'headers' => [
				'Content-Type' => 'application/json',
			],
			'body' => json_encode( [
				'query' => $query,
				'context' => $ai_context,
				'knowledge_base' => 'seo_forge',
				'max_tokens' => 500
			] ),
			'timeout' => 30
		] );

		if ( is_wp_error( $response ) ) {
			return $this->search_knowledge_base( $query );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['response'] ) ) {
			return [
				'message' => $data['response'],
				'type' => 'ai',
				'confidence' => $data['confidence'] ?? 0.8,
				'sources' => $data['sources'] ?? [],
				'suggestions' => $data['suggestions'] ?? []
			];
		}

		return $this->search_knowledge_base( $query );
	}

	/**
	 * Prepare AI context.
	 */
	private function prepare_ai_context( $context ) {
		global $wp;
		
		$ai_context = [
			'current_url' => home_url( $wp->request ),
			'site_title' => get_bloginfo( 'name' ),
			'site_description' => get_bloginfo( 'description' ),
			'user_context' => $context
		];

		// Add current page context if available
		if ( is_singular() ) {
			$post = get_queried_object();
			$ai_context['page_type'] = 'post';
			$ai_context['page_title'] = $post->post_title;
			$ai_context['page_content'] = wp_trim_words( $post->post_content, 100 );
		} elseif ( is_home() || is_front_page() ) {
			$ai_context['page_type'] = 'homepage';
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$ai_context['page_type'] = 'archive';
			$ai_context['archive_title'] = get_the_archive_title();
		}

		return $ai_context;
	}

	/**
	 * Analyze current page.
	 */
	private function analyze_current_page( $context ) {
		global $wp;
		$current_url = home_url( $wp->request );
		
		return [
			'message' => sprintf( 
				__( 'I can help you analyze the current page (%s). Would you like me to check the SEO optimization, content quality, or technical aspects?', 'seo-forge' ),
				$current_url
			),
			'type' => 'action',
			'action_buttons' => [
				[
					'label' => __( 'SEO Analysis', 'seo-forge' ),
					'action' => 'seo_analysis',
					'url' => admin_url( 'admin.php?page=seo-forge-analyzer' )
				],
				[
					'label' => __( 'Content Check', 'seo-forge' ),
					'action' => 'content_analysis',
					'url' => admin_url( 'admin.php?page=seo-forge-content' )
				],
				[
					'label' => __( 'Technical Audit', 'seo-forge' ),
					'action' => 'technical_audit',
					'url' => admin_url( 'admin.php?page=seo-forge-site-analysis' )
				]
			]
		];
	}

	/**
	 * Suggest keyword research.
	 */
	private function suggest_keyword_research( $context ) {
		return [
			'message' => __( 'I can help you research keywords for better SEO performance. What type of keywords are you looking for?', 'seo-forge' ),
			'type' => 'action',
			'action_buttons' => [
				[
					'label' => __( 'Start Keyword Research', 'seo-forge' ),
					'action' => 'keyword_research',
					'url' => admin_url( 'admin.php?page=seo-forge-keywords' )
				],
				[
					'label' => __( 'Track Rankings', 'seo-forge' ),
					'action' => 'rank_tracking',
					'url' => admin_url( 'admin.php?page=seo-forge-rank-tracker' )
				]
			],
			'tips' => [
				__( 'Focus on long-tail keywords for better conversion rates', 'seo-forge' ),
				__( 'Consider user intent when selecting keywords', 'seo-forge' ),
				__( 'Analyze competitor keywords for opportunities', 'seo-forge' )
			]
		];
	}

	/**
	 * Suggest content generation.
	 */
	private function suggest_content_generation( $context ) {
		return [
			'message' => __( 'I can help you create SEO-optimized content. What type of content do you need?', 'seo-forge' ),
			'type' => 'action',
			'action_buttons' => [
				[
					'label' => __( 'Generate Content', 'seo-forge' ),
					'action' => 'content_generation',
					'url' => admin_url( 'admin.php?page=seo-forge-content' )
				],
				[
					'label' => __( 'Create Images', 'seo-forge' ),
					'action' => 'image_generation',
					'url' => admin_url( 'admin.php?page=seo-forge-images' )
				]
			],
			'content_types' => [
				__( 'Blog posts and articles', 'seo-forge' ),
				__( 'Product descriptions', 'seo-forge' ),
				__( 'Meta descriptions and titles', 'seo-forge' ),
				__( 'Social media content', 'seo-forge' )
			]
		];
	}

	/**
	 * Suggest image generation.
	 */
	private function suggest_image_generation( $context ) {
		return [
			'message' => __( 'I can help you create stunning images using AI. What kind of images do you need for your content?', 'seo-forge' ),
			'type' => 'action',
			'action_buttons' => [
				[
					'label' => __( 'Generate Images', 'seo-forge' ),
					'action' => 'image_generation',
					'url' => admin_url( 'admin.php?page=seo-forge-images' )
				]
			],
			'image_types' => [
				__( 'Blog post featured images', 'seo-forge' ),
				__( 'Product photography', 'seo-forge' ),
				__( 'Social media graphics', 'seo-forge' ),
				__( 'Website banners and headers', 'seo-forge' )
			]
		];
	}

	/**
	 * Check site health.
	 */
	private function check_site_health() {
		return [
			'message' => __( 'Let me help you check your website\'s SEO health. I can analyze various aspects of your site.', 'seo-forge' ),
			'type' => 'action',
			'action_buttons' => [
				[
					'label' => __( 'Site Analysis', 'seo-forge' ),
					'action' => 'site_analysis',
					'url' => admin_url( 'admin.php?page=seo-forge-site-analysis' )
				],
				[
					'label' => __( 'Analytics Dashboard', 'seo-forge' ),
					'action' => 'analytics',
					'url' => admin_url( 'admin.php?page=seo-forge-analytics' )
				]
			],
			'health_checks' => [
				__( 'Page loading speed', 'seo-forge' ),
				__( 'Mobile responsiveness', 'seo-forge' ),
				__( 'SSL certificate status', 'seo-forge' ),
				__( 'Meta tags optimization', 'seo-forge' ),
				__( 'Internal linking structure', 'seo-forge' )
			]
		];
	}

	/**
	 * Provide local SEO tips.
	 */
	private function provide_local_seo_tips() {
		return [
			'message' => __( 'Local SEO is crucial for businesses serving specific geographic areas. Here are some key strategies:', 'seo-forge' ),
			'type' => 'tips',
			'action_buttons' => [
				[
					'label' => __( 'Local SEO Setup', 'seo-forge' ),
					'action' => 'local_seo_setup',
					'url' => admin_url( 'admin.php?page=seo-forge-local-seo' )
				]
			],
			'tips' => [
				__( 'Claim and optimize your Google My Business listing', 'seo-forge' ),
				__( 'Ensure NAP (Name, Address, Phone) consistency across all platforms', 'seo-forge' ),
				__( 'Collect and respond to customer reviews', 'seo-forge' ),
				__( 'Create location-specific content and landing pages', 'seo-forge' ),
				__( 'Build local citations and directory listings', 'seo-forge' ),
				__( 'Use local keywords in your content and meta tags', 'seo-forge' )
			]
		];
	}

	/**
	 * Handle chatbot feedback.
	 */
	public function handle_chatbot_feedback() {
		check_ajax_referer( 'seo_forge_chatbot_nonce', 'nonce' );

		$message_id = sanitize_text_field( $_POST['message_id'] ?? '' );
		$feedback = sanitize_text_field( $_POST['feedback'] ?? '' );
		$comment = sanitize_textarea_field( $_POST['comment'] ?? '' );

		// Store feedback in database
		global $wpdb;
		$table_name = $wpdb->prefix . 'seo_forge_chatbot_feedback';

		$wpdb->insert(
			$table_name,
			[
				'message_id' => $message_id,
				'feedback' => $feedback,
				'comment' => $comment,
				'user_id' => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'],
				'created_at' => current_time( 'mysql' )
			],
			[ '%s', '%s', '%s', '%d', '%s', '%s' ]
		);

		wp_send_json_success( [
			'message' => __( 'Thank you for your feedback!', 'seo-forge' )
		] );
	}

	/**
	 * Log chatbot interaction.
	 */
	private function log_chatbot_interaction( $query, $response, $type ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'seo_forge_chatbot_logs';

		$wpdb->insert(
			$table_name,
			[
				'user_id' => get_current_user_id(),
				'query' => $query,
				'response' => json_encode( $response ),
				'type' => $type,
				'ip_address' => $_SERVER['REMOTE_ADDR'],
				'user_agent' => $_SERVER['HTTP_USER_AGENT'],
				'created_at' => current_time( 'mysql' )
			],
			[ '%d', '%s', '%s', '%s', '%s', '%s', '%s' ]
		);
	}

	/**
	 * Render chatbot HTML.
	 */
	public function render_chatbot() {
		if ( ! $this->should_show_chatbot() ) {
			return;
		}

		$settings = $this->get_chatbot_settings();
		include SEO_FORGE_PATH . 'templates/chatbot/chatbot.php';
	}

	/**
	 * Chatbot admin page.
	 */
	public function chatbot_admin_page() {
		include SEO_FORGE_PATH . 'templates/admin/chatbot.php';
	}
}