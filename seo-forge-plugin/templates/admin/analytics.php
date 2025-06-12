<?php
/**
 * Analytics Admin Page
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap seo-forge-admin">
	<h1><?php _e( 'SEO Forge - Analytics', 'seo-forge' ); ?></h1>
	
	<div class="seo-forge-container">
		<div class="seo-forge-header">
			<div class="seo-forge-nav">
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge' ); ?>" class="nav-tab">
					<?php _e( 'Dashboard', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-rank-tracker' ); ?>" class="nav-tab">
					<?php _e( 'Rank Tracker', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-analytics' ); ?>" class="nav-tab nav-tab-active">
					<?php _e( 'Analytics', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-settings' ); ?>" class="nav-tab">
					<?php _e( 'Settings', 'seo-forge' ); ?>
				</a>
			</div>
		</div>

		<div class="seo-forge-content">
			<!-- Analytics Overview -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h2><?php _e( 'SEO Performance Overview', 'seo-forge' ); ?></h2>
					<div class="date-range-selector">
						<select id="date-range">
							<option value="7"><?php _e( 'Last 7 days', 'seo-forge' ); ?></option>
							<option value="30" selected><?php _e( 'Last 30 days', 'seo-forge' ); ?></option>
							<option value="90"><?php _e( 'Last 3 months', 'seo-forge' ); ?></option>
							<option value="365"><?php _e( 'Last year', 'seo-forge' ); ?></option>
						</select>
						<button id="refresh-data" class="button">
							<span class="dashicons dashicons-update"></span>
							<?php _e( 'Refresh', 'seo-forge' ); ?>
						</button>
					</div>
				</div>

				<div class="card-body">
					<div class="analytics-stats">
						<div class="stat-card">
							<div class="stat-icon">
								<span class="dashicons dashicons-visibility"></span>
							</div>
							<div class="stat-content">
								<div class="stat-number" id="total-impressions">-</div>
								<div class="stat-label"><?php _e( 'Total Impressions', 'seo-forge' ); ?></div>
								<div class="stat-change positive" id="impressions-change">+0%</div>
							</div>
						</div>

						<div class="stat-card">
							<div class="stat-icon">
								<span class="dashicons dashicons-admin-links"></span>
							</div>
							<div class="stat-content">
								<div class="stat-number" id="total-clicks">-</div>
								<div class="stat-label"><?php _e( 'Total Clicks', 'seo-forge' ); ?></div>
								<div class="stat-change positive" id="clicks-change">+0%</div>
							</div>
						</div>

						<div class="stat-card">
							<div class="stat-icon">
								<span class="dashicons dashicons-chart-line"></span>
							</div>
							<div class="stat-content">
								<div class="stat-number" id="avg-ctr">-</div>
								<div class="stat-label"><?php _e( 'Average CTR', 'seo-forge' ); ?></div>
								<div class="stat-change neutral" id="ctr-change">+0%</div>
							</div>
						</div>

						<div class="stat-card">
							<div class="stat-icon">
								<span class="dashicons dashicons-search"></span>
							</div>
							<div class="stat-content">
								<div class="stat-number" id="avg-position">-</div>
								<div class="stat-label"><?php _e( 'Average Position', 'seo-forge' ); ?></div>
								<div class="stat-change positive" id="position-change">+0</div>
							</div>
						</div>
					</div>

					<!-- Performance Chart -->
					<div class="chart-container">
						<canvas id="performance-chart" width="400" height="200"></canvas>
					</div>
				</div>
			</div>

			<!-- Top Performing Content -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h3><?php _e( 'Top Performing Content', 'seo-forge' ); ?></h3>
					<div class="content-filters">
						<select id="content-metric">
							<option value="clicks"><?php _e( 'By Clicks', 'seo-forge' ); ?></option>
							<option value="impressions"><?php _e( 'By Impressions', 'seo-forge' ); ?></option>
							<option value="ctr"><?php _e( 'By CTR', 'seo-forge' ); ?></option>
							<option value="position"><?php _e( 'By Position', 'seo-forge' ); ?></option>
						</select>
					</div>
				</div>

				<div class="card-body">
					<div class="content-table-container">
						<table id="content-table" class="wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th><?php _e( 'Page', 'seo-forge' ); ?></th>
									<th><?php _e( 'Clicks', 'seo-forge' ); ?></th>
									<th><?php _e( 'Impressions', 'seo-forge' ); ?></th>
									<th><?php _e( 'CTR', 'seo-forge' ); ?></th>
									<th><?php _e( 'Avg Position', 'seo-forge' ); ?></th>
									<th><?php _e( 'Actions', 'seo-forge' ); ?></th>
								</tr>
							</thead>
							<tbody id="content-tbody">
								<tr class="loading-row">
									<td colspan="6" class="text-center">
										<span class="dashicons dashicons-update spin"></span>
										<?php _e( 'Loading performance data...', 'seo-forge' ); ?>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<!-- Top Keywords -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h3><?php _e( 'Top Performing Keywords', 'seo-forge' ); ?></h3>
					<div class="keyword-filters">
						<select id="keyword-metric">
							<option value="clicks"><?php _e( 'By Clicks', 'seo-forge' ); ?></option>
							<option value="impressions"><?php _e( 'By Impressions', 'seo-forge' ); ?></option>
							<option value="ctr"><?php _e( 'By CTR', 'seo-forge' ); ?></option>
						</select>
						<input type="text" id="keyword-search" placeholder="<?php _e( 'Search keywords...', 'seo-forge' ); ?>" />
					</div>
				</div>

				<div class="card-body">
					<div class="keywords-table-container">
						<table id="keywords-table" class="wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th><?php _e( 'Keyword', 'seo-forge' ); ?></th>
									<th><?php _e( 'Clicks', 'seo-forge' ); ?></th>
									<th><?php _e( 'Impressions', 'seo-forge' ); ?></th>
									<th><?php _e( 'CTR', 'seo-forge' ); ?></th>
									<th><?php _e( 'Position', 'seo-forge' ); ?></th>
									<th><?php _e( 'Trend', 'seo-forge' ); ?></th>
								</tr>
							</thead>
							<tbody id="keywords-tbody">
								<tr class="loading-row">
									<td colspan="6" class="text-center">
										<span class="dashicons dashicons-update spin"></span>
										<?php _e( 'Loading keyword data...', 'seo-forge' ); ?>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<!-- Search Console Integration -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h3><?php _e( 'Google Search Console Integration', 'seo-forge' ); ?></h3>
				</div>

				<div class="card-body">
					<div class="integration-status">
						<div class="status-item">
							<div class="status-icon" id="gsc-status">
								<span class="dashicons dashicons-minus"></span>
							</div>
							<div class="status-info">
								<h4><?php _e( 'Search Console Connection', 'seo-forge' ); ?></h4>
								<p><?php _e( 'Connect your Google Search Console for real-time data', 'seo-forge' ); ?></p>
							</div>
							<div class="status-action">
								<button id="connect-gsc" class="button button-primary">
									<span class="dashicons dashicons-admin-links"></span>
									<?php _e( 'Connect', 'seo-forge' ); ?>
								</button>
							</div>
						</div>

						<div class="status-item">
							<div class="status-icon" id="ga-status">
								<span class="dashicons dashicons-minus"></span>
							</div>
							<div class="status-info">
								<h4><?php _e( 'Google Analytics Integration', 'seo-forge' ); ?></h4>
								<p><?php _e( 'Connect Google Analytics for comprehensive insights', 'seo-forge' ); ?></p>
							</div>
							<div class="status-action">
								<button id="connect-ga" class="button button-primary">
									<span class="dashicons dashicons-chart-area"></span>
									<?php _e( 'Connect', 'seo-forge' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- SEO Issues -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h3><?php _e( 'SEO Issues & Opportunities', 'seo-forge' ); ?></h3>
					<button id="scan-issues" class="button">
						<span class="dashicons dashicons-search"></span>
						<?php _e( 'Scan for Issues', 'seo-forge' ); ?>
					</button>
				</div>

				<div class="card-body">
					<div id="seo-issues-list" class="seo-issues-list">
						<div class="no-issues">
							<span class="dashicons dashicons-yes-alt"></span>
							<p><?php _e( 'Click "Scan for Issues" to check for SEO opportunities.', 'seo-forge' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
.seo-forge-admin .analytics-stats {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 20px;
	margin-bottom: 30px;
}

.seo-forge-admin .stat-card {
	display: flex;
	align-items: center;
	padding: 25px;
	background: #fff;
	border: 1px solid #e1e5e9;
	border-radius: 12px;
	box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.seo-forge-admin .stat-icon {
	width: 60px;
	height: 60px;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	margin-right: 20px;
	background: linear-gradient(135deg, #0073aa, #005a87);
	color: white;
	font-size: 24px;
}

.seo-forge-admin .stat-content {
	flex: 1;
}

.seo-forge-admin .stat-number {
	font-size: 2.2em;
	font-weight: bold;
	color: #1d2327;
	margin-bottom: 5px;
	line-height: 1;
}

.seo-forge-admin .stat-label {
	font-size: 14px;
	color: #646970;
	margin-bottom: 8px;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.seo-forge-admin .stat-change {
	font-size: 12px;
	font-weight: 600;
	padding: 2px 8px;
	border-radius: 12px;
	display: inline-block;
}

.seo-forge-admin .stat-change.positive {
	background: #d4edda;
	color: #155724;
}

.seo-forge-admin .stat-change.negative {
	background: #f8d7da;
	color: #721c24;
}

.seo-forge-admin .stat-change.neutral {
	background: #e2e3e5;
	color: #383d41;
}

.seo-forge-admin .chart-container {
	margin: 30px 0;
	padding: 20px;
	background: #f8f9fa;
	border-radius: 8px;
	text-align: center;
}

.seo-forge-admin .date-range-selector,
.seo-forge-admin .content-filters,
.seo-forge-admin .keyword-filters {
	display: flex;
	gap: 10px;
	align-items: center;
}

.seo-forge-admin .content-table-container,
.seo-forge-admin .keywords-table-container {
	overflow-x: auto;
}

.seo-forge-admin .loading-row .dashicons.spin {
	animation: spin 1s linear infinite;
}

@keyframes spin {
	from { transform: rotate(0deg); }
	to { transform: rotate(360deg); }
}

.seo-forge-admin .trend-indicator {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	font-size: 12px;
	font-weight: bold;
}

.seo-forge-admin .trend-indicator.up {
	color: #28a745;
}

.seo-forge-admin .trend-indicator.down {
	color: #dc3545;
}

.seo-forge-admin .trend-indicator.stable {
	color: #6c757d;
}

.seo-forge-admin .integration-status {
	display: grid;
	gap: 20px;
}

.seo-forge-admin .status-item {
	display: flex;
	align-items: center;
	padding: 20px;
	border: 1px solid #ddd;
	border-radius: 8px;
	background: #fff;
}

.seo-forge-admin .status-icon {
	width: 50px;
	height: 50px;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	margin-right: 20px;
	background: #f8f9fa;
	border: 2px solid #e9ecef;
	font-size: 20px;
}

.seo-forge-admin .status-icon.connected {
	background: #d4edda;
	border-color: #28a745;
	color: #28a745;
}

.seo-forge-admin .status-info {
	flex: 1;
}

.seo-forge-admin .status-info h4 {
	margin: 0 0 5px 0;
	font-size: 16px;
	color: #1d2327;
}

.seo-forge-admin .status-info p {
	margin: 0;
	color: #646970;
	font-size: 14px;
}

.seo-forge-admin .status-action {
	margin-left: 20px;
}

.seo-forge-admin .seo-issues-list {
	min-height: 150px;
}

.seo-forge-admin .issue-item {
	display: flex;
	align-items: center;
	padding: 15px;
	border: 1px solid #ddd;
	border-radius: 6px;
	margin-bottom: 10px;
	background: #fff;
}

.seo-forge-admin .issue-severity {
	width: 12px;
	height: 12px;
	border-radius: 50%;
	margin-right: 15px;
	flex-shrink: 0;
}

.seo-forge-admin .issue-severity.high {
	background: #dc3545;
}

.seo-forge-admin .issue-severity.medium {
	background: #ffc107;
}

.seo-forge-admin .issue-severity.low {
	background: #17a2b8;
}

.seo-forge-admin .issue-content {
	flex: 1;
}

.seo-forge-admin .issue-title {
	font-weight: 600;
	margin-bottom: 5px;
}

.seo-forge-admin .issue-description {
	color: #666;
	font-size: 14px;
}

.seo-forge-admin .no-issues {
	text-align: center;
	padding: 40px;
	color: #666;
}

.seo-forge-admin .no-issues .dashicons {
	font-size: 48px;
	margin-bottom: 15px;
	opacity: 0.5;
}

@media (max-width: 768px) {
	.seo-forge-admin .analytics-stats {
		grid-template-columns: repeat(2, 1fr);
	}
	
	.seo-forge-admin .stat-card {
		flex-direction: column;
		text-align: center;
	}
	
	.seo-forge-admin .stat-icon {
		margin: 0 0 15px 0;
	}
	
	.seo-forge-admin .date-range-selector,
	.seo-forge-admin .content-filters,
	.seo-forge-admin .keyword-filters {
		flex-direction: column;
		align-items: stretch;
	}
	
	.seo-forge-admin .status-item {
		flex-direction: column;
		text-align: center;
	}
	
	.seo-forge-admin .status-icon {
		margin: 0 0 15px 0;
	}
	
	.seo-forge-admin .status-action {
		margin: 15px 0 0 0;
	}
}
</style>

<script>
jQuery(document).ready(function($) {
	// Initialize analytics data
	loadAnalyticsData();
	
	// Date range change
	$('#date-range').on('change', function() {
		loadAnalyticsData();
	});
	
	// Refresh data
	$('#refresh-data').on('click', function() {
		$(this).find('.dashicons').addClass('spin');
		loadAnalyticsData();
		
		setTimeout(function() {
			$('#refresh-data .dashicons').removeClass('spin');
		}, 2000);
	});
	
	// Content metric filter
	$('#content-metric').on('change', function() {
		loadContentData();
	});
	
	// Keyword metric filter
	$('#keyword-metric').on('change', function() {
		loadKeywordData();
	});
	
	// Keyword search
	$('#keyword-search').on('input', function() {
		filterKeywords();
	});
	
	// Connect integrations
	$('#connect-gsc').on('click', function() {
		$(this).text('<?php _e( 'Connecting...', 'seo-forge' ); ?>');
		
		setTimeout(function() {
			$('#gsc-status').addClass('connected').html('<span class="dashicons dashicons-yes"></span>');
			$('#connect-gsc').text('<?php _e( 'Connected', 'seo-forge' ); ?>').prop('disabled', true);
		}, 2000);
	});
	
	$('#connect-ga').on('click', function() {
		$(this).text('<?php _e( 'Connecting...', 'seo-forge' ); ?>');
		
		setTimeout(function() {
			$('#ga-status').addClass('connected').html('<span class="dashicons dashicons-yes"></span>');
			$('#connect-ga').text('<?php _e( 'Connected', 'seo-forge' ); ?>').prop('disabled', true);
		}, 2000);
	});
	
	// Scan for SEO issues
	$('#scan-issues').on('click', function() {
		$(this).text('<?php _e( 'Scanning...', 'seo-forge' ); ?>');
		
		setTimeout(function() {
			$('#scan-issues').text('<?php _e( 'Scan for Issues', 'seo-forge' ); ?>');
			showSEOIssues();
		}, 3000);
	});
	
	function loadAnalyticsData() {
		// Simulate loading analytics data
		setTimeout(function() {
			$('#total-impressions').text('45,230');
			$('#impressions-change').text('+12.5%').removeClass('neutral').addClass('positive');
			
			$('#total-clicks').text('2,847');
			$('#clicks-change').text('+8.3%').removeClass('neutral').addClass('positive');
			
			$('#avg-ctr').text('6.3%');
			$('#ctr-change').text('-0.2%').removeClass('neutral').addClass('negative');
			
			$('#avg-position').text('12.4');
			$('#position-change').text('+2.1').removeClass('neutral').addClass('positive');
			
			loadContentData();
			loadKeywordData();
		}, 1000);
	}
	
	function loadContentData() {
		const contentData = [
			{
				page: '<?php echo home_url(); ?>',
				clicks: 1247,
				impressions: 18430,
				ctr: 6.8,
				position: 8.2
			},
			{
				page: '<?php echo home_url(); ?>/about',
				clicks: 892,
				impressions: 12650,
				ctr: 7.1,
				position: 11.5
			},
			{
				page: '<?php echo home_url(); ?>/services',
				clicks: 634,
				impressions: 9840,
				ctr: 6.4,
				position: 15.3
			},
			{
				page: '<?php echo home_url(); ?>/contact',
				clicks: 421,
				impressions: 7230,
				ctr: 5.8,
				position: 18.7
			}
		];
		
		const tbody = $('#content-tbody');
		tbody.empty();
		
		contentData.forEach(function(item) {
			const row = `
				<tr>
					<td><a href="${item.page}" target="_blank">${item.page}</a></td>
					<td><strong>${item.clicks.toLocaleString()}</strong></td>
					<td>${item.impressions.toLocaleString()}</td>
					<td>${item.ctr}%</td>
					<td>${item.position}</td>
					<td>
						<button class="button button-small"><?php _e( 'Optimize', 'seo-forge' ); ?></button>
					</td>
				</tr>
			`;
			tbody.append(row);
		});
	}
	
	function loadKeywordData() {
		const keywordData = [
			{
				keyword: 'wordpress seo',
				clicks: 342,
				impressions: 4820,
				ctr: 7.1,
				position: 6.2,
				trend: 'up'
			},
			{
				keyword: 'seo optimization',
				clicks: 298,
				impressions: 5640,
				ctr: 5.3,
				position: 12.8,
				trend: 'up'
			},
			{
				keyword: 'content marketing',
				clicks: 187,
				impressions: 3210,
				ctr: 5.8,
				position: 15.4,
				trend: 'stable'
			},
			{
				keyword: 'digital marketing',
				clicks: 156,
				impressions: 2890,
				ctr: 5.4,
				position: 18.9,
				trend: 'down'
			}
		];
		
		const tbody = $('#keywords-tbody');
		tbody.empty();
		
		keywordData.forEach(function(item) {
			const trendIcon = {
				'up': '↗',
				'down': '↘',
				'stable': '→'
			};
			
			const row = `
				<tr>
					<td><strong>${item.keyword}</strong></td>
					<td>${item.clicks.toLocaleString()}</td>
					<td>${item.impressions.toLocaleString()}</td>
					<td>${item.ctr}%</td>
					<td>${item.position}</td>
					<td>
						<span class="trend-indicator ${item.trend}">
							${trendIcon[item.trend]} ${item.trend}
						</span>
					</td>
				</tr>
			`;
			tbody.append(row);
		});
	}
	
	function filterKeywords() {
		const searchTerm = $('#keyword-search').val().toLowerCase();
		
		$('#keywords-tbody tr').each(function() {
			const keyword = $(this).find('td:first').text().toLowerCase();
			if (keyword.includes(searchTerm)) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	}
	
	function showSEOIssues() {
		const issues = [
			{
				severity: 'high',
				title: '<?php _e( 'Missing Meta Descriptions', 'seo-forge' ); ?>',
				description: '<?php _e( '12 pages are missing meta descriptions', 'seo-forge' ); ?>'
			},
			{
				severity: 'medium',
				title: '<?php _e( 'Slow Loading Pages', 'seo-forge' ); ?>',
				description: '<?php _e( '8 pages have loading times over 3 seconds', 'seo-forge' ); ?>'
			},
			{
				severity: 'low',
				title: '<?php _e( 'Missing Alt Text', 'seo-forge' ); ?>',
				description: '<?php _e( '5 images are missing alt text', 'seo-forge' ); ?>'
			}
		];
		
		const list = $('#seo-issues-list');
		list.empty();
		
		issues.forEach(function(issue) {
			const issueHtml = `
				<div class="issue-item">
					<div class="issue-severity ${issue.severity}"></div>
					<div class="issue-content">
						<div class="issue-title">${issue.title}</div>
						<div class="issue-description">${issue.description}</div>
					</div>
					<div class="issue-action">
						<button class="button button-small"><?php _e( 'Fix', 'seo-forge' ); ?></button>
					</div>
				</div>
			`;
			list.append(issueHtml);
		});
	}
});
</script>