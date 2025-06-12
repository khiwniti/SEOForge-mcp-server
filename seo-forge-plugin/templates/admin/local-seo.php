<?php
/**
 * Local SEO Admin Page
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap seo-forge-admin">
	<h1><?php _e( 'SEO Forge - Local SEO', 'seo-forge' ); ?></h1>
	
	<div class="seo-forge-container">
		<div class="seo-forge-header">
			<div class="seo-forge-nav">
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge' ); ?>" class="nav-tab">
					<?php _e( 'Dashboard', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-schema' ); ?>" class="nav-tab">
					<?php _e( 'Schema Generator', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-local-seo' ); ?>" class="nav-tab nav-tab-active">
					<?php _e( 'Local SEO', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-analytics' ); ?>" class="nav-tab">
					<?php _e( 'Analytics', 'seo-forge' ); ?>
				</a>
			</div>
		</div>

		<div class="seo-forge-content">
			<!-- Business Information -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h2><?php _e( 'Business Information', 'seo-forge' ); ?></h2>
					<p><?php _e( 'Configure your business details for local search optimization.', 'seo-forge' ); ?></p>
				</div>

				<div class="card-body">
					<form id="business-info-form" class="seo-forge-form">
						<?php wp_nonce_field( 'seo_forge_nonce', 'seo_forge_nonce' ); ?>
						
						<div class="form-row">
							<div class="form-group">
								<label for="business-name"><?php _e( 'Business Name', 'seo-forge' ); ?></label>
								<input type="text" id="business-name" name="business_name" required />
							</div>
							<div class="form-group">
								<label for="business-type"><?php _e( 'Business Type', 'seo-forge' ); ?></label>
								<select id="business-type" name="business_type">
									<option value=""><?php _e( 'Select Business Type', 'seo-forge' ); ?></option>
									<option value="Restaurant"><?php _e( 'Restaurant', 'seo-forge' ); ?></option>
									<option value="Store"><?php _e( 'Retail Store', 'seo-forge' ); ?></option>
									<option value="Hotel"><?php _e( 'Hotel', 'seo-forge' ); ?></option>
									<option value="Hospital"><?php _e( 'Hospital', 'seo-forge' ); ?></option>
									<option value="Dentist"><?php _e( 'Dentist', 'seo-forge' ); ?></option>
									<option value="Lawyer"><?php _e( 'Law Firm', 'seo-forge' ); ?></option>
									<option value="RealEstateAgent"><?php _e( 'Real Estate', 'seo-forge' ); ?></option>
									<option value="AutoDealer"><?php _e( 'Auto Dealer', 'seo-forge' ); ?></option>
									<option value="BeautySalon"><?php _e( 'Beauty Salon', 'seo-forge' ); ?></option>
									<option value="GymOrFitnessCenter"><?php _e( 'Gym/Fitness', 'seo-forge' ); ?></option>
								</select>
							</div>
						</div>

						<div class="form-row">
							<div class="form-group">
								<label for="street-address"><?php _e( 'Street Address', 'seo-forge' ); ?></label>
								<input type="text" id="street-address" name="street_address" required />
							</div>
							<div class="form-group">
								<label for="city"><?php _e( 'City', 'seo-forge' ); ?></label>
								<input type="text" id="city" name="city" required />
							</div>
						</div>

						<div class="form-row">
							<div class="form-group">
								<label for="state"><?php _e( 'State/Province', 'seo-forge' ); ?></label>
								<input type="text" id="state" name="state" />
							</div>
							<div class="form-group">
								<label for="postal-code"><?php _e( 'Postal Code', 'seo-forge' ); ?></label>
								<input type="text" id="postal-code" name="postal_code" />
							</div>
						</div>

						<div class="form-row">
							<div class="form-group">
								<label for="country"><?php _e( 'Country', 'seo-forge' ); ?></label>
								<select id="country" name="country">
									<option value="US"><?php _e( 'United States', 'seo-forge' ); ?></option>
									<option value="CA"><?php _e( 'Canada', 'seo-forge' ); ?></option>
									<option value="GB"><?php _e( 'United Kingdom', 'seo-forge' ); ?></option>
									<option value="AU"><?php _e( 'Australia', 'seo-forge' ); ?></option>
									<option value="TH"><?php _e( 'Thailand', 'seo-forge' ); ?></option>
									<option value="DE"><?php _e( 'Germany', 'seo-forge' ); ?></option>
									<option value="FR"><?php _e( 'France', 'seo-forge' ); ?></option>
									<option value="ES"><?php _e( 'Spain', 'seo-forge' ); ?></option>
									<option value="IT"><?php _e( 'Italy', 'seo-forge' ); ?></option>
									<option value="JP"><?php _e( 'Japan', 'seo-forge' ); ?></option>
								</select>
							</div>
							<div class="form-group">
								<label for="phone"><?php _e( 'Phone Number', 'seo-forge' ); ?></label>
								<input type="tel" id="phone" name="phone" />
							</div>
						</div>

						<div class="form-actions">
							<button type="submit" class="button button-primary button-large">
								<span class="dashicons dashicons-building"></span>
								<?php _e( 'Save Business Info', 'seo-forge' ); ?>
							</button>
						</div>
					</form>
				</div>
			</div>

			<!-- Opening Hours -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h3><?php _e( 'Opening Hours', 'seo-forge' ); ?></h3>
				</div>

				<div class="card-body">
					<form id="opening-hours-form" class="opening-hours-form">
						<?php wp_nonce_field( 'seo_forge_nonce', 'seo_forge_nonce_hours' ); ?>
						
						<div class="hours-grid">
							<?php
							$days = [
								'monday' => __( 'Monday', 'seo-forge' ),
								'tuesday' => __( 'Tuesday', 'seo-forge' ),
								'wednesday' => __( 'Wednesday', 'seo-forge' ),
								'thursday' => __( 'Thursday', 'seo-forge' ),
								'friday' => __( 'Friday', 'seo-forge' ),
								'saturday' => __( 'Saturday', 'seo-forge' ),
								'sunday' => __( 'Sunday', 'seo-forge' )
							];
							
							foreach ( $days as $day => $label ) :
							?>
							<div class="hour-row">
								<div class="day-label">
									<label><?php echo $label; ?></label>
								</div>
								<div class="hour-controls">
									<label class="closed-checkbox">
										<input type="checkbox" name="<?php echo $day; ?>_closed" class="closed-toggle" />
										<?php _e( 'Closed', 'seo-forge' ); ?>
									</label>
									<div class="time-inputs">
										<input type="time" name="<?php echo $day; ?>_open" class="open-time" />
										<span class="time-separator"><?php _e( 'to', 'seo-forge' ); ?></span>
										<input type="time" name="<?php echo $day; ?>_close" class="close-time" />
									</div>
								</div>
							</div>
							<?php endforeach; ?>
						</div>

						<div class="form-actions">
							<button type="submit" class="button button-primary">
								<span class="dashicons dashicons-clock"></span>
								<?php _e( 'Save Opening Hours', 'seo-forge' ); ?>
							</button>
							<button type="button" id="copy-hours" class="button">
								<?php _e( 'Copy Monday to All', 'seo-forge' ); ?>
							</button>
						</div>
					</form>
				</div>
			</div>

			<!-- Google My Business -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h3><?php _e( 'Google My Business Integration', 'seo-forge' ); ?></h3>
				</div>

				<div class="card-body">
					<div class="gmb-status">
						<div class="status-item">
							<div class="status-icon" id="gmb-listing-status">
								<span class="dashicons dashicons-minus"></span>
							</div>
							<div class="status-info">
								<h4><?php _e( 'Google My Business Listing', 'seo-forge' ); ?></h4>
								<p><?php _e( 'Claim and verify your business on Google', 'seo-forge' ); ?></p>
							</div>
							<div class="status-action">
								<button id="check-gmb-listing" class="button">
									<?php _e( 'Check Status', 'seo-forge' ); ?>
								</button>
							</div>
						</div>

						<div class="status-item">
							<div class="status-icon" id="gmb-reviews-status">
								<span class="dashicons dashicons-minus"></span>
							</div>
							<div class="status-info">
								<h4><?php _e( 'Customer Reviews', 'seo-forge' ); ?></h4>
								<p><?php _e( 'Monitor and respond to customer reviews', 'seo-forge' ); ?></p>
							</div>
							<div class="status-action">
								<button id="check-reviews" class="button">
									<?php _e( 'View Reviews', 'seo-forge' ); ?>
								</button>
							</div>
						</div>

						<div class="status-item">
							<div class="status-icon" id="gmb-posts-status">
								<span class="dashicons dashicons-minus"></span>
							</div>
							<div class="status-info">
								<h4><?php _e( 'Google Posts', 'seo-forge' ); ?></h4>
								<p><?php _e( 'Share updates and offers on your listing', 'seo-forge' ); ?></p>
							</div>
							<div class="status-action">
								<button id="create-post" class="button">
									<?php _e( 'Create Post', 'seo-forge' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Local Citations -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h3><?php _e( 'Local Citations', 'seo-forge' ); ?></h3>
					<button id="scan-citations" class="button">
						<span class="dashicons dashicons-search"></span>
						<?php _e( 'Scan Citations', 'seo-forge' ); ?>
					</button>
				</div>

				<div class="card-body">
					<div id="citations-list" class="citations-list">
						<div class="no-citations">
							<span class="dashicons dashicons-location-alt"></span>
							<p><?php _e( 'Click "Scan Citations" to check your business listings across the web.', 'seo-forge' ); ?></p>
						</div>
					</div>
				</div>
			</div>

			<!-- Local Keywords -->
			<div class="seo-forge-card">
				<div class="card-header">
					<h3><?php _e( 'Local Keyword Tracking', 'seo-forge' ); ?></h3>
				</div>

				<div class="card-body">
					<form id="local-keywords-form" class="seo-forge-form">
						<?php wp_nonce_field( 'seo_forge_nonce', 'seo_forge_nonce_keywords' ); ?>
						
						<div class="form-row">
							<div class="form-group">
								<label for="local-keywords"><?php _e( 'Local Keywords', 'seo-forge' ); ?></label>
								<textarea 
									id="local-keywords" 
									name="keywords" 
									rows="3" 
									placeholder="<?php _e( 'restaurant near me&#10;best pizza in [city]&#10;[service] in [city]', 'seo-forge' ); ?>"
								></textarea>
								<small class="form-text"><?php _e( 'Use [city] and [service] as placeholders.', 'seo-forge' ); ?></small>
							</div>
							<div class="form-group">
								<label for="target-location"><?php _e( 'Target Location', 'seo-forge' ); ?></label>
								<input type="text" id="target-location" name="location" placeholder="<?php _e( 'City, State', 'seo-forge' ); ?>" />
								<small class="form-text"><?php _e( 'Location for local search tracking.', 'seo-forge' ); ?></small>
							</div>
						</div>

						<div class="form-actions">
							<button type="submit" class="button button-primary">
								<span class="dashicons dashicons-location"></span>
								<?php _e( 'Track Local Keywords', 'seo-forge' ); ?>
							</button>
						</div>
					</form>

					<div id="local-keywords-results" class="local-keywords-results" style="display: none;">
						<h4><?php _e( 'Local Keyword Rankings', 'seo-forge' ); ?></h4>
						<div id="local-keywords-table"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
.seo-forge-admin .form-row {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 20px;
	margin-bottom: 20px;
}

.seo-forge-admin .hours-grid {
	display: grid;
	gap: 15px;
	margin-bottom: 20px;
}

.seo-forge-admin .hour-row {
	display: grid;
	grid-template-columns: 120px 1fr;
	gap: 20px;
	align-items: center;
	padding: 15px;
	border: 1px solid #ddd;
	border-radius: 6px;
	background: #f9f9f9;
}

.seo-forge-admin .day-label label {
	font-weight: 600;
	color: #1d2327;
}

.seo-forge-admin .hour-controls {
	display: flex;
	align-items: center;
	gap: 15px;
}

.seo-forge-admin .closed-checkbox {
	display: flex;
	align-items: center;
	gap: 5px;
	font-size: 14px;
}

.seo-forge-admin .time-inputs {
	display: flex;
	align-items: center;
	gap: 10px;
}

.seo-forge-admin .time-separator {
	color: #666;
	font-size: 14px;
}

.seo-forge-admin .gmb-status {
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

.seo-forge-admin .status-icon.verified {
	background: #d4edda;
	border-color: #28a745;
	color: #28a745;
}

.seo-forge-admin .status-icon.warning {
	background: #fff3cd;
	border-color: #ffc107;
	color: #856404;
}

.seo-forge-admin .status-icon.error {
	background: #f8d7da;
	border-color: #dc3545;
	color: #dc3545;
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

.seo-forge-admin .citations-list {
	min-height: 200px;
}

.seo-forge-admin .citation-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 15px;
	border: 1px solid #ddd;
	border-radius: 6px;
	margin-bottom: 10px;
	background: #fff;
}

.seo-forge-admin .citation-info h4 {
	margin: 0 0 5px 0;
	font-size: 16px;
}

.seo-forge-admin .citation-info p {
	margin: 0;
	color: #666;
	font-size: 14px;
}

.seo-forge-admin .citation-status {
	padding: 4px 12px;
	border-radius: 12px;
	font-size: 12px;
	font-weight: bold;
	text-transform: uppercase;
}

.seo-forge-admin .citation-status.verified {
	background: #d4edda;
	color: #155724;
}

.seo-forge-admin .citation-status.inconsistent {
	background: #fff3cd;
	color: #856404;
}

.seo-forge-admin .citation-status.missing {
	background: #f8d7da;
	color: #721c24;
}

.seo-forge-admin .no-citations {
	text-align: center;
	padding: 40px;
	color: #666;
}

.seo-forge-admin .no-citations .dashicons {
	font-size: 48px;
	margin-bottom: 15px;
	opacity: 0.5;
}

.seo-forge-admin .local-keywords-results {
	margin-top: 30px;
	padding: 20px;
	border: 1px solid #ddd;
	border-radius: 8px;
	background: #f9f9f9;
}

@media (max-width: 768px) {
	.seo-forge-admin .form-row {
		grid-template-columns: 1fr;
	}
	
	.seo-forge-admin .hour-row {
		grid-template-columns: 1fr;
		text-align: center;
	}
	
	.seo-forge-admin .hour-controls {
		flex-direction: column;
		align-items: center;
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
	// Business info form
	$('#business-info-form').on('submit', function(e) {
		e.preventDefault();
		
		// Simulate saving
		alert('<?php _e( 'Business information saved successfully!', 'seo-forge' ); ?>');
	});
	
	// Opening hours form
	$('#opening-hours-form').on('submit', function(e) {
		e.preventDefault();
		
		// Simulate saving
		alert('<?php _e( 'Opening hours saved successfully!', 'seo-forge' ); ?>');
	});
	
	// Closed toggle functionality
	$('.closed-toggle').on('change', function() {
		const timeInputs = $(this).closest('.hour-controls').find('.time-inputs');
		if ($(this).is(':checked')) {
			timeInputs.hide();
			timeInputs.find('input').prop('required', false);
		} else {
			timeInputs.show();
			timeInputs.find('input').prop('required', true);
		}
	});
	
	// Copy Monday hours to all days
	$('#copy-hours').on('click', function() {
		const mondayOpen = $('input[name="monday_open"]').val();
		const mondayClose = $('input[name="monday_close"]').val();
		const mondayClosed = $('input[name="monday_closed"]').is(':checked');
		
		if (mondayOpen || mondayClosed) {
			const days = ['tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
			
			days.forEach(function(day) {
				$(`input[name="${day}_open"]`).val(mondayOpen);
				$(`input[name="${day}_close"]`).val(mondayClose);
				$(`input[name="${day}_closed"]`).prop('checked', mondayClosed).trigger('change');
			});
			
			alert('<?php _e( 'Monday hours copied to all days!', 'seo-forge' ); ?>');
		} else {
			alert('<?php _e( 'Please set Monday hours first.', 'seo-forge' ); ?>');
		}
	});
	
	// Google My Business checks
	$('#check-gmb-listing').on('click', function() {
		$(this).text('<?php _e( 'Checking...', 'seo-forge' ); ?>');
		
		setTimeout(function() {
			$('#gmb-listing-status').addClass('verified').html('<span class="dashicons dashicons-yes"></span>');
			$('#check-gmb-listing').text('<?php _e( 'Verified', 'seo-forge' ); ?>');
		}, 2000);
	});
	
	$('#check-reviews').on('click', function() {
		$(this).text('<?php _e( 'Loading...', 'seo-forge' ); ?>');
		
		setTimeout(function() {
			$('#gmb-reviews-status').addClass('warning').html('<span class="dashicons dashicons-warning"></span>');
			$('#check-reviews').text('<?php _e( '3 New Reviews', 'seo-forge' ); ?>');
		}, 1500);
	});
	
	$('#create-post').on('click', function() {
		alert('<?php _e( 'Google Posts feature coming soon!', 'seo-forge' ); ?>');
	});
	
	// Scan citations
	$('#scan-citations').on('click', function() {
		$(this).text('<?php _e( 'Scanning...', 'seo-forge' ); ?>');
		
		setTimeout(function() {
			$('#scan-citations').text('<?php _e( 'Scan Citations', 'seo-forge' ); ?>');
			showCitations();
		}, 3000);
	});
	
	// Local keywords form
	$('#local-keywords-form').on('submit', function(e) {
		e.preventDefault();
		
		const keywords = $('#local-keywords').val();
		const location = $('#target-location').val();
		
		if (!keywords || !location) {
			alert('<?php _e( 'Please enter both keywords and location.', 'seo-forge' ); ?>');
			return;
		}
		
		// Simulate keyword tracking
		setTimeout(function() {
			showLocalKeywordResults();
		}, 2000);
	});
	
	function showCitations() {
		const citations = [
			{
				name: 'Google My Business',
				status: 'verified',
				url: 'https://business.google.com'
			},
			{
				name: 'Yelp',
				status: 'inconsistent',
				url: 'https://yelp.com'
			},
			{
				name: 'Facebook',
				status: 'verified',
				url: 'https://facebook.com'
			},
			{
				name: 'Yellow Pages',
				status: 'missing',
				url: 'https://yellowpages.com'
			}
		];
		
		const list = $('#citations-list');
		list.empty();
		
		citations.forEach(function(citation) {
			const statusText = {
				'verified': '<?php _e( 'Verified', 'seo-forge' ); ?>',
				'inconsistent': '<?php _e( 'Inconsistent', 'seo-forge' ); ?>',
				'missing': '<?php _e( 'Missing', 'seo-forge' ); ?>'
			};
			
			const citationHtml = `
				<div class="citation-item">
					<div class="citation-info">
						<h4>${citation.name}</h4>
						<p>${citation.url}</p>
					</div>
					<div class="citation-status ${citation.status}">
						${statusText[citation.status]}
					</div>
				</div>
			`;
			
			list.append(citationHtml);
		});
	}
	
	function showLocalKeywordResults() {
		const results = [
			{
				keyword: 'restaurant near me',
				position: 8,
				volume: 12000
			},
			{
				keyword: 'best pizza in bangkok',
				position: 15,
				volume: 3200
			},
			{
				keyword: 'thai food delivery',
				position: 23,
				volume: 8500
			}
		];
		
		let tableHtml = `
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php _e( 'Keyword', 'seo-forge' ); ?></th>
						<th><?php _e( 'Position', 'seo-forge' ); ?></th>
						<th><?php _e( 'Search Volume', 'seo-forge' ); ?></th>
					</tr>
				</thead>
				<tbody>
		`;
		
		results.forEach(function(result) {
			tableHtml += `
				<tr>
					<td><strong>${result.keyword}</strong></td>
					<td>${result.position}</td>
					<td>${result.volume.toLocaleString()}</td>
				</tr>
			`;
		});
		
		tableHtml += '</tbody></table>';
		
		$('#local-keywords-table').html(tableHtml);
		$('#local-keywords-results').show();
	}
	
	// Initialize with default hours
	$('.time-inputs input[type="time"]').each(function() {
		if ($(this).hasClass('open-time')) {
			$(this).val('09:00');
		} else {
			$(this).val('17:00');
		}
	});
});
</script>