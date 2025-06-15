(function($) {
    'use strict';

    // Admin JavaScript functionality for SEO-Forge
    $(document).ready(function() {
        
        // Tab functionality
        $('.tab-button').on('click', function() {
            var targetTab = $(this).data('tab');
            
            // Update active tab button
            $('.tab-button').removeClass('active');
            $(this).addClass('active');
            
            // Show/hide tab panels
            $('.tab-panel').removeClass('active');
            $('#' + targetTab).addClass('active');
        });

        // Content Generation Forms
        $('#blog-content-form').on('submit', function(e) {
            e.preventDefault();
            generateBlogContent();
        });

        $('#title-generator-form').on('submit', function(e) {
            e.preventDefault();
            generateTitle();
        });

        $('#meta-generator-form').on('submit', function(e) {
            e.preventDefault();
            generateMetaDescription();
        });

        $('#image-generator-form').on('submit', function(e) {
            e.preventDefault();
            generateImage();
        });

        $('#seo-analysis-form').on('submit', function(e) {
            e.preventDefault();
            analyzeSEO();
        });

        // Keyword suggestions
        $('#get-keyword-suggestions').on('click', function() {
            getKeywordSuggestions();
        });

        // Copy content functionality
        $('#copy-content').on('click', function() {
            var content = $('#generated-content').text();
            navigator.clipboard.writeText(content).then(function() {
                showNotification('Content copied to clipboard!', 'success');
            });
        });

        // Create post functionality
        $('#create-post').on('click', function() {
            createPostFromContent();
        });

        // Image actions
        $('#download-image').on('click', function() {
            downloadGeneratedImage();
        });

        $('#add-to-media').on('click', function() {
            addImageToMediaLibrary();
        });

        // Functions
        function generateBlogContent() {
            var formData = {
                action: 'seo_forge_generate_content',
                nonce: seoForgeAjax.nonce,
                topic: $('#blog-topic').val(),
                keywords: $('#blog-keywords').val().split(',').map(s => s.trim()).filter(s => s),
                word_count: $('#word-count').val()
            };

            showLoading('Generating blog content...');

            $.ajax({
                url: seoForgeAjax.ajaxurl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        $('#generated-content').html(formatContent(response.data.content));
                        $('#blog-content-result').fadeIn();
                        showNotification('Content generated successfully!', 'success');
                        updateContentCount();
                    } else {
                        showNotification(response.data.message || 'Failed to generate content', 'error');
                    }
                },
                error: function() {
                    hideLoading();
                    showNotification('Network error. Please try again.', 'error');
                }
            });
        }

        function generateTitle() {
            var formData = {
                action: 'seo_forge_generate_title',
                nonce: seoForgeAjax.nonce,
                content: $('#content-for-title').val(),
                keyword: $('#target-keyword-title').val()
            };

            showToolLoading('#title-result');

            $.ajax({
                url: seoForgeAjax.ajaxurl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showToolResult('#title-result', response.data.title, 'success');
                    } else {
                        showToolResult('#title-result', response.data.message || 'Failed to generate title', 'error');
                    }
                },
                error: function() {
                    showToolResult('#title-result', 'Network error. Please try again.', 'error');
                }
            });
        }

        function generateMetaDescription() {
            var formData = {
                action: 'seo_forge_generate_meta',
                nonce: seoForgeAjax.nonce,
                content: $('#content-for-meta').val(),
                keyword: $('#target-keyword-meta').val()
            };

            showToolLoading('#meta-result');

            $.ajax({
                url: seoForgeAjax.ajaxurl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showToolResult('#meta-result', response.data.meta_description, 'success');
                    } else {
                        showToolResult('#meta-result', response.data.message || 'Failed to generate meta description', 'error');
                    }
                },
                error: function() {
                    showToolResult('#meta-result', 'Network error. Please try again.', 'error');
                }
            });
        }

        function generateImage() {
            var formData = {
                action: 'seo_forge_generate_image',
                nonce: seoForgeAjax.nonce,
                prompt: $('#image-prompt').val(),
                style: $('#image-style').val()
            };

            showLoading('Generating image...');

            $.ajax({
                url: seoForgeAjax.ajaxurl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        $('#generated-image').html('<img src="' + response.data.image_url + '" alt="Generated Image" style="max-width: 100%; height: auto;">');
                        $('#image-generation-result').fadeIn();
                        showNotification('Image generated successfully!', 'success');
                        updateImageCount();
                    } else {
                        showNotification(response.data.message || 'Failed to generate image', 'error');
                    }
                },
                error: function() {
                    hideLoading();
                    showNotification('Network error. Please try again.', 'error');
                }
            });
        }

        function analyzeSEO() {
            var formData = {
                action: 'seo_forge_analyze_seo',
                nonce: seoForgeAjax.nonce,
                content: $('#content-for-analysis').val(),
                keyword: $('#target-keyword-analysis').val()
            };

            showToolLoading('#seo-analysis-result');

            $.ajax({
                url: seoForgeAjax.ajaxurl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        var analysisHtml = formatSEOAnalysis(response.data);
                        showToolResult('#seo-analysis-result', analysisHtml, 'success');
                    } else {
                        showToolResult('#seo-analysis-result', response.data.message || 'Failed to analyze SEO', 'error');
                    }
                },
                error: function() {
                    showToolResult('#seo-analysis-result', 'Network error. Please try again.', 'error');
                }
            });
        }

        function getKeywordSuggestions() {
            var topic = $('#blog-topic').val();
            if (!topic) {
                showNotification('Please enter a topic first', 'warning');
                return;
            }

            var formData = {
                action: 'seo_forge_get_keywords',
                nonce: seoForgeAjax.nonce,
                topic: topic
            };

            $.ajax({
                url: seoForgeAjax.ajaxurl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#blog-keywords').val(response.data.keywords.join(', '));
                        showNotification('Keyword suggestions added!', 'success');
                    } else {
                        showNotification(response.data.message || 'Failed to get keyword suggestions', 'error');
                    }
                },
                error: function() {
                    showNotification('Network error. Please try again.', 'error');
                }
            });
        }

        function createPostFromContent() {
            var content = $('#generated-content').html();
            var title = $('#blog-topic').val();
            
            // Create new post with generated content
            var postData = {
                action: 'wp_ajax_inline_save',
                post_title: title,
                content: content,
                post_status: 'draft'
            };

            // Redirect to new post editor
            var url = 'post-new.php?post_title=' + encodeURIComponent(title) + '&content=' + encodeURIComponent(content);
            window.open(url, '_blank');
        }

        function downloadGeneratedImage() {
            var imageUrl = $('#generated-image img').attr('src');
            if (imageUrl) {
                var link = document.createElement('a');
                link.download = 'seo-forge-generated-image.jpg';
                link.href = imageUrl;
                link.click();
            }
        }

        function addImageToMediaLibrary() {
            var imageUrl = $('#generated-image img').attr('src');
            if (!imageUrl) return;

            var formData = {
                action: 'seo_forge_save_image',
                nonce: seoForgeAjax.nonce,
                image_url: imageUrl
            };

            $.ajax({
                url: seoForgeAjax.ajaxurl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showNotification('Image added to media library!', 'success');
                    } else {
                        showNotification('Failed to add image to media library', 'error');
                    }
                },
                error: function() {
                    showNotification('Network error. Please try again.', 'error');
                }
            });
        }

        // Utility functions
        function showLoading(message) {
            $('#loading-message').text(message);
            $('#loading-overlay').fadeIn();
        }

        function hideLoading() {
            $('#loading-overlay').fadeOut();
        }

        function showToolLoading(selector) {
            $(selector).html('<i class="fas fa-spinner fa-spin"></i> Loading...').addClass('loading').show();
        }

        function showToolResult(selector, content, type) {
            $(selector).removeClass('loading success error').addClass(type).html(content).show();
        }

        function showNotification(message, type) {
            var notification = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            $('.wrap h1').after(notification);
            
            setTimeout(function() {
                notification.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }

        function formatContent(content) {
            // Basic formatting for generated content
            return content.replace(/\n\n/g, '</p><p>').replace(/\n/g, '<br>');
        }

        function formatSEOAnalysis(data) {
            var html = '<div class="seo-analysis">';
            html += '<h4>SEO Analysis Results</h4>';
            
            if (data.score) {
                html += '<div class="seo-score">Score: ' + data.score + '/100</div>';
            }
            
            if (data.recommendations && data.recommendations.length > 0) {
                html += '<h5>Recommendations:</h5><ul>';
                data.recommendations.forEach(function(rec) {
                    html += '<li>' + rec + '</li>';
                });
                html += '</ul>';
            }
            
            html += '</div>';
            return html;
        }

        function updateContentCount() {
            var count = parseInt($('.stat-number').first().text()) + 1;
            $('.stat-number').first().text(count);
        }

        function updateImageCount() {
            var count = parseInt($('.stat-number').last().text()) + 1;
            $('.stat-number').last().text(count);
        }

        // API connection test
        $('#test-api-connection').on('click', function() {
            var button = $(this);
            var result = $('#api-status-result');
            
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Testing...');
            
            $.ajax({
                url: seoForgeAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'seo_forge_test_api',
                    nonce: seoForgeAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        result.html('<div class="notice notice-success inline"><p><i class="fas fa-check-circle"></i> API connection successful!</p></div>');
                    } else {
                        result.html('<div class="notice notice-error inline"><p><i class="fas fa-exclamation-circle"></i> API connection failed: ' + (response.data.message || 'Unknown error') + '</p></div>');
                    }
                },
                error: function() {
                    result.html('<div class="notice notice-error inline"><p><i class="fas fa-exclamation-circle"></i> Network error occurred</p></div>');
                },
                complete: function() {
                    button.prop('disabled', false).html('<i class="fas fa-check-circle"></i> Test API Connection');
                }
            });
        });

    });

})(jQuery);
