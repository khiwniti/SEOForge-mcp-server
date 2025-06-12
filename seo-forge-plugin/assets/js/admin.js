/**
 * SEO Forge Admin JavaScript
 */

(function($) {
    'use strict';

    const SEOForge = {
        init: function() {
            this.bindEvents();
            this.initCharacterCounters();
        },

        bindEvents: function() {
            // Test connection
            $(document).on('click', '.seo-forge-test-connection', this.testConnection);
            
            // Generate content
            $(document).on('click', '.seo-forge-generate-content', this.generateContent);
            
            // Analyze SEO
            $(document).on('click', '.seo-forge-analyze-seo', this.analyzeSEO);
            
            // Research keywords
            $(document).on('click', '.seo-forge-research-keywords', this.researchKeywords);
            
            // Use keyword
            $(document).on('click', '.seo-forge-use-keyword', this.useKeyword);
            
            // Use generated content
            $(document).on('click', '.seo-forge-use-content', this.useContent);
        },

        initCharacterCounters: function() {
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