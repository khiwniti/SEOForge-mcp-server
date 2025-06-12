<?php
/**
 * SEO Forge SEO Analyzer Template
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap seo-forge-analyzer">
    <h1><?php _e( 'SEO Analyzer', 'seo-forge' ); ?></h1>
    <p class="description"><?php _e( 'Analyze your content for SEO optimization and get actionable recommendations.', 'seo-forge' ); ?></p>
    
    <div class="seo-forge-analyzer-container">
        <div class="seo-forge-analyzer-form">
            <div class="seo-forge-card">
                <h3><?php _e( 'Content Analysis', 'seo-forge' ); ?></h3>
                
                <div class="seo-forge-form-group">
                    <label for="analyze_url"><?php _e( 'URL to Analyze', 'seo-forge' ); ?></label>
                    <input type="url" name="analyze_url" id="analyze_url" placeholder="<?php _e( 'https://example.com/page-to-analyze', 'seo-forge' ); ?>" />
                    <div class="description"><?php _e( 'Enter the URL of the page you want to analyze', 'seo-forge' ); ?></div>
                </div>

                <div class="seo-forge-form-group">
                    <label for="analyze_content"><?php _e( 'Content to Analyze', 'seo-forge' ); ?></label>
                    <textarea name="analyze_content" id="analyze_content" rows="10" placeholder="<?php _e( 'Paste your content here for analysis...', 'seo-forge' ); ?>"></textarea>
                    <div class="description"><?php _e( 'Paste the content you want to analyze for SEO optimization', 'seo-forge' ); ?></div>
                </div>

                <div class="seo-forge-form-group">
                    <label for="focus_keyword"><?php _e( 'Focus Keyword', 'seo-forge' ); ?></label>
                    <input type="text" name="focus_keyword" id="focus_keyword" placeholder="<?php _e( 'Enter your target keyword', 'seo-forge' ); ?>" />
                    <div class="description"><?php _e( 'The main keyword you want to optimize for', 'seo-forge' ); ?></div>
                </div>

                <div class="seo-forge-form-group">
                    <label for="meta_title"><?php _e( 'Meta Title', 'seo-forge' ); ?></label>
                    <input type="text" name="meta_title" id="meta_title" placeholder="<?php _e( 'Enter meta title', 'seo-forge' ); ?>" maxlength="60" />
                    <div class="description"><?php _e( 'The title tag for this page (recommended: 50-60 characters)', 'seo-forge' ); ?></div>
                    <div class="character-count">0/60</div>
                </div>

                <div class="seo-forge-form-group">
                    <label for="meta_description"><?php _e( 'Meta Description', 'seo-forge' ); ?></label>
                    <textarea name="meta_description" id="meta_description" rows="3" placeholder="<?php _e( 'Enter meta description', 'seo-forge' ); ?>" maxlength="160"></textarea>
                    <div class="description"><?php _e( 'The meta description for this page (recommended: 150-160 characters)', 'seo-forge' ); ?></div>
                    <div class="character-count">0/160</div>
                </div>

                <div class="seo-forge-form-group">
                    <button type="button" class="seo-forge-button seo-forge-analyze-content">
                        <?php _e( 'Analyze SEO', 'seo-forge' ); ?>
                    </button>
                    <button type="button" class="seo-forge-button secondary seo-forge-clear-form">
                        <?php _e( 'Clear Form', 'seo-forge' ); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="seo-forge-analyzer-results" style="display: none;">
            <div class="seo-forge-card">
                <h3><?php _e( 'SEO Analysis Results', 'seo-forge' ); ?></h3>
                <div class="seo-score-container">
                    <div class="seo-score-circle">
                        <span class="score-number">0</span>
                        <span class="score-label"><?php _e( 'SEO Score', 'seo-forge' ); ?></span>
                    </div>
                    <div class="score-description">
                        <p><?php _e( 'Your content will be analyzed and scored based on SEO best practices.', 'seo-forge' ); ?></p>
                    </div>
                </div>
                
                <div class="seo-recommendations">
                    <h4><?php _e( 'Recommendations', 'seo-forge' ); ?></h4>
                    <div class="recommendations-list">
                        <!-- Recommendations will be populated by JavaScript -->
                    </div>
                </div>

                <div class="seo-checklist">
                    <h4><?php _e( 'SEO Checklist', 'seo-forge' ); ?></h4>
                    <div class="checklist-items">
                        <!-- Checklist items will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="seo-forge-quick-tips">
        <div class="seo-forge-card">
            <h3><?php _e( 'SEO Quick Tips', 'seo-forge' ); ?></h3>
            <ul>
                <li><?php _e( 'Use your focus keyword in the title, first paragraph, and throughout the content naturally', 'seo-forge' ); ?></li>
                <li><?php _e( 'Keep your meta title under 60 characters and meta description under 160 characters', 'seo-forge' ); ?></li>
                <li><?php _e( 'Use header tags (H1, H2, H3) to structure your content', 'seo-forge' ); ?></li>
                <li><?php _e( 'Include internal and external links to relevant, high-quality content', 'seo-forge' ); ?></li>
                <li><?php _e( 'Optimize images with descriptive alt text and file names', 'seo-forge' ); ?></li>
                <li><?php _e( 'Ensure your content is comprehensive and provides value to readers', 'seo-forge' ); ?></li>
            </ul>
        </div>
    </div>
</div>

<style>
.seo-forge-analyzer-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 20px 0;
}

.seo-score-container {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.seo-score-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: conic-gradient(#28a745 0deg, #28a745 0deg, #e9ecef 0deg);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
}

.seo-score-circle::before {
    content: '';
    position: absolute;
    width: 70px;
    height: 70px;
    background: white;
    border-radius: 50%;
}

.score-number {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    z-index: 1;
}

.score-label {
    font-size: 12px;
    color: #666;
    z-index: 1;
}

.character-count {
    font-size: 12px;
    color: #666;
    text-align: right;
    margin-top: 5px;
}

.recommendations-list, .checklist-items {
    margin-top: 10px;
}

.recommendation-item, .checklist-item {
    padding: 10px;
    margin-bottom: 10px;
    border-left: 4px solid #ddd;
    background: #f8f9fa;
    border-radius: 0 4px 4px 0;
}

.recommendation-item.high-priority {
    border-left-color: #dc3545;
}

.recommendation-item.medium-priority {
    border-left-color: #ffc107;
}

.recommendation-item.low-priority {
    border-left-color: #28a745;
}

.checklist-item.passed {
    border-left-color: #28a745;
    background: #d4edda;
}

.checklist-item.failed {
    border-left-color: #dc3545;
    background: #f8d7da;
}

@media (max-width: 768px) {
    .seo-forge-analyzer-container {
        grid-template-columns: 1fr;
    }
    
    .seo-score-container {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Character counting
    $('#meta_title').on('input', function() {
        const length = $(this).val().length;
        $(this).siblings('.character-count').text(length + '/60');
        
        if (length > 60) {
            $(this).siblings('.character-count').css('color', '#dc3545');
        } else if (length > 50) {
            $(this).siblings('.character-count').css('color', '#ffc107');
        } else {
            $(this).siblings('.character-count').css('color', '#28a745');
        }
    });

    $('#meta_description').on('input', function() {
        const length = $(this).val().length;
        $(this).siblings('.character-count').text(length + '/160');
        
        if (length > 160) {
            $(this).siblings('.character-count').css('color', '#dc3545');
        } else if (length > 150) {
            $(this).siblings('.character-count').css('color', '#ffc107');
        } else {
            $(this).siblings('.character-count').css('color', '#28a745');
        }
    });

    // Clear form
    $('.seo-forge-clear-form').on('click', function() {
        $('.seo-forge-analyzer-form input, .seo-forge-analyzer-form textarea').val('');
        $('.character-count').text('0/60').css('color', '#666');
        $('.seo-forge-analyzer-results').hide();
    });

    // Auto-populate from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const postId = urlParams.get('post_id');
    if (postId) {
        // Could fetch post data via AJAX if needed
    }
});
</script>