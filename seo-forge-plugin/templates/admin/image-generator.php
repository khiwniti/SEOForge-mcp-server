<?php
/**
 * Image Generator Admin Page
 *
 * @package SEO_Forge
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap seo-forge-admin">
	<h1><?php _e( 'SEO Forge - Image Generator', 'seo-forge' ); ?></h1>
	
	<div class="seo-forge-container">
		<div class="seo-forge-header">
			<div class="seo-forge-nav">
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge' ); ?>" class="nav-tab">
					<?php _e( 'Dashboard', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-content' ); ?>" class="nav-tab">
					<?php _e( 'Content Generator', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-images' ); ?>" class="nav-tab nav-tab-active">
					<?php _e( 'Image Generator', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-analyzer' ); ?>" class="nav-tab">
					<?php _e( 'SEO Analyzer', 'seo-forge' ); ?>
				</a>
				<a href="<?php echo admin_url( 'admin.php?page=seo-forge-keywords' ); ?>" class="nav-tab">
					<?php _e( 'Keyword Research', 'seo-forge' ); ?>
				</a>
			</div>
		</div>

		<div class="seo-forge-content">
			<div class="seo-forge-card">
				<div class="card-header">
					<h2><?php _e( 'AI-Powered Image Generation', 'seo-forge' ); ?></h2>
					<p><?php _e( 'Generate high-quality images using Flux AI models for your content.', 'seo-forge' ); ?></p>
				</div>

				<div class="card-body">
					<form id="image-generator-form" class="seo-forge-form">
						<?php wp_nonce_field( 'seo_forge_nonce', 'seo_forge_nonce' ); ?>
						
						<div class="form-row">
							<div class="form-group">
								<label for="image-prompt"><?php _e( 'Image Prompt', 'seo-forge' ); ?></label>
								<textarea 
									id="image-prompt" 
									name="prompt" 
									rows="3" 
									placeholder="<?php _e( 'Describe the image you want to generate...', 'seo-forge' ); ?>"
									required
								></textarea>
								<small class="form-text"><?php _e( 'Be specific and descriptive for better results.', 'seo-forge' ); ?></small>
							</div>
						</div>

						<div class="form-row">
							<div class="form-group">
								<label for="flux-model"><?php _e( 'Flux Model', 'seo-forge' ); ?></label>
								<select id="flux-model" name="model">
									<option value="flux-schnell"><?php _e( 'Flux Schnell (Fast)', 'seo-forge' ); ?></option>
									<option value="flux-dev"><?php _e( 'Flux Dev (Balanced)', 'seo-forge' ); ?></option>
									<option value="flux-pro"><?php _e( 'Flux Pro (High Quality)', 'seo-forge' ); ?></option>
								</select>
							</div>

							<div class="form-group">
								<label for="image-style"><?php _e( 'Style', 'seo-forge' ); ?></label>
								<select id="image-style" name="style">
									<option value="professional"><?php _e( 'Professional', 'seo-forge' ); ?></option>
									<option value="creative"><?php _e( 'Creative', 'seo-forge' ); ?></option>
									<option value="minimalist"><?php _e( 'Minimalist', 'seo-forge' ); ?></option>
									<option value="vibrant"><?php _e( 'Vibrant', 'seo-forge' ); ?></option>
									<option value="elegant"><?php _e( 'Elegant', 'seo-forge' ); ?></option>
									<option value="modern"><?php _e( 'Modern', 'seo-forge' ); ?></option>
									<option value="artistic"><?php _e( 'Artistic', 'seo-forge' ); ?></option>
									<option value="commercial"><?php _e( 'Commercial', 'seo-forge' ); ?></option>
									<option value="natural"><?php _e( 'Natural', 'seo-forge' ); ?></option>
								</select>
							</div>
						</div>

						<div class="form-row">
							<div class="form-group">
								<label for="image-width"><?php _e( 'Width', 'seo-forge' ); ?></label>
								<select id="image-width" name="width">
									<option value="512">512px</option>
									<option value="768">768px</option>
									<option value="1024" selected>1024px</option>
									<option value="1280">1280px</option>
									<option value="1920">1920px</option>
								</select>
							</div>

							<div class="form-group">
								<label for="image-height"><?php _e( 'Height', 'seo-forge' ); ?></label>
								<select id="image-height" name="height">
									<option value="512">512px</option>
									<option value="768">768px</option>
									<option value="1024" selected>1024px</option>
									<option value="1280">1280px</option>
									<option value="1920">1920px</option>
								</select>
							</div>
						</div>

						<div class="form-actions">
							<button type="submit" class="button button-primary button-large">
								<span class="dashicons dashicons-images-alt2"></span>
								<?php _e( 'Generate Image', 'seo-forge' ); ?>
							</button>
							<button type="button" id="batch-generate" class="button button-secondary">
								<span class="dashicons dashicons-format-gallery"></span>
								<?php _e( 'Batch Generate', 'seo-forge' ); ?>
							</button>
						</div>
					</form>

					<!-- Batch Generation Form -->
					<div id="batch-form" class="batch-form" style="display: none;">
						<h3><?php _e( 'Batch Image Generation', 'seo-forge' ); ?></h3>
						<form id="batch-generator-form">
							<?php wp_nonce_field( 'seo_forge_nonce', 'seo_forge_nonce_batch' ); ?>
							
							<div class="form-group">
								<label><?php _e( 'Image Prompts (one per line)', 'seo-forge' ); ?></label>
								<textarea 
									id="batch-prompts" 
									name="prompts" 
									rows="6" 
									placeholder="<?php _e( 'Professional business meeting&#10;Team collaboration workspace&#10;Digital marketing dashboard', 'seo-forge' ); ?>"
									required
								></textarea>
								<small class="form-text"><?php _e( 'Enter up to 10 prompts, one per line.', 'seo-forge' ); ?></small>
							</div>

							<div class="form-actions">
								<button type="submit" class="button button-primary">
									<?php _e( 'Generate Batch', 'seo-forge' ); ?>
								</button>
								<button type="button" id="cancel-batch" class="button">
									<?php _e( 'Cancel', 'seo-forge' ); ?>
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>

			<!-- Results Section -->
			<div id="generation-results" class="seo-forge-card" style="display: none;">
				<div class="card-header">
					<h3><?php _e( 'Generated Images', 'seo-forge' ); ?></h3>
				</div>
				<div class="card-body">
					<div id="image-gallery" class="image-gallery"></div>
				</div>
			</div>

			<!-- Loading State -->
			<div id="generation-loading" class="seo-forge-loading" style="display: none;">
				<div class="loading-spinner"></div>
				<p><?php _e( 'Generating your images... This may take a few moments.', 'seo-forge' ); ?></p>
			</div>
		</div>
	</div>
</div>

<style>
.seo-forge-admin .image-gallery {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
	gap: 20px;
	margin-top: 20px;
}

.seo-forge-admin .image-item {
	border: 1px solid #ddd;
	border-radius: 8px;
	overflow: hidden;
	background: #fff;
	box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.seo-forge-admin .image-item img {
	width: 100%;
	height: 200px;
	object-fit: cover;
}

.seo-forge-admin .image-item .image-info {
	padding: 15px;
}

.seo-forge-admin .image-item .image-prompt {
	font-size: 14px;
	color: #666;
	margin-bottom: 10px;
}

.seo-forge-admin .image-item .image-actions {
	display: flex;
	gap: 10px;
}

.seo-forge-admin .image-item .image-actions button {
	flex: 1;
	padding: 8px 12px;
	border: none;
	border-radius: 4px;
	cursor: pointer;
	font-size: 12px;
}

.seo-forge-admin .image-item .download-btn {
	background: #0073aa;
	color: white;
}

.seo-forge-admin .image-item .insert-btn {
	background: #00a32a;
	color: white;
}

.seo-forge-admin .batch-form {
	margin-top: 30px;
	padding: 20px;
	border: 1px solid #ddd;
	border-radius: 8px;
	background: #f9f9f9;
}

.seo-forge-admin .form-row {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 20px;
	margin-bottom: 20px;
}

.seo-forge-admin .form-row .form-group {
	margin-bottom: 0;
}

@media (max-width: 768px) {
	.seo-forge-admin .form-row {
		grid-template-columns: 1fr;
	}
	
	.seo-forge-admin .image-gallery {
		grid-template-columns: 1fr;
	}
}
</style>

<script>
jQuery(document).ready(function($) {
	// Single image generation
	$('#image-generator-form').on('submit', function(e) {
		e.preventDefault();
		
		const formData = new FormData(this);
		formData.append('action', 'seo_forge_generate_flux_image');
		
		$('#generation-loading').show();
		$('#generation-results').hide();
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				$('#generation-loading').hide();
				
				if (response.success) {
					displayImages([response.data]);
				} else {
					alert('Error: ' + response.data.message);
				}
			},
			error: function() {
				$('#generation-loading').hide();
				alert('<?php _e( 'An error occurred while generating the image.', 'seo-forge' ); ?>');
			}
		});
	});
	
	// Batch generation toggle
	$('#batch-generate').on('click', function() {
		$('#batch-form').toggle();
	});
	
	$('#cancel-batch').on('click', function() {
		$('#batch-form').hide();
	});
	
	// Batch image generation
	$('#batch-generator-form').on('submit', function(e) {
		e.preventDefault();
		
		const prompts = $('#batch-prompts').val().split('\n').filter(p => p.trim());
		if (prompts.length === 0) {
			alert('<?php _e( 'Please enter at least one prompt.', 'seo-forge' ); ?>');
			return;
		}
		
		if (prompts.length > 10) {
			alert('<?php _e( 'Maximum 10 prompts allowed.', 'seo-forge' ); ?>');
			return;
		}
		
		const formData = new FormData();
		formData.append('action', 'seo_forge_generate_flux_batch');
		formData.append('nonce', $('#seo_forge_nonce_batch').val());
		formData.append('model', $('#flux-model').val());
		formData.append('style', $('#image-style').val());
		
		prompts.forEach((prompt, index) => {
			formData.append(`prompts[${index}]`, prompt.trim());
		});
		
		$('#generation-loading').show();
		$('#generation-results').hide();
		$('#batch-form').hide();
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				$('#generation-loading').hide();
				
				if (response.success) {
					displayImages(response.data.images);
				} else {
					alert('Error: ' + response.data.message);
				}
			},
			error: function() {
				$('#generation-loading').hide();
				alert('<?php _e( 'An error occurred while generating the batch images.', 'seo-forge' ); ?>');
			}
		});
	});
	
	// Display images function
	function displayImages(images) {
		const gallery = $('#image-gallery');
		gallery.empty();
		
		images.forEach(function(image) {
			const imageHtml = `
				<div class="image-item">
					<img src="${image.image_url}" alt="${image.prompt || 'Generated Image'}" />
					<div class="image-info">
						<div class="image-prompt">${image.prompt || 'Generated Image'}</div>
						<div class="image-actions">
							<button class="download-btn" onclick="downloadImage('${image.image_url}', '${image.prompt || 'image'}')">
								<?php _e( 'Download', 'seo-forge' ); ?>
							</button>
							<button class="insert-btn" onclick="insertToMediaLibrary('${image.image_url}', '${image.prompt || 'Generated Image'}')">
								<?php _e( 'Add to Media', 'seo-forge' ); ?>
							</button>
						</div>
					</div>
				</div>
			`;
			gallery.append(imageHtml);
		});
		
		$('#generation-results').show();
	}
	
	// Global functions for image actions
	window.downloadImage = function(url, filename) {
		const a = document.createElement('a');
		a.href = url;
		a.download = filename.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '.jpg';
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
	};
	
	window.insertToMediaLibrary = function(url, title) {
		// This would require additional backend functionality
		alert('<?php _e( 'Media library integration coming soon!', 'seo-forge' ); ?>');
	};
});
</script>