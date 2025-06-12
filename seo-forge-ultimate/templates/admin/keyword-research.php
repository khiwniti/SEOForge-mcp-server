<?php
/**
 * SEO Forge Keyword Research Template
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap seo-forge-keywords">
    <h1><?php _e( 'Keyword Research', 'seo-forge' ); ?></h1>
    <p class="description"><?php _e( 'Discover high-value keywords for your content strategy with search volume, difficulty, and competition data.', 'seo-forge' ); ?></p>
    
    <div class="seo-forge-keywords-container">
        <div class="seo-forge-keywords-form">
            <div class="seo-forge-card">
                <h3><?php _e( 'Keyword Research', 'seo-forge' ); ?></h3>
                
                <div class="seo-forge-form-group">
                    <label for="seed_keyword"><?php _e( 'Seed Keyword', 'seo-forge' ); ?></label>
                    <input type="text" name="seed_keyword" id="seed_keyword" placeholder="<?php _e( 'Enter your main keyword or topic', 'seo-forge' ); ?>" />
                    <div class="description"><?php _e( 'Enter a keyword or topic to find related keywords', 'seo-forge' ); ?></div>
                </div>

                <div class="seo-forge-form-group">
                    <label for="research_language"><?php _e( 'Language', 'seo-forge' ); ?></label>
                    <select name="research_language" id="research_language">
                        <?php foreach ( SEO_Forge_Keyword_Research::get_languages() as $key => $label ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, get_option( 'seo_forge_default_language', 'en' ) ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="description"><?php _e( 'Target language for keyword research', 'seo-forge' ); ?></div>
                </div>

                <div class="seo-forge-form-group">
                    <label for="research_country"><?php _e( 'Country', 'seo-forge' ); ?></label>
                    <select name="research_country" id="research_country">
                        <?php foreach ( SEO_Forge_Keyword_Research::get_countries() as $key => $label ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, get_option( 'seo_forge_default_country', 'US' ) ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="description"><?php _e( 'Target country for search volume data', 'seo-forge' ); ?></div>
                </div>

                <div class="seo-forge-form-group">
                    <label for="keyword_limit"><?php _e( 'Number of Keywords', 'seo-forge' ); ?></label>
                    <select name="keyword_limit" id="keyword_limit">
                        <option value="25">25 <?php _e( 'keywords', 'seo-forge' ); ?></option>
                        <option value="50" selected>50 <?php _e( 'keywords', 'seo-forge' ); ?></option>
                        <option value="100">100 <?php _e( 'keywords', 'seo-forge' ); ?></option>
                        <option value="200">200 <?php _e( 'keywords', 'seo-forge' ); ?></option>
                    </select>
                    <div class="description"><?php _e( 'Maximum number of keywords to return', 'seo-forge' ); ?></div>
                </div>

                <div class="seo-forge-form-group">
                    <button type="button" class="seo-forge-button seo-forge-research-keywords">
                        <?php _e( 'Research Keywords', 'seo-forge' ); ?>
                    </button>
                    <button type="button" class="seo-forge-button secondary seo-forge-get-suggestions">
                        <?php _e( 'Get Suggestions', 'seo-forge' ); ?>
                    </button>
                </div>
            </div>

            <div class="seo-forge-card">
                <h3><?php _e( 'Keyword Filters', 'seo-forge' ); ?></h3>
                
                <div class="seo-forge-form-group">
                    <label for="min_search_volume"><?php _e( 'Minimum Search Volume', 'seo-forge' ); ?></label>
                    <input type="number" name="min_search_volume" id="min_search_volume" value="100" min="0" />
                    <div class="description"><?php _e( 'Filter keywords by minimum monthly search volume', 'seo-forge' ); ?></div>
                </div>

                <div class="seo-forge-form-group">
                    <label for="max_difficulty"><?php _e( 'Maximum Difficulty', 'seo-forge' ); ?></label>
                    <input type="range" name="max_difficulty" id="max_difficulty" min="0" max="100" value="70" />
                    <div class="range-value">70</div>
                    <div class="description"><?php _e( 'Filter keywords by maximum SEO difficulty (0-100)', 'seo-forge' ); ?></div>
                </div>

                <div class="seo-forge-form-group">
                    <label for="keyword_type"><?php _e( 'Keyword Type', 'seo-forge' ); ?></label>
                    <select name="keyword_type" id="keyword_type">
                        <option value="all"><?php _e( 'All Keywords', 'seo-forge' ); ?></option>
                        <option value="questions"><?php _e( 'Questions', 'seo-forge' ); ?></option>
                        <option value="long_tail"><?php _e( 'Long-tail Keywords', 'seo-forge' ); ?></option>
                        <option value="commercial"><?php _e( 'Commercial Intent', 'seo-forge' ); ?></option>
                        <option value="informational"><?php _e( 'Informational', 'seo-forge' ); ?></option>
                    </select>
                    <div class="description"><?php _e( 'Filter by keyword intent and type', 'seo-forge' ); ?></div>
                </div>
            </div>
        </div>

        <div class="seo-forge-keywords-results" style="display: none;">
            <div class="seo-forge-card">
                <div class="keywords-header">
                    <h3><?php _e( 'Keyword Research Results', 'seo-forge' ); ?></h3>
                    <div class="keywords-actions">
                        <button type="button" class="seo-forge-button secondary seo-forge-export-keywords">
                            <?php _e( 'Export CSV', 'seo-forge' ); ?>
                        </button>
                        <button type="button" class="seo-forge-button secondary seo-forge-save-keywords">
                            <?php _e( 'Save Keywords', 'seo-forge' ); ?>
                        </button>
                    </div>
                </div>
                
                <div class="keywords-stats">
                    <div class="stat-item">
                        <span class="stat-number" id="total-keywords">0</span>
                        <span class="stat-label"><?php _e( 'Keywords Found', 'seo-forge' ); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="avg-volume">0</span>
                        <span class="stat-label"><?php _e( 'Avg. Volume', 'seo-forge' ); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="avg-difficulty">0</span>
                        <span class="stat-label"><?php _e( 'Avg. Difficulty', 'seo-forge' ); ?></span>
                    </div>
                </div>

                <div class="keywords-table-container">
                    <table class="keywords-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all-keywords"></th>
                                <th class="sortable" data-sort="keyword"><?php _e( 'Keyword', 'seo-forge' ); ?> <span class="sort-arrow"></span></th>
                                <th class="sortable" data-sort="volume"><?php _e( 'Search Volume', 'seo-forge' ); ?> <span class="sort-arrow"></span></th>
                                <th class="sortable" data-sort="difficulty"><?php _e( 'Difficulty', 'seo-forge' ); ?> <span class="sort-arrow"></span></th>
                                <th class="sortable" data-sort="cpc"><?php _e( 'CPC', 'seo-forge' ); ?> <span class="sort-arrow"></span></th>
                                <th><?php _e( 'Competition', 'seo-forge' ); ?></th>
                                <th><?php _e( 'Actions', 'seo-forge' ); ?></th>
                            </tr>
                        </thead>
                        <tbody id="keywords-table-body">
                            <!-- Keywords will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="seo-forge-keyword-tips">
        <div class="seo-forge-card">
            <h3><?php _e( 'Keyword Research Tips', 'seo-forge' ); ?></h3>
            <div class="tips-grid">
                <div class="tip-item">
                    <h4><?php _e( 'Search Volume', 'seo-forge' ); ?></h4>
                    <p><?php _e( 'Higher search volume means more potential traffic, but also more competition.', 'seo-forge' ); ?></p>
                </div>
                <div class="tip-item">
                    <h4><?php _e( 'Keyword Difficulty', 'seo-forge' ); ?></h4>
                    <p><?php _e( 'Lower difficulty scores indicate easier ranking opportunities for new content.', 'seo-forge' ); ?></p>
                </div>
                <div class="tip-item">
                    <h4><?php _e( 'Long-tail Keywords', 'seo-forge' ); ?></h4>
                    <p><?php _e( 'Longer, more specific keywords often have lower competition and higher conversion rates.', 'seo-forge' ); ?></p>
                </div>
                <div class="tip-item">
                    <h4><?php _e( 'User Intent', 'seo-forge' ); ?></h4>
                    <p><?php _e( 'Consider whether users are looking for information, products, or services.', 'seo-forge' ); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.seo-forge-keywords-container {
    display: grid;
    grid-template-columns: 400px 1fr;
    gap: 20px;
    margin: 20px 0;
}

.seo-forge-keywords-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.range-value {
    text-align: center;
    font-weight: bold;
    color: #2271b1;
    margin-top: 5px;
}

.keywords-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.keywords-actions {
    display: flex;
    gap: 10px;
}

.keywords-stats {
    display: flex;
    gap: 30px;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #2271b1;
}

.stat-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
}

.keywords-table-container {
    overflow-x: auto;
}

.keywords-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.keywords-table th,
.keywords-table td {
    padding: 12px 8px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.keywords-table th {
    background: #f8f9fa;
    font-weight: 600;
    position: sticky;
    top: 0;
}

.keywords-table th.sortable {
    cursor: pointer;
    user-select: none;
}

.keywords-table th.sortable:hover {
    background: #e9ecef;
}

.sort-arrow {
    margin-left: 5px;
    opacity: 0.5;
}

.sort-arrow.asc::after {
    content: '↑';
}

.sort-arrow.desc::after {
    content: '↓';
}

.difficulty-bar {
    width: 60px;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.difficulty-fill {
    height: 100%;
    border-radius: 4px;
}

.difficulty-easy { background: #28a745; }
.difficulty-medium { background: #ffc107; }
.difficulty-hard { background: #dc3545; }

.competition-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
}

.competition-low { background: #d4edda; color: #155724; }
.competition-medium { background: #fff3cd; color: #856404; }
.competition-high { background: #f8d7da; color: #721c24; }

.tips-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.tip-item h4 {
    margin: 0 0 8px 0;
    color: #2271b1;
}

.tip-item p {
    margin: 0;
    color: #666;
    font-size: 14px;
    line-height: 1.5;
}

@media (max-width: 1024px) {
    .seo-forge-keywords-container {
        grid-template-columns: 1fr;
    }
    
    .seo-forge-keywords-form {
        flex-direction: row;
        flex-wrap: wrap;
    }
    
    .seo-forge-keywords-form .seo-forge-card {
        flex: 1;
        min-width: 300px;
    }
}

@media (max-width: 768px) {
    .keywords-header {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }
    
    .keywords-stats {
        flex-direction: column;
        gap: 15px;
    }
    
    .tips-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Range slider value display
    $('#max_difficulty').on('input', function() {
        $('.range-value').text($(this).val());
    });

    // Select all keywords
    $('#select-all-keywords').on('change', function() {
        $('.keyword-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Table sorting
    $('.sortable').on('click', function() {
        const column = $(this).data('sort');
        const currentSort = $(this).find('.sort-arrow');
        const isAsc = currentSort.hasClass('asc');
        
        // Reset all sort arrows
        $('.sort-arrow').removeClass('asc desc');
        
        // Set current sort direction
        if (isAsc) {
            currentSort.addClass('desc');
        } else {
            currentSort.addClass('asc');
        }
        
        // Sort table (would be implemented with actual data)
        console.log('Sorting by', column, isAsc ? 'desc' : 'asc');
    });

    // Export keywords
    $('.seo-forge-export-keywords').on('click', function() {
        // Implementation for CSV export
        console.log('Exporting keywords to CSV');
    });

    // Save keywords
    $('.seo-forge-save-keywords').on('click', function() {
        // Implementation for saving keywords
        console.log('Saving selected keywords');
    });
});
</script>