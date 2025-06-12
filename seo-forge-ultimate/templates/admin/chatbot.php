<?php
/**
 * Chatbot Admin Page
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;

// Handle form submission
if ( isset( $_POST['submit'] ) && wp_verify_nonce( $_POST['seo_forge_nonce'], 'seo_forge_chatbot_settings' ) ) {
	$settings = [
		'enabled'           => isset( $_POST['enabled'] ),
		'position'          => sanitize_text_field( $_POST['position'] ),
		'theme'             => sanitize_text_field( $_POST['theme'] ),
		'primary_color'     => sanitize_hex_color( $_POST['primary_color'] ),
		'secondary_color'   => sanitize_hex_color( $_POST['secondary_color'] ),
		'text_color'        => sanitize_hex_color( $_POST['text_color'] ),
		'welcome_message'   => sanitize_textarea_field( $_POST['welcome_message'] ),
		'offline_message'   => sanitize_textarea_field( $_POST['offline_message'] ),
		'max_messages'      => intval( $_POST['max_messages'] ),
		'typing_delay'      => intval( $_POST['typing_delay'] ),
		'auto_open'         => isset( $_POST['auto_open'] ),
		'sound_enabled'     => isset( $_POST['sound_enabled'] ),
		'show_branding'     => isset( $_POST['show_branding'] ),
		'pages'             => array_map( 'intval', $_POST['pages'] ?? [] ),
		'user_roles'        => array_map( 'sanitize_text_field', $_POST['user_roles'] ?? [] ),
	];

	update_option( 'seo_forge_chatbot_settings', $settings );
	echo '<div class="notice notice-success"><p>' . __( 'Chatbot settings saved successfully!', 'seo-forge' ) . '</p></div>';
}

$settings = get_option( 'seo_forge_chatbot_settings', [] );
$defaults = [
	'enabled'           => true,
	'position'          => 'bottom-right',
	'theme'             => 'auto',
	'primary_color'     => '#0073aa',
	'secondary_color'   => '#f8f9fa',
	'text_color'        => '#333333',
	'welcome_message'   => __( 'Hi! I\'m your SEO assistant. How can I help you today?', 'seo-forge' ),
	'offline_message'   => __( 'I\'m currently offline. Please leave a message and I\'ll get back to you.', 'seo-forge' ),
	'max_messages'      => 50,
	'typing_delay'      => 1000,
	'auto_open'         => false,
	'sound_enabled'     => true,
	'show_branding'     => true,
	'pages'             => [],
	'user_roles'        => [],
];
$settings = wp_parse_args( $settings, $defaults );
?>

<div class="wrap seo-forge-admin">
	<h1><?php _e( 'SEO Forge - AI Chatbot', 'seo-forge' ); ?></h1>
	
	<div class="seo-forge-container">
		<div class="seo-forge-header">
			<div class="seo-forge-nav">
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge' ); ?>" class="nav-tab">
					<?php _e( 'Dashboard', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-settings' ); ?>" class="nav-tab">
					<?php _e( 'Settings', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-chatbot' ); ?>" class="nav-tab nav-tab-active">
					<?php _e( 'AI Chatbot', 'seo-forge' ); ?>
				</a>
			</div>
		</div>

		<div class="seo-forge-content">
			<form method="post" action="">
				<?php wp_nonce_field( 'seo_forge_chatbot_settings', 'seo_forge_nonce' ); ?>

				<!-- General Settings -->
				<div class="seo-forge-card">
					<div class="card-header">
						<h2><?php _e( 'General Settings', 'seo-forge' ); ?></h2>
						<p><?php _e( 'Configure basic chatbot functionality and behavior.', 'seo-forge' ); ?></p>
					</div>

					<div class="card-body">
						<div class="form-row">
							<div class="form-group">
								<label class="toggle-label">
									<input type="checkbox" name="enabled" value="1" <?php checked( $settings['enabled'] ); ?> />
									<span class="toggle-slider"></span>
									<?php _e( 'Enable Chatbot', 'seo-forge' ); ?>
								</label>
								<small class="form-text"><?php _e( 'Show the chatbot on your website frontend.', 'seo-forge' ); ?></small>
							</div>

							<div class="form-group">
								<label for="position"><?php _e( 'Position', 'seo-forge' ); ?></label>
								<select id="position" name="position">
									<option value="bottom-right" <?php selected( $settings['position'], 'bottom-right' ); ?>><?php _e( 'Bottom Right', 'seo-forge' ); ?></option>
									<option value="bottom-left" <?php selected( $settings['position'], 'bottom-left' ); ?>><?php _e( 'Bottom Left', 'seo-forge' ); ?></option>
									<option value="top-right" <?php selected( $settings['position'], 'top-right' ); ?>><?php _e( 'Top Right', 'seo-forge' ); ?></option>
									<option value="top-left" <?php selected( $settings['position'], 'top-left' ); ?>><?php _e( 'Top Left', 'seo-forge' ); ?></option>
								</select>
							</div>
						</div>

						<div class="form-row">
							<div class="form-group">
								<label for="max_messages"><?php _e( 'Max Messages', 'seo-forge' ); ?></label>
								<input type="number" id="max_messages" name="max_messages" value="<?php echo esc_attr( $settings['max_messages'] ); ?>" min="10" max="200" />
								<small class="form-text"><?php _e( 'Maximum number of messages to keep in chat history.', 'seo-forge' ); ?></small>
							</div>

							<div class="form-group">
								<label for="typing_delay"><?php _e( 'Typing Delay (ms)', 'seo-forge' ); ?></label>
								<input type="number" id="typing_delay" name="typing_delay" value="<?php echo esc_attr( $settings['typing_delay'] ); ?>" min="500" max="5000" step="100" />
								<small class="form-text"><?php _e( 'Delay before showing bot response to simulate typing.', 'seo-forge' ); ?></small>
							</div>
						</div>

						<div class="form-row">
							<div class="form-group">
								<label class="toggle-label">
									<input type="checkbox" name="auto_open" value="1" <?php checked( $settings['auto_open'] ); ?> />
									<span class="toggle-slider"></span>
									<?php _e( 'Auto Open', 'seo-forge' ); ?>
								</label>
								<small class="form-text"><?php _e( 'Automatically open chatbot when page loads.', 'seo-forge' ); ?></small>
							</div>

							<div class="form-group">
								<label class="toggle-label">
									<input type="checkbox" name="sound_enabled" value="1" <?php checked( $settings['sound_enabled'] ); ?> />
									<span class="toggle-slider"></span>
									<?php _e( 'Sound Notifications', 'seo-forge' ); ?>
								</label>
								<small class="form-text"><?php _e( 'Play sound when receiving messages.', 'seo-forge' ); ?></small>
							</div>
						</div>

						<div class="form-group">
							<label class="toggle-label">
								<input type="checkbox" name="show_branding" value="1" <?php checked( $settings['show_branding'] ); ?> />
								<span class="toggle-slider"></span>
								<?php _e( 'Show SEO Forge Branding', 'seo-forge' ); ?>
							</label>
							<small class="form-text"><?php _e( 'Display "Powered by SEO Forge" in the chatbot.', 'seo-forge' ); ?></small>
						</div>
					</div>
				</div>

				<!-- Appearance Settings -->
				<div class="seo-forge-card">
					<div class="card-header">
						<h3><?php _e( 'Appearance & Theming', 'seo-forge' ); ?></h3>
						<p><?php _e( 'Customize the chatbot appearance to match your website.', 'seo-forge' ); ?></p>
					</div>

					<div class="card-body">
						<div class="form-row">
							<div class="form-group">
								<label for="theme"><?php _e( 'Theme', 'seo-forge' ); ?></label>
								<select id="theme" name="theme">
									<option value="auto" <?php selected( $settings['theme'], 'auto' ); ?>><?php _e( 'Auto (Follow System)', 'seo-forge' ); ?></option>
									<option value="light" <?php selected( $settings['theme'], 'light' ); ?>><?php _e( 'Light', 'seo-forge' ); ?></option>
									<option value="dark" <?php selected( $settings['theme'], 'dark' ); ?>><?php _e( 'Dark', 'seo-forge' ); ?></option>
									<option value="custom" <?php selected( $settings['theme'], 'custom' ); ?>><?php _e( 'Custom Colors', 'seo-forge' ); ?></option>
								</select>
							</div>

							<div class="form-group">
								<label for="primary_color"><?php _e( 'Primary Color', 'seo-forge' ); ?></label>
								<div class="color-picker-wrapper">
									<input type="color" id="primary_color" name="primary_color" value="<?php echo esc_attr( $settings['primary_color'] ); ?>" />
									<input type="text" class="color-text" value="<?php echo esc_attr( $settings['primary_color'] ); ?>" readonly />
								</div>
							</div>
						</div>

						<div class="form-row">
							<div class="form-group">
								<label for="secondary_color"><?php _e( 'Secondary Color', 'seo-forge' ); ?></label>
								<div class="color-picker-wrapper">
									<input type="color" id="secondary_color" name="secondary_color" value="<?php echo esc_attr( $settings['secondary_color'] ); ?>" />
									<input type="text" class="color-text" value="<?php echo esc_attr( $settings['secondary_color'] ); ?>" readonly />
								</div>
							</div>

							<div class="form-group">
								<label for="text_color"><?php _e( 'Text Color', 'seo-forge' ); ?></label>
								<div class="color-picker-wrapper">
									<input type="color" id="text_color" name="text_color" value="<?php echo esc_attr( $settings['text_color'] ); ?>" />
									<input type="text" class="color-text" value="<?php echo esc_attr( $settings['text_color'] ); ?>" readonly />
								</div>
							</div>
						</div>

						<div class="chatbot-preview">
							<h4><?php _e( 'Preview', 'seo-forge' ); ?></h4>
							<div class="preview-container">
								<div class="chatbot-preview-widget" id="chatbot-preview">
									<div class="chatbot-header">
										<div class="chatbot-avatar">
											<span class="dashicons dashicons-admin-users"></span>
										</div>
										<div class="chatbot-title">
											<h4><?php _e( 'SEO Assistant', 'seo-forge' ); ?></h4>
											<span class="status online"><?php _e( 'Online', 'seo-forge' ); ?></span>
										</div>
									</div>
									<div class="chatbot-messages">
										<div class="message bot-message">
											<div class="message-content">
												<?php echo esc_html( $settings['welcome_message'] ); ?>
											</div>
										</div>
										<div class="message user-message">
											<div class="message-content">
												<?php _e( 'How can I improve my SEO?', 'seo-forge' ); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Messages Settings -->
				<div class="seo-forge-card">
					<div class="card-header">
						<h3><?php _e( 'Messages & Content', 'seo-forge' ); ?></h3>
						<p><?php _e( 'Customize chatbot messages and responses.', 'seo-forge' ); ?></p>
					</div>

					<div class="card-body">
						<div class="form-group">
							<label for="welcome_message"><?php _e( 'Welcome Message', 'seo-forge' ); ?></label>
							<textarea id="welcome_message" name="welcome_message" rows="3"><?php echo esc_textarea( $settings['welcome_message'] ); ?></textarea>
							<small class="form-text"><?php _e( 'First message shown when chatbot opens.', 'seo-forge' ); ?></small>
						</div>

						<div class="form-group">
							<label for="offline_message"><?php _e( 'Offline Message', 'seo-forge' ); ?></label>
							<textarea id="offline_message" name="offline_message" rows="3"><?php echo esc_textarea( $settings['offline_message'] ); ?></textarea>
							<small class="form-text"><?php _e( 'Message shown when AI service is unavailable.', 'seo-forge' ); ?></small>
						</div>
					</div>
				</div>

				<!-- Display Rules -->
				<div class="seo-forge-card">
					<div class="card-header">
						<h3><?php _e( 'Display Rules', 'seo-forge' ); ?></h3>
						<p><?php _e( 'Control where and to whom the chatbot is shown.', 'seo-forge' ); ?></p>
					</div>

					<div class="card-body">
						<div class="form-row">
							<div class="form-group">
								<label for="pages"><?php _e( 'Show on Pages', 'seo-forge' ); ?></label>
								<select id="pages" name="pages[]" multiple class="multiselect">
									<option value=""><?php _e( 'All Pages (Leave empty for all)', 'seo-forge' ); ?></option>
									<?php
									$pages = get_pages();
									foreach ( $pages as $page ) {
										$selected = in_array( $page->ID, $settings['pages'] ) ? 'selected' : '';
										echo '<option value="' . $page->ID . '" ' . $selected . '>' . esc_html( $page->post_title ) . '</option>';
									}
									?>
								</select>
								<small class="form-text"><?php _e( 'Select specific pages to show chatbot. Leave empty to show on all pages.', 'seo-forge' ); ?></small>
							</div>

							<div class="form-group">
								<label for="user_roles"><?php _e( 'Show to User Roles', 'seo-forge' ); ?></label>
								<select id="user_roles" name="user_roles[]" multiple class="multiselect">
									<option value=""><?php _e( 'All Users (Leave empty for all)', 'seo-forge' ); ?></option>
									<?php
									$roles = wp_roles()->get_names();
									foreach ( $roles as $role_key => $role_name ) {
										$selected = in_array( $role_key, $settings['user_roles'] ) ? 'selected' : '';
										echo '<option value="' . $role_key . '" ' . $selected . '>' . esc_html( $role_name ) . '</option>';
									}
									?>
									<option value="guest" <?php echo in_array( 'guest', $settings['user_roles'] ) ? 'selected' : ''; ?>><?php _e( 'Guests (Non-logged in)', 'seo-forge' ); ?></option>
								</select>
								<small class="form-text"><?php _e( 'Select user roles that can see the chatbot. Leave empty to show to all users.', 'seo-forge' ); ?></small>
							</div>
						</div>
					</div>
				</div>

				<!-- Knowledge Base -->
				<div class="seo-forge-card">
					<div class="card-header">
						<h3><?php _e( 'Knowledge Base', 'seo-forge' ); ?></h3>
						<p><?php _e( 'The chatbot uses comprehensive SEO knowledge to answer questions.', 'seo-forge' ); ?></p>
					</div>

					<div class="card-body">
						<div class="knowledge-stats">
							<div class="stat-item">
								<div class="stat-number">6</div>
								<div class="stat-label"><?php _e( 'Categories', 'seo-forge' ); ?></div>
							</div>
							<div class="stat-item">
								<div class="stat-number">25+</div>
								<div class="stat-label"><?php _e( 'Topics', 'seo-forge' ); ?></div>
							</div>
							<div class="stat-item">
								<div class="stat-number">100+</div>
								<div class="stat-label"><?php _e( 'Answers', 'seo-forge' ); ?></div>
							</div>
						</div>

						<div class="knowledge-categories">
							<div class="category-grid">
								<div class="category-item">
									<span class="dashicons dashicons-search"></span>
									<h4><?php _e( 'SEO Basics', 'seo-forge' ); ?></h4>
									<p><?php _e( 'Fundamental SEO concepts and best practices', 'seo-forge' ); ?></p>
								</div>
								<div class="category-item">
									<span class="dashicons dashicons-admin-network"></span>
									<h4><?php _e( 'Keyword Research', 'seo-forge' ); ?></h4>
									<p><?php _e( 'Finding and analyzing the right keywords', 'seo-forge' ); ?></p>
								</div>
								<div class="category-item">
									<span class="dashicons dashicons-edit"></span>
									<h4><?php _e( 'Content Optimization', 'seo-forge' ); ?></h4>
									<p><?php _e( 'Creating and optimizing content for search', 'seo-forge' ); ?></p>
								</div>
								<div class="category-item">
									<span class="dashicons dashicons-admin-tools"></span>
									<h4><?php _e( 'Technical SEO', 'seo-forge' ); ?></h4>
									<p><?php _e( 'Technical aspects of website optimization', 'seo-forge' ); ?></p>
								</div>
								<div class="category-item">
									<span class="dashicons dashicons-location"></span>
									<h4><?php _e( 'Local SEO', 'seo-forge' ); ?></h4>
									<p><?php _e( 'Optimizing for local search results', 'seo-forge' ); ?></p>
								</div>
								<div class="category-item">
									<span class="dashicons dashicons-chart-area"></span>
									<h4><?php _e( 'Analytics', 'seo-forge' ); ?></h4>
									<p><?php _e( 'Tracking and measuring SEO performance', 'seo-forge' ); ?></p>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Quick Actions -->
				<div class="seo-forge-card">
					<div class="card-header">
						<h3><?php _e( 'Quick Actions', 'seo-forge' ); ?></h3>
						<p><?php _e( 'Pre-configured actions users can trigger with one click.', 'seo-forge' ); ?></p>
					</div>

					<div class="card-body">
						<div class="quick-actions-grid">
							<div class="action-item">
								<span class="dashicons dashicons-search"></span>
								<h4><?php _e( 'Analyze Page', 'seo-forge' ); ?></h4>
								<p><?php _e( 'Quick SEO analysis of current page', 'seo-forge' ); ?></p>
							</div>
							<div class="action-item">
								<span class="dashicons dashicons-admin-network"></span>
								<h4><?php _e( 'Research Keywords', 'seo-forge' ); ?></h4>
								<p><?php _e( 'Find relevant keywords for content', 'seo-forge' ); ?></p>
							</div>
							<div class="action-item">
								<span class="dashicons dashicons-edit"></span>
								<h4><?php _e( 'Generate Content', 'seo-forge' ); ?></h4>
								<p><?php _e( 'Create SEO-optimized content', 'seo-forge' ); ?></p>
							</div>
							<div class="action-item">
								<span class="dashicons dashicons-format-image"></span>
								<h4><?php _e( 'Create Images', 'seo-forge' ); ?></h4>
								<p><?php _e( 'Generate AI-powered images', 'seo-forge' ); ?></p>
							</div>
							<div class="action-item">
								<span class="dashicons dashicons-heart"></span>
								<h4><?php _e( 'Site Health', 'seo-forge' ); ?></h4>
								<p><?php _e( 'Check overall website health', 'seo-forge' ); ?></p>
							</div>
							<div class="action-item">
								<span class="dashicons dashicons-location"></span>
								<h4><?php _e( 'Local SEO', 'seo-forge' ); ?></h4>
								<p><?php _e( 'Local business optimization tips', 'seo-forge' ); ?></p>
							</div>
						</div>
					</div>
				</div>

				<div class="form-actions">
					<button type="submit" name="submit" class="button button-primary button-large">
						<span class="dashicons dashicons-yes"></span>
						<?php _e( 'Save Chatbot Settings', 'seo-forge' ); ?>
					</button>
					<button type="button" id="test-chatbot" class="button button-secondary">
						<span class="dashicons dashicons-visibility"></span>
						<?php _e( 'Test Chatbot', 'seo-forge' ); ?>
					</button>
					<button type="button" id="reset-settings" class="button">
						<span class="dashicons dashicons-undo"></span>
						<?php _e( 'Reset to Defaults', 'seo-forge' ); ?>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<style>
.seo-forge-admin .toggle-label {
	display: flex;
	align-items: center;
	gap: 10px;
	cursor: pointer;
	font-weight: 600;
}

.seo-forge-admin .toggle-slider {
	position: relative;
	width: 50px;
	height: 24px;
	background: #ccc;
	border-radius: 12px;
	transition: background 0.3s;
}

.seo-forge-admin .toggle-slider:before {
	content: '';
	position: absolute;
	top: 2px;
	left: 2px;
	width: 20px;
	height: 20px;
	background: white;
	border-radius: 50%;
	transition: transform 0.3s;
}

.seo-forge-admin input[type="checkbox"]:checked + .toggle-slider {
	background: #0073aa;
}

.seo-forge-admin input[type="checkbox"]:checked + .toggle-slider:before {
	transform: translateX(26px);
}

.seo-forge-admin input[type="checkbox"] {
	display: none;
}

.seo-forge-admin .color-picker-wrapper {
	display: flex;
	gap: 10px;
	align-items: center;
}

.seo-forge-admin .color-picker-wrapper input[type="color"] {
	width: 50px;
	height: 40px;
	border: none;
	border-radius: 6px;
	cursor: pointer;
}

.seo-forge-admin .color-picker-wrapper .color-text {
	width: 100px;
	font-family: monospace;
}

.seo-forge-admin .chatbot-preview {
	margin-top: 30px;
	padding: 20px;
	background: #f8f9fa;
	border-radius: 8px;
}

.seo-forge-admin .preview-container {
	display: flex;
	justify-content: center;
	padding: 20px;
}

.seo-forge-admin .chatbot-preview-widget {
	width: 300px;
	background: white;
	border-radius: 12px;
	box-shadow: 0 4px 20px rgba(0,0,0,0.15);
	overflow: hidden;
}

.seo-forge-admin .chatbot-header {
	padding: 15px;
	background: var(--primary-color, #0073aa);
	color: white;
	display: flex;
	align-items: center;
	gap: 10px;
}

.seo-forge-admin .chatbot-avatar {
	width: 40px;
	height: 40px;
	background: rgba(255,255,255,0.2);
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
}

.seo-forge-admin .chatbot-title h4 {
	margin: 0;
	font-size: 16px;
}

.seo-forge-admin .chatbot-title .status {
	font-size: 12px;
	opacity: 0.8;
}

.seo-forge-admin .chatbot-messages {
	padding: 15px;
	max-height: 200px;
	overflow-y: auto;
}

.seo-forge-admin .message {
	margin-bottom: 15px;
}

.seo-forge-admin .message-content {
	padding: 10px 15px;
	border-radius: 18px;
	font-size: 14px;
	line-height: 1.4;
}

.seo-forge-admin .bot-message .message-content {
	background: #f1f1f1;
	color: #333;
	margin-right: 20px;
}

.seo-forge-admin .user-message .message-content {
	background: var(--primary-color, #0073aa);
	color: white;
	margin-left: 20px;
	text-align: right;
}

.seo-forge-admin .knowledge-stats {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 20px;
	margin-bottom: 30px;
}

.seo-forge-admin .category-grid,
.seo-forge-admin .quick-actions-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 20px;
}

.seo-forge-admin .category-item,
.seo-forge-admin .action-item {
	padding: 20px;
	border: 1px solid #ddd;
	border-radius: 8px;
	text-align: center;
	background: #fff;
}

.seo-forge-admin .category-item .dashicons,
.seo-forge-admin .action-item .dashicons {
	font-size: 32px;
	color: #0073aa;
	margin-bottom: 10px;
}

.seo-forge-admin .category-item h4,
.seo-forge-admin .action-item h4 {
	margin: 0 0 10px 0;
	font-size: 16px;
}

.seo-forge-admin .category-item p,
.seo-forge-admin .action-item p {
	margin: 0;
	color: #666;
	font-size: 14px;
}

.seo-forge-admin .multiselect {
	height: 120px;
}

@media (max-width: 768px) {
	.seo-forge-admin .knowledge-stats {
		grid-template-columns: 1fr;
	}
	
	.seo-forge-admin .category-grid,
	.seo-forge-admin .quick-actions-grid {
		grid-template-columns: 1fr;
	}
	
	.seo-forge-admin .chatbot-preview-widget {
		width: 100%;
		max-width: 300px;
	}
}
</style>

<script>
jQuery(document).ready(function($) {
	// Color picker updates
	$('input[type="color"]').on('change', function() {
		$(this).siblings('.color-text').val($(this).val());
		updatePreview();
	});
	
	// Theme change
	$('#theme').on('change', function() {
		updatePreview();
	});
	
	// Update preview
	function updatePreview() {
		const primaryColor = $('#primary_color').val();
		const secondaryColor = $('#secondary_color').val();
		const textColor = $('#text_color').val();
		
		$('#chatbot-preview').css({
			'--primary-color': primaryColor,
			'--secondary-color': secondaryColor,
			'--text-color': textColor
		});
		
		$('.chatbot-header').css('background', primaryColor);
		$('.user-message .message-content').css('background', primaryColor);
	}
	
	// Test chatbot
	$('#test-chatbot').on('click', function() {
		alert('<?php _e( 'Chatbot test functionality coming soon!', 'seo-forge' ); ?>');
	});
	
	// Reset settings
	$('#reset-settings').on('click', function() {
		if (confirm('<?php _e( 'Are you sure you want to reset all settings to defaults?', 'seo-forge' ); ?>')) {
			location.reload();
		}
	});
	
	// Initialize preview
	updatePreview();
});
</script>