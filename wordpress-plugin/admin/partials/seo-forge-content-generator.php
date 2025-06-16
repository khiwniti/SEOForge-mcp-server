<?php
/**
 * Content Generator page for SEO-Forge plugin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap seo-forge-content-generator">
    <h1><i class="fas fa-magic"></i> <?php _e('Content Generator', 'seo-forge'); ?></h1>

    <div class="content-generator-container">
        <div class="generator-tabs">
            <div class="tab-nav">
                <button class="tab-button active" data-tab="blog-content">
                    <i class="fas fa-file-alt"></i> <?php _e('Blog Content', 'seo-forge'); ?>
                </button>
                <button class="tab-button" data-tab="seo-optimization">
                    <i class="fas fa-search"></i> <?php _e('SEO Optimization', 'seo-forge'); ?>
                </button>
                <button class="tab-button" data-tab="image-generation">
                    <i class="fas fa-image"></i> <?php _e('Image Generation', 'seo-forge'); ?>
                </button>
            </div>

            <div class="tab-content">
                <!-- Blog Content Tab -->
                <div id="blog-content" class="tab-panel active">
                    <div class="panel-header">
                        <h2><?php _e('Generate Blog Content', 'seo-forge'); ?></h2>
                        <p><?php _e('Create SEO-optimized blog posts with AI assistance', 'seo-forge'); ?></p>
                    </div>

                    <form id="blog-content-form" class="generator-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="blog-topic"><?php _e('Topic/Title', 'seo-forge'); ?></label>
                                <input type="text" id="blog-topic" name="topic" placeholder="<?php _e('Enter your blog topic or title', 'seo-forge'); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="blog-keywords"><?php _e('Target Keywords', 'seo-forge'); ?></label>
                                <input type="text" id="blog-keywords" name="keywords" placeholder="<?php _e('Enter keywords separated by commas', 'seo-forge'); ?>">
                                <small class="form-text"><?php _e('Optional: Add target keywords for better SEO optimization', 'seo-forge'); ?></small>
                            </div>
                            <div class="form-group">
                                <label for="word-count"><?php _e('Word Count', 'seo-forge'); ?></label>
                                <select id="word-count" name="word_count">
                                    <option value="500">500 <?php _e('words', 'seo-forge'); ?></option>
                                    <option value="1000" selected>1000 <?php _e('words', 'seo-forge'); ?></option>
                                    <option value="1500">1500 <?php _e('words', 'seo-forge'); ?></option>
                                    <option value="2000">2000 <?php _e('words', 'seo-forge'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="button button-primary">
                                <i class="fas fa-magic"></i> <?php _e('Generate Content', 'seo-forge'); ?>
                            </button>
                            <button type="button" class="button" id="get-keyword-suggestions">
                                <i class="fas fa-lightbulb"></i> <?php _e('Get Keyword Suggestions', 'seo-forge'); ?>
                            </button>
                        </div>
                    </form>

                    <div id="blog-content-result" class="generation-result" style="display: none;">
                        <div class="result-header">
                            <h3><?php _e('Generated Content', 'seo-forge'); ?></h3>
                            <div class="result-actions">
                                <button class="button" id="copy-content">
                                    <i class="fas fa-copy"></i> <?php _e('Copy', 'seo-forge'); ?>
                                </button>
                                <button class="button button-primary" id="create-post">
                                    <i class="fas fa-plus"></i> <?php _e('Create Post', 'seo-forge'); ?>
                                </button>
                            </div>
                        </div>
                        <div class="result-content">
                            <div id="generated-content"></div>
                        </div>
                    </div>
                </div>

                <!-- SEO Optimization Tab -->
                <div id="seo-optimization" class="tab-panel">
                    <div class="panel-header">
                        <h2><?php _e('SEO Optimization Tools', 'seo-forge'); ?></h2>
                        <p><?php _e('Optimize your content for better search engine rankings', 'seo-forge'); ?></p>
                    </div>

                    <div class="seo-tools">
                        <div class="tool-section">
                            <h3><?php _e('Title Optimization', 'seo-forge'); ?></h3>
                            <form id="title-generator-form">
                                <div class="form-group">
                                    <label for="content-for-title"><?php _e('Content', 'seo-forge'); ?></label>
                                    <textarea id="content-for-title" name="content" rows="4" placeholder="<?php _e('Paste your content here to generate an optimized title', 'seo-forge'); ?>"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="target-keyword-title"><?php _e('Target Keyword', 'seo-forge'); ?></label>
                                    <input type="text" id="target-keyword-title" name="keyword" placeholder="<?php _e('Enter target keyword', 'seo-forge'); ?>">
                                </div>
                                <button type="submit" class="button button-primary">
                                    <i class="fas fa-heading"></i> <?php _e('Generate Title', 'seo-forge'); ?>
                                </button>
                            </form>
                            <div id="title-result" class="tool-result"></div>
                        </div>

                        <div class="tool-section">
                            <h3><?php _e('Meta Description', 'seo-forge'); ?></h3>
                            <form id="meta-generator-form">
                                <div class="form-group">
                                    <label for="content-for-meta"><?php _e('Content', 'seo-forge'); ?></label>
                                    <textarea id="content-for-meta" name="content" rows="4" placeholder="<?php _e('Paste your content here to generate a meta description', 'seo-forge'); ?>"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="target-keyword-meta"><?php _e('Target Keyword', 'seo-forge'); ?></label>
                                    <input type="text" id="target-keyword-meta" name="keyword" placeholder="<?php _e('Enter target keyword', 'seo-forge'); ?>">
                                </div>
                                <button type="submit" class="button button-primary">
                                    <i class="fas fa-tags"></i> <?php _e('Generate Meta Description', 'seo-forge'); ?>
                                </button>
                            </form>
                            <div id="meta-result" class="tool-result"></div>
                        </div>

                        <div class="tool-section">
                            <h3><?php _e('SEO Analysis', 'seo-forge'); ?></h3>
                            <form id="seo-analysis-form">
                                <div class="form-group">
                                    <label for="content-for-analysis"><?php _e('Content to Analyze', 'seo-forge'); ?></label>
                                    <textarea id="content-for-analysis" name="content" rows="6" placeholder="<?php _e('Paste your content here for SEO analysis', 'seo-forge'); ?>"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="target-keyword-analysis"><?php _e('Target Keyword', 'seo-forge'); ?></label>
                                    <input type="text" id="target-keyword-analysis" name="keyword" placeholder="<?php _e('Enter target keyword', 'seo-forge'); ?>">
                                </div>
                                <button type="submit" class="button button-primary">
                                    <i class="fas fa-chart-line"></i> <?php _e('Analyze SEO', 'seo-forge'); ?>
                                </button>
                            </form>
                            <div id="seo-analysis-result" class="tool-result"></div>
                        </div>
                    </div>
                </div>

                <!-- Image Generation Tab -->
                <div id="image-generation" class="tab-panel">
                    <div class="panel-header">
                        <h2><?php _e('AI Image Generation', 'seo-forge'); ?></h2>
                        <p><?php _e('Create custom images for your content using AI', 'seo-forge'); ?></p>
                    </div>

                    <form id="image-generator-form" class="generator-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="image-prompt"><?php _e('Image Description', 'seo-forge'); ?></label>
                                <textarea id="image-prompt" name="prompt" rows="3" placeholder="<?php _e('Describe the image you want to generate...', 'seo-forge'); ?>" required></textarea>
                                <small class="form-text"><?php _e('Be specific about colors, style, objects, and mood', 'seo-forge'); ?></small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="image-style"><?php _e('Image Style', 'seo-forge'); ?></label>
                                <select id="image-style" name="style">
                                    <option value="realistic"><?php _e('Realistic', 'seo-forge'); ?></option>
                                    <option value="artistic"><?php _e('Artistic', 'seo-forge'); ?></option>
                                    <option value="cartoon"><?php _e('Cartoon', 'seo-forge'); ?></option>
                                    <option value="sketch"><?php _e('Sketch', 'seo-forge'); ?></option>
                                    <option value="modern"><?php _e('Modern', 'seo-forge'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="button button-primary">
                                <i class="fas fa-image"></i> <?php _e('Generate Image', 'seo-forge'); ?>
                            </button>
                        </div>
                    </form>

                    <div id="image-generation-result" class="generation-result" style="display: none;">
                        <div class="result-header">
                            <h3><?php _e('Generated Image', 'seo-forge'); ?></h3>
                            <div class="result-actions">
                                <button class="button" id="download-image">
                                    <i class="fas fa-download"></i> <?php _e('Download', 'seo-forge'); ?>
                                </button>
                                <button class="button button-primary" id="add-to-media">
                                    <i class="fas fa-plus"></i> <?php _e('Add to Media Library', 'seo-forge'); ?>
                                </button>
                            </div>
                        </div>
                        <div class="result-content">
                            <div id="generated-image"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading overlay -->
    <div id="loading-overlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="spinner"></div>
            <p id="loading-message"><?php _e('Generating content...', 'seo-forge'); ?></p>
        </div>
    </div>
</div>
