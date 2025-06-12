/**
 * SEO Forge Admin JavaScript
 * Professional UI interactions and functionality with Progress Bars
 */

(function($) {
    'use strict';

    // Global SEO Forge object
    window.SEOForge = window.SEOForge || {};
    
    /**
     * Debug logging system for API calls
     */
    SEOForge.Debug = {
        enabled: true, // Set to false to disable logging
        apiCalls: [],
        
        log: function(level, message, data) {
            if (!this.enabled) return;
            
            const timestamp = new Date().toISOString();
            const logEntry = {
                timestamp: timestamp,
                level: level,
                message: message,
                data: data || null
            };
            
            // Store in memory for debugging
            this.apiCalls.push(logEntry);
            
            // Console output with styling
            const style = this.getLogStyle(level);
            console.group(`%c[SEO Forge ${level.toUpperCase()}] ${timestamp}`, style);
            console.log(`%c${message}`, 'font-weight: bold;');
            if (data) {
                console.log('Data:', data);
            }
            console.groupEnd();
        },
        
        getLogStyle: function(level) {
            const styles = {
                'info': 'color: #2196F3; font-weight: bold;',
                'success': 'color: #4CAF50; font-weight: bold;',
                'warning': 'color: #FF9800; font-weight: bold;',
                'error': 'color: #F44336; font-weight: bold;',
                'api': 'color: #9C27B0; font-weight: bold;',
                'progress': 'color: #00BCD4; font-weight: bold;'
            };
            return styles[level] || 'color: #333; font-weight: bold;';
        },
        
        apiStart: function(endpoint, params) {
            this.log('api', `🚀 API Call Started: ${endpoint}`, {
                endpoint: endpoint,
                parameters: params,
                startTime: performance.now()
            });
        },
        
        apiSuccess: function(endpoint, response, startTime) {
            const duration = performance.now() - startTime;
            this.log('success', `✅ API Call Successful: ${endpoint} (${duration.toFixed(2)}ms)`, {
                endpoint: endpoint,
                response: response,
                duration: duration
            });
        },
        
        apiError: function(endpoint, error, startTime) {
            const duration = performance.now() - startTime;
            this.log('error', `❌ API Call Failed: ${endpoint} (${duration.toFixed(2)}ms)`, {
                endpoint: endpoint,
                error: error,
                duration: duration
            });
        },
        
        progress: function(step, percentage, details) {
            this.log('progress', `📊 Progress Update: ${step} (${percentage}%)`, {
                step: step,
                percentage: percentage,
                details: details
            });
        },
        
        getReport: function() {
            return {
                totalCalls: this.apiCalls.length,
                calls: this.apiCalls,
                summary: this.getSummary()
            };
        },
        
        getSummary: function() {
            const summary = {
                total: this.apiCalls.length,
                success: 0,
                error: 0,
                api: 0,
                progress: 0
            };
            
            this.apiCalls.forEach(call => {
                if (summary[call.level] !== undefined) {
                    summary[call.level]++;
                }
            });
            
            return summary;
        }
    };

    /**
     * Initialize SEO Forge admin functionality
     */
    SEOForge.init = function() {
        SEOForge.Debug.log('info', '🚀 SEO Forge Admin Initializing...', {
            version: '1.6.1',
            userAgent: navigator.userAgent,
            timestamp: new Date().toISOString()
        });
        
        this.bindEvents();
        this.initCharacterCounters();
        this.initTooltips();
        this.initProgressBars();
        this.initMenuIcon();
        this.checkAPIConnection();
        
        SEOForge.Debug.log('success', '✅ SEO Forge Admin Initialized Successfully');
        
        // Add debug console commands
        this.addDebugCommands();
    };
    
    /**
     * Add debug commands to console
     */
    SEOForge.addDebugCommands = function() {
        window.SEOForgeDebug = {
            getReport: () => SEOForge.Debug.getReport(),
            clearLogs: () => { SEOForge.Debug.apiCalls = []; console.log('Debug logs cleared'); },
            enableLogging: () => { SEOForge.Debug.enabled = true; console.log('Debug logging enabled'); },
            disableLogging: () => { SEOForge.Debug.enabled = false; console.log('Debug logging disabled'); },
            testAPI: () => SEOForge.testConnection()
        };
        
        SEOForge.Debug.log('info', '🔧 Debug commands available: SEOForgeDebug.getReport(), SEOForgeDebug.clearLogs(), etc.');
        
        // Add debug panel to admin pages
        this.addDebugPanel();
    };
    
    /**
     * Add debug panel to admin interface
     */
    SEOForge.addDebugPanel = function() {
        if ($('.seo-forge-debug-panel').length) return;
        
        const debugPanel = `
            <div class="seo-forge-debug-panel" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; background: #fff; border: 1px solid #ddd; border-radius: 5px; padding: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 300px; display: none;">
                <div class="debug-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <strong>🔧 SEO Forge Debug</strong>
                    <button class="debug-close" style="background: none; border: none; font-size: 16px; cursor: pointer;">×</button>
                </div>
                <div class="debug-stats" style="font-size: 12px; margin-bottom: 10px;">
                    <div>API Calls: <span class="debug-api-count">0</span></div>
                    <div>Success: <span class="debug-success-count">0</span></div>
                    <div>Errors: <span class="debug-error-count">0</span></div>
                </div>
                <div class="debug-actions" style="display: flex; gap: 5px; flex-wrap: wrap;">
                    <button class="debug-btn debug-show-logs" style="font-size: 11px; padding: 3px 6px;">Show Logs</button>
                    <button class="debug-btn debug-clear-logs" style="font-size: 11px; padding: 3px 6px;">Clear</button>
                    <button class="debug-btn debug-test-api" style="font-size: 11px; padding: 3px 6px;">Test API</button>
                </div>
            </div>
            <div class="seo-forge-debug-toggle" style="position: fixed; bottom: 20px; right: 20px; z-index: 10000; background: #0073aa; color: white; border: none; border-radius: 50%; width: 40px; height: 40px; cursor: pointer; font-size: 14px;">🔧</div>
        `;
        
        $('body').append(debugPanel);
        
        // Bind debug panel events
        $('.seo-forge-debug-toggle').on('click', function() {
            $('.seo-forge-debug-panel').toggle();
            SEOForge.updateDebugStats();
        });
        
        $('.debug-close').on('click', function() {
            $('.seo-forge-debug-panel').hide();
        });
        
        $('.debug-show-logs').on('click', function() {
            console.group('🔧 SEO Forge Debug Report');
            console.log(SEOForge.Debug.getReport());
            console.groupEnd();
        });
        
        $('.debug-clear-logs').on('click', function() {
            SEOForge.Debug.apiCalls = [];
            SEOForge.updateDebugStats();
            console.log('🧹 Debug logs cleared');
        });
        
        $('.debug-test-api').on('click', function() {
            SEOForge.testConnection({ preventDefault: () => {} });
        });
        
        // Update stats every 5 seconds
        setInterval(() => {
            if ($('.seo-forge-debug-panel').is(':visible')) {
                SEOForge.updateDebugStats();
            }
        }, 5000);
    };
    
    /**
     * Update debug panel statistics
     */
    SEOForge.updateDebugStats = function() {
        const summary = SEOForge.Debug.getSummary();
        $('.debug-api-count').text(summary.total);
        $('.debug-success-count').text(summary.success);
        $('.debug-error-count').text(summary.error);
    };

    /**
     * Initialize menu icon
     */
    SEOForge.initMenuIcon = function() {
        // Ensure the SEO Forge menu icon is visible
        const menuItem = $('#adminmenu a[href="admin.php?page=seo-forge"]').parent();
        
        if (menuItem.length) {
            // Add specific class for styling
            menuItem.addClass('menu-icon-seo-forge');
            
            // Ensure dashicon is properly loaded
            const iconElement = menuItem.find('.wp-menu-image');
            if (iconElement.length && !iconElement.hasClass('dashicons-chart-area')) {
                iconElement.addClass('dashicons-chart-area');
            }
            
            SEOForge.Debug.log('info', '🎨 Menu icon initialized', {
                menuItem: menuItem.length,
                iconElement: iconElement.length
            });
        } else {
            SEOForge.Debug.log('warning', '⚠️ Menu item not found for icon initialization');
        }
    };

    /**
     * Initialize progress bar system
     */
    SEOForge.initProgressBars = function() {
        // Create progress bar container if it doesn't exist
        if (!$('.seo-forge-progress-container').length) {
            $('body').append(`
                <div class="seo-forge-progress-container" style="display: none;">
                    <div class="seo-forge-progress-overlay"></div>
                    <div class="seo-forge-progress-modal">
                        <div class="progress-header">
                            <h3 class="progress-title">Processing...</h3>
                            <div class="progress-status">Initializing...</div>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 0%"></div>
                            </div>
                            <div class="progress-percentage">0%</div>
                        </div>
                        <div class="progress-details">
                            <div class="progress-step">Starting process...</div>
                            <div class="progress-time">Estimated time: Calculating...</div>
                        </div>
                        <button type="button" class="seo-forge-cancel-request" style="display: none;">Cancel Request</button>
                    </div>
                </div>
            `);
        }
    };

    /**
     * Show progress bar with steps
     */
    SEOForge.showProgress = function(title, steps) {
        const $container = $('.seo-forge-progress-container');
        const $modal = $('.seo-forge-progress-modal');
        
        $container.find('.progress-title').text(title);
        $container.find('.progress-status').text('Starting...');
        $container.find('.progress-fill').css('width', '0%');
        $container.find('.progress-percentage').text('0%');
        $container.find('.progress-step').text('Initializing...');
        $container.find('.progress-time').text('Estimated time: Calculating...');
        
        $container.show();
        
        // Store steps for progress tracking
        SEOForge.progressSteps = steps || ['Connecting to API', 'Processing request', 'Generating results', 'Finalizing'];
        SEOForge.currentStep = 0;
        SEOForge.startTime = Date.now();
        
        return $container;
    };

    /**
     * Update progress bar
     */
    SEOForge.updateProgress = function(percentage, step, status) {
        const $container = $('.seo-forge-progress-container');
        
        if (percentage !== undefined) {
            $container.find('.progress-fill').css('width', percentage + '%');
            $container.find('.progress-percentage').text(Math.round(percentage) + '%');
        }
        
        if (step !== undefined) {
            $container.find('.progress-step').text(step);
            SEOForge.currentStep++;
        }
        
        if (status !== undefined) {
            $container.find('.progress-status').text(status);
        }
        
        // Update estimated time
        const elapsed = Date.now() - SEOForge.startTime;
        const estimated = (elapsed / (percentage || 1)) * 100;
        const remaining = Math.max(0, estimated - elapsed);
        
        if (remaining > 0) {
            $container.find('.progress-time').text(`Estimated time remaining: ${Math.ceil(remaining / 1000)}s`);
        } else {
            $container.find('.progress-time').text('Almost done...');
        }
    };

    /**
     * Hide progress bar
     */
    SEOForge.hideProgress = function() {
        $('.seo-forge-progress-container').hide();
    };

    /**
     * Enhanced AJAX request with progress tracking and comprehensive logging
     */
    SEOForge.ajaxWithProgress = function(options) {
        const steps = options.steps || ['Connecting', 'Processing', 'Completing'];
        const title = options.title || 'Processing Request';
        const startTime = performance.now();
        const endpoint = options.data?.action || options.url || 'unknown';
        
        // Log API call start
        SEOForge.Debug.apiStart(endpoint, {
            url: options.url,
            method: options.type || options.method || 'POST',
            data: options.data,
            title: title,
            steps: steps
        });
        
        // Show progress
        SEOForge.showProgress(title, steps);
        
        // Simulate progress for better UX
        let progress = 0;
        const progressInterval = setInterval(() => {
            if (progress < 90) {
                progress += Math.random() * 15;
                const currentStep = steps[Math.floor((progress / 100) * steps.length)] || steps[steps.length - 1];
                SEOForge.updateProgress(progress, currentStep);
                
                // Log progress updates
                SEOForge.Debug.progress(currentStep, Math.round(progress), {
                    endpoint: endpoint,
                    elapsed: performance.now() - startTime
                });
            }
        }, 500);
        
        // Enhanced AJAX options
        const ajaxOptions = {
            ...options,
            beforeSend: function(xhr) {
                SEOForge.Debug.log('info', `📤 Sending request to: ${endpoint}`, {
                    url: options.url,
                    headers: xhr.getAllResponseHeaders ? xhr.getAllResponseHeaders() : 'N/A',
                    requestData: options.data
                });
                
                // Add CORS headers
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                if (options.beforeSend) options.beforeSend(xhr);
            },
            success: function(response) {
                clearInterval(progressInterval);
                SEOForge.updateProgress(100, 'Complete', 'Success!');
                
                // Log successful response
                SEOForge.Debug.apiSuccess(endpoint, response, startTime);
                
                setTimeout(() => {
                    SEOForge.hideProgress();
                    if (options.success) options.success(response);
                }, 500);
            },
            error: function(xhr, status, error) {
                clearInterval(progressInterval);
                SEOForge.updateProgress(100, 'Error', 'Request failed');
                
                // Enhanced error handling with detailed logging
                let errorMessage = 'An error occurred';
                const errorDetails = {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error,
                    headers: xhr.getAllResponseHeaders ? xhr.getAllResponseHeaders() : 'N/A'
                };
                
                if (xhr.status === 403) {
                    errorMessage = 'Access denied. Please check your API configuration.';
                } else if (xhr.status === 404) {
                    errorMessage = 'API endpoint not found. Please check your server URL.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Please try again later.';
                } else if (xhr.status === 0) {
                    errorMessage = 'Network error. Please check your connection.';
                }
                
                // Log error details
                SEOForge.Debug.apiError(endpoint, errorDetails, startTime);
                
                setTimeout(() => {
                    SEOForge.hideProgress();
                    SEOForge.showNotice(errorMessage, 'error');
                    if (options.error) options.error(xhr, status, error);
                }, 1000);
            },
            complete: function(xhr, status) {
                clearInterval(progressInterval);
                
                SEOForge.Debug.log('info', `🏁 Request completed: ${endpoint}`, {
                    status: status,
                    duration: performance.now() - startTime,
                    responseHeaders: xhr.getAllResponseHeaders ? xhr.getAllResponseHeaders() : 'N/A'
                });
                
                if (options.complete) options.complete(xhr, status);
            }
        };
        
        return $.ajax(ajaxOptions);
    };

    /**
     * Bind event handlers
     */
    SEOForge.bindEvents = function() {
        // Test API connection
        $(document).on('click', '.seo-forge-test-connection', this.testConnection);
        
        // Generate content
        $(document).on('click', '.seo-forge-generate-content', this.generateContent);
        
        // Analyze SEO
        $(document).on('click', '.seo-forge-analyze-seo, .seo-forge-analyze-content', this.analyzeSEO);
        
        // Research keywords
        $(document).on('click', '.seo-forge-research-keywords', this.researchKeywords);
        
        // Clear forms
        $(document).on('click', '.seo-forge-clear-form', this.clearForm);
        
        // Copy to clipboard
        $(document).on('click', '.seo-forge-copy', this.copyToClipboard);
        
        // Form submissions
        $(document).on('submit', '.seo-forge-form', this.handleFormSubmit);
        
        // Use keyword
        $(document).on('click', '.seo-forge-use-keyword', this.useKeyword);
        
        // Use generated content
        $(document).on('click', '.seo-forge-use-content', this.useContent);
        
        // Chatbot management
        $(document).on('click', '.seo-forge-enable-chatbot', this.enableChatbot);
        $(document).on('click', '.seo-forge-reset-chatbot', this.resetChatbot);
    };

    /**
     * Initialize character counters
     */
    SEOForge.initCharacterCounters = function() {
        $('input[data-character-limit], textarea[data-character-limit]').each(function() {
            const $input = $(this);
            const limit = parseInt($input.data('character-limit'));
            const $counter = $('<div class="character-count"></div>');
            
            $input.after($counter);
            
            $input.on('input', function() {
                const length = $input.val().length;
                $counter.text(length + '/' + limit + ' characters');
                
                if (length > limit) {
                    $counter.addClass('over-limit');
                } else {
                    $counter.removeClass('over-limit');
                }
            });
            
            $input.trigger('input');
        });
    };

    /**
     * Initialize tooltips
     */
    SEOForge.initTooltips = function() {
        $('[data-tooltip]').each(function() {
            const $element = $(this);
            const tooltip = $element.data('tooltip');
            
            $element.attr('title', tooltip);
        });
    };

    /**
     * Check API connection status with logging
     */
    SEOForge.checkAPIConnection = function() {
        const $status = $('.seo-forge-status-indicator');
        if (!$status.length) {
            SEOForge.Debug.log('warning', '⚠️ Status indicator not found, skipping connection check');
            return;
        }
        
        const startTime = performance.now();
        SEOForge.Debug.log('info', '🔍 Checking API connection status...');
        
        $.ajax({
            url: seoForge.ajaxUrl,
            type: 'POST',
            data: {
                action: 'seo_forge_test_connection',
                nonce: seoForge.nonce
            },
            beforeSend: function() {
                SEOForge.Debug.log('info', '📤 Sending connection test request', {
                    url: seoForge.ajaxUrl,
                    action: 'seo_forge_test_connection'
                });
            },
            success: function(response) {
                const duration = performance.now() - startTime;
                SEOForge.Debug.log('success', `✅ Connection test completed (${duration.toFixed(2)}ms)`, {
                    response: response,
                    connected: response.success
                });
                
                if (response.success) {
                    $status.addClass('connected');
                } else {
                    $status.removeClass('connected');
                }
            },
            error: function(xhr, status, error) {
                const duration = performance.now() - startTime;
                SEOForge.Debug.log('error', `❌ Connection test failed (${duration.toFixed(2)}ms)`, {
                    status: xhr.status,
                    error: error,
                    responseText: xhr.responseText
                });
                
                $status.removeClass('connected');
            }
        });
    };

    /**
     * Test API connection
     */
    SEOForge.testConnection = function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const originalText = $button.text();
        
        $button.prop('disabled', true)
               .html('<span class="seo-forge-spinner"></span> ' + seoForge.strings.processing);
        
        SEOForge.ajaxWithProgress({
            url: seoForge.ajaxUrl,
            type: 'POST',
            title: 'Testing API Connection',
            steps: ['Connecting to server', 'Verifying credentials', 'Testing endpoints'],
            data: {
                action: 'seo_forge_test_connection',
                nonce: seoForge.nonce
            },
            success: function(response) {
                if (response.success) {
                    SEOForge.showNotice(response.data.message, 'success');
                    $('.seo-forge-status-indicator').addClass('connected');
                } else {
                    SEOForge.showNotice(response.data.message, 'error');
                    $('.seo-forge-status-indicator').removeClass('connected');
                }
            },
            error: function() {
                $('.seo-forge-status-indicator').removeClass('connected');
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    };

    /**
     * Generate content
     */
    SEOForge.generateContent = function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const $form = $button.closest('form, .seo-forge-form');
        const originalText = $button.text();
        
        const data = {
            action: 'seo_forge_generate_content',
            nonce: seoForge.nonce,
            keywords: $form.find('[name="keywords"]').val(),
            industry: $form.find('[name="industry"]').val(),
            content_type: $form.find('[name="content_type"]').val(),
            language: $form.find('[name="language"]').val()
        };
        
        if (!data.keywords) {
            SEOForge.showNotice('Please enter keywords for content generation.', 'warning');
            return;
        }
        
        $button.prop('disabled', true)
               .html('<span class="seo-forge-spinner"></span> ' + seoForge.strings.generating);
        
        SEOForge.ajaxWithProgress({
            url: seoForge.ajaxUrl,
            type: 'POST',
            title: 'Generating AI Content',
            steps: ['Analyzing keywords', 'Generating content', 'Optimizing for SEO', 'Finalizing output'],
            data: data,
            success: function(response) {
                if (response.success) {
                    SEOForge.displayContentResults(response.data);
                    SEOForge.showNotice(seoForge.strings.success, 'success');
                } else {
                    SEOForge.showNotice(response.data.message || seoForge.strings.error, 'error');
                }
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    };

    /**
     * Analyze SEO
     */
    SEOForge.analyzeSEO = function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const $form = $button.closest('form, .seo-forge-analyzer-form');
        const originalText = $button.text();
        
        const data = {
            action: 'seo_forge_analyze_seo',
            nonce: seoForge.nonce,
            analyze_url: $form.find('[name="analyze_url"]').val(),
            analyze_content: $form.find('[name="analyze_content"]').val(),
            focus_keyword: $form.find('[name="focus_keyword"]').val(),
            meta_title: $form.find('[name="meta_title"]').val(),
            meta_description: $form.find('[name="meta_description"]').val()
        };
        
        if (!data.analyze_content && !data.analyze_url) {
            SEOForge.showNotice('Please enter content or URL to analyze.', 'warning');
            return;
        }
        
        $button.prop('disabled', true)
               .html('<span class="seo-forge-spinner"></span> ' + seoForge.strings.analyzing);
        
        SEOForge.ajaxWithProgress({
            url: seoForge.ajaxUrl,
            type: 'POST',
            title: 'Analyzing SEO Performance',
            steps: ['Fetching content', 'Analyzing keywords', 'Checking meta tags', 'Generating recommendations'],
            data: data,
            success: function(response) {
                if (response.success) {
                    SEOForge.displaySEOResults(response.data);
                    SEOForge.showNotice(seoForge.strings.success, 'success');
                } else {
                    SEOForge.showNotice(response.data.message || seoForge.strings.error, 'error');
                }
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    };

    /**
     * Research keywords
     */
    SEOForge.researchKeywords = function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const $form = $button.closest('form, .seo-forge-keywords-form');
        const originalText = $button.text();
        
        const data = {
            action: 'seo_forge_research_keywords',
            nonce: seoForge.nonce,
            seed_keyword: $form.find('[name="seed_keyword"]').val(),
            research_language: $form.find('[name="research_language"]').val(),
            research_country: $form.find('[name="research_country"]').val(),
            keyword_limit: $form.find('[name="keyword_limit"]').val()
        };
        
        if (!data.seed_keyword) {
            SEOForge.showNotice('Please enter a seed keyword for research.', 'warning');
            return;
        }
        
        $button.prop('disabled', true)
               .html('<span class="seo-forge-spinner"></span> ' + seoForge.strings.researching);
        
        SEOForge.ajaxWithProgress({
            url: seoForge.ajaxUrl,
            type: 'POST',
            title: 'Researching Keywords',
            steps: ['Analyzing seed keyword', 'Finding related terms', 'Calculating metrics', 'Ranking results'],
            data: data,
            success: function(response) {
                if (response.success) {
                    SEOForge.displayKeywordResults(response.data);
                    SEOForge.showNotice(seoForge.strings.success, 'success');
                } else {
                    SEOForge.showNotice(response.data.message || seoForge.strings.error, 'error');
                }
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    };

    /**
     * Clear form
     */
    SEOForge.clearForm = function(e) {
        e.preventDefault();
        
        const $form = $(this).closest('form, .seo-forge-form');
        $form.find('input[type="text"], input[type="url"], textarea, select').val('');
        $form.find('.character-count').text('0/0 characters').removeClass('over-limit');
        $('.seo-forge-results, .seo-forge-content-results, .seo-forge-analyzer-results, .seo-forge-keywords-results').hide();
    };

    /**
     * Copy text to clipboard
     */
    SEOForge.copyToClipboard = function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const target = $button.data('target');
        const $target = $(target);
        
        if ($target.length) {
            $target.select();
            document.execCommand('copy');
            
            const originalText = $button.text();
            $button.text('Copied!');
            
            setTimeout(function() {
                $button.text(originalText);
            }, 2000);
        }
    };

    /**
     * Handle form submissions with comprehensive logging
     */
    SEOForge.handleFormSubmit = function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitButton = $form.find('[type="submit"]');
        const originalText = $submitButton.text();
        const formData = $form.serialize();
        const formAction = $form.attr('action') || seoForge.ajaxUrl;
        const startTime = performance.now();
        
        SEOForge.Debug.log('info', '📝 Form submission started', {
            formId: $form.attr('id'),
            formClass: $form.attr('class'),
            action: formAction,
            data: formData
        });
        
        $submitButton.prop('disabled', true)
                     .html('<span class="seo-forge-spinner"></span> ' + seoForge.strings.processing);
        
        $.ajax({
            url: formAction,
            type: 'POST',
            data: formData,
            beforeSend: function(xhr) {
                SEOForge.Debug.log('info', '📤 Sending form data', {
                    url: formAction,
                    serializedData: formData
                });
            },
            success: function(response) {
                const duration = performance.now() - startTime;
                SEOForge.Debug.log('success', `✅ Form submission successful (${duration.toFixed(2)}ms)`, {
                    response: response,
                    success: response.success
                });
                
                if (response.success) {
                    SEOForge.showNotice(response.data.message || seoForge.strings.success, 'success');
                } else {
                    SEOForge.showNotice(response.data.message || seoForge.strings.error, 'error');
                }
            },
            error: function(xhr, status, error) {
                const duration = performance.now() - startTime;
                SEOForge.Debug.log('error', `❌ Form submission failed (${duration.toFixed(2)}ms)`, {
                    status: xhr.status,
                    error: error,
                    responseText: xhr.responseText
                });
                
                SEOForge.showNotice(seoForge.strings.error, 'error');
            },
            complete: function(xhr, status) {
                const duration = performance.now() - startTime;
                SEOForge.Debug.log('info', `🏁 Form submission completed (${duration.toFixed(2)}ms)`, {
                    status: status,
                    finalDuration: duration
                });
                
                $submitButton.prop('disabled', false).text(originalText);
            }
        });
    };

    /**
     * Display content generation results
     */
    SEOForge.displayContentResults = function(data) {
        const $container = $('.seo-forge-content-results');
        if (!$container.length) return;
        
        let html = '<h3>Generated Content</h3>';
        
        if (data.content) {
            html += '<textarea readonly>' + data.content + '</textarea>';
            html += '<div class="seo-forge-form-group">';
            html += '<button type="button" class="seo-forge-button seo-forge-use-content">Use This Content</button>';
            html += '<button type="button" class="seo-forge-button secondary seo-forge-copy" data-target="textarea">Copy to Clipboard</button>';
            html += '</div>';
        }
        
        $container.html(html).show();
    };

    /**
     * Display SEO analysis results
     */
    SEOForge.displaySEOResults = function(data) {
        const $container = $('.seo-forge-analyzer-results');
        if (!$container.length) return;
        
        // Update score circle
        if (data.score !== undefined) {
            const $scoreNumber = $('.score-number');
            const $scoreCircle = $('.seo-score-circle');
            
            $scoreNumber.text(data.score);
            
            $scoreCircle.removeClass('good average poor');
            if (data.score >= 80) {
                $scoreCircle.addClass('good');
            } else if (data.score >= 60) {
                $scoreCircle.addClass('average');
            } else {
                $scoreCircle.addClass('poor');
            }
        }
        
        // Update recommendations
        if (data.recommendations) {
            const $recommendations = $('.recommendations-list');
            let html = '';
            
            data.recommendations.forEach(function(rec) {
                const priority = rec.priority || 'medium';
                html += '<div class="recommendation-item ' + priority + '-priority">';
                html += '<strong>' + rec.title + '</strong><br>';
                html += rec.description;
                html += '</div>';
            });
            
            $recommendations.html(html);
        }
        
        // Update checklist
        if (data.checklist) {
            const $checklist = $('.checklist-items');
            let html = '';
            
            data.checklist.forEach(function(item) {
                const status = item.passed ? 'passed' : 'failed';
                html += '<div class="checklist-item ' + status + '">';
                html += '<span class="status">' + (item.passed ? '✓' : '✗') + '</span>';
                html += item.text;
                html += '</div>';
            });
            
            $checklist.html(html);
        }
        
        $container.show();
    };

    /**
     * Display keyword research results
     */
    SEOForge.displayKeywordResults = function(data) {
        const $container = $('.seo-forge-keywords-results');
        if (!$container.length) return;
        
        if (data.keywords && data.keywords.length) {
            // Update stats
            $('#total-keywords').text(data.keywords.length);
            
            const avgVolume = data.keywords.reduce((sum, kw) => sum + (kw.search_volume || 0), 0) / data.keywords.length;
            $('#avg-volume').text(Math.round(avgVolume));
            
            const avgDifficulty = data.keywords.reduce((sum, kw) => sum + (kw.difficulty || 0), 0) / data.keywords.length;
            $('#avg-difficulty').text(Math.round(avgDifficulty));
            
            // Update table
            const $tbody = $('#keywords-table-body');
            let html = '';
            
            data.keywords.forEach(function(keyword, index) {
                const difficulty = keyword.difficulty || 0;
                let difficultyClass = 'easy';
                if (difficulty > 70) difficultyClass = 'hard';
                else if (difficulty > 40) difficultyClass = 'medium';
                
                html += '<tr>';
                html += '<td><input type="checkbox" class="keyword-checkbox" value="' + keyword.keyword + '"></td>';
                html += '<td>' + keyword.keyword + '</td>';
                html += '<td>' + (keyword.search_volume || 'N/A') + '</td>';
                html += '<td><span class="difficulty ' + difficultyClass + '">' + difficulty + '</span></td>';
                html += '<td>$' + (keyword.cpc || '0.00') + '</td>';
                html += '<td>' + (keyword.competition || 'Unknown') + '</td>';
                html += '<td><button type="button" class="seo-forge-button secondary seo-forge-use-keyword" data-keyword="' + keyword.keyword + '">Use</button></td>';
                html += '</tr>';
            });
            
            $tbody.html(html);
        }
        
        $container.show();
    };

    /**
     * Use keyword
     */
    SEOForge.useKeyword = function(e) {
        e.preventDefault();
        
        const keyword = $(this).data('keyword');
        const $keywordInput = $('[name="keywords"], [name="focus_keyword"]').first();
        
        if ($keywordInput.length) {
            $keywordInput.val(keyword);
            SEOForge.showNotice('Keyword "' + keyword + '" has been added.', 'success');
        }
    };

    /**
     * Use generated content
     */
    SEOForge.useContent = function(e) {
        e.preventDefault();
        
        const content = $('.seo-forge-content-results textarea').val();
        
        if (content) {
            // Try to find WordPress editor
            if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
                tinymce.activeEditor.setContent(content);
            } else if ($('#content').length) {
                $('#content').val(content);
            }
            
            SEOForge.showNotice('Content has been added to the editor.', 'success');
        }
    };

    /**
     * Show notification
     */
    SEOForge.showNotice = function(message, type) {
        type = type || 'info';
        
        const $notice = $('<div class="seo-forge-alert seo-forge-alert-' + type + '">' + message + '</div>');
        
        // Remove existing notices
        $('.seo-forge-alert').remove();
        
        // Add new notice
        $('.wrap').first().prepend($notice);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                $notice.remove();
            });
        }, 5000);
    };

    /**
     * Use generated content
     */
    SEOForge.useContent = function(e) {
        e.preventDefault();
        
        const $contentDiv = $(this).closest('.seo-forge-generated-content').find('.content-preview');
        const content = $contentDiv.html();
        
        // Try to insert into WordPress editor
        if (typeof wp !== 'undefined' && wp.editor) {
            wp.editor.insert(content);
            SEOForge.showNotice('Content inserted into editor!', 'success');
        } else if ($('#content').length) {
            $('#content').val(content);
            SEOForge.showNotice('Content inserted into editor!', 'success');
        } else {
            SEOForge.showNotice('Editor not found. Please copy the content manually.', 'warning');
        }
    };

    /**
     * Enable chatbot
     */
    SEOForge.enableChatbot = function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const originalText = $button.text();
        
        SEOForge.Debug.log('info', '🤖 Enabling chatbot...');
        
        $button.prop('disabled', true).text('Enabling...');
        
        SEOForge.ajaxWithProgress({
            url: seoForge.ajaxUrl,
            type: 'POST',
            data: {
                action: 'seo_forge_enable_chatbot',
                nonce: seoForge.nonce
            },
            title: 'Enabling Chatbot',
            steps: ['Initializing Settings', 'Enabling Chatbot', 'Complete'],
            success: function(response) {
                SEOForge.Debug.log('success', '✅ Chatbot enabled successfully', response);
                SEOForge.showNotice(response.data.message, 'success');
                
                // Reload page to show chatbot
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            },
            error: function(xhr, status, error) {
                SEOForge.Debug.log('error', '❌ Failed to enable chatbot', {
                    status: xhr.status,
                    error: error
                });
                SEOForge.showNotice('Failed to enable chatbot. Please try again.', 'error');
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    };

    /**
     * Reset chatbot settings
     */
    SEOForge.resetChatbot = function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to reset all chatbot settings to defaults?')) {
            return;
        }
        
        const $button = $(this);
        const originalText = $button.text();
        
        SEOForge.Debug.log('info', '🔄 Resetting chatbot settings...');
        
        $button.prop('disabled', true).text('Resetting...');
        
        SEOForge.ajaxWithProgress({
            url: seoForge.ajaxUrl,
            type: 'POST',
            data: {
                action: 'seo_forge_reset_chatbot',
                nonce: seoForge.nonce
            },
            title: 'Resetting Chatbot',
            steps: ['Clearing Settings', 'Applying Defaults', 'Complete'],
            success: function(response) {
                SEOForge.Debug.log('success', '✅ Chatbot settings reset successfully', response);
                SEOForge.showNotice(response.data.message, 'success');
                
                // Reload page to apply new settings
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            },
            error: function(xhr, status, error) {
                SEOForge.Debug.log('error', '❌ Failed to reset chatbot settings', {
                    status: xhr.status,
                    error: error
                });
                SEOForge.showNotice('Failed to reset chatbot settings. Please try again.', 'error');
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        SEOForge.init();
        
        // Auto-enable chatbot if it's not working
        setTimeout(() => {
            if (!$('.seo-forge-chatbot').length && $('.seo-forge-enable-chatbot').length) {
                SEOForge.Debug.log('warning', '⚠️ Chatbot not found, showing enable button');
            }
        }, 2000);
    });

})(jQuery);