jQuery(document).ready(function($) {
    'use strict';
    
    // Frontend SEO suggestions for visitors (if enabled)
    if (typeof seoforge_mcp_ajax !== 'undefined') {
        
        // Add SEO suggestions widget to posts/pages
        function initSEOWidget() {
            if ($('.seoforge-widget').length === 0) {
                return;
            }
            
            $('.seoforge-widget').each(function() {
                var $widget = $(this);
                var postId = $widget.data('post-id');
                
                if (postId) {
                    loadSEOSuggestions($widget, postId);
                }
            });
        }
        
        function loadSEOSuggestions($widget, postId) {
            var data = {
                action: 'seoforge_mcp_request',
                nonce: seoforge_mcp_ajax.nonce,
                seoforge_action: 'get_public_suggestions',
                data: {
                    post_id: postId
                }
            };
            
            $.post(seoforge_mcp_ajax.ajax_url, data, function(response) {
                if (response.success && response.data.suggestions) {
                    displaySuggestions($widget, response.data.suggestions);
                }
            });
        }
        
        function displaySuggestions($widget, suggestions) {
            var html = '<div class="seoforge-suggestions-list">';
            html += '<h4>Related Topics</h4>';
            
            suggestions.forEach(function(suggestion) {
                html += '<div class="suggestion-item">';
                html += '<a href="#" class="suggestion-link" data-keyword="' + suggestion.keyword + '">';
                html += suggestion.title;
                html += '</a>';
                html += '</div>';
            });
            
            html += '</div>';
            $widget.html(html);
        }
        
        // Handle suggestion clicks
        $(document).on('click', '.suggestion-link', function(e) {
            e.preventDefault();
            var keyword = $(this).data('keyword');
            
            // Track suggestion click
            if (typeof gtag !== 'undefined') {
                gtag('event', 'seoforge_suggestion_click', {
                    'keyword': keyword
                });
            }
            
            // You could implement search or navigation here
            console.log('SEOForge suggestion clicked:', keyword);
        });
        
        // Initialize on page load
        initSEOWidget();
        
        // Reinitialize on AJAX content load (for themes that use AJAX)
        $(document).ajaxComplete(function() {
            setTimeout(initSEOWidget, 500);
        });
    }
    
    // Reading time calculator
    function calculateReadingTime() {
        var $content = $('.entry-content, .post-content, .content, article');
        if ($content.length === 0) return;
        
        var text = $content.text();
        var wordCount = text.split(/\s+/).length;
        var readingTime = Math.ceil(wordCount / 200); // Average reading speed
        
        if (readingTime > 0) {
            var readingTimeHtml = '<div class="seoforge-reading-time">';
            readingTimeHtml += '<span class="reading-time-icon">📖</span>';
            readingTimeHtml += '<span class="reading-time-text">' + readingTime + ' min read</span>';
            readingTimeHtml += '</div>';
            
            // Insert reading time before content
            $content.first().prepend(readingTimeHtml);
        }
    }
    
    // Progress bar for reading
    function initReadingProgress() {
        if ($('.seoforge-reading-progress').length > 0) return;
        
        var $progressBar = $('<div class="seoforge-reading-progress"><div class="progress-fill"></div></div>');
        $('body').prepend($progressBar);
        
        $(window).on('scroll', function() {
            var scrollTop = $(window).scrollTop();
            var docHeight = $(document).height() - $(window).height();
            var scrollPercent = (scrollTop / docHeight) * 100;
            
            $('.progress-fill').css('width', scrollPercent + '%');
        });
    }
    
    // Table of contents generator
    function generateTableOfContents() {
        var $content = $('.entry-content, .post-content, .content, article');
        var $headings = $content.find('h2, h3, h4');
        
        if ($headings.length < 3) return; // Only show TOC if there are enough headings
        
        var tocHtml = '<div class="seoforge-toc">';
        tocHtml += '<h3>Table of Contents</h3>';
        tocHtml += '<ul class="toc-list">';
        
        $headings.each(function(index) {
            var $heading = $(this);
            var id = 'seoforge-heading-' + index;
            var text = $heading.text();
            var level = parseInt($heading.prop('tagName').substring(1));
            
            // Add ID to heading for anchor links
            $heading.attr('id', id);
            
            tocHtml += '<li class="toc-level-' + level + '">';
            tocHtml += '<a href="#' + id + '">' + text + '</a>';
            tocHtml += '</li>';
        });
        
        tocHtml += '</ul></div>';
        
        // Insert TOC after first paragraph or at beginning of content
        var $firstParagraph = $content.find('p').first();
        if ($firstParagraph.length > 0) {
            $firstParagraph.after(tocHtml);
        } else {
            $content.prepend(tocHtml);
        }
        
        // Smooth scrolling for TOC links
        $('.toc-list a').on('click', function(e) {
            e.preventDefault();
            var target = $(this).attr('href');
            $('html, body').animate({
                scrollTop: $(target).offset().top - 100
            }, 500);
        });
    }
    
    // Social sharing enhancement
    function enhanceSocialSharing() {
        $('.seoforge-social-share').each(function() {
            var $container = $(this);
            var url = encodeURIComponent(window.location.href);
            var title = encodeURIComponent(document.title);
            
            var shareButtons = {
                facebook: 'https://www.facebook.com/sharer/sharer.php?u=' + url,
                twitter: 'https://twitter.com/intent/tweet?url=' + url + '&text=' + title,
                linkedin: 'https://www.linkedin.com/sharing/share-offsite/?url=' + url,
                pinterest: 'https://pinterest.com/pin/create/button/?url=' + url + '&description=' + title
            };
            
            var buttonsHtml = '<div class="social-buttons">';
            Object.keys(shareButtons).forEach(function(platform) {
                buttonsHtml += '<a href="' + shareButtons[platform] + '" target="_blank" rel="noopener" class="share-' + platform + '">';
                buttonsHtml += '<span class="share-icon"></span>';
                buttonsHtml += '<span class="share-text">' + platform.charAt(0).toUpperCase() + platform.slice(1) + '</span>';
                buttonsHtml += '</a>';
            });
            buttonsHtml += '</div>';
            
            $container.html(buttonsHtml);
        });
    }
    
    // Initialize all frontend features
    function initSEOForgeFeatures() {
        calculateReadingTime();
        initReadingProgress();
        generateTableOfContents();
        enhanceSocialSharing();
    }
    
    // Run on page load
    initSEOForgeFeatures();
    
    // Run on AJAX page loads (for SPA themes)
    $(document).ajaxComplete(function() {
        setTimeout(initSEOForgeFeatures, 500);
    });
});

