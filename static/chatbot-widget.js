/**
 * Universal MCP Chatbot Widget
 * Embeddable customer service chatbot with Facebook Messenger-like UI
 * 
 * Usage:
 * <script src="https://your-server.vercel.app/static/chatbot-widget.js"></script>
 * <script>
 *   UMCPChatbot.init({
 *     serverUrl: 'https://your-server.vercel.app',
 *     websiteUrl: 'https://your-website.com',
 *     companyName: 'Your Company',
 *     primaryColor: '#667eea',
 *     position: 'bottom-right'
 *   });
 * </script>
 */

(function() {
    'use strict';

    const UMCPChatbot = {
        config: {
            serverUrl: '',
            websiteUrl: window.location.href,
            companyName: 'Customer Support',
            primaryColor: '#667eea',
            position: 'bottom-right',
            autoOpen: false,
            autoOpenDelay: 5000,
            showNotifications: true
        },

        isInitialized: false,
        isOpen: false,
        chatHistory: [],
        elements: {},

        init: function(options = {}) {
            if (this.isInitialized) return;

            // Merge config
            Object.assign(this.config, options);

            // Create and inject styles
            this.injectStyles();

            // Create widget HTML
            this.createWidget();

            // Initialize event listeners
            this.initializeEventListeners();

            // Auto-open if configured
            if (this.config.autoOpen) {
                setTimeout(() => this.openChat(), this.config.autoOpenDelay);
            }

            this.isInitialized = true;
        },

        injectStyles: function() {
            const style = document.createElement('style');
            style.textContent = `
                .umcp-chatbot-widget {
                    position: fixed;
                    ${this.config.position.includes('bottom') ? 'bottom: 20px;' : 'top: 20px;'}
                    ${this.config.position.includes('right') ? 'right: 20px;' : 'left: 20px;'}
                    z-index: 10000;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                }

                .umcp-chatbot-toggle {
                    width: 60px;
                    height: 60px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, ${this.config.primaryColor} 0%, ${this.adjustColor(this.config.primaryColor, -20)} 100%);
                    border: none;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-size: 24px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                    transition: all 0.3s ease;
                    position: relative;
                }

                .umcp-chatbot-toggle:hover {
                    transform: scale(1.1);
                    box-shadow: 0 6px 25px rgba(0, 0, 0, 0.2);
                }

                .umcp-chatbot-toggle.active {
                    background: #dc3545;
                }

                .umcp-notification-badge {
                    position: absolute;
                    top: -5px;
                    right: -5px;
                    width: 20px;
                    height: 20px;
                    background: #ff4757;
                    border-radius: 50%;
                    display: none;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-size: 12px;
                    font-weight: bold;
                    animation: umcp-pulse 2s infinite;
                }

                @keyframes umcp-pulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                    100% { transform: scale(1); }
                }

                .umcp-chatbot-window {
                    position: absolute;
                    ${this.config.position.includes('bottom') ? 'bottom: 80px;' : 'top: 80px;'}
                    ${this.config.position.includes('right') ? 'right: 0;' : 'left: 0;'}
                    width: 380px;
                    height: 600px;
                    background: white;
                    border-radius: 16px;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                    display: none;
                    flex-direction: column;
                    overflow: hidden;
                    transform: scale(0.8);
                    opacity: 0;
                    transition: all 0.3s ease;
                }

                .umcp-chatbot-window.show {
                    display: flex;
                    transform: scale(1);
                    opacity: 1;
                }

                .umcp-chatbot-header {
                    background: linear-gradient(135deg, ${this.config.primaryColor} 0%, ${this.adjustColor(this.config.primaryColor, -20)} 100%);
                    color: white;
                    padding: 16px 20px;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }

                .umcp-chatbot-avatar {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.2);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    position: relative;
                    font-size: 14px;
                }

                .umcp-online-dot {
                    position: absolute;
                    bottom: 2px;
                    right: 2px;
                    width: 12px;
                    height: 12px;
                    background: #42b883;
                    border: 2px solid white;
                    border-radius: 50%;
                }

                .umcp-chatbot-info h3 {
                    margin: 0;
                    font-size: 16px;
                    font-weight: 600;
                }

                .umcp-chatbot-status {
                    font-size: 12px;
                    opacity: 0.9;
                    margin-top: 2px;
                }

                .umcp-chatbot-close {
                    margin-left: auto;
                    background: none;
                    border: none;
                    color: white;
                    font-size: 20px;
                    cursor: pointer;
                    padding: 4px;
                    border-radius: 4px;
                    transition: background-color 0.2s;
                }

                .umcp-chatbot-close:hover {
                    background: rgba(255, 255, 255, 0.1);
                }

                .umcp-chatbot-messages {
                    flex: 1;
                    overflow-y: auto;
                    padding: 16px;
                    background: #f8f9fa;
                }

                .umcp-message {
                    display: flex;
                    margin-bottom: 12px;
                    animation: umcp-messageSlide 0.3s ease-out;
                }

                @keyframes umcp-messageSlide {
                    from { opacity: 0; transform: translateY(10px); }
                    to { opacity: 1; transform: translateY(0); }
                }

                .umcp-message.user {
                    justify-content: flex-end;
                }

                .umcp-message-bubble {
                    max-width: 80%;
                    padding: 10px 14px;
                    border-radius: 16px;
                    word-wrap: break-word;
                    font-size: 14px;
                    line-height: 1.4;
                }

                .umcp-message.bot .umcp-message-bubble {
                    background: white;
                    color: #333;
                    border-bottom-left-radius: 4px;
                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
                }

                .umcp-message.user .umcp-message-bubble {
                    background: linear-gradient(135deg, ${this.config.primaryColor} 0%, ${this.adjustColor(this.config.primaryColor, -20)} 100%);
                    color: white;
                    border-bottom-right-radius: 4px;
                }

                .umcp-message-time {
                    font-size: 10px;
                    opacity: 0.7;
                    margin-top: 4px;
                    text-align: center;
                }

                .umcp-typing-indicator {
                    display: flex;
                    align-items: center;
                    margin-bottom: 12px;
                    opacity: 0;
                    transition: opacity 0.3s;
                }

                .umcp-typing-indicator.show {
                    opacity: 1;
                }

                .umcp-typing-bubble {
                    background: white;
                    border-radius: 16px;
                    padding: 10px 14px;
                    border-bottom-left-radius: 4px;
                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
                }

                .umcp-typing-dots {
                    display: flex;
                    gap: 3px;
                }

                .umcp-typing-dot {
                    width: 6px;
                    height: 6px;
                    background: #999;
                    border-radius: 50%;
                    animation: umcp-typingDot 1.4s infinite;
                }

                .umcp-typing-dot:nth-child(2) { animation-delay: 0.2s; }
                .umcp-typing-dot:nth-child(3) { animation-delay: 0.4s; }

                @keyframes umcp-typingDot {
                    0%, 60%, 100% { transform: scale(1); opacity: 0.5; }
                    30% { transform: scale(1.2); opacity: 1; }
                }

                .umcp-quick-replies {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 6px;
                    padding: 0 16px 12px;
                }

                .umcp-quick-reply {
                    background: white;
                    border: 1px solid #e1e5e9;
                    border-radius: 16px;
                    padding: 6px 12px;
                    font-size: 12px;
                    cursor: pointer;
                    transition: all 0.2s;
                    color: ${this.config.primaryColor};
                }

                .umcp-quick-reply:hover {
                    background: #f0f2f5;
                    border-color: ${this.config.primaryColor};
                }

                .umcp-chatbot-input {
                    padding: 16px;
                    border-top: 1px solid #e1e5e9;
                    background: white;
                }

                .umcp-input-wrapper {
                    display: flex;
                    align-items: flex-end;
                    background: #f0f2f5;
                    border-radius: 20px;
                    padding: 8px 12px;
                    gap: 8px;
                }

                .umcp-message-input {
                    flex: 1;
                    border: none;
                    background: transparent;
                    outline: none;
                    font-size: 14px;
                    resize: none;
                    max-height: 80px;
                    padding: 4px 0;
                    font-family: inherit;
                }

                .umcp-send-btn {
                    width: 28px;
                    height: 28px;
                    border: none;
                    background: ${this.config.primaryColor};
                    color: white;
                    border-radius: 50%;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 12px;
                    transition: background-color 0.2s;
                }

                .umcp-send-btn:hover {
                    background: ${this.adjustColor(this.config.primaryColor, -10)};
                }

                .umcp-send-btn:disabled {
                    background: #ccc;
                    cursor: not-allowed;
                }

                .umcp-product-card {
                    background: white;
                    border-radius: 12px;
                    overflow: hidden;
                    margin: 8px 0;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                    max-width: 250px;
                }

                .umcp-product-image {
                    width: 100%;
                    height: 120px;
                    background: linear-gradient(135deg, ${this.config.primaryColor} 0%, ${this.adjustColor(this.config.primaryColor, -20)} 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-size: 32px;
                }

                .umcp-product-info {
                    padding: 12px;
                }

                .umcp-product-title {
                    font-weight: 600;
                    margin-bottom: 4px;
                    font-size: 14px;
                }

                .umcp-product-price {
                    color: ${this.config.primaryColor};
                    font-weight: 600;
                    margin-bottom: 4px;
                }

                .umcp-product-description {
                    color: #666;
                    font-size: 12px;
                    line-height: 1.3;
                }

                /* Mobile Responsive */
                @media (max-width: 480px) {
                    .umcp-chatbot-window {
                        width: calc(100vw - 40px);
                        height: calc(100vh - 100px);
                        ${this.config.position.includes('bottom') ? 'bottom: 80px;' : 'top: 80px;'}
                        right: 20px;
                        left: 20px;
                    }
                }

                /* Scrollbar */
                .umcp-chatbot-messages::-webkit-scrollbar {
                    width: 4px;
                }

                .umcp-chatbot-messages::-webkit-scrollbar-track {
                    background: transparent;
                }

                .umcp-chatbot-messages::-webkit-scrollbar-thumb {
                    background: #ccc;
                    border-radius: 2px;
                }
            `;
            document.head.appendChild(style);
        },

        createWidget: function() {
            const widget = document.createElement('div');
            widget.className = 'umcp-chatbot-widget';
            widget.innerHTML = `
                <button class="umcp-chatbot-toggle" id="umcpChatbotToggle">
                    💬
                    <div class="umcp-notification-badge" id="umcpNotificationBadge">1</div>
                </button>

                <div class="umcp-chatbot-window" id="umcpChatbotWindow">
                    <div class="umcp-chatbot-header">
                        <div class="umcp-chatbot-avatar">
                            ${this.getInitials(this.config.companyName)}
                            <div class="umcp-online-dot"></div>
                        </div>
                        <div class="umcp-chatbot-info">
                            <h3>${this.config.companyName}</h3>
                            <div class="umcp-chatbot-status">Online • Typically replies instantly</div>
                        </div>
                        <button class="umcp-chatbot-close" id="umcpChatbotClose">×</button>
                    </div>

                    <div class="umcp-chatbot-messages" id="umcpChatbotMessages">
                        <div class="umcp-message bot">
                            <div class="umcp-message-bubble">
                                👋 Hi there! Welcome to ${this.config.companyName}! I'm your AI assistant. How can I help you today?
                                <div class="umcp-message-time">Just now</div>
                            </div>
                        </div>
                    </div>

                    <div class="umcp-quick-replies" id="umcpQuickReplies">
                        <div class="umcp-quick-reply" data-message="What products do you sell?">🛍️ Products</div>
                        <div class="umcp-quick-reply" data-message="How can I place an order?">📦 Orders</div>
                        <div class="umcp-quick-reply" data-message="What are your shipping options?">🚚 Shipping</div>
                        <div class="umcp-quick-reply" data-message="Contact information">📞 Contact</div>
                    </div>

                    <div class="umcp-typing-indicator" id="umcpTypingIndicator">
                        <div class="umcp-typing-bubble">
                            <div class="umcp-typing-dots">
                                <div class="umcp-typing-dot"></div>
                                <div class="umcp-typing-dot"></div>
                                <div class="umcp-typing-dot"></div>
                            </div>
                        </div>
                    </div>

                    <div class="umcp-chatbot-input">
                        <div class="umcp-input-wrapper">
                            <textarea 
                                class="umcp-message-input" 
                                id="umcpMessageInput" 
                                placeholder="Type your message..."
                                rows="1"
                            ></textarea>
                            <button class="umcp-send-btn" id="umcpSendBtn" disabled>➤</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(widget);

            // Store element references
            this.elements = {
                toggle: document.getElementById('umcpChatbotToggle'),
                window: document.getElementById('umcpChatbotWindow'),
                close: document.getElementById('umcpChatbotClose'),
                messages: document.getElementById('umcpChatbotMessages'),
                input: document.getElementById('umcpMessageInput'),
                sendBtn: document.getElementById('umcpSendBtn'),
                typing: document.getElementById('umcpTypingIndicator'),
                quickReplies: document.getElementById('umcpQuickReplies'),
                badge: document.getElementById('umcpNotificationBadge')
            };
        },

        initializeEventListeners: function() {
            this.elements.toggle.addEventListener('click', () => this.toggleChat());
            this.elements.close.addEventListener('click', () => this.closeChat());
            this.elements.sendBtn.addEventListener('click', () => this.sendMessage());
            
            this.elements.input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });

            this.elements.input.addEventListener('input', () => {
                const hasText = this.elements.input.value.trim().length > 0;
                this.elements.sendBtn.disabled = !hasText;
                
                // Auto-resize
                this.elements.input.style.height = 'auto';
                this.elements.input.style.height = Math.min(this.elements.input.scrollHeight, 80) + 'px';
            });

            this.elements.quickReplies.addEventListener('click', (e) => {
                if (e.target.classList.contains('umcp-quick-reply')) {
                    const message = e.target.dataset.message;
                    this.elements.input.value = message;
                    this.sendMessage();
                }
            });

            // Close on outside click
            document.addEventListener('click', (e) => {
                if (this.isOpen && !e.target.closest('.umcp-chatbot-widget')) {
                    this.closeChat();
                }
            });
        },

        toggleChat: function() {
            if (this.isOpen) {
                this.closeChat();
            } else {
                this.openChat();
            }
        },

        openChat: function() {
            this.isOpen = true;
            this.elements.window.classList.add('show');
            this.elements.toggle.classList.add('active');
            this.elements.toggle.innerHTML = '×';
            this.hideNotificationBadge();
            this.scrollToBottom();
            
            // Focus input
            setTimeout(() => this.elements.input.focus(), 300);
        },

        closeChat: function() {
            this.isOpen = false;
            this.elements.window.classList.remove('show');
            this.elements.toggle.classList.remove('active');
            this.elements.toggle.innerHTML = '💬';
        },

        hideNotificationBadge: function() {
            this.elements.badge.style.display = 'none';
        },

        showNotificationBadge: function(count = 1) {
            if (!this.config.showNotifications) return;
            this.elements.badge.textContent = count;
            this.elements.badge.style.display = 'flex';
        },

        async sendMessage() {
            const message = this.elements.input.value.trim();
            if (!message) return;

            // Add user message
            this.addMessage(message, 'user');
            this.elements.input.value = '';
            this.elements.sendBtn.disabled = true;
            this.elements.input.style.height = 'auto';

            // Add to chat history
            this.chatHistory.push({
                sender: 'user',
                text: message,
                timestamp: new Date().toISOString()
            });

            // Show typing
            this.showTyping();

            try {
                // Call chatbot API
                const response = await fetch(`${this.config.serverUrl}/universal-mcp/chatbot`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        message: message,
                        website_url: this.config.websiteUrl,
                        chat_history: this.chatHistory.slice(-10)
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.hideTyping();
                        this.addMessage(data.response.text, 'bot');
                        
                        // Add to chat history
                        this.chatHistory.push({
                            sender: 'bot',
                            text: data.response.text,
                            timestamp: new Date().toISOString()
                        });

                        // Show products if any
                        if (data.response.products && data.response.products.length > 0) {
                            this.addProductCards(data.response.products);
                        }

                        // Update quick replies
                        if (data.response.suggestions) {
                            this.updateQuickReplies(data.response.suggestions);
                        }
                    } else {
                        throw new Error('API response not successful');
                    }
                } else {
                    throw new Error('API request failed');
                }
            } catch (error) {
                console.error('UMCP Chatbot API Error:', error);
                this.hideTyping();
                this.addMessage("I apologize, but I'm having trouble right now. Please try again or contact our support team.", 'bot');
            }
        },

        addMessage: function(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `umcp-message ${sender}`;
            
            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            
            messageDiv.innerHTML = `
                <div class="umcp-message-bubble">
                    ${text}
                    <div class="umcp-message-time">${time}</div>
                </div>
            `;

            this.elements.messages.appendChild(messageDiv);
            this.scrollToBottom();

            // Show notification if chat is closed
            if (!this.isOpen && sender === 'bot') {
                this.showNotificationBadge();
            }
        },

        addProductCards: function(products) {
            products.forEach(product => {
                const cardDiv = document.createElement('div');
                cardDiv.className = 'umcp-message bot';
                
                cardDiv.innerHTML = `
                    <div class="umcp-product-card">
                        <div class="umcp-product-image">🛍️</div>
                        <div class="umcp-product-info">
                            <div class="umcp-product-title">${product.name || product.title}</div>
                            <div class="umcp-product-price">${product.price}</div>
                            <div class="umcp-product-description">${product.description}</div>
                        </div>
                    </div>
                `;

                this.elements.messages.appendChild(cardDiv);
            });
            
            this.scrollToBottom();
        },

        updateQuickReplies: function(suggestions) {
            this.elements.quickReplies.innerHTML = '';
            suggestions.slice(0, 4).forEach(suggestion => {
                const replyDiv = document.createElement('div');
                replyDiv.className = 'umcp-quick-reply';
                replyDiv.dataset.message = this.getMessageForAction(suggestion.action);
                replyDiv.textContent = suggestion.text;
                this.elements.quickReplies.appendChild(replyDiv);
            });
        },

        getMessageForAction: function(action) {
            const actionMessages = {
                'products': 'What products do you sell?',
                'order': 'How can I place an order?',
                'contact': 'How can I contact you?',
                'pricing': 'What are your prices?',
                'shipping': 'What are your shipping options?',
                'payment': 'What payment methods do you accept?',
                'delivery': 'What are your delivery options?',
                'bulk': 'Tell me about bulk discounts',
                'quote': 'I need a price quote',
                'call': 'I want to call you',
                'email': 'I want to send an email',
                'human': 'I want to speak to a human',
                'help': 'I need help'
            };
            
            return actionMessages[action] || 'Tell me more';
        },

        showTyping: function() {
            this.elements.typing.classList.add('show');
            this.scrollToBottom();
        },

        hideTyping: function() {
            this.elements.typing.classList.remove('show');
        },

        scrollToBottom: function() {
            setTimeout(() => {
                this.elements.messages.scrollTop = this.elements.messages.scrollHeight;
            }, 100);
        },

        getInitials: function(name) {
            return name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2);
        },

        adjustColor: function(color, amount) {
            const usePound = color[0] === '#';
            const col = usePound ? color.slice(1) : color;
            const num = parseInt(col, 16);
            let r = (num >> 16) + amount;
            let g = (num >> 8 & 0x00FF) + amount;
            let b = (num & 0x0000FF) + amount;
            r = r > 255 ? 255 : r < 0 ? 0 : r;
            g = g > 255 ? 255 : g < 0 ? 0 : g;
            b = b > 255 ? 255 : b < 0 ? 0 : b;
            return (usePound ? '#' : '') + (r << 16 | g << 8 | b).toString(16).padStart(6, '0');
        }
    };

    // Expose to global scope
    window.UMCPChatbot = UMCPChatbot;

    // Auto-initialize if config is provided
    if (window.UMCPChatbotConfig) {
        UMCPChatbot.init(window.UMCPChatbotConfig);
    }

})();