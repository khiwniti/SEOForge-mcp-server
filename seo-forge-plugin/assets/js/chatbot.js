/**
 * SEO Forge Chatbot JavaScript
 * Interactive AI chatbot with knowledge base integration
 */

(function($) {
    'use strict';

    // Global chatbot object
    window.SEOForgeChatbot = {
        isOpen: false,
        isMinimized: false,
        messageHistory: [],
        currentMessageId: null,
        settings: {},
        knowledgeBase: {},
        quickActions: [],
        
        // Initialize chatbot
        init: function() {
            this.settings = seoForgeChatbot.settings || {};
            this.knowledgeBase = seoForgeChatbot.knowledge_base || {};
            this.quickActions = seoForgeChatbot.quick_actions || [];
            
            this.bindEvents();
            this.loadChatHistory();
            this.checkAutoOpen();
            this.initializeAccessibility();
        },

        // Bind event handlers
        bindEvents: function() {
            const self = this;

            // Toggle chatbot
            $(document).on('click', '#chatbot-toggle', function() {
                self.toggleChatbot();
            });

            // Close chatbot
            $(document).on('click', '#close-btn', function() {
                self.closeChatbot();
            });

            // Minimize chatbot
            $(document).on('click', '#minimize-btn', function() {
                self.toggleMinimize();
            });

            // Send message
            $(document).on('click', '#send-btn', function() {
                self.sendMessage();
            });

            // Input field events
            $(document).on('keydown', '#chatbot-input-field', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    self.sendMessage();
                } else if (e.key === 'Enter' && e.shiftKey) {
                    // Allow new line
                    return;
                }
            });

            $(document).on('input', '#chatbot-input-field', function() {
                self.handleInputChange();
                self.autoResizeTextarea(this);
            });

            // Quick actions
            $(document).on('click', '.quick-action-btn', function() {
                const action = $(this).data('action');
                self.handleQuickAction(action);
            });

            // Action buttons in messages
            $(document).on('click', '.action-button', function(e) {
                const url = $(this).attr('href');
                if (url && url.startsWith(seoForgeChatbot.strings.admin_url || '/wp-admin/')) {
                    // Open admin links in new tab
                    e.preventDefault();
                    window.open(url, '_blank');
                }
            });

            // Clear chat
            $(document).on('click', '#clear-chat', function() {
                self.clearChat();
            });

            // Export chat
            $(document).on('click', '#export-chat', function() {
                self.exportChat();
            });

            // Feedback modal
            $(document).on('click', '.feedback-btn', function() {
                const feedback = $(this).data('feedback');
                self.handleFeedback(feedback);
            });

            $(document).on('click', '#close-feedback', function() {
                self.closeFeedbackModal();
            });

            $(document).on('click', '#submit-feedback', function() {
                self.submitFeedback();
            });

            // Close modal on outside click
            $(document).on('click', '.feedback-modal', function(e) {
                if (e.target === this) {
                    self.closeFeedbackModal();
                }
            });

            // Keyboard shortcuts
            $(document).on('keydown', function(e) {
                // Escape to close chatbot
                if (e.key === 'Escape' && self.isOpen) {
                    self.closeChatbot();
                }
                
                // Ctrl/Cmd + K to open chatbot
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    self.openChatbot();
                    $('#chatbot-input-field').focus();
                }
            });
        },

        // Toggle chatbot open/close
        toggleChatbot: function() {
            if (this.isOpen) {
                this.closeChatbot();
            } else {
                this.openChatbot();
            }
        },

        // Open chatbot
        openChatbot: function() {
            $('#chatbot-toggle').addClass('active');
            $('#chatbot-widget').addClass('open');
            this.isOpen = true;
            this.isMinimized = false;
            
            // Focus input field
            setTimeout(() => {
                $('#chatbot-input-field').focus();
            }, 300);

            // Hide notification badge
            $('#notification-badge').hide();

            // Track event
            this.trackEvent('chatbot_opened');
        },

        // Close chatbot
        closeChatbot: function() {
            $('#chatbot-toggle').removeClass('active');
            $('#chatbot-widget').removeClass('open');
            this.isOpen = false;
            this.isMinimized = false;

            // Track event
            this.trackEvent('chatbot_closed');
        },

        // Toggle minimize
        toggleMinimize: function() {
            if (this.isMinimized) {
                $('#chatbot-widget').removeClass('minimized');
                this.isMinimized = false;
            } else {
                $('#chatbot-widget').addClass('minimized');
                this.isMinimized = true;
            }

            // Track event
            this.trackEvent('chatbot_minimized', { minimized: this.isMinimized });
        },

        // Check auto open setting
        checkAutoOpen: function() {
            if (this.settings.auto_open && !this.hasInteracted()) {
                setTimeout(() => {
                    this.openChatbot();
                }, 2000);
            }
        },

        // Check if user has interacted before
        hasInteracted: function() {
            return localStorage.getItem('seo_forge_chatbot_interacted') === 'true';
        },

        // Mark as interacted
        markAsInteracted: function() {
            localStorage.setItem('seo_forge_chatbot_interacted', 'true');
        },

        // Handle input change
        handleInputChange: function() {
            const input = $('#chatbot-input-field').val().trim();
            const sendBtn = $('#send-btn');
            
            if (input.length > 0) {
                sendBtn.prop('disabled', false);
            } else {
                sendBtn.prop('disabled', true);
            }
        },

        // Auto resize textarea
        autoResizeTextarea: function(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 100) + 'px';
        },

        // Send message
        sendMessage: function() {
            const input = $('#chatbot-input-field');
            const message = input.val().trim();
            
            if (!message) return;

            // Add user message to chat
            this.addMessage(message, 'user');
            
            // Clear input
            input.val('').trigger('input');
            
            // Show typing indicator
            this.showTypingIndicator();
            
            // Process message
            this.processMessage(message);

            // Mark as interacted
            this.markAsInteracted();

            // Track event
            this.trackEvent('message_sent', { message_length: message.length });
        },

        // Add message to chat
        addMessage: function(content, type, options = {}) {
            const messagesContainer = $('#chatbot-messages');
            const messageId = 'msg_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            
            let messageHtml = '';
            
            if (type === 'user') {
                messageHtml = `
                    <div class="message user-message" data-message-id="${messageId}">
                        <div class="message-avatar">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 12C14.21 12 16 10.21 16 8C16 5.79 14.21 4 12 4C9.79 4 8 5.79 8 8C8 10.21 9.79 12 12 12ZM12 14C9.33 14 4 15.34 4 18V20H20V18C20 15.34 14.67 14 12 14Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="message-content">
                            <div class="message-text">${this.escapeHtml(content)}</div>
                            <div class="message-time">${timestamp}</div>
                        </div>
                    </div>
                `;
            } else {
                messageHtml = `
                    <div class="message bot-message" data-message-id="${messageId}">
                        <div class="message-avatar">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2Z" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="message-content">
                            <div class="message-text">${this.formatBotMessage(content, options)}</div>
                            <div class="message-time">${timestamp}</div>
                        </div>
                    </div>
                `;
            }
            
            // Hide quick actions if this is not the first message
            if (messagesContainer.find('.message').length > 1) {
                $('#quick-actions').hide();
            }
            
            messagesContainer.append(messageHtml);
            this.scrollToBottom();
            
            // Store in history
            this.messageHistory.push({
                id: messageId,
                content: content,
                type: type,
                timestamp: new Date().toISOString(),
                options: options
            });
            
            // Save to localStorage
            this.saveChatHistory();
            
            // Show feedback for bot messages
            if (type === 'bot' && options.type !== 'error') {
                setTimeout(() => {
                    this.showFeedbackOption(messageId);
                }, 2000);
            }

            return messageId;
        },

        // Format bot message with rich content
        formatBotMessage: function(content, options = {}) {
            let html = this.escapeHtml(content);
            
            // Add action buttons
            if (options.action_buttons && options.action_buttons.length > 0) {
                html += '<div class="action-buttons">';
                options.action_buttons.forEach(button => {
                    const url = button.url || '#';
                    html += `<a href="${url}" class="action-button" target="_blank">${this.escapeHtml(button.label)}</a>`;
                });
                html += '</div>';
            }
            
            // Add tips list
            if (options.tips && options.tips.length > 0) {
                html += '<ul class="tips-list">';
                options.tips.forEach(tip => {
                    html += `<li>${this.escapeHtml(tip)}</li>`;
                });
                html += '</ul>';
            }
            
            // Add content types list
            if (options.content_types && options.content_types.length > 0) {
                html += '<ul class="content-types-list">';
                options.content_types.forEach(type => {
                    html += `<li>${this.escapeHtml(type)}</li>`;
                });
                html += '</ul>';
            }
            
            // Add health checks list
            if (options.health_checks && options.health_checks.length > 0) {
                html += '<ul class="health-checks-list">';
                options.health_checks.forEach(check => {
                    html += `<li>${this.escapeHtml(check)}</li>`;
                });
                html += '</ul>';
            }
            
            // Add image types list
            if (options.image_types && options.image_types.length > 0) {
                html += '<ul class="content-types-list">';
                options.image_types.forEach(type => {
                    html += `<li>${this.escapeHtml(type)}</li>`;
                });
                html += '</ul>';
            }
            
            return html;
        },

        // Show typing indicator
        showTypingIndicator: function() {
            $('#typing-indicator').show();
            this.scrollToBottom();
        },

        // Hide typing indicator
        hideTypingIndicator: function() {
            $('#typing-indicator').hide();
        },

        // Process user message
        processMessage: function(message) {
            const self = this;
            
            // Simulate typing delay
            setTimeout(() => {
                self.hideTypingIndicator();
                
                // Send to server for processing
                $.ajax({
                    url: seoForgeChatbot.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'seo_forge_chatbot_query',
                        nonce: seoForgeChatbot.nonce,
                        query: message,
                        context: self.getCurrentContext(),
                        action_type: 'chat'
                    },
                    success: function(response) {
                        if (response.success) {
                            self.handleBotResponse(response.data);
                        } else {
                            self.handleError(response.data.message);
                        }
                    },
                    error: function() {
                        self.handleError(seoForgeChatbot.strings.error);
                    }
                });
            }, this.settings.typing_delay || 1000);
        },

        // Handle quick action
        handleQuickAction: function(action) {
            const self = this;
            
            // Add user message
            const actionLabels = {
                'analyze_page': seoForgeChatbot.strings.analyze_page || 'Analyze this page',
                'research_keywords': seoForgeChatbot.strings.research_keywords || 'Research keywords',
                'generate_content': seoForgeChatbot.strings.generate_content || 'Generate content',
                'generate_images': seoForgeChatbot.strings.generate_images || 'Generate images',
                'site_health': seoForgeChatbot.strings.site_health || 'Check site health',
                'local_seo': seoForgeChatbot.strings.local_seo || 'Local SEO help'
            };
            
            this.addMessage(actionLabels[action] || action, 'user');
            this.showTypingIndicator();
            
            // Process quick action
            $.ajax({
                url: seoForgeChatbot.ajaxurl,
                type: 'POST',
                data: {
                    action: 'seo_forge_chatbot_query',
                    nonce: seoForgeChatbot.nonce,
                    query: action,
                    context: this.getCurrentContext(),
                    action_type: 'quick_action'
                },
                success: function(response) {
                    self.hideTypingIndicator();
                    if (response.success) {
                        self.handleBotResponse(response.data);
                    } else {
                        self.handleError(response.data.message);
                    }
                },
                error: function() {
                    self.hideTypingIndicator();
                    self.handleError(seoForgeChatbot.strings.error);
                }
            });

            // Track event
            this.trackEvent('quick_action_used', { action: action });
        },

        // Handle bot response
        handleBotResponse: function(data) {
            this.currentMessageId = this.addMessage(data.message, 'bot', data);
            
            // Play notification sound
            this.playNotificationSound();
        },

        // Handle error
        handleError: function(message) {
            this.addMessage(message || seoForgeChatbot.strings.error, 'bot', { type: 'error' });
        },

        // Get current context
        getCurrentContext: function() {
            return {
                url: window.location.href,
                title: document.title,
                user_agent: navigator.userAgent,
                timestamp: new Date().toISOString()
            };
        },

        // Show feedback option
        showFeedbackOption: function(messageId) {
            // Implementation for feedback UI would go here
            // For now, we'll skip this to keep the code concise
        },

        // Handle feedback
        handleFeedback: function(feedback) {
            if (feedback === 'negative') {
                $('#feedback-comment').show();
            } else {
                this.submitFeedback(feedback);
            }
        },

        // Submit feedback
        submitFeedback: function(feedback = null) {
            const feedbackValue = feedback || $('.feedback-btn.active').data('feedback');
            const comment = $('#feedback-text').val();
            
            $.ajax({
                url: seoForgeChatbot.ajaxurl,
                type: 'POST',
                data: {
                    action: 'seo_forge_chatbot_feedback',
                    nonce: seoForgeChatbot.nonce,
                    message_id: this.currentMessageId,
                    feedback: feedbackValue,
                    comment: comment
                },
                success: (response) => {
                    if (response.success) {
                        this.addMessage(seoForgeChatbot.strings.feedback_thanks, 'bot');
                    }
                    this.closeFeedbackModal();
                }
            });

            // Track event
            this.trackEvent('feedback_submitted', { 
                feedback: feedbackValue, 
                has_comment: !!comment 
            });
        },

        // Close feedback modal
        closeFeedbackModal: function() {
            $('#feedback-modal').hide();
            $('#feedback-comment').hide();
            $('#feedback-text').val('');
            $('.feedback-btn').removeClass('active');
        },

        // Clear chat
        clearChat: function() {
            if (confirm(seoForgeChatbot.strings.confirm_clear || 'Clear all messages?')) {
                $('#chatbot-messages').empty();
                this.messageHistory = [];
                this.saveChatHistory();
                
                // Show welcome message and quick actions again
                this.addMessage(this.settings.welcome_message, 'bot');
                $('#quick-actions').show();

                // Track event
                this.trackEvent('chat_cleared');
            }
        },

        // Export chat
        exportChat: function() {
            const chatData = {
                messages: this.messageHistory,
                exported_at: new Date().toISOString(),
                site_url: window.location.origin
            };
            
            const blob = new Blob([JSON.stringify(chatData, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `seo-forge-chat-${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            // Track event
            this.trackEvent('chat_exported');
        },

        // Load chat history
        loadChatHistory: function() {
            const history = localStorage.getItem('seo_forge_chatbot_history');
            if (history) {
                try {
                    this.messageHistory = JSON.parse(history);
                    
                    // Restore messages (limit to last 10 for performance)
                    const recentMessages = this.messageHistory.slice(-10);
                    recentMessages.forEach(msg => {
                        if (msg.type === 'user' || msg.type === 'bot') {
                            this.addMessage(msg.content, msg.type, msg.options || {});
                        }
                    });
                    
                    // Hide quick actions if there are messages
                    if (recentMessages.length > 1) {
                        $('#quick-actions').hide();
                    }
                } catch (e) {
                    console.warn('Failed to load chat history:', e);
                }
            }
        },

        // Save chat history
        saveChatHistory: function() {
            try {
                // Keep only last 50 messages to prevent localStorage bloat
                const recentHistory = this.messageHistory.slice(-50);
                localStorage.setItem('seo_forge_chatbot_history', JSON.stringify(recentHistory));
            } catch (e) {
                console.warn('Failed to save chat history:', e);
            }
        },

        // Scroll to bottom
        scrollToBottom: function() {
            const messagesContainer = $('#chatbot-messages');
            messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
        },

        // Play notification sound
        playNotificationSound: function() {
            if (this.settings.sound_enabled && !this.isOpen) {
                const audio = $('#chatbot-notification-sound')[0];
                if (audio) {
                    audio.play().catch(() => {
                        // Ignore autoplay restrictions
                    });
                }
                
                // Show notification badge
                $('#notification-badge').show();
            }
        },

        // Initialize accessibility features
        initializeAccessibility: function() {
            // Add ARIA labels
            $('#chatbot-toggle').attr('aria-label', seoForgeChatbot.strings.toggle_chatbot || 'Toggle chatbot');
            $('#chatbot-input-field').attr('aria-label', seoForgeChatbot.strings.message_input || 'Type your message');
            $('#send-btn').attr('aria-label', seoForgeChatbot.strings.send_message || 'Send message');
            
            // Add role attributes
            $('#chatbot-messages').attr('role', 'log').attr('aria-live', 'polite');
            $('#chatbot-widget').attr('role', 'dialog').attr('aria-labelledby', 'chatbot-title');
        },

        // Track events (for analytics)
        trackEvent: function(eventName, properties = {}) {
            // Send to analytics if available
            if (typeof gtag !== 'undefined') {
                gtag('event', eventName, {
                    event_category: 'SEO Forge Chatbot',
                    ...properties
                });
            }
            
            // Custom tracking hook
            if (typeof window.seoForgeChatbotTrack === 'function') {
                window.seoForgeChatbotTrack(eventName, properties);
            }
        },

        // Escape HTML
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        if (typeof seoForgeChatbot !== 'undefined') {
            SEOForgeChatbot.init();
        }
    });

})(jQuery);