// CSS for frontend features (injected via JavaScript)
(function() {
    var css = `
        .seoforge-reading-time {
            display: inline-flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 14px;
            color: #666;
        }
        
        .reading-time-icon {
            margin-right: 5px;
        }
        
        .seoforge-reading-progress {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: rgba(0,0,0,0.1);
            z-index: 9999;
        }
        
        .progress-fill {
            height: 100%;
            background: #0073aa;
            transition: width 0.3s ease;
        }
        
        .seoforge-toc {
            background: #f8f9fa;
            border: 1px solid #e2e4e7;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .seoforge-toc h3 {
            margin: 0 0 15px 0;
            font-size: 18px;
            color: #23282d;
        }
        
        .toc-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .toc-list li {
            margin-bottom: 8px;
        }
        
        .toc-level-2 {
            padding-left: 0;
        }
        
        .toc-level-3 {
            padding-left: 20px;
        }
        
        .toc-level-4 {
            padding-left: 40px;
        }
        
        .toc-list a {
            color: #0073aa;
            text-decoration: none;
            font-size: 14px;
        }
        
        .toc-list a:hover {
            text-decoration: underline;
        }
        
        .seoforge-suggestions-list {
            background: #fff;
            border: 1px solid #e2e4e7;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .seoforge-suggestions-list h4 {
            margin: 0 0 15px 0;
            color: #23282d;
        }
        
        .suggestion-item {
            margin-bottom: 10px;
        }
        
        .suggestion-link {
            color: #0073aa;
            text-decoration: none;
            font-size: 14px;
        }
        
        .suggestion-link:hover {
            text-decoration: underline;
        }
        
        .social-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .social-buttons a {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            background: #f8f9fa;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: background 0.3s ease;
        }
        
        .social-buttons a:hover {
            background: #e2e4e7;
        }
        
        .share-icon {
            margin-right: 5px;
        }
        
        @media (max-width: 768px) {
            .seoforge-toc {
                margin: 15px 0;
                padding: 15px;
            }
            
            .social-buttons {
                justify-content: center;
            }
            
            .social-buttons a {
                flex: 1;
                justify-content: center;
                min-width: 120px;
            }
        }
    `;
    
    var style = document.createElement('style');
    style.textContent = css;
    document.head.appendChild(style);
})();
