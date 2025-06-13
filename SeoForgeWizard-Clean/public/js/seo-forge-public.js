(function($) {
    'use strict';

    /**
     * SEO-Forge Public JavaScript
     * Handles frontend functionality and general utilities
     */

    // Initialize when DOM is ready
    $(document).ready(function() {
        initSEOForge();
    });

    /**
     * Initialize SEO-Forge public functionality
     */
    function initSEOForge() {
        // Check if chatbot is enabled
        if (typeof seoForgeChatbot !== 'undefined' && seoForgeChatbot.enabled) {
            // Chatbot is handled by separate script
            console.log('SEO-Forge: Chatbot enabled');
        }

        // Initialize other public features
        initSEOEnhancements();
        initPerformanceTracking();
    }

    /**
     * Initialize SEO enhancements
     */
    function initSEOEnhancements() {
        // Add schema markup for articles
        addArticleSchemaMarkup();
        
        // Optimize images for SEO
        optimizeImages();
        
        // Add social media meta tags
        addSocialMetaTags();
        
        // Track user engagement
        trackUserEngagement();
    }

    /**
     * Add article schema markup
     */
    function addArticleSchemaMarkup() {
        // Check if we're on a single post/page
        if (!$('body').hasClass('single-post') && !$('body').hasClass('single-page')) {
            return;
        }

        var articleData = {
            "@context": "https://schema.org",
            "@type": "Article",
            "headline": $('h1.entry-title, h1.post-title, h1').first().text(),
            "datePublished": $('time[datetime]').attr('datetime') || $('meta[property="article:published_time"]').attr('content'),
            "dateModified": $('time[datetime]').attr('datetime') || $('meta[property="article:modified_time"]').attr('content'),
            "author": {
                "@type": "Person",
                "name": $('.author-name, .by-author').first().text() || $('meta[name="author"]').attr('content')
            },
            "publisher": {
                "@type": "Organization",
                "name": $('meta[property="og:site_name"]').attr('content') || document.title.split(' - ').pop()
            }
        };

        // Add featured image if available
        var featuredImage = $('meta[property="og:image"]').attr('content');
        if (featuredImage) {
            articleData.image = featuredImage;
        }

        // Add description
        var description = $('meta[name="description"]').attr('content');
        if (description) {
            articleData.description = description;
        }

        // Only add schema if we have required data
        if (articleData.headline && articleData.author.name) {
            $('head').append('<script type="application/ld+json">' + JSON.stringify(articleData) + '</script>');
        }
    }

    /**
     * Optimize images for SEO
     */
    function optimizeImages() {
        $('img').each(function() {
            var $img = $(this);
            
            // Add alt text if missing
            if (!$img.attr('alt')) {
                var title = $img.attr('title') || $img.closest('figure').find('figcaption').text() || '';
                if (title) {
                    $img.attr('alt', title.trim());
                }
            }

            // Add loading="lazy" for better performance
            if (!$img.attr('loading')) {
                $img.attr('loading', 'lazy');
            }

            // Add decoding="async" for better performance
            if (!$img.attr('decoding')) {
                $img.attr('decoding', 'async');
            }
        });
    }

    /**
     * Add social media meta tags dynamically
     */
    function addSocialMetaTags() {
        var currentUrl = window.location.href;
        var title = document.title;
        var description = $('meta[name="description"]').attr('content') || '';
        var image = $('meta[property="og:image"]').attr('content') || '';

        // Add Twitter Card meta tags if not present
        if (!$('meta[name="twitter:card"]').length) {
            $('head').append('<meta name="twitter:card" content="summary_large_image">');
        }
        if (!$('meta[name="twitter:title"]').length && title) {
            $('head').append('<meta name="twitter:title" content="' + title + '">');
        }
        if (!$('meta[name="twitter:description"]').length && description) {
            $('head').append('<meta name="twitter:description" content="' + description + '">');
        }
        if (!$('meta[name="twitter:image"]').length && image) {
            $('head').append('<meta name="twitter:image" content="' + image + '">');
        }

        // Add Open Graph meta tags if not present
        if (!$('meta[property="og:url"]').length) {
            $('head').append('<meta property="og:url" content="' + currentUrl + '">');
        }
        if (!$('meta[property="og:type"]').length) {
            var ogType = $('body').hasClass('single-post') ? 'article' : 'website';
            $('head').append('<meta property="og:type" content="' + ogType + '">');
        }
    }

    /**
     * Track user engagement for SEO analytics
     */
    function trackUserEngagement() {
        var engagementData = {
            startTime: Date.now(),
            scrollDepth: 0,
            timeOnPage: 0,
            interactions: 0
        };

        // Track scroll depth
        $(window).on('scroll', throttle(function() {
            var scrollTop = $(window).scrollTop();
            var docHeight = $(document).height();
            var winHeight = $(window).height();
            var scrollPercent = Math.round(scrollTop / (docHeight - winHeight) * 100);
            
            engagementData.scrollDepth = Math.max(engagementData.scrollDepth, scrollPercent);
        }, 100));

        // Track interactions
        $('a, button, input, textarea').on('click focus', function() {
            engagementData.interactions++;
        });

        // Send engagement data when user leaves
        $(window).on('beforeunload', function() {
            engagementData.timeOnPage = Date.now() - engagementData.startTime;
            sendEngagementData(engagementData);
        });

        // Also send data periodically for long sessions
        setInterval(function() {
            engagementData.timeOnPage = Date.now() - engagementData.startTime;
            sendEngagementData(engagementData);
        }, 30000); // Every 30 seconds
    }

    /**
     * Send engagement data to server
     */
    function sendEngagementData(data) {
        if (typeof seoForgeChatbot !== 'undefined') {
            // Use beacon API for reliable data sending
            if (navigator.sendBeacon) {
                var formData = new FormData();
                formData.append('action', 'seo_forge_track_engagement');
                formData.append('nonce', seoForgeChatbot.nonce);
                formData.append('data', JSON.stringify(data));
                formData.append('url', window.location.href);
                
                navigator.sendBeacon(seoForgeChatbot.ajaxurl, formData);
            }
        }
    }

    /**
     * Initialize performance tracking
     */
    function initPerformanceTracking() {
        // Track Core Web Vitals
        if ('web-vital' in window) {
            trackCoreWebVitals();
        }

        // Track page load times
        $(window).on('load', function() {
            setTimeout(function() {
                trackPagePerformance();
            }, 1000);
        });
    }

    /**
     * Track Core Web Vitals
     */
    function trackCoreWebVitals() {
        // This would typically use the web-vitals library
        // For now, we'll track basic performance metrics
        
        if (window.performance && window.performance.timing) {
            var timing = window.performance.timing;
            var metrics = {
                loadTime: timing.loadEventEnd - timing.navigationStart,
                domContentLoaded: timing.domContentLoadedEventEnd - timing.navigationStart,
                firstPaint: 0,
                firstContentfulPaint: 0
            };

            // Get paint metrics if available
            if (window.performance.getEntriesByType) {
                var paintEntries = window.performance.getEntriesByType('paint');
                paintEntries.forEach(function(entry) {
                    if (entry.name === 'first-paint') {
                        metrics.firstPaint = entry.startTime;
                    } else if (entry.name === 'first-contentful-paint') {
                        metrics.firstContentfulPaint = entry.startTime;
                    }
                });
            }

            sendPerformanceData(metrics);
        }
    }

    /**
     * Track page performance
     */
    function trackPagePerformance() {
        var performanceData = {
            url: window.location.href,
            userAgent: navigator.userAgent,
            timestamp: Date.now(),
            viewport: {
                width: window.innerWidth,
                height: window.innerHeight
            }
        };

        // Add resource timing data
        if (window.performance && window.performance.getEntriesByType) {
            var resources = window.performance.getEntriesByType('resource');
            performanceData.resourceCount = resources.length;
            
            // Calculate total resource size
            var totalSize = 0;
            resources.forEach(function(resource) {
                if (resource.transferSize) {
                    totalSize += resource.transferSize;
                }
            });
            performanceData.totalResourceSize = totalSize;
        }

        sendPerformanceData(performanceData);
    }

    /**
     * Send performance data to server
     */
    function sendPerformanceData(data) {
        if (typeof seoForgeChatbot !== 'undefined') {
            $.ajax({
                url: seoForgeChatbot.ajaxurl,
                type: 'POST',
                data: {
                    action: 'seo_forge_track_performance',
                    nonce: seoForgeChatbot.nonce,
                    data: JSON.stringify(data)
                },
                success: function(response) {
                    // Performance data sent successfully
                },
                error: function() {
                    // Handle error silently
                }
            });
        }
    }

    /**
     * Utility function to throttle function calls
     */
    function throttle(func, limit) {
        var lastFunc;
        var lastRan;
        return function() {
            var context = this;
            var args = arguments;
            if (!lastRan) {
                func.apply(context, args);
                lastRan = Date.now();
            } else {
                clearTimeout(lastFunc);
                lastFunc = setTimeout(function() {
                    if ((Date.now() - lastRan) >= limit) {
                        func.apply(context, args);
                        lastRan = Date.now();
                    }
                }, limit - (Date.now() - lastRan));
            }
        };
    }

    /**
     * Utility function to debounce function calls
     */
    function debounce(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }

    // Expose utilities to global scope for other scripts
    window.SEOForge = {
        throttle: throttle,
        debounce: debounce,
        trackEngagement: trackUserEngagement,
        trackPerformance: trackPagePerformance
    };

})(jQuery);
