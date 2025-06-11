jQuery(document).ready(function($) {
    'use strict';
    
    // Content Generation
    $('#generate-content-btn').on('click', function() {
        var $button = $(this);
        var $result = $('#seoforge-content-result');
        var $content = $('#generated-content');
        
        var keywords = $('#seoforge-keywords').val().split(',').map(k => k.trim()).filter(k => k);
        var industry = $('#seoforge-industry').val();
        var title = $('#title').val() || $('#post-title-0').val() || '';
        
        if (!keywords.length) {
            alert(seoforge_mcp_admin.strings.error + ' Please enter keywords.');
            return;
        }
        
        $button.prop('disabled', true).text(seoforge_mcp_admin.strings.generating);
        
        var data = {
            action: 'seoforge_mcp_request',
            nonce: seoforge_mcp_admin.nonce,
            seoforge_action: 'generate_content',
            data: {
                title: title,
                keywords: keywords,
                industry: industry,
                content_type: 'blog_post',
                language: 'en'
            }
        };
        
        $.post(seoforge_mcp_admin.ajax_url, data, function(response) {
            if (response.success) {
                var generated = response.data.data.generated_content;
                var html = '<div class="seoforge-generated-content">';
                html += '<h5>Title: ' + generated.title + '</h5>';
                html += '<p><strong>Meta Description:</strong> ' + generated.meta_description + '</p>';
                html += '<p><strong>Focus Keyword:</strong> ' + generated.focus_keyword + '</p>';
                html += '<p><strong>SEO Score:</strong> ' + generated.seo_score + '/100</p>';
                html += '<div class="content-preview">' + generated.content + '</div>';
                html += '<button type="button" class="button apply-content-btn">Apply to Post</button>';
                html += '</div>';
                
                $content.html(html);
                $result.show();
            } else {
                alert(seoforge_mcp_admin.strings.error + ' ' + response.data);
            }
        }).fail(function() {
            alert(seoforge_mcp_admin.strings.error);
        }).always(function() {
            $button.prop('disabled', false).text('Generate Content');
        });
    });
    
    // Apply generated content to post
    $(document).on('click', '.apply-content-btn', function() {
        var $contentDiv = $(this).siblings('.content-preview');
        var content = $contentDiv.text();
        
        // Try different editor types
        if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
            tinymce.get('content').setContent(content);
        } else if ($('#content').length) {
            $('#content').val(content);
        } else if ($('.wp-block-post-content').length) {
            // Gutenberg editor
            wp.data.dispatch('core/editor').editPost({content: content});
        }
        
        $(this).text('Applied!').prop('disabled', true);
    });
    
    // SEO Analysis
    $('#analyze-seo-btn').on('click', function() {
        var $button = $(this);
        var $result = $('#seoforge-seo-result');
        var $content = $('#seo-analysis-content');
        
        var title = $('#title').val() || $('#post-title-0').val() || '';
        var content = '';
        
        // Get content from different editor types
        if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
            content = tinymce.get('content').getContent();
        } else if ($('#content').length) {
            content = $('#content').val();
        } else if (wp.data && wp.data.select('core/editor')) {
            content = wp.data.select('core/editor').getEditedPostContent();
        }
        
        if (!content.trim()) {
            alert('Please add some content to analyze.');
            return;
        }
        
        $button.prop('disabled', true).text(seoforge_mcp_admin.strings.analyzing);
        
        var data = {
            action: 'seoforge_mcp_request',
            nonce: seoforge_mcp_admin.nonce,
            seoforge_action: 'analyze_seo',
            data: {
                title: title,
                content: content,
                url: window.location.href,
                language: 'en'
            }
        };
        
        $.post(seoforge_mcp_admin.ajax_url, data, function(response) {
            if (response.success) {
                var analysis = response.data.data.seo_analysis;
                var html = '<div class="seoforge-seo-analysis">';
                html += '<div class="seo-score-overall">';
                html += '<h5>Overall SEO Score: <span class="score-' + getScoreClass(analysis.overall_score) + '">' + analysis.overall_score + '/100</span></h5>';
                html += '</div>';
                
                html += '<div class="seo-section">';
                html += '<h6>Title Analysis (Score: ' + analysis.title_analysis.score + '/100)</h6>';
                html += '<ul>';
                analysis.title_analysis.recommendations.forEach(function(rec) {
                    html += '<li>' + rec + '</li>';
                });
                html += '</ul>';
                html += '</div>';
                
                html += '<div class="seo-section">';
                html += '<h6>Content Analysis (Score: ' + analysis.content_analysis.score + '/100)</h6>';
                html += '<p>Word Count: ' + analysis.content_analysis.word_count + '</p>';
                html += '<p>Keyword Density: ' + analysis.content_analysis.keyword_density + '%</p>';
                html += '<ul>';
                analysis.content_analysis.recommendations.forEach(function(rec) {
                    html += '<li>' + rec + '</li>';
                });
                html += '</ul>';
                html += '</div>';
                
                html += '<div class="seo-section">';
                html += '<h6>Improvement Suggestions:</h6>';
                html += '<ul>';
                response.data.data.improvement_suggestions.forEach(function(suggestion) {
                    html += '<li>' + suggestion + '</li>';
                });
                html += '</ul>';
                html += '</div>';
                
                html += '</div>';
                
                $content.html(html);
                $result.show();
            } else {
                alert(seoforge_mcp_admin.strings.error + ' ' + response.data);
            }
        }).fail(function() {
            alert(seoforge_mcp_admin.strings.error);
        }).always(function() {
            $button.prop('disabled', false).text('Analyze SEO');
        });
    });
    
    // Get Suggestions
    $('#generate-suggestions-btn').on('click', function() {
        var $button = $(this);
        var industry = $('#seoforge-industry').val();
        var postType = $('#post_type').val() || 'post';
        
        $button.prop('disabled', true).text('Getting suggestions...');
        
        var data = {
            action: 'seoforge_mcp_request',
            nonce: seoforge_mcp_admin.nonce,
            seoforge_action: 'get_suggestions',
            data: {
                post_type: postType,
                category: industry,
                language: 'en'
            }
        };
        
        $.post(seoforge_mcp_admin.ajax_url, data, function(response) {
            if (response.success) {
                var suggestions = response.data.data.suggestions;
                var html = '<div class="seoforge-suggestions">';
                html += '<h5>Content Suggestions:</h5>';
                
                suggestions.forEach(function(suggestion) {
                    html += '<div class="suggestion-item">';
                    html += '<strong>' + suggestion.type.charAt(0).toUpperCase() + suggestion.type.slice(1) + ':</strong> ';
                    html += suggestion.suggestion;
                    html += '<br><small><em>' + suggestion.reason + '</em></small>';
                    html += '</div>';
                });
                
                html += '</div>';
                
                $('#generated-content').html(html);
                $('#seoforge-content-result').show();
            } else {
                alert(seoforge_mcp_admin.strings.error + ' ' + response.data);
            }
        }).fail(function() {
            alert(seoforge_mcp_admin.strings.error);
        }).always(function() {
            $button.prop('disabled', false).text('Get Suggestions');
        });
    });
    
    // Keyword Research (Dashboard)
    $('#keyword-research-btn').on('click', function() {
        var seedKeyword = prompt('Enter a seed keyword for research:');
        if (!seedKeyword) return;
        
        var $button = $(this);
        $button.prop('disabled', true).text('Researching...');
        
        var data = {
            action: 'seoforge_mcp_request',
            nonce: seoforge_mcp_admin.nonce,
            seoforge_action: 'research_keywords',
            data: {
                seed_keyword: seedKeyword,
                industry: 'general',
                language: 'en'
            }
        };
        
        $.post(seoforge_mcp_admin.ajax_url, data, function(response) {
            if (response.success) {
                var keywords = response.data.data.keywords;
                var html = '<div class="keyword-research-results">';
                html += '<h3>Keyword Research Results for "' + seedKeyword + '"</h3>';
                html += '<table class="wp-list-table widefat fixed striped">';
                html += '<thead><tr><th>Keyword</th><th>Search Volume</th><th>Difficulty</th><th>CPC</th></tr></thead>';
                html += '<tbody>';
                
                keywords.forEach(function(keyword) {
                    html += '<tr>';
                    html += '<td>' + keyword.keyword + '</td>';
                    html += '<td>' + keyword.search_volume + '</td>';
                    html += '<td>' + keyword.difficulty + '</td>';
                    html += '<td>$' + keyword.cpc + '</td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table></div>';
                
                // Create modal or append to dashboard
                if ($('#keyword-research-modal').length === 0) {
                    $('body').append('<div id="keyword-research-modal" class="seoforge-modal"><div class="modal-content">' + html + '<button class="modal-close">Close</button></div></div>');
                } else {
                    $('#keyword-research-modal .modal-content').html(html + '<button class="modal-close">Close</button>');
                }
                $('#keyword-research-modal').show();
            } else {
                alert(seoforge_mcp_admin.strings.error + ' ' + response.data);
            }
        }).fail(function() {
            alert(seoforge_mcp_admin.strings.error);
        }).always(function() {
            $button.prop('disabled', false).text('Start Research');
        });
    });
    
    // Close modal
    $(document).on('click', '.modal-close, .seoforge-modal', function(e) {
        if (e.target === this) {
            $('.seoforge-modal').hide();
        }
    });
    
    // Helper function to get score class
    function getScoreClass(score) {
        if (score >= 80) return 'good';
        if (score >= 60) return 'average';
        return 'poor';
    }
    
    // Auto-save keywords and industry
    $('#seoforge-keywords, #seoforge-industry').on('change', function() {
        // Trigger autosave if available
        if (typeof wp !== 'undefined' && wp.autosave) {
            wp.autosave.server.triggerSave();
        }
    });
});
