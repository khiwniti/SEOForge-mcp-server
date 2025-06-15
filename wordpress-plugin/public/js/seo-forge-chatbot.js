(function($) {
    'use strict';

    /**
     * SEO-Forge Chatbot JavaScript
     * Handles chatbot functionality and user interactions
     */

    var chatbot = {
        initialized: false,
        isOpen: false,
        messageHistory: [],
        context: {},
        typingTimer: null,
        reconnectAttempts: 0,
        maxReconnectAttempts: 3
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        if (typeof seoForgeChatbot !== 'undefined' && seoForgeChatbot.enabled) {
            initChatbot();
        }
    });

    /**
     * Initialize the chatbot
     */
    function initChatbot() {
        if (chatbot.initialized) return;

        createChatbotHTML();
        bindEvents();
        loadChatHistory();
        chatbot.initialized = true;

        console.log('SEO-Forge Chatbot initialized');
    }

    /**
     * Create chatbot HTML structure
     */
    function createChatbotHTML() {
        var position = seoForgeChatbot.position || 'bottom-right';
        var color = seoForgeChatbot.color || '#007cba';
        var welcomeMessage = seoForgeChatbot.welcome_message || 'Hello! How can I help you today?';
        var placeholder = seoForgeChatbot.placeholder || 'Type your message...';

        var chatbotHTML = `
            <div id="seo-forge-chatbot" class="seo-forge-chatbot-container position-${position}">
                <div class="seo-forge-chatbot-toggle" style="background-color: ${color};">
                    <i class="fas fa-comment-dots"></i>
                </div>
                <div class="seo-forge-chatbot-window position-${position}">
                    <div class="seo-forge-chatbot-header" style="background: linear-gradient(135deg, ${color}, ${darkenColor(color, 20)});">
                        <h4>
                            ${seoForgeChatbot.strings.seo_assistant}
                            <div class="seo-forge-chatbot-status">
                                ${seoForgeChatbot.strings.online || 'Online'}
                            </div>
                        </h4>
                        <button class="seo-forge-chatbot-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="seo-forge-chatbot-messages">
                        <div class="seo-forge-chatbot-message bot-message">
                            <div class="message-avatar"></div>
                            <div class="message-content">
                                ${welcomeMessage}
                                <div class="seo-forge-quick-actions">
                                    <span class="seo-forge-quick-action" data-message="How can I improve my SEO?">
                                        💡 SEO Tips
                                    </span>
                                    <span class="seo-forge-quick-action" data-message="Generate content for my website">
                                        ✍️ Content Help
                                    </span>
                                    <span class="seo-forge-quick-action" data-message="Analyze my page SEO">
                                        🔍 SEO Analysis
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="seo-forge-chatbot-input-container">
                        <textarea class="seo-forge-chatbot-input" placeholder="${placeholder}" rows="1"></textarea>
                        <button class="seo-forge-chatbot-send" style="background: linear-gradient(135deg, ${color}, ${darkenColor(color, 20)});">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        $('body').append(chatbotHTML);

        // Auto-resize textarea
        autoResizeTextarea();
    }

    /**
     * Bind event handlers
     */
    function bindEvents() {
        var $chatbot = $('#seo-forge-chatbot');

        // Toggle chatbot
        $chatbot.on('click', '.seo-forge-chatbot-toggle', function() {
            toggleChatbot();
        });

        // Close chatbot
        $chatbot.on('click', '.seo-forge-chatbot-close', function() {
            closeChatbot();
        });

        // Send message
        $chatbot.on('click', '.seo-forge-chatbot-send', function() {
            sendMessage();
        });

        // Send message on Enter (but not Shift+Enter)
        $chatbot.on('keydown', '.seo-forge-chatbot-input', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Quick actions
        $chatbot.on('click', '.seo-forge-quick-action', function() {
            var message = $(this).data('message');
            if (message) {
                $('.seo-forge-chatbot-input').val(message);
                sendMessage();
            }
        });

        // Auto-resize textarea on input
        $chatbot.on('input', '.seo-forge-chatbot-input', function() {
            autoResizeTextarea();
        });

        // Close on outside click
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#seo-forge-chatbot').length && chatbot.isOpen) {
                closeChatbot();
            }
        });

        // Handle keyboard navigation
        $chatbot.on('keydown', function(e) {
            if (e.key === 'Escape' && chatbot.isOpen) {
                closeChatbot();
            }
        });
    }

    /**
     * Toggle chatbot open/close
     */
    function toggleChatbot() {
        if (chatbot.isOpen) {
            closeChatbot();
        } else {
            openChatbot();
        }
    }

    /**
     * Open chatbot
     */
    function openChatbot() {
        var $window = $('.seo-forge-chatbot-window');
        var $toggle = $('.seo-forge-chatbot-toggle');

        $window.show();
        setTimeout(function() {
            $window.addClass('open');
            $toggle.find('i').removeClass('fa-comment-dots').addClass('fa-times');
        }, 10);

        chatbot.isOpen = true;
        
        // Focus on input
        setTimeout(function() {
            $('.seo-forge-chatbot-input').focus();
        }, 300);

        // Scroll to bottom
        scrollToBottom();

        // Track interaction
        trackEvent('chatbot_opened');
    }

    /**
     * Close chatbot
     */
    function closeChatbot() {
        var $window = $('.seo-forge-chatbot-window');
        var $toggle = $('.seo-forge-chatbot-toggle');

        $window.removeClass('open');
        $toggle.find('i').removeClass('fa-times').addClass('fa-comment-dots');

        setTimeout(function() {
            $window.hide();
        }, 300);

        chatbot.isOpen = false;

        // Track interaction
        trackEvent('chatbot_closed');
    }

    /**
     * Send message
     */
    function sendMessage() {
        var $input = $('.seo-forge-chatbot-input');
        var message = $input.val().trim();

        if (!message) return;

        // Clear input
        $input.val('');
        autoResizeTextarea();

        // Add user message to chat
        addMessage(message, 'user');

        // Show typing indicator
        showTypingIndicator();

        // Send to server
        sendMessageToServer(message);

        // Track interaction
        trackEvent('message_sent', { message_length: message.length });
    }

    /**
     * Add message to chat
     */
    function addMessage(content, type, options = {}) {
        var $messages = $('.seo-forge-chatbot-messages');
        var timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        var messageClass = type === 'user' ? 'user-message' : 'bot-message';

        var messageHTML = `
            <div class="seo-forge-chatbot-message ${messageClass}">
                <div class="message-avatar"></div>
                <div class="message-content">
                    ${content}
                    ${options.showTime ? `<div class="message-time">${timestamp}</div>` : ''}
                </div>
            </div>
        `;

        $messages.append(messageHTML);
        scrollToBottom();

        // Store in history
        chatbot.messageHistory.push({
            content: content,
            type: type,
            timestamp: Date.now()
        });

        // Save to localStorage
        saveChatHistory();
    }

    /**
     * Show typing indicator
     */
    function showTypingIndicator() {
        var $messages = $('.seo-forge-chatbot-messages');
        
        var typingHTML = `
            <div class="seo-forge-chatbot-message bot-message typing-indicator">
                <div class="message-avatar"></div>
                <div class="seo-forge-typing-indicator">
                    <span>${seoForgeChatbot.strings.thinking}</span>
                    <div class="seo-forge-typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        `;

        $messages.append(typingHTML);
        scrollToBottom();
    }

    /**
     * Hide typing indicator
     */
    function hideTypingIndicator() {
        $('.typing-indicator').remove();
    }

    /**
     * Send message to server
     */
    function sendMessageToServer(message) {
        $.ajax({
            url: seoForgeChatbot.ajaxurl,
            type: 'POST',
            data: {
                action: 'seo_forge_chatbot_message',
                message: message,
                context: chatbot.context,
                nonce: seoForgeChatbot.nonce
            },
            timeout: 30000,
            success: function(response) {
                hideTypingIndicator();
                
                if (response.success) {
                    var botMessage = response.data.message;
                    var newContext = response.data.context || {};
                    
                    // Update context
                    chatbot.context = { ...chatbot.context, ...newContext };
                    
                    // Add bot response
                    addMessage(botMessage, 'bot', { showTime: true });
                    
                    // Reset reconnect attempts
                    chatbot.reconnectAttempts = 0;
                    
                    // Add quick actions if provided
                    if (response.data.quick_actions) {
                        addQuickActions(response.data.quick_actions);
                    }
                } else {
                    handleError(response.data.message || seoForgeChatbot.strings.error_message);
                }
            },
            error: function(xhr, status, error) {
                hideTypingIndicator();
                
                if (status === 'timeout') {
                    handleError('Request timed out. Please try again.');
                } else if (chatbot.reconnectAttempts < chatbot.maxReconnectAttempts) {
                    chatbot.reconnectAttempts++;
                    handleError(`Connection failed. Retrying... (${chatbot.reconnectAttempts}/${chatbot.maxReconnectAttempts})`);
                    
                    // Retry after delay
                    setTimeout(function() {
                        sendMessageToServer(message);
                    }, 2000 * chatbot.reconnectAttempts);
                } else {
                    handleError(seoForgeChatbot.strings.network_error);
                }
            }
        });
    }

    /**
     * Handle error messages
     */
    function handleError(errorMessage) {
        addMessage(`❌ ${errorMessage}`, 'bot');
        trackEvent('chatbot_error', { error: errorMessage });
    }

    /**
     * Add quick actions to the last bot message
     */
    function addQuickActions(actions) {
        if (!actions || !Array.isArray(actions)) return;

        var $lastMessage = $('.seo-forge-chatbot-message.bot-message').last();
        var $messageContent = $lastMessage.find('.message-content');

        var actionsHTML = '<div class="seo-forge-quick-actions">';
        actions.forEach(function(action) {
            actionsHTML += `<span class="seo-forge-quick-action" data-message="${action.message}">${action.label}</span>`;
        });
        actionsHTML += '</div>';

        $messageContent.append(actionsHTML);
    }

    /**
     * Auto-resize textarea
     */
    function autoResizeTextarea() {
        var $textarea = $('.seo-forge-chatbot-input');
        if ($textarea.length) {
            $textarea[0].style.height = 'auto';
            $textarea[0].style.height = Math.min($textarea[0].scrollHeight, 80) + 'px';
        }
    }

    /**
     * Scroll to bottom of messages
     */
    function scrollToBottom() {
        var $messages = $('.seo-forge-chatbot-messages');
        if ($messages.length) {
            $messages.animate({ scrollTop: $messages[0].scrollHeight }, 300);
        }
    }

    /**
     * Load chat history from localStorage
     */
    function loadChatHistory() {
        try {
            var history = localStorage.getItem('seo_forge_chat_history');
            if (history) {
                chatbot.messageHistory = JSON.parse(history);
                
                // Restore messages (limit to last 10 for performance)
                var recentMessages = chatbot.messageHistory.slice(-10);
                recentMessages.forEach(function(msg) {
                    if (msg.type !== 'user') { // Skip user messages on reload
                        addMessage(msg.content, msg.type, { showTime: false });
                    }
                });
            }
        } catch (e) {
            console.warn('Failed to load chat history:', e);
        }
    }

    /**
     * Save chat history to localStorage
     */
    function saveChatHistory() {
        try {
            // Keep only last 50 messages
            var historyToSave = chatbot.messageHistory.slice(-50);
            localStorage.setItem('seo_forge_chat_history', JSON.stringify(historyToSave));
        } catch (e) {
            console.warn('Failed to save chat history:', e);
        }
    }

    /**
     * Track events for analytics
     */
    function trackEvent(eventName, data = {}) {
        $.ajax({
            url: seoForgeChatbot.ajaxurl,
            type: 'POST',
            data: {
                action: 'seo_forge_track_chatbot_event',
                event: eventName,
                data: JSON.stringify(data),
                nonce: seoForgeChatbot.nonce
            },
            success: function() {
                // Event tracked successfully
            },
            error: function() {
                // Handle error silently
            }
        });
    }

    /**
     * Utility function to darken a color
     */
    function darkenColor(color, percent) {
        var num = parseInt(color.replace("#", ""), 16);
        var amt = Math.round(2.55 * percent);
        var R = (num >> 16) - amt;
        var G = (num >> 8 & 0x00FF) - amt;
        var B = (num & 0x0000FF) - amt;
        return "#" + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
            (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
            (B < 255 ? B < 1 ? 0 : B : 255)).toString(16).slice(1);
    }

    /**
     * Clear chat history
     */
    function clearChatHistory() {
        chatbot.messageHistory = [];
        localStorage.removeItem('seo_forge_chat_history');
        $('.seo-forge-chatbot-messages').empty();
        
        // Re-add welcome message
        var welcomeMessage = seoForgeChatbot.welcome_message || 'Hello! How can I help you today?';
        addMessage(welcomeMessage, 'bot');
    }

    /**
     * Update chatbot settings
     */
    function updateSettings(newSettings) {
        if (newSettings.color) {
            $('.seo-forge-chatbot-toggle').css('background-color', newSettings.color);
            $('.seo-forge-chatbot-header').css('background', `linear-gradient(135deg, ${newSettings.color}, ${darkenColor(newSettings.color, 20)})`);
            $('.seo-forge-chatbot-send').css('background', `linear-gradient(135deg, ${newSettings.color}, ${darkenColor(newSettings.color, 20)})`);
        }
        
        if (newSettings.position) {
            $('#seo-forge-chatbot').removeClass().addClass(`seo-forge-chatbot-container position-${newSettings.position}`);
            $('.seo-forge-chatbot-window').removeClass().addClass(`seo-forge-chatbot-window position-${newSettings.position}`);
        }
        
        if (newSettings.welcome_message) {
            seoForgeChatbot.welcome_message = newSettings.welcome_message;
        }
        
        if (newSettings.placeholder) {
            $('.seo-forge-chatbot-input').attr('placeholder', newSettings.placeholder);
        }
    }

    // Expose public methods
    window.SEOForgeChatbot = {
        open: openChatbot,
        close: closeChatbot,
        toggle: toggleChatbot,
        sendMessage: function(message) {
            $('.seo-forge-chatbot-input').val(message);
            sendMessage();
        },
        clearHistory: clearChatHistory,
        updateSettings: updateSettings,
        isOpen: function() {
            return chatbot.isOpen;
        }
    };

    // Handle page visibility changes
    $(document).on('visibilitychange', function() {
        if (document.hidden) {
            // Page is hidden, save state
            saveChatHistory();
        } else {
            // Page is visible, check for updates
            if (chatbot.isOpen) {
                scrollToBottom();
            }
        }
    });

})(jQuery);
