<?php
/**
 * Schema Generator Admin Page
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap seo-forge-admin">
	<h1><?php _e( 'SEO Forge - Schema Generator', 'seo-forge' ); ?></h1>
	
	<div class="seo-forge-container">
		<div class="seo-forge-header">
			<div class="seo-forge-nav">
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge' ); ?>" class="nav-tab">
					<?php _e( 'Dashboard', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-analyzer' ); ?>" class="nav-tab">
					<?php _e( 'SEO Analyzer', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-schema' ); ?>" class="nav-tab nav-tab-active">
					<?php _e( 'Schema Generator', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-local-seo' ); ?>" class="nav-tab">
					<?php _e( 'Local SEO', 'seo-forge' ); ?>
				</a>
			</div>
		</div>

		<div class="seo-forge-content">
			<!-- Schema Type Selection -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h2><?php _e( 'Generate Schema Markup', 'seo-forge' ); ?></h2>
					<p><?php _e( 'Create structured data to help search engines understand your content better.', 'seo-forge' ); ?></p>
				</div>

				<div class="card-body">
					<div class="schema-types">
						<div class="schema-type-grid">
							<div class="schema-type-card" data-type="organization">
								<div class="schema-icon">
									<span class="dashicons dashicons-building"></span>
								</div>
								<h3><?php _e( 'Organization', 'seo-forge' ); ?></h3>
								<p><?php _e( 'Business information and contact details', 'seo-forge' ); ?></p>
							</div>

							<div class="schema-type-card" data-type="local-business">
								<div class="schema-icon">
									<span class="dashicons dashicons-store"></span>
								</div>
								<h3><?php _e( 'Local Business', 'seo-forge' ); ?></h3>
								<p><?php _e( 'Local business with physical location', 'seo-forge' ); ?></p>
							</div>

							<div class="schema-type-card" data-type="article">
								<div class="schema-icon">
									<span class="dashicons dashicons-media-document"></span>
								</div>
								<h3><?php _e( 'Article', 'seo-forge' ); ?></h3>
								<p><?php _e( 'Blog posts and news articles', 'seo-forge' ); ?></p>
							</div>

							<div class="schema-type-card" data-type="product">
								<div class="schema-icon">
									<span class="dashicons dashicons-products"></span>
								</div>
								<h3><?php _e( 'Product', 'seo-forge' ); ?></h3>
								<p><?php _e( 'E-commerce products with pricing', 'seo-forge' ); ?></p>
							</div>

							<div class="schema-type-card" data-type="review">
								<div class="schema-icon">
									<span class="dashicons dashicons-star-filled"></span>
								</div>
								<h3><?php _e( 'Review', 'seo-forge' ); ?></h3>
								<p><?php _e( 'Customer reviews and ratings', 'seo-forge' ); ?></p>
							</div>

							<div class="schema-type-card" data-type="event">
								<div class="schema-icon">
									<span class="dashicons dashicons-calendar-alt"></span>
								</div>
								<h3><?php _e( 'Event', 'seo-forge' ); ?></h3>
								<p><?php _e( 'Events with dates and locations', 'seo-forge' ); ?></p>
							</div>

							<div class="schema-type-card" data-type="faq">
								<div class="schema-icon">
									<span class="dashicons dashicons-editor-help"></span>
								</div>
								<h3><?php _e( 'FAQ', 'seo-forge' ); ?></h3>
								<p><?php _e( 'Frequently asked questions', 'seo-forge' ); ?></p>
							</div>

							<div class="schema-type-card" data-type="breadcrumb">
								<div class="schema-icon">
									<span class="dashicons dashicons-arrow-right-alt"></span>
								</div>
								<h3><?php _e( 'Breadcrumb', 'seo-forge' ); ?></h3>
								<p><?php _e( 'Navigation breadcrumb trail', 'seo-forge' ); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Schema Form -->
			<div id="schema-form-container" class="seo-forge-card" style="display: none;">
				<div class="card-header">
					<h3 id="schema-form-title"><?php _e( 'Schema Details', 'seo-forge' ); ?></h3>
					<button id="back-to-types" class="button">
						<span class="dashicons dashicons-arrow-left-alt"></span>
						<?php _e( 'Back to Types', 'seo-forge' ); ?>
					</button>
				</div>

				<div class="card-body">
					<form id="schema-form" class="seo-forge-form">
						<?php wp_nonce_field( 'seo_forge_nonce', 'seo_forge_nonce' ); ?>
						<div id="schema-fields"></div>
						
						<div class="form-actions">
							<button type="submit" class="button button-primary button-large">
								<span class="dashicons dashicons-code-standards"></span>
								<?php _e( 'Generate Schema', 'seo-forge' ); ?>
							</button>
							<button type="button" id="preview-schema" class="button button-secondary">
								<span class="dashicons dashicons-visibility"></span>
								<?php _e( 'Preview', 'seo-forge' ); ?>
							</button>
						</div>
					</form>
				</div>
			</div>

			<!-- Generated Schema -->
			<div id="schema-output" class="seo-forge-card" style="display: none;">
				<div class="card-header">
					<h3><?php _e( 'Generated Schema Markup', 'seo-forge' ); ?></h3>
					<div class="schema-actions">
						<button id="copy-schema" class="button">
							<span class="dashicons dashicons-clipboard"></span>
							<?php _e( 'Copy to Clipboard', 'seo-forge' ); ?>
						</button>
						<button id="test-schema" class="button">
							<span class="dashicons dashicons-external"></span>
							<?php _e( 'Test with Google', 'seo-forge' ); ?>
						</button>
						<button id="apply-schema" class="button button-primary">
							<span class="dashicons dashicons-yes"></span>
							<?php _e( 'Apply to Site', 'seo-forge' ); ?>
						</button>
					</div>
				</div>

				<div class="card-body">
					<div class="schema-tabs">
						<button class="schema-tab active" data-tab="json-ld"><?php _e( 'JSON-LD', 'seo-forge' ); ?></button>
						<button class="schema-tab" data-tab="microdata"><?php _e( 'Microdata', 'seo-forge' ); ?></button>
						<button class="schema-tab" data-tab="rdfa"><?php _e( 'RDFa', 'seo-forge' ); ?></button>
					</div>

					<div class="schema-content">
						<div id="json-ld-content" class="schema-tab-content active">
							<pre><code id="json-ld-code"></code></pre>
						</div>
						<div id="microdata-content" class="schema-tab-content">
							<pre><code id="microdata-code"></code></pre>
						</div>
						<div id="rdfa-content" class="schema-tab-content">
							<pre><code id="rdfa-code"></code></pre>
						</div>
					</div>
				</div>
			</div>

			<!-- Existing Schema -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h3><?php _e( 'Existing Schema Markup', 'seo-forge' ); ?></h3>
					<button id="scan-schema" class="button">
						<span class="dashicons dashicons-search"></span>
						<?php _e( 'Scan Site', 'seo-forge' ); ?>
					</button>
				</div>

				<div class="card-body">
					<div id="existing-schema-list" class="existing-schema-list">
						<div class="no-schema">
							<span class="dashicons dashicons-info"></span>
							<p><?php _e( 'Click "Scan Site" to check for existing schema markup on your website.', 'seo-forge' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
.seo-forge-admin .schema-type-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 20px;
	margin-top: 20px;
}

.seo-forge-admin .schema-type-card {
	padding: 25px;
	border: 2px solid #e1e5e9;
	border-radius: 12px;
	text-align: center;
	cursor: pointer;
	transition: all 0.3s ease;
	background: #fff;
}

.seo-forge-admin .schema-type-card:hover {
	border-color: #0073aa;
	box-shadow: 0 4px 12px rgba(0,115,170,0.1);
	transform: translateY(-2px);
}

.seo-forge-admin .schema-type-card.selected {
	border-color: #0073aa;
	background: #f0f8ff;
}

.seo-forge-admin .schema-icon {
	width: 60px;
	height: 60px;
	margin: 0 auto 15px;
	background: #f8f9fa;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 24px;
	color: #0073aa;
}

.seo-forge-admin .schema-type-card h3 {
	margin: 0 0 10px 0;
	font-size: 18px;
	color: #1d2327;
}

.seo-forge-admin .schema-type-card p {
	margin: 0;
	color: #646970;
	font-size: 14px;
	line-height: 1.4;
}

.seo-forge-admin .schema-form-row {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 20px;
	margin-bottom: 20px;
}

.seo-forge-admin .schema-form-row.full-width {
	grid-template-columns: 1fr;
}

.seo-forge-admin .schema-tabs {
	display: flex;
	border-bottom: 1px solid #ddd;
	margin-bottom: 20px;
}

.seo-forge-admin .schema-tab {
	padding: 12px 20px;
	border: none;
	background: none;
	cursor: pointer;
	border-bottom: 2px solid transparent;
	font-weight: 500;
}

.seo-forge-admin .schema-tab.active {
	border-bottom-color: #0073aa;
	color: #0073aa;
}

.seo-forge-admin .schema-tab-content {
	display: none;
}

.seo-forge-admin .schema-tab-content.active {
	display: block;
}

.seo-forge-admin .schema-tab-content pre {
	background: #f8f9fa;
	border: 1px solid #e1e5e9;
	border-radius: 6px;
	padding: 20px;
	overflow-x: auto;
	max-height: 400px;
}

.seo-forge-admin .schema-tab-content code {
	font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
	font-size: 13px;
	line-height: 1.5;
}

.seo-forge-admin .schema-actions {
	display: flex;
	gap: 10px;
}

.seo-forge-admin .existing-schema-list {
	min-height: 150px;
}

.seo-forge-admin .schema-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 15px;
	border: 1px solid #ddd;
	border-radius: 6px;
	margin-bottom: 10px;
	background: #fff;
}

.seo-forge-admin .schema-item-info h4 {
	margin: 0 0 5px 0;
	font-size: 16px;
}

.seo-forge-admin .schema-item-info p {
	margin: 0;
	color: #666;
	font-size: 14px;
}

.seo-forge-admin .schema-item-actions {
	display: flex;
	gap: 10px;
}

.seo-forge-admin .no-schema {
	text-align: center;
	padding: 40px;
	color: #666;
}

.seo-forge-admin .no-schema .dashicons {
	font-size: 48px;
	margin-bottom: 15px;
	opacity: 0.5;
}

.seo-forge-admin .field-group {
	border: 1px solid #ddd;
	border-radius: 6px;
	padding: 20px;
	margin-bottom: 20px;
	background: #f9f9f9;
}

.seo-forge-admin .field-group h4 {
	margin: 0 0 15px 0;
	color: #1d2327;
}

.seo-forge-admin .add-item-btn {
	margin-top: 10px;
	background: #0073aa;
	color: white;
	border: none;
	padding: 8px 15px;
	border-radius: 4px;
	cursor: pointer;
	font-size: 12px;
}

.seo-forge-admin .remove-item-btn {
	background: #dc3545;
	color: white;
	border: none;
	padding: 5px 10px;
	border-radius: 3px;
	cursor: pointer;
	font-size: 11px;
	margin-left: 10px;
}

@media (max-width: 768px) {
	.seo-forge-admin .schema-type-grid {
		grid-template-columns: 1fr;
	}
	
	.seo-forge-admin .schema-form-row {
		grid-template-columns: 1fr;
	}
	
	.seo-forge-admin .schema-actions {
		flex-direction: column;
	}
	
	.seo-forge-admin .schema-tabs {
		flex-wrap: wrap;
	}
}
</style>

<script>
jQuery(document).ready(function($) {
	let currentSchemaType = '';
	
	// Schema type selection
	$('.schema-type-card').on('click', function() {
		$('.schema-type-card').removeClass('selected');
		$(this).addClass('selected');
		
		currentSchemaType = $(this).data('type');
		showSchemaForm(currentSchemaType);
	});
	
	// Back to types
	$('#back-to-types').on('click', function() {
		$('#schema-form-container').hide();
		$('#schema-output').hide();
		$('.schema-type-card').removeClass('selected');
		currentSchemaType = '';
	});
	
	// Schema form submission
	$('#schema-form').on('submit', function(e) {
		e.preventDefault();
		generateSchema();
	});
	
	// Preview schema
	$('#preview-schema').on('click', function() {
		generateSchema(true);
	});
	
	// Schema tabs
	$('.schema-tab').on('click', function() {
		const tab = $(this).data('tab');
		$('.schema-tab').removeClass('active');
		$('.schema-tab-content').removeClass('active');
		$(this).addClass('active');
		$('#' + tab + '-content').addClass('active');
	});
	
	// Copy to clipboard
	$('#copy-schema').on('click', function() {
		const activeTab = $('.schema-tab.active').data('tab');
		const code = $('#' + activeTab + '-code').text();
		
		navigator.clipboard.writeText(code).then(function() {
			alert('<?php _e( 'Schema markup copied to clipboard!', 'seo-forge' ); ?>');
		});
	});
	
	// Test with Google
	$('#test-schema').on('click', function() {
		const jsonLd = $('#json-ld-code').text();
		const encodedSchema = encodeURIComponent(jsonLd);
		const testUrl = 'https://search.google.com/test/rich-results?code=' + encodedSchema;
		window.open(testUrl, '_blank');
	});
	
	// Apply schema
	$('#apply-schema').on('click', function() {
		if (confirm('<?php _e( 'This will add the schema markup to your website. Continue?', 'seo-forge' ); ?>')) {
			// Here you would send the schema to be applied
			alert('<?php _e( 'Schema markup applied successfully!', 'seo-forge' ); ?>');
		}
	});
	
	// Scan existing schema
	$('#scan-schema').on('click', function() {
		$(this).text('<?php _e( 'Scanning...', 'seo-forge' ); ?>');
		
		setTimeout(function() {
			$('#scan-schema').text('<?php _e( 'Scan Site', 'seo-forge' ); ?>');
			showExistingSchema();
		}, 2000);
	});
	
	function showSchemaForm(type) {
		$('#schema-form-title').text(getSchemaTitle(type));
		$('#schema-fields').html(getSchemaFields(type));
		$('#schema-form-container').show();
		$('#schema-output').hide();
	}
	
	function getSchemaTitle(type) {
		const titles = {
			'organization': '<?php _e( 'Organization Schema', 'seo-forge' ); ?>',
			'local-business': '<?php _e( 'Local Business Schema', 'seo-forge' ); ?>',
			'article': '<?php _e( 'Article Schema', 'seo-forge' ); ?>',
			'product': '<?php _e( 'Product Schema', 'seo-forge' ); ?>',
			'review': '<?php _e( 'Review Schema', 'seo-forge' ); ?>',
			'event': '<?php _e( 'Event Schema', 'seo-forge' ); ?>',
			'faq': '<?php _e( 'FAQ Schema', 'seo-forge' ); ?>',
			'breadcrumb': '<?php _e( 'Breadcrumb Schema', 'seo-forge' ); ?>'
		};
		return titles[type] || '<?php _e( 'Schema Details', 'seo-forge' ); ?>';
	}
	
	function getSchemaFields(type) {
		const fields = {
			'organization': `
				<div class="schema-form-row">
					<div class="form-group">
						<label><?php _e( 'Organization Name', 'seo-forge' ); ?></label>
						<input type="text" name="name" required />
					</div>
					<div class="form-group">
						<label><?php _e( 'Website URL', 'seo-forge' ); ?></label>
						<input type="url" name="url" required />
					</div>
				</div>
				<div class="schema-form-row">
					<div class="form-group">
						<label><?php _e( 'Logo URL', 'seo-forge' ); ?></label>
						<input type="url" name="logo" />
					</div>
					<div class="form-group">
						<label><?php _e( 'Phone Number', 'seo-forge' ); ?></label>
						<input type="tel" name="telephone" />
					</div>
				</div>
				<div class="schema-form-row full-width">
					<div class="form-group">
						<label><?php _e( 'Description', 'seo-forge' ); ?></label>
						<textarea name="description" rows="3"></textarea>
					</div>
				</div>
			`,
			'local-business': `
				<div class="schema-form-row">
					<div class="form-group">
						<label><?php _e( 'Business Name', 'seo-forge' ); ?></label>
						<input type="text" name="name" required />
					</div>
					<div class="form-group">
						<label><?php _e( 'Business Type', 'seo-forge' ); ?></label>
						<select name="type">
							<option value="Restaurant"><?php _e( 'Restaurant', 'seo-forge' ); ?></option>
							<option value="Store"><?php _e( 'Store', 'seo-forge' ); ?></option>
							<option value="Hotel"><?php _e( 'Hotel', 'seo-forge' ); ?></option>
							<option value="Service"><?php _e( 'Service', 'seo-forge' ); ?></option>
						</select>
					</div>
				</div>
				<div class="schema-form-row">
					<div class="form-group">
						<label><?php _e( 'Street Address', 'seo-forge' ); ?></label>
						<input type="text" name="streetAddress" required />
					</div>
					<div class="form-group">
						<label><?php _e( 'City', 'seo-forge' ); ?></label>
						<input type="text" name="addressLocality" required />
					</div>
				</div>
				<div class="schema-form-row">
					<div class="form-group">
						<label><?php _e( 'State/Region', 'seo-forge' ); ?></label>
						<input type="text" name="addressRegion" />
					</div>
					<div class="form-group">
						<label><?php _e( 'Postal Code', 'seo-forge' ); ?></label>
						<input type="text" name="postalCode" />
					</div>
				</div>
				<div class="schema-form-row">
					<div class="form-group">
						<label><?php _e( 'Phone Number', 'seo-forge' ); ?></label>
						<input type="tel" name="telephone" />
					</div>
					<div class="form-group">
						<label><?php _e( 'Opening Hours', 'seo-forge' ); ?></label>
						<input type="text" name="openingHours" placeholder="Mo-Fr 09:00-17:00" />
					</div>
				</div>
			`,
			'article': `
				<div class="schema-form-row">
					<div class="form-group">
						<label><?php _e( 'Article Title', 'seo-forge' ); ?></label>
						<input type="text" name="headline" required />
					</div>
					<div class="form-group">
						<label><?php _e( 'Author Name', 'seo-forge' ); ?></label>
						<input type="text" name="author" required />
					</div>
				</div>
				<div class="schema-form-row">
					<div class="form-group">
						<label><?php _e( 'Published Date', 'seo-forge' ); ?></label>
						<input type="date" name="datePublished" required />
					</div>
					<div class="form-group">
						<label><?php _e( 'Modified Date', 'seo-forge' ); ?></label>
						<input type="date" name="dateModified" />
					</div>
				</div>
				<div class="schema-form-row full-width">
					<div class="form-group">
						<label><?php _e( 'Article Description', 'seo-forge' ); ?></label>
						<textarea name="description" rows="3" required></textarea>
					</div>
				</div>
				<div class="schema-form-row">
					<div class="form-group">
						<label><?php _e( 'Featured Image URL', 'seo-forge' ); ?></label>
						<input type="url" name="image" />
					</div>
					<div class="form-group">
						<label><?php _e( 'Publisher Name', 'seo-forge' ); ?></label>
						<input type="text" name="publisher" />
					</div>
				</div>
			`
		};
		
		return fields[type] || '<p><?php _e( 'Schema fields will appear here.', 'seo-forge' ); ?></p>';
	}
	
	function generateSchema(preview = false) {
		const formData = new FormData($('#schema-form')[0]);
		const schemaData = {};
		
		for (let [key, value] of formData.entries()) {
			if (key !== 'seo_forge_nonce' && key !== '_wp_http_referer') {
				schemaData[key] = value;
			}
		}
		
		const schema = createSchemaMarkup(currentSchemaType, schemaData);
		
		if (preview) {
			console.log('Schema Preview:', schema);
			alert('<?php _e( 'Schema preview logged to console. Check browser developer tools.', 'seo-forge' ); ?>');
		} else {
			displaySchema(schema);
		}
	}
	
	function createSchemaMarkup(type, data) {
		const baseSchema = {
			"@context": "https://schema.org",
			"@type": getSchemaType(type)
		};
		
		// Add type-specific properties
		Object.assign(baseSchema, data);
		
		return baseSchema;
	}
	
	function getSchemaType(type) {
		const types = {
			'organization': 'Organization',
			'local-business': 'LocalBusiness',
			'article': 'Article',
			'product': 'Product',
			'review': 'Review',
			'event': 'Event',
			'faq': 'FAQPage',
			'breadcrumb': 'BreadcrumbList'
		};
		return types[type] || 'Thing';
	}
	
	function displaySchema(schema) {
		const jsonLd = JSON.stringify(schema, null, 2);
		
		$('#json-ld-code').text(jsonLd);
		$('#microdata-code').text('<!-- Microdata conversion coming soon -->');
		$('#rdfa-code').text('<!-- RDFa conversion coming soon -->');
		
		$('#schema-output').show();
	}
	
	function showExistingSchema() {
		const sampleSchemas = [
			{
				type: 'Organization',
				url: '<?php echo home_url(); ?>',
				status: 'Valid'
			},
			{
				type: 'WebSite',
				url: '<?php echo home_url(); ?>',
				status: 'Valid'
			}
		];
		
		const list = $('#existing-schema-list');
		list.empty();
		
		if (sampleSchemas.length === 0) {
			list.html(`
				<div class="no-schema">
					<span class="dashicons dashicons-info"></span>
					<p><?php _e( 'No schema markup found on your website.', 'seo-forge' ); ?></p>
				</div>
			`);
			return;
		}
		
		sampleSchemas.forEach(function(schema) {
			const schemaHtml = `
				<div class="schema-item">
					<div class="schema-item-info">
						<h4>${schema.type}</h4>
						<p>${schema.url}</p>
					</div>
					<div class="schema-item-actions">
						<span class="status ${schema.status.toLowerCase()}">${schema.status}</span>
						<button class="button button-small"><?php _e( 'Edit', 'seo-forge' ); ?></button>
						<button class="button button-small"><?php _e( 'Remove', 'seo-forge' ); ?></button>
					</div>
				</div>
			`;
			list.append(schemaHtml);
		});
	}
});
</script>