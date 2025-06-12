<?php
/**
 * Chatbot Frontend Template
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;

$position_class = 'chatbot-' . $settings['position'];
$theme_class = 'chatbot-theme-' . $settings['theme'];
?>

<!-- SEO Forge Chatbot -->
<div id="seo-forge-chatbot" class="seo-forge-chatbot <?php echo esc_attr( $position_class . ' ' . $theme_class ); ?>" 
     data-auto-open="<?php echo $settings['auto_open'] ? 'true' : 'false'; ?>"
     data-sound="<?php echo $settings['sound_enabled'] ? 'true' : 'false'; ?>"
     style="--primary-color: <?php echo esc_attr( $settings['primary_color'] ); ?>; --secondary-color: <?php echo esc_attr( $settings['secondary_color'] ); ?>; --text-color: <?php echo esc_attr( $settings['text_color'] ); ?>;">
	
	<!-- Chatbot Toggle Button -->
	<div class="chatbot-toggle" id="chatbot-toggle">
		<div class="toggle-icon">
			<svg class="icon-chat" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2ZM20 16H5.17L4 17.17V4H20V16Z" fill="currentColor"/>
				<circle cx="7" cy="10" r="1" fill="currentColor"/>
				<circle cx="12" cy="10" r="1" fill="currentColor"/>
				<circle cx="17" cy="10" r="1" fill="currentColor"/>
			</svg>
			<svg class="icon-close" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12L19 6.41Z" fill="currentColor"/>
			</svg>
		</div>
		<div class="notification-badge" id="notification-badge" style="display: none;">
			<span class="badge-count">1</span>
		</div>
	</div>

	<!-- Chatbot Widget -->
	<div class="chatbot-widget" id="chatbot-widget">
		<!-- Header -->
		<div class="chatbot-header">
			<div class="header-content">
				<div class="bot-avatar">
					<?php if ( ! empty( $settings['bot_avatar'] ) ) : ?>
						<img src="<?php echo esc_url( $settings['bot_avatar'] ); ?>" alt="<?php _e( 'SEO Assistant', 'seo-forge' ); ?>" />
					<?php else : ?>
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 1H5C3.89 1 3 1.89 3 3V19C3 20.1 3.9 21 5 21H11V19H5V3H13V9H21Z" fill="currentColor"/>
						</svg>
					<?php endif; ?>
				</div>
				<div class="bot-info">
					<h4 class="bot-name"><?php _e( 'SEO Assistant', 'seo-forge' ); ?></h4>
					<span class="bot-status online"><?php _e( 'Online', 'seo-forge' ); ?></span>
				</div>
			</div>
			<div class="header-actions">
				<button class="action-btn minimize-btn" id="minimize-btn" title="<?php _e( 'Minimize', 'seo-forge' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M19 13H5V11H19V13Z" fill="currentColor"/>
					</svg>
				</button>
				<button class="action-btn close-btn" id="close-btn" title="<?php _e( 'Close', 'seo-forge' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12L19 6.41Z" fill="currentColor"/>
					</svg>
				</button>
			</div>
		</div>

		<!-- Messages Container -->
		<div class="chatbot-messages" id="chatbot-messages">
			<!-- Welcome Message -->
			<div class="message bot-message welcome-message">
				<div class="message-avatar">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2Z" fill="currentColor"/>
					</svg>
				</div>
				<div class="message-content">
					<div class="message-text"><?php echo esc_html( $settings['welcome_message'] ); ?></div>
					<div class="message-time"><?php echo current_time( 'H:i' ); ?></div>
				</div>
			</div>

			<!-- Quick Actions -->
			<div class="quick-actions" id="quick-actions">
				<div class="quick-actions-title"><?php _e( 'Quick Actions:', 'seo-forge' ); ?></div>
				<div class="quick-actions-grid">
					<button class="quick-action-btn" data-action="analyze_page">
						<span class="dashicons dashicons-search"></span>
						<?php _e( 'Analyze Page', 'seo-forge' ); ?>
					</button>
					<button class="quick-action-btn" data-action="research_keywords">
						<span class="dashicons dashicons-admin-network"></span>
						<?php _e( 'Keywords', 'seo-forge' ); ?>
					</button>
					<button class="quick-action-btn" data-action="generate_content">
						<span class="dashicons dashicons-edit"></span>
						<?php _e( 'Content', 'seo-forge' ); ?>
					</button>
					<button class="quick-action-btn" data-action="generate_images">
						<span class="dashicons dashicons-format-image"></span>
						<?php _e( 'Images', 'seo-forge' ); ?>
					</button>
					<button class="quick-action-btn" data-action="site_health">
						<span class="dashicons dashicons-heart"></span>
						<?php _e( 'Site Health', 'seo-forge' ); ?>
					</button>
					<button class="quick-action-btn" data-action="local_seo">
						<span class="dashicons dashicons-location"></span>
						<?php _e( 'Local SEO', 'seo-forge' ); ?>
					</button>
				</div>
			</div>

			<!-- Typing Indicator -->
			<div class="typing-indicator" id="typing-indicator" style="display: none;">
				<div class="message-avatar">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2Z" fill="currentColor"/>
					</svg>
				</div>
				<div class="typing-dots">
					<span></span>
					<span></span>
					<span></span>
				</div>
			</div>
		</div>

		<!-- Input Area -->
		<div class="chatbot-input">
			<div class="input-container">
				<textarea 
					id="chatbot-input-field" 
					placeholder="<?php echo esc_attr( __( 'Ask me about SEO, content, keywords...', 'seo-forge' ) ); ?>"
					rows="1"
					maxlength="500"
				></textarea>
				<button class="send-btn" id="send-btn" disabled>
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M2.01 21L23 12L2.01 3L2 10L17 12L2 14L2.01 21Z" fill="currentColor"/>
					</svg>
				</button>
			</div>
			<div class="input-actions">
				<button class="action-link" id="clear-chat">
					<span class="dashicons dashicons-trash"></span>
					<?php _e( 'Clear Chat', 'seo-forge' ); ?>
				</button>
				<button class="action-link" id="export-chat">
					<span class="dashicons dashicons-download"></span>
					<?php _e( 'Export', 'seo-forge' ); ?>
				</button>
			</div>
		</div>

		<!-- Footer -->
		<?php if ( $settings['show_branding'] ) : ?>
		<div class="chatbot-footer">
			<div class="powered-by">
				<?php _e( 'Powered by', 'seo-forge' ); ?> 
				<a href="https://seoforge.ai" target="_blank" rel="noopener">
					<strong>SEO Forge</strong>
				</a>
			</div>
		</div>
		<?php endif; ?>
	</div>

	<!-- Feedback Modal -->
	<div class="feedback-modal" id="feedback-modal" style="display: none;">
		<div class="modal-content">
			<div class="modal-header">
				<h4><?php _e( 'Was this helpful?', 'seo-forge' ); ?></h4>
				<button class="close-modal" id="close-feedback">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12L19 6.41Z" fill="currentColor"/>
					</svg>
				</button>
			</div>
			<div class="modal-body">
				<div class="feedback-buttons">
					<button class="feedback-btn positive" data-feedback="positive">
						<span class="dashicons dashicons-thumbs-up"></span>
						<?php _e( 'Yes', 'seo-forge' ); ?>
					</button>
					<button class="feedback-btn negative" data-feedback="negative">
						<span class="dashicons dashicons-thumbs-down"></span>
						<?php _e( 'No', 'seo-forge' ); ?>
					</button>
				</div>
				<div class="feedback-comment" id="feedback-comment" style="display: none;">
					<textarea placeholder="<?php _e( 'How can we improve? (optional)', 'seo-forge' ); ?>" id="feedback-text"></textarea>
					<button class="submit-feedback" id="submit-feedback"><?php _e( 'Submit', 'seo-forge' ); ?></button>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Audio for notifications -->
<audio id="chatbot-notification-sound" preload="auto" style="display: none;">
	<source src="<?php echo SEO_FORGE_URL; ?>assets/sounds/notification.mp3" type="audio/mpeg">
	<source src="<?php echo SEO_FORGE_URL; ?>assets/sounds/notification.ogg" type="audio/ogg">
</audio>