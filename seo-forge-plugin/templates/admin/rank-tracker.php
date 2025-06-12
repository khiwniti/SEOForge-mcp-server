<?php
/**
 * Rank Tracker Admin Page
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap seo-forge-admin">
	<h1><?php _e( 'SEO Forge - Rank Tracker', 'seo-forge' ); ?></h1>
	
	<div class="seo-forge-container">
		<div class="seo-forge-header">
			<div class="seo-forge-nav">
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge' ); ?>" class="nav-tab">
					<?php _e( 'Dashboard', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-keywords' ); ?>" class="nav-tab">
					<?php _e( 'Keyword Research', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-rank-tracker' ); ?>" class="nav-tab nav-tab-active">
					<?php _e( 'Rank Tracker', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-analytics' ); ?>" class="nav-tab">
					<?php _e( 'Analytics', 'seo-forge' ); ?>
				</a>
			</div>
		</div>

		<div class="seo-forge-content">
			<!-- Add Keywords -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h2><?php _e( 'Track New Keywords', 'seo-forge' ); ?></h2>
				</div>

				<div class="card-body">
					<form id="add-keywords-form" class="seo-forge-form">
						<?php wp_nonce_field( 'seo_forge_nonce', 'seo_forge_nonce' ); ?>
						
						<div class="form-row">
							<div class="form-group">
								<label for="keywords-to-track"><?php _e( 'Keywords to Track', 'seo-forge' ); ?></label>
								<textarea 
									id="keywords-to-track" 
									name="keywords" 
									rows="3" 
									placeholder="<?php _e( 'Enter keywords to track (one per line)', 'seo-forge' ); ?>"
									required
								></textarea>
								<small class="form-text"><?php _e( 'Add up to 50 keywords at once.', 'seo-forge' ); ?></small>
							</div>

							<div class="form-group">
								<label for="target-location"><?php _e( 'Target Location', 'seo-forge' ); ?></label>
								<select id="target-location" name="location">
									<option value="global"><?php _e( 'Global', 'seo-forge' ); ?></option>
									<option value="US"><?php _e( 'United States', 'seo-forge' ); ?></option>
									<option value="GB"><?php _e( 'United Kingdom', 'seo-forge' ); ?></option>
									<option value="CA"><?php _e( 'Canada', 'seo-forge' ); ?></option>
									<option value="AU"><?php _e( 'Australia', 'seo-forge' ); ?></option>
									<option value="TH"><?php _e( 'Thailand', 'seo-forge' ); ?></option>
									<option value="DE"><?php _e( 'Germany', 'seo-forge' ); ?></option>
									<option value="FR"><?php _e( 'France', 'seo-forge' ); ?></option>
									<option value="ES"><?php _e( 'Spain', 'seo-forge' ); ?></option>
									<option value="IT"><?php _e( 'Italy', 'seo-forge' ); ?></option>
									<option value="JP"><?php _e( 'Japan', 'seo-forge' ); ?></option>
								</select>
							</div>
						</div>

						<div class="form-row">
							<div class="form-group">
								<label for="target-device"><?php _e( 'Device Type', 'seo-forge' ); ?></label>
								<select id="target-device" name="device">
									<option value="desktop"><?php _e( 'Desktop', 'seo-forge' ); ?></option>
									<option value="mobile"><?php _e( 'Mobile', 'seo-forge' ); ?></option>
									<option value="tablet"><?php _e( 'Tablet', 'seo-forge' ); ?></option>
								</select>
							</div>

							<div class="form-group">
								<label for="tracking-frequency"><?php _e( 'Tracking Frequency', 'seo-forge' ); ?></label>
								<select id="tracking-frequency" name="frequency">
									<option value="daily"><?php _e( 'Daily', 'seo-forge' ); ?></option>
									<option value="weekly"><?php _e( 'Weekly', 'seo-forge' ); ?></option>
									<option value="monthly"><?php _e( 'Monthly', 'seo-forge' ); ?></option>
								</select>
							</div>
						</div>

						<div class="form-actions">
							<button type="submit" class="button button-primary button-large">
								<span class="dashicons dashicons-plus-alt"></span>
								<?php _e( 'Start Tracking', 'seo-forge' ); ?>
							</button>
						</div>
					</form>
				</div>
			</div>

			<!-- Ranking Overview -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h3><?php _e( 'Ranking Overview', 'seo-forge' ); ?></h3>
					<div class="overview-filters">
						<select id="time-period">
							<option value="7"><?php _e( 'Last 7 days', 'seo-forge' ); ?></option>
							<option value="30"><?php _e( 'Last 30 days', 'seo-forge' ); ?></option>
							<option value="90"><?php _e( 'Last 3 months', 'seo-forge' ); ?></option>
							<option value="365"><?php _e( 'Last year', 'seo-forge' ); ?></option>
						</select>
						<button id="refresh-rankings" class="button">
							<span class="dashicons dashicons-update"></span>
							<?php _e( 'Refresh', 'seo-forge' ); ?>
						</button>
					</div>
				</div>

				<div class="card-body">
					<div class="ranking-stats">
						<div class="stat-item">
							<div class="stat-number" id="total-keywords">0</div>
							<div class="stat-label"><?php _e( 'Keywords Tracked', 'seo-forge' ); ?></div>
						</div>
						<div class="stat-item">
							<div class="stat-number" id="avg-position">-</div>
							<div class="stat-label"><?php _e( 'Average Position', 'seo-forge' ); ?></div>
						</div>
						<div class="stat-item">
							<div class="stat-number" id="top-10-keywords">0</div>
							<div class="stat-label"><?php _e( 'Top 10 Rankings', 'seo-forge' ); ?></div>
						</div>
						<div class="stat-item">
							<div class="stat-number" id="improved-rankings">0</div>
							<div class="stat-label"><?php _e( 'Improved Rankings', 'seo-forge' ); ?></div>
						</div>
					</div>

					<!-- Ranking Chart -->
					<div class="ranking-chart-container">
						<canvas id="ranking-chart" width="400" height="200"></canvas>
					</div>
				</div>
			</div>

			<!-- Keywords Table -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h3><?php _e( 'Tracked Keywords', 'seo-forge' ); ?></h3>
					<div class="table-controls">
						<input type="text" id="keyword-search" placeholder="<?php _e( 'Search keywords...', 'seo-forge' ); ?>" />
						<select id="position-filter">
							<option value="all"><?php _e( 'All Positions', 'seo-forge' ); ?></option>
							<option value="1-10"><?php _e( 'Top 10', 'seo-forge' ); ?></option>
							<option value="11-20"><?php _e( '11-20', 'seo-forge' ); ?></option>
							<option value="21-50"><?php _e( '21-50', 'seo-forge' ); ?></option>
							<option value="51+"><?php _e( '51+', 'seo-forge' ); ?></option>
						</select>
					</div>
				</div>

				<div class="card-body">
					<div class="keywords-table-container">
						<table id="keywords-table" class="wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th class="sortable" data-sort="keyword">
										<?php _e( 'Keyword', 'seo-forge' ); ?>
										<span class="sort-indicator"></span>
									</th>
									<th class="sortable" data-sort="position">
										<?php _e( 'Position', 'seo-forge' ); ?>
										<span class="sort-indicator"></span>
									</th>
									<th class="sortable" data-sort="change">
										<?php _e( 'Change', 'seo-forge' ); ?>
										<span class="sort-indicator"></span>
									</th>
									<th class="sortable" data-sort="volume">
										<?php _e( 'Search Volume', 'seo-forge' ); ?>
										<span class="sort-indicator"></span>
									</th>
									<th class="sortable" data-sort="difficulty">
										<?php _e( 'Difficulty', 'seo-forge' ); ?>
										<span class="sort-indicator"></span>
									</th>
									<th><?php _e( 'URL', 'seo-forge' ); ?></th>
									<th><?php _e( 'Last Updated', 'seo-forge' ); ?></th>
									<th><?php _e( 'Actions', 'seo-forge' ); ?></th>
								</tr>
							</thead>
							<tbody id="keywords-tbody">
								<tr class="no-keywords">
									<td colspan="8" class="text-center">
										<span class="dashicons dashicons-chart-line"></span>
										<p><?php _e( 'No keywords are being tracked yet. Add some keywords to get started!', 'seo-forge' ); ?></p>
									</td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="table-pagination">
						<div class="pagination-info">
							<span id="pagination-info"><?php _e( 'Showing 0 of 0 keywords', 'seo-forge' ); ?></span>
						</div>
						<div class="pagination-controls">
							<button id="prev-page" class="button" disabled>
								<span class="dashicons dashicons-arrow-left-alt2"></span>
								<?php _e( 'Previous', 'seo-forge' ); ?>
							</button>
							<span id="page-numbers"></span>
							<button id="next-page" class="button" disabled>
								<?php _e( 'Next', 'seo-forge' ); ?>
								<span class="dashicons dashicons-arrow-right-alt2"></span>
							</button>
						</div>
					</div>
				</div>
			</div>

			<!-- Loading State -->
			<div id="tracking-loading" class="seo-forge-loading" style="display: none;">
				<div class="loading-spinner"></div>
				<p><?php _e( 'Updating keyword rankings... This may take a few moments.', 'seo-forge' ); ?></p>
			</div>
		</div>
	</div>
</div>

<style>
.seo-forge-admin .ranking-stats {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 20px;
	margin-bottom: 30px;
}

.seo-forge-admin .ranking-chart-container {
	margin: 30px 0;
	padding: 20px;
	background: #f8f9fa;
	border-radius: 8px;
	text-align: center;
}

.seo-forge-admin .table-controls {
	display: flex;
	gap: 15px;
	align-items: center;
}

.seo-forge-admin .table-controls input,
.seo-forge-admin .table-controls select {
	min-width: 150px;
}

.seo-forge-admin .keywords-table-container {
	overflow-x: auto;
	margin-bottom: 20px;
}

.seo-forge-admin #keywords-table {
	min-width: 800px;
}

.seo-forge-admin #keywords-table th.sortable {
	cursor: pointer;
	position: relative;
	user-select: none;
}

.seo-forge-admin #keywords-table th.sortable:hover {
	background: #f0f0f1;
}

.seo-forge-admin .sort-indicator {
	position: absolute;
	right: 8px;
	top: 50%;
	transform: translateY(-50%);
	opacity: 0.5;
}

.seo-forge-admin .sort-indicator:before {
	content: "↕";
}

.seo-forge-admin th.sorted-asc .sort-indicator:before {
	content: "↑";
	opacity: 1;
}

.seo-forge-admin th.sorted-desc .sort-indicator:before {
	content: "↓";
	opacity: 1;
}

.seo-forge-admin .position-badge {
	display: inline-block;
	padding: 4px 8px;
	border-radius: 12px;
	font-size: 12px;
	font-weight: bold;
	text-align: center;
	min-width: 30px;
}

.seo-forge-admin .position-badge.top-3 {
	background: #d4edda;
	color: #155724;
}

.seo-forge-admin .position-badge.top-10 {
	background: #d1ecf1;
	color: #0c5460;
}

.seo-forge-admin .position-badge.top-20 {
	background: #fff3cd;
	color: #856404;
}

.seo-forge-admin .position-badge.low {
	background: #f8d7da;
	color: #721c24;
}

.seo-forge-admin .change-indicator {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	font-size: 12px;
	font-weight: bold;
}

.seo-forge-admin .change-indicator.positive {
	color: #28a745;
}

.seo-forge-admin .change-indicator.negative {
	color: #dc3545;
}

.seo-forge-admin .change-indicator.neutral {
	color: #6c757d;
}

.seo-forge-admin .difficulty-bar {
	width: 60px;
	height: 8px;
	background: #e9ecef;
	border-radius: 4px;
	overflow: hidden;
	position: relative;
}

.seo-forge-admin .difficulty-fill {
	height: 100%;
	border-radius: 4px;
	transition: width 0.3s ease;
}

.seo-forge-admin .difficulty-fill.easy {
	background: #28a745;
}

.seo-forge-admin .difficulty-fill.medium {
	background: #ffc107;
}

.seo-forge-admin .difficulty-fill.hard {
	background: #dc3545;
}

.seo-forge-admin .table-pagination {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 15px 0;
	border-top: 1px solid #ddd;
}

.seo-forge-admin .pagination-controls {
	display: flex;
	gap: 10px;
	align-items: center;
}

.seo-forge-admin .no-keywords {
	text-align: center;
	color: #666;
}

.seo-forge-admin .no-keywords .dashicons {
	font-size: 48px;
	margin-bottom: 10px;
	opacity: 0.5;
}

.seo-forge-admin .form-row {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 20px;
	margin-bottom: 20px;
}

@media (max-width: 768px) {
	.seo-forge-admin .form-row {
		grid-template-columns: 1fr;
	}
	
	.seo-forge-admin .ranking-stats {
		grid-template-columns: repeat(2, 1fr);
	}
	
	.seo-forge-admin .table-controls {
		flex-direction: column;
		align-items: stretch;
	}
	
	.seo-forge-admin .table-pagination {
		flex-direction: column;
		gap: 15px;
	}
}
</style>

<script>
jQuery(document).ready(function($) {
	let currentPage = 1;
	let keywordsData = [];
	let filteredData = [];
	
	// Add keywords form
	$('#add-keywords-form').on('submit', function(e) {
		e.preventDefault();
		
		const keywords = $('#keywords-to-track').val().split('\n').filter(k => k.trim());
		if (keywords.length === 0) {
			alert('<?php _e( 'Please enter at least one keyword.', 'seo-forge' ); ?>');
			return;
		}
		
		if (keywords.length > 50) {
			alert('<?php _e( 'Maximum 50 keywords allowed at once.', 'seo-forge' ); ?>');
			return;
		}
		
		$('#tracking-loading').show();
		
		// Simulate adding keywords
		setTimeout(function() {
			$('#tracking-loading').hide();
			$('#keywords-to-track').val('');
			
			// Add sample keywords to the table
			addSampleKeywords(keywords);
			updateStats();
			
			alert('<?php _e( 'Keywords added successfully! Rankings will be updated within 24 hours.', 'seo-forge' ); ?>');
		}, 2000);
	});
	
	// Refresh rankings
	$('#refresh-rankings').on('click', function() {
		$('#tracking-loading').show();
		
		setTimeout(function() {
			$('#tracking-loading').hide();
			updateRankings();
			alert('<?php _e( 'Rankings updated successfully!', 'seo-forge' ); ?>');
		}, 3000);
	});
	
	// Search and filter
	$('#keyword-search').on('input', function() {
		filterKeywords();
	});
	
	$('#position-filter').on('change', function() {
		filterKeywords();
	});
	
	// Table sorting
	$('.sortable').on('click', function() {
		const sortBy = $(this).data('sort');
		const isAsc = $(this).hasClass('sorted-asc');
		
		$('.sortable').removeClass('sorted-asc sorted-desc');
		
		if (isAsc) {
			$(this).addClass('sorted-desc');
			sortKeywords(sortBy, 'desc');
		} else {
			$(this).addClass('sorted-asc');
			sortKeywords(sortBy, 'asc');
		}
	});
	
	function addSampleKeywords(newKeywords) {
		newKeywords.forEach(function(keyword, index) {
			const keywordData = {
				keyword: keyword.trim(),
				position: Math.floor(Math.random() * 100) + 1,
				change: Math.floor(Math.random() * 21) - 10,
				volume: Math.floor(Math.random() * 10000) + 100,
				difficulty: Math.floor(Math.random() * 100) + 1,
				url: '<?php echo home_url(); ?>',
				lastUpdated: '<?php echo date('Y-m-d H:i:s'); ?>'
			};
			
			keywordsData.push(keywordData);
		});
		
		filteredData = [...keywordsData];
		renderKeywordsTable();
	}
	
	function updateStats() {
		const totalKeywords = keywordsData.length;
		const avgPosition = totalKeywords > 0 ? 
			Math.round(keywordsData.reduce((sum, k) => sum + k.position, 0) / totalKeywords) : 0;
		const top10Count = keywordsData.filter(k => k.position <= 10).length;
		const improvedCount = keywordsData.filter(k => k.change > 0).length;
		
		$('#total-keywords').text(totalKeywords);
		$('#avg-position').text(avgPosition || '-');
		$('#top-10-keywords').text(top10Count);
		$('#improved-rankings').text(improvedCount);
	}
	
	function filterKeywords() {
		const searchTerm = $('#keyword-search').val().toLowerCase();
		const positionFilter = $('#position-filter').val();
		
		filteredData = keywordsData.filter(function(keyword) {
			const matchesSearch = keyword.keyword.toLowerCase().includes(searchTerm);
			let matchesPosition = true;
			
			if (positionFilter !== 'all') {
				switch (positionFilter) {
					case '1-10':
						matchesPosition = keyword.position >= 1 && keyword.position <= 10;
						break;
					case '11-20':
						matchesPosition = keyword.position >= 11 && keyword.position <= 20;
						break;
					case '21-50':
						matchesPosition = keyword.position >= 21 && keyword.position <= 50;
						break;
					case '51+':
						matchesPosition = keyword.position >= 51;
						break;
				}
			}
			
			return matchesSearch && matchesPosition;
		});
		
		currentPage = 1;
		renderKeywordsTable();
	}
	
	function sortKeywords(sortBy, direction) {
		filteredData.sort(function(a, b) {
			let aVal = a[sortBy];
			let bVal = b[sortBy];
			
			if (typeof aVal === 'string') {
				aVal = aVal.toLowerCase();
				bVal = bVal.toLowerCase();
			}
			
			if (direction === 'asc') {
				return aVal > bVal ? 1 : -1;
			} else {
				return aVal < bVal ? 1 : -1;
			}
		});
		
		renderKeywordsTable();
	}
	
	function renderKeywordsTable() {
		const tbody = $('#keywords-tbody');
		tbody.empty();
		
		if (filteredData.length === 0) {
			tbody.append(`
				<tr class="no-keywords">
					<td colspan="8" class="text-center">
						<span class="dashicons dashicons-chart-line"></span>
						<p><?php _e( 'No keywords found matching your criteria.', 'seo-forge' ); ?></p>
					</td>
				</tr>
			`);
			return;
		}
		
		const itemsPerPage = 20;
		const startIndex = (currentPage - 1) * itemsPerPage;
		const endIndex = startIndex + itemsPerPage;
		const pageData = filteredData.slice(startIndex, endIndex);
		
		pageData.forEach(function(keyword) {
			const positionClass = getPositionClass(keyword.position);
			const changeClass = getChangeClass(keyword.change);
			const difficultyClass = getDifficultyClass(keyword.difficulty);
			
			const row = `
				<tr>
					<td><strong>${keyword.keyword}</strong></td>
					<td>
						<span class="position-badge ${positionClass}">${keyword.position}</span>
					</td>
					<td>
						<span class="change-indicator ${changeClass}">
							${keyword.change > 0 ? '↑' : keyword.change < 0 ? '↓' : '→'}
							${Math.abs(keyword.change)}
						</span>
					</td>
					<td>${keyword.volume.toLocaleString()}</td>
					<td>
						<div class="difficulty-bar">
							<div class="difficulty-fill ${difficultyClass}" style="width: ${keyword.difficulty}%"></div>
						</div>
						${keyword.difficulty}%
					</td>
					<td><a href="${keyword.url}" target="_blank">${keyword.url}</a></td>
					<td>${formatDate(keyword.lastUpdated)}</td>
					<td>
						<button class="button button-small" onclick="removeKeyword('${keyword.keyword}')">
							<?php _e( 'Remove', 'seo-forge' ); ?>
						</button>
					</td>
				</tr>
			`;
			
			tbody.append(row);
		});
		
		updatePagination();
	}
	
	function getPositionClass(position) {
		if (position <= 3) return 'top-3';
		if (position <= 10) return 'top-10';
		if (position <= 20) return 'top-20';
		return 'low';
	}
	
	function getChangeClass(change) {
		if (change > 0) return 'positive';
		if (change < 0) return 'negative';
		return 'neutral';
	}
	
	function getDifficultyClass(difficulty) {
		if (difficulty <= 30) return 'easy';
		if (difficulty <= 70) return 'medium';
		return 'hard';
	}
	
	function formatDate(dateString) {
		const date = new Date(dateString);
		return date.toLocaleDateString();
	}
	
	function updatePagination() {
		const itemsPerPage = 20;
		const totalPages = Math.ceil(filteredData.length / itemsPerPage);
		const startItem = (currentPage - 1) * itemsPerPage + 1;
		const endItem = Math.min(currentPage * itemsPerPage, filteredData.length);
		
		$('#pagination-info').text(
			`<?php _e( 'Showing', 'seo-forge' ); ?> ${startItem}-${endItem} <?php _e( 'of', 'seo-forge' ); ?> ${filteredData.length} <?php _e( 'keywords', 'seo-forge' ); ?>`
		);
		
		$('#prev-page').prop('disabled', currentPage === 1);
		$('#next-page').prop('disabled', currentPage === totalPages);
		
		// Generate page numbers
		let pageNumbers = '';
		for (let i = 1; i <= totalPages; i++) {
			if (i === currentPage) {
				pageNumbers += `<span class="current-page">${i}</span>`;
			} else {
				pageNumbers += `<button class="page-number" data-page="${i}">${i}</button>`;
			}
		}
		$('#page-numbers').html(pageNumbers);
	}
	
	// Pagination controls
	$('#prev-page').on('click', function() {
		if (currentPage > 1) {
			currentPage--;
			renderKeywordsTable();
		}
	});
	
	$('#next-page').on('click', function() {
		const totalPages = Math.ceil(filteredData.length / 20);
		if (currentPage < totalPages) {
			currentPage++;
			renderKeywordsTable();
		}
	});
	
	$(document).on('click', '.page-number', function() {
		currentPage = parseInt($(this).data('page'));
		renderKeywordsTable();
	});
	
	// Global function for removing keywords
	window.removeKeyword = function(keyword) {
		if (confirm('<?php _e( 'Are you sure you want to stop tracking this keyword?', 'seo-forge' ); ?>')) {
			keywordsData = keywordsData.filter(k => k.keyword !== keyword);
			filteredData = filteredData.filter(k => k.keyword !== keyword);
			renderKeywordsTable();
			updateStats();
		}
	};
	
	function updateRankings() {
		// Simulate ranking updates
		keywordsData.forEach(function(keyword) {
			const oldPosition = keyword.position;
			keyword.position = Math.max(1, keyword.position + Math.floor(Math.random() * 11) - 5);
			keyword.change = oldPosition - keyword.position;
			keyword.lastUpdated = new Date().toISOString();
		});
		
		filteredData = [...keywordsData];
		renderKeywordsTable();
		updateStats();
	}
	
	// Initialize with sample data
	setTimeout(function() {
		const sampleKeywords = [
			'SEO optimization',
			'WordPress plugin',
			'Content marketing',
			'Digital marketing',
			'Keyword research'
		];
		addSampleKeywords(sampleKeywords);
	}, 1000);
});
</script>