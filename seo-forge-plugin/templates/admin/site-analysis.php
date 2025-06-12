<?php
/**
 * Site Analysis Admin Page
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap seo-forge-admin">
	<h1><?php _e( 'SEO Forge - Site Analysis', 'seo-forge' ); ?></h1>
	
	<div class="seo-forge-container">
		<div class="seo-forge-header">
			<div class="seo-forge-nav">
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge' ); ?>" class="nav-tab">
					<?php _e( 'Dashboard', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-content' ); ?>" class="nav-tab">
					<?php _e( 'Content Generator', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-analyzer' ); ?>" class="nav-tab">
					<?php _e( 'SEO Analyzer', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-site-analysis' ); ?>" class="nav-tab nav-tab-active">
					<?php _e( 'Site Analysis', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-keywords' ); ?>" class="nav-tab">
					<?php _e( 'Keyword Research', 'seo-forge' ); ?>
				</a>
			</div>
		</div>

		<div class="seo-forge-content">
			<!-- Site Overview -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h2><?php _e( 'Site Overview', 'seo-forge' ); ?></h2>
					<button id="analyze-site" class="button button-primary">
						<span class="dashicons dashicons-search"></span>
						<?php _e( 'Analyze Site', 'seo-forge' ); ?>
					</button>
				</div>

				<div class="card-body">
					<div class="site-stats">
						<div class="stat-item">
							<div class="stat-number" id="total-pages">-</div>
							<div class="stat-label"><?php _e( 'Total Pages', 'seo-forge' ); ?></div>
						</div>
						<div class="stat-item">
							<div class="stat-number" id="seo-score">-</div>
							<div class="stat-label"><?php _e( 'SEO Score', 'seo-forge' ); ?></div>
						</div>
						<div class="stat-item">
							<div class="stat-number" id="issues-found">-</div>
							<div class="stat-label"><?php _e( 'Issues Found', 'seo-forge' ); ?></div>
						</div>
						<div class="stat-item">
							<div class="stat-number" id="last-analyzed">-</div>
							<div class="stat-label"><?php _e( 'Last Analyzed', 'seo-forge' ); ?></div>
						</div>
					</div>
				</div>
			</div>

			<!-- SEO Issues -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h3><?php _e( 'SEO Issues', 'seo-forge' ); ?></h3>
					<div class="issue-filters">
						<button class="filter-btn active" data-filter="all"><?php _e( 'All', 'seo-forge' ); ?></button>
						<button class="filter-btn" data-filter="critical"><?php _e( 'Critical', 'seo-forge' ); ?></button>
						<button class="filter-btn" data-filter="warning"><?php _e( 'Warning', 'seo-forge' ); ?></button>
						<button class="filter-btn" data-filter="notice"><?php _e( 'Notice', 'seo-forge' ); ?></button>
					</div>
				</div>

				<div class="card-body">
					<div id="issues-list" class="issues-list">
						<div class="no-issues">
							<span class="dashicons dashicons-search"></span>
							<p><?php _e( 'Click "Analyze Site" to scan your website for SEO issues.', 'seo-forge' ); ?></p>
						</div>
					</div>
				</div>
			</div>

			<!-- Page Analysis -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h3><?php _e( 'Page Analysis', 'seo-forge' ); ?></h3>
					<div class="page-controls">
						<select id="page-selector">
							<option value=""><?php _e( 'Select a page to analyze', 'seo-forge' ); ?></option>
						</select>
						<button id="analyze-page" class="button"><?php _e( 'Analyze Page', 'seo-forge' ); ?></button>
					</div>
				</div>

				<div class="card-body">
					<div id="page-analysis-results" class="page-analysis-results">
						<div class="no-analysis">
							<span class="dashicons dashicons-analytics"></span>
							<p><?php _e( 'Select a page and click "Analyze Page" to see detailed SEO analysis.', 'seo-forge' ); ?></p>
						</div>
					</div>
				</div>
			</div>

			<!-- Technical SEO -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h3><?php _e( 'Technical SEO', 'seo-forge' ); ?></h3>
				</div>

				<div class="card-body">
					<div class="technical-checks">
						<div class="check-item">
							<div class="check-status" id="sitemap-status">
								<span class="dashicons dashicons-minus"></span>
							</div>
							<div class="check-info">
								<h4><?php _e( 'XML Sitemap', 'seo-forge' ); ?></h4>
								<p><?php _e( 'Helps search engines discover your content', 'seo-forge' ); ?></p>
							</div>
							<div class="check-action">
								<button class="button button-small" id="generate-sitemap">
									<?php _e( 'Generate', 'seo-forge' ); ?>
								</button>
							</div>
						</div>

						<div class="check-item">
							<div class="check-status" id="robots-status">
								<span class="dashicons dashicons-minus"></span>
							</div>
							<div class="check-info">
								<h4><?php _e( 'Robots.txt', 'seo-forge' ); ?></h4>
								<p><?php _e( 'Controls how search engines crawl your site', 'seo-forge' ); ?></p>
							</div>
							<div class="check-action">
								<button class="button button-small" id="check-robots">
									<?php _e( 'Check', 'seo-forge' ); ?>
								</button>
							</div>
						</div>

						<div class="check-item">
							<div class="check-status" id="ssl-status">
								<span class="dashicons dashicons-minus"></span>
							</div>
							<div class="check-info">
								<h4><?php _e( 'SSL Certificate', 'seo-forge' ); ?></h4>
								<p><?php _e( 'Ensures secure connection to your website', 'seo-forge' ); ?></p>
							</div>
							<div class="check-action">
								<button class="button button-small" id="check-ssl">
									<?php _e( 'Verify', 'seo-forge' ); ?>
								</button>
							</div>
						</div>

						<div class="check-item">
							<div class="check-status" id="speed-status">
								<span class="dashicons dashicons-minus"></span>
							</div>
							<div class="check-info">
								<h4><?php _e( 'Page Speed', 'seo-forge' ); ?></h4>
								<p><?php _e( 'Fast loading pages improve user experience', 'seo-forge' ); ?></p>
							</div>
							<div class="check-action">
								<button class="button button-small" id="test-speed">
									<?php _e( 'Test', 'seo-forge' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Loading State -->
			<div id="analysis-loading" class="seo-forge-loading" style="display: none;">
				<div class="loading-spinner"></div>
				<p><?php _e( 'Analyzing your website... This may take a few moments.', 'seo-forge' ); ?></p>
			</div>
		</div>
	</div>
</div>

<style>
.seo-forge-admin .site-stats {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 20px;
	margin-bottom: 20px;
}

.seo-forge-admin .stat-item {
	text-align: center;
	padding: 20px;
	background: #f8f9fa;
	border-radius: 8px;
	border: 1px solid #e9ecef;
}

.seo-forge-admin .stat-number {
	font-size: 2.5em;
	font-weight: bold;
	color: #0073aa;
	margin-bottom: 5px;
}

.seo-forge-admin .stat-label {
	font-size: 14px;
	color: #666;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.seo-forge-admin .issue-filters {
	display: flex;
	gap: 10px;
}

.seo-forge-admin .filter-btn {
	padding: 8px 16px;
	border: 1px solid #ddd;
	background: #fff;
	border-radius: 4px;
	cursor: pointer;
	font-size: 12px;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.seo-forge-admin .filter-btn.active {
	background: #0073aa;
	color: white;
	border-color: #0073aa;
}

.seo-forge-admin .issues-list {
	min-height: 200px;
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

.seo-forge-admin .issue-severity.critical {
	background: #dc3545;
}

.seo-forge-admin .issue-severity.warning {
	background: #ffc107;
}

.seo-forge-admin .issue-severity.notice {
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

.seo-forge-admin .issue-action {
	margin-left: 15px;
}

.seo-forge-admin .page-controls {
	display: flex;
	gap: 10px;
	align-items: center;
}

.seo-forge-admin .page-controls select {
	min-width: 250px;
}

.seo-forge-admin .technical-checks {
	display: grid;
	gap: 15px;
}

.seo-forge-admin .check-item {
	display: flex;
	align-items: center;
	padding: 20px;
	border: 1px solid #ddd;
	border-radius: 8px;
	background: #fff;
}

.seo-forge-admin .check-status {
	width: 40px;
	height: 40px;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	margin-right: 20px;
	background: #f8f9fa;
	border: 2px solid #e9ecef;
}

.seo-forge-admin .check-status.pass {
	background: #d4edda;
	border-color: #28a745;
	color: #28a745;
}

.seo-forge-admin .check-status.fail {
	background: #f8d7da;
	border-color: #dc3545;
	color: #dc3545;
}

.seo-forge-admin .check-info {
	flex: 1;
}

.seo-forge-admin .check-info h4 {
	margin: 0 0 5px 0;
	font-size: 16px;
}

.seo-forge-admin .check-info p {
	margin: 0;
	color: #666;
	font-size: 14px;
}

.seo-forge-admin .check-action {
	margin-left: 20px;
}

.seo-forge-admin .no-issues,
.seo-forge-admin .no-analysis {
	text-align: center;
	padding: 40px;
	color: #666;
}

.seo-forge-admin .no-issues .dashicons,
.seo-forge-admin .no-analysis .dashicons {
	font-size: 48px;
	margin-bottom: 15px;
	opacity: 0.5;
}

@media (max-width: 768px) {
	.seo-forge-admin .site-stats {
		grid-template-columns: repeat(2, 1fr);
	}
	
	.seo-forge-admin .page-controls {
		flex-direction: column;
		align-items: stretch;
	}
	
	.seo-forge-admin .check-item {
		flex-direction: column;
		text-align: center;
	}
	
	.seo-forge-admin .check-status {
		margin: 0 0 15px 0;
	}
	
	.seo-forge-admin .check-action {
		margin: 15px 0 0 0;
	}
}
</style>

<script>
jQuery(document).ready(function($) {
	// Site analysis
	$('#analyze-site').on('click', function() {
		$('#analysis-loading').show();
		
		// Simulate analysis - replace with actual API call
		setTimeout(function() {
			$('#analysis-loading').hide();
			
			// Update stats
			$('#total-pages').text('47');
			$('#seo-score').text('78/100');
			$('#issues-found').text('12');
			$('#last-analyzed').text('<?php echo date('M j, Y'); ?>');
			
			// Show sample issues
			showSampleIssues();
			
			// Update technical checks
			updateTechnicalChecks();
			
			// Populate page selector
			populatePageSelector();
		}, 3000);
	});
	
	// Issue filters
	$('.filter-btn').on('click', function() {
		$('.filter-btn').removeClass('active');
		$(this).addClass('active');
		
		const filter = $(this).data('filter');
		filterIssues(filter);
	});
	
	// Page analysis
	$('#analyze-page').on('click', function() {
		const selectedPage = $('#page-selector').val();
		if (!selectedPage) {
			alert('<?php _e( 'Please select a page to analyze.', 'seo-forge' ); ?>');
			return;
		}
		
		$('#analysis-loading').show();
		
		// Simulate page analysis
		setTimeout(function() {
			$('#analysis-loading').hide();
			showPageAnalysis(selectedPage);
		}, 2000);
	});
	
	// Technical checks
	$('#generate-sitemap').on('click', function() {
		$(this).text('<?php _e( 'Generating...', 'seo-forge' ); ?>');
		setTimeout(function() {
			$('#sitemap-status').addClass('pass').html('<span class="dashicons dashicons-yes"></span>');
			$('#generate-sitemap').text('<?php _e( 'View', 'seo-forge' ); ?>');
		}, 1500);
	});
	
	$('#check-robots').on('click', function() {
		$(this).text('<?php _e( 'Checking...', 'seo-forge' ); ?>');
		setTimeout(function() {
			$('#robots-status').addClass('pass').html('<span class="dashicons dashicons-yes"></span>');
			$('#check-robots').text('<?php _e( 'Edit', 'seo-forge' ); ?>');
		}, 1000);
	});
	
	$('#check-ssl').on('click', function() {
		$(this).text('<?php _e( 'Verifying...', 'seo-forge' ); ?>');
		setTimeout(function() {
			$('#ssl-status').addClass('pass').html('<span class="dashicons dashicons-yes"></span>');
			$('#check-ssl').text('<?php _e( 'Valid', 'seo-forge' ); ?>');
		}, 1200);
	});
	
	$('#test-speed').on('click', function() {
		$(this).text('<?php _e( 'Testing...', 'seo-forge' ); ?>');
		setTimeout(function() {
			$('#speed-status').addClass('pass').html('<span class="dashicons dashicons-yes"></span>');
			$('#test-speed').text('<?php _e( 'Good', 'seo-forge' ); ?>');
		}, 2000);
	});
	
	function showSampleIssues() {
		const issues = [
			{
				severity: 'critical',
				title: '<?php _e( 'Missing Meta Descriptions', 'seo-forge' ); ?>',
				description: '<?php _e( '8 pages are missing meta descriptions', 'seo-forge' ); ?>',
				action: '<?php _e( 'Fix Now', 'seo-forge' ); ?>'
			},
			{
				severity: 'warning',
				title: '<?php _e( 'Large Image Files', 'seo-forge' ); ?>',
				description: '<?php _e( '15 images could be optimized for better performance', 'seo-forge' ); ?>',
				action: '<?php _e( 'Optimize', 'seo-forge' ); ?>'
			},
			{
				severity: 'notice',
				title: '<?php _e( 'Alt Text Missing', 'seo-forge' ); ?>',
				description: '<?php _e( '3 images are missing alt text', 'seo-forge' ); ?>',
				action: '<?php _e( 'Add Alt Text', 'seo-forge' ); ?>'
			}
		];
		
		const issuesList = $('#issues-list');
		issuesList.empty();
		
		issues.forEach(function(issue) {
			const issueHtml = `
				<div class="issue-item" data-severity="${issue.severity}">
					<div class="issue-severity ${issue.severity}"></div>
					<div class="issue-content">
						<div class="issue-title">${issue.title}</div>
						<div class="issue-description">${issue.description}</div>
					</div>
					<div class="issue-action">
						<button class="button button-small">${issue.action}</button>
					</div>
				</div>
			`;
			issuesList.append(issueHtml);
		});
	}
	
	function filterIssues(filter) {
		if (filter === 'all') {
			$('.issue-item').show();
		} else {
			$('.issue-item').hide();
			$(`.issue-item[data-severity="${filter}"]`).show();
		}
	}
	
	function updateTechnicalChecks() {
		// This would be replaced with actual checks
		setTimeout(function() {
			$('#sitemap-status').addClass('fail').html('<span class="dashicons dashicons-no"></span>');
			$('#robots-status').addClass('pass').html('<span class="dashicons dashicons-yes"></span>');
			$('#ssl-status').addClass('pass').html('<span class="dashicons dashicons-yes"></span>');
			$('#speed-status').addClass('fail').html('<span class="dashicons dashicons-no"></span>');
		}, 500);
	}
	
	function populatePageSelector() {
		const pages = [
			{ value: 'home', text: '<?php _e( 'Homepage', 'seo-forge' ); ?>' },
			{ value: 'about', text: '<?php _e( 'About Us', 'seo-forge' ); ?>' },
			{ value: 'contact', text: '<?php _e( 'Contact', 'seo-forge' ); ?>' },
			{ value: 'blog', text: '<?php _e( 'Blog', 'seo-forge' ); ?>' }
		];
		
		const selector = $('#page-selector');
		pages.forEach(function(page) {
			selector.append(`<option value="${page.value}">${page.text}</option>`);
		});
	}
	
	function showPageAnalysis(page) {
		const analysisHtml = `
			<div class="page-analysis-result">
				<h4><?php _e( 'Analysis Results for:', 'seo-forge' ); ?> ${page}</h4>
				<div class="analysis-metrics">
					<div class="metric">
						<span class="metric-label"><?php _e( 'SEO Score:', 'seo-forge' ); ?></span>
						<span class="metric-value">85/100</span>
					</div>
					<div class="metric">
						<span class="metric-label"><?php _e( 'Readability:', 'seo-forge' ); ?></span>
						<span class="metric-value"><?php _e( 'Good', 'seo-forge' ); ?></span>
					</div>
					<div class="metric">
						<span class="metric-label"><?php _e( 'Load Time:', 'seo-forge' ); ?></span>
						<span class="metric-value">2.3s</span>
					</div>
				</div>
			</div>
		`;
		
		$('#page-analysis-results').html(analysisHtml);
	}
});
</script>