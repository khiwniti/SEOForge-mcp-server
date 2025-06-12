/**
 * SEO Forge Admin JavaScript
 * Professional UI interactions and functionality
 */

(function($) {
    'use strict';

    // Global SEO Forge object
    window.SEOForge = window.SEOForge || {};

    /**
     * Initialize SEO Forge admin functionality
     */
    SEOForge.init = function() {
        this.bindEvents();
        this.initCharacterCounters();
        this.initTooltips();
        this.checkAPIConnection();
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
     * Check API connection status
     */
    SEOForge.checkAPIConnection = function() {
        const $status = $('.seo-forge-status-indicator');
        if (!$status.length) return;
        
        $.ajax({
            url: seoForge.ajaxUrl,
            type: 'POST',
            data: {
                action: 'seo_forge_test_connection',
                nonce: seoForge.nonce
            },
            success: function(response) {
                if (response.success) {
                    $status.addClass('connected');
                } else {
                    $status.removeClass('connected');
                }
            },
            error: function() {
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
        
        $.ajax({
            url: seoForge.ajaxUrl,
            type: 'POST',
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
                SEOForge.showNotice(seoForge.strings.error, 'error');
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
        
        $.ajax({
            url: seoForge.ajaxUrl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    SEOForge.displayContentResults(response.data);
                    SEOForge.showNotice(seoForge.strings.success, 'success');
                } else {
                    SEOForge.showNotice(response.data.message || seoForge.strings.error, 'error');
                }
            },
            error: function() {
                SEOForge.showNotice(seoForge.strings.error, 'error');
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
        
        $.ajax({
            url: seoForge.ajaxUrl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    SEOForge.displaySEOResults(response.data);
                    SEOForge.showNotice(seoForge.strings.success, 'success');
                } else {
                    SEOForge.showNotice(response.data.message || seoForge.strings.error, 'error');
                }
            },
            error: function() {
                SEOForge.showNotice(seoForge.strings.error, 'error');
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
        
        $.ajax({
            url: seoForge.ajaxUrl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    SEOForge.displayKeywordResults(response.data);
                    SEOForge.showNotice(seoForge.strings.success, 'success');
                } else {
                    SEOForge.showNotice(response.data.message || seoForge.strings.error, 'error');
                }
            },
            error: function() {
                SEOForge.showNotice(seoForge.strings.error, 'error');
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
     * Handle form submissions
     */
    SEOForge.handleFormSubmit = function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitButton = $form.find('[type="submit"]');
        const originalText = $submitButton.text();
        
        $submitButton.prop('disabled', true)
                     .html('<span class="seo-forge-spinner"></span> ' + seoForge.strings.processing);
        
        $.ajax({
            url: $form.attr('action') || seoForge.ajaxUrl,
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    SEOForge.showNotice(response.data.message || seoForge.strings.success, 'success');
                } else {
                    SEOForge.showNotice(response.data.message || seoForge.strings.error, 'error');
                }
            },
            error: function() {
                SEOForge.showNotice(seoForge.strings.error, 'error');
            },
            complete: function() {
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
     * Initialize when document is ready
     */
    $(document).ready(function() {
        SEOForge.init();
    });

})(jQuery);
        },

        showLoading: function($button) {
            const originalText = $button.text();
            $button.data('original-text', originalText);
            $button.prop('disabled', true);
            $button.html('<span class="seo-forge-spinner"></span> ' + seoForge.strings.generating);
        },

        hideLoading: function($button) {
            const originalText = $button.data('original-text');
            $button.prop('disabled', false);
            $button.text(originalText);
        },

        showNotice: function(message, type = 'success') {
            const $notice = $('<div class="seo-forge-notice ' + type + '">' + message + '</div>');
            $('.seo-forge-results').prepend($notice);
            
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $notice.remove();
                });
            }, 5000);
        },

        testConnection: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            SEOForge.showLoading($button);
            
            $.ajax({
                url: seoForge.ajaxUrl,
                type: 'POST',
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
                    SEOForge.showNotice(seoForge.strings.error, 'error');
                },
                complete: function() {
                    SEOForge.hideLoading($button);
                }
            });
        },

        generateContent: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $form = $button.closest('form, .seo-forge-form');
            
            const data = {
                action: 'seo_forge_generate_content',
                nonce: seoForge.nonce,
                keywords: $form.find('[name="keywords"]').val(),
                industry: $form.find('[name="industry"]').val(),
                content_type: $form.find('[name="content_type"]').val(),
                language: $form.find('[name="language"]').val() || 'en'
            };
            
            if (!data.keywords) {
                SEOForge.showNotice('Please enter keywords first.', 'error');
                return;
            }
            
            SEOForge.showLoading($button);
            
            $.ajax({
                url: seoForge.ajaxUrl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        SEOForge.displayGeneratedContent(response.data);
                        SEOForge.showNotice('Content generated successfully!', 'success');
                    } else {
                        SEOForge.showNotice(response.data.message, 'error');
                    }
                },
                error: function() {
                    SEOForge.showNotice(seoForge.strings.error, 'error');
                },
                complete: function() {
                    SEOForge.hideLoading($button);
                }
            });
        },

        displayGeneratedContent: function(data) {
            const $results = $('.seo-forge-content-results');
            
            let html = '<div class="seo-forge-generated-content">';
            html += '<h4>' + (data.title || 'Generated Content') + '</h4>';
            html += '<div class="content-preview">' + data.content + '</div>';
            
            if (data.image_url) {
                html += '<div class="generated-image">';
                html += '<img src="' + data.image_url + '" alt="Generated image" style="max-width: 100%; height: auto;">';
                html += '</div>';
            }
            
            html += '<div class="content-actions">';
            html += '<button type="button" class="seo-forge-button seo-forge-use-content">Use This Content</button>';
            html += '<button type="button" class="seo-forge-button secondary seo-forge-regenerate">Regenerate</button>';
            html += '</div>';
            html += '</div>';
            
            $results.html(html);
            $results.show();
        },

        analyzeSEO: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const content = $('#content').val() || wp.editor.getContent('content') || '';
            const focusKeyword = $('[name="_seo_forge_focus_keyword"]').val();
            
            if (!content) {
                SEOForge.showNotice('Please add some content first.', 'error');
                return;
            }
            
            SEOForge.showLoading($button);
            
            $.ajax({
                url: seoForge.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'seo_forge_analyze_seo',
                    nonce: seoForge.nonce,
                    content: content,
                    focus_keyword: focusKeyword,
                    url: window.location.href
                },
                success: function(response) {
                    if (response.success) {
                        SEOForge.displaySEOAnalysis(response.data);
                        SEOForge.showNotice('SEO analysis completed!', 'success');
                    } else {
                        SEOForge.showNotice(response.data.message, 'error');
                    }
                },
                error: function() {
                    SEOForge.showNotice(seoForge.strings.error, 'error');
                },
                complete: function() {
                    SEOForge.hideLoading($button);
                }
            });
        },

        displaySEOAnalysis: function(data) {
            const $results = $('.seo-forge-seo-results');
            
            let scoreClass = 'poor';
            if (data.score >= 70) scoreClass = 'good';
            else if (data.score >= 40) scoreClass = 'average';
            
            let html = '<div class="seo-forge-analysis-results">';
            html += '<div class="seo-forge-score">';
            html += '<div class="seo-forge-score-circle ' + scoreClass + '">' + data.score + '</div>';
            html += '<div>';
            html += '<h4>SEO Score: ' + data.score + '/100</h4>';
            html += '<p>' + SEOForge.getScoreDescription(data.score) + '</p>';
            html += '</div>';
            html += '</div>';
            
            if (data.issues && data.issues.length > 0) {
                html += '<h5>Issues Found:</h5>';
                html += '<ul>';
                data.issues.forEach(function(issue) {
                    html += '<li>' + issue + '</li>';
                });
                html += '</ul>';
            }
            
            if (data.recommendations && data.recommendations.length > 0) {
                html += '<h5>Recommendations:</h5>';
                html += '<ul>';
                data.recommendations.forEach(function(rec) {
                    html += '<li>' + rec + '</li>';
                });
                html += '</ul>';
            }
            
            html += '</div>';
            
            $results.html(html);
            $results.show();
        },

        getScoreDescription: function(score) {
            if (score >= 80) return 'Excellent SEO optimization!';
            if (score >= 70) return 'Good SEO optimization with minor improvements needed.';
            if (score >= 40) return 'Average SEO optimization with room for improvement.';
            return 'Poor SEO optimization. Significant improvements needed.';
        },

        researchKeywords: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $form = $button.closest('form, .seo-forge-form');
            
            const data = {
                action: 'seo_forge_research_keywords',
                nonce: seoForge.nonce,
                seed_keywords: $form.find('[name="seed_keywords"]').val(),
                language: $form.find('[name="language"]').val() || 'en',
                country: $form.find('[name="country"]').val() || 'US',
                limit: $form.find('[name="limit"]').val() || 50
            };
            
            if (!data.seed_keywords) {
                SEOForge.showNotice('Please enter seed keywords first.', 'error');
                return;
            }
            
            SEOForge.showLoading($button);
            
            $.ajax({
                url: seoForge.ajaxUrl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        SEOForge.displayKeywordResults(response.data.keywords);
                        SEOForge.showNotice('Keyword research completed!', 'success');
                    } else {
                        SEOForge.showNotice(response.data.message, 'error');
                    }
                },
                error: function() {
                    SEOForge.showNotice(seoForge.strings.error, 'error');
                },
                complete: function() {
                    SEOForge.hideLoading($button);
                }
            });
        },

        displayKeywordResults: function(keywords) {
            const $results = $('.seo-forge-keyword-results');
            
            let html = '<table class="seo-forge-keyword-table">';
            html += '<thead>';
            html += '<tr>';
            html += '<th>Keyword</th>';
            html += '<th>Search Volume</th>';
            html += '<th>Difficulty</th>';
            html += '<th>CPC</th>';
            html += '<th>Actions</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';
            
            keywords.forEach(function(keyword) {
                const difficultyClass = SEOForge.getDifficultyClass(keyword.difficulty);
                
                html += '<tr>';
                html += '<td><strong>' + keyword.keyword + '</strong></td>';
                html += '<td>' + SEOForge.formatSearchVolume(keyword.search_volume) + '</td>';
                html += '<td><span class="difficulty ' + difficultyClass + '">' + keyword.difficulty + '</span></td>';
                html += '<td>$' + parseFloat(keyword.cpc).toFixed(2) + '</td>';
                html += '<td><button type="button" class="seo-forge-button seo-forge-use-keyword" data-keyword="' + keyword.keyword + '">Use</button></td>';
                html += '</tr>';
            });
            
            html += '</tbody>';
            html += '</table>';
            
            $results.html(html);
            $results.show();
        },

        getDifficultyClass: function(difficulty) {
            if (difficulty <= 30) return 'easy';
            if (difficulty <= 60) return 'medium';
            return 'hard';
        },

        formatSearchVolume: function(volume) {
            if (volume >= 1000000) {
                return (volume / 1000000).toFixed(1) + 'M';
            } else if (volume >= 1000) {
                return (volume / 1000).toFixed(1) + 'K';
            }
            return volume.toLocaleString();
        },

        useKeyword: function(e) {
            e.preventDefault();
            
            const keyword = $(this).data('keyword');
            const $focusKeywordField = $('[name="_seo_forge_focus_keyword"]');
            
            if ($focusKeywordField.length) {
                $focusKeywordField.val(keyword);
                SEOForge.showNotice('Keyword "' + keyword + '" added as focus keyword!', 'success');
            } else {
                SEOForge.showNotice('Focus keyword field not found.', 'error');
            }
        },

        useContent: function(e) {
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
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        SEOForge.init();
    });

})(jQuery);