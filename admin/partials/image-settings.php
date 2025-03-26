<?php
/**
 * Provide a admin area view for the image settings
 *
 * @link       https://satyendramaurya.site
 * @since      1.0.0
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/admin/partials
 */

// Get current options
$options = get_option('ai_news_scraper_options', array());
$image_settings = isset($options['image_settings']) ? $options['image_settings'] : array();

// Get settings values or defaults
$use_scraped_images = isset($image_settings['use_scraped_images']) ? $image_settings['use_scraped_images'] : true;
$use_ai_images = isset($image_settings['use_ai_images']) ? $image_settings['use_ai_images'] : false;
?>

<div class="wrap ai-news-scraper-container">
    <!-- Alerts container for displaying messages -->
    <div id="alerts-container"></div>

    <div class="ai-news-scraper-header">
        <h1><i class="fas fa-image"></i> Image Settings</h1>
        <p>Configure how featured images are handled for your auto-generated content</p>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Featured Image Configuration</h5>
                </div>
                <div class="card-body">
                    <form id="image-settings-form" class="settings-form">
                        <div class="settings-section">
                            <h3>Image Source Settings</h3>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="image_source" id="use-scraped-images" value="scraped" <?php checked($use_scraped_images, true); ?>>
                                    <label class="form-check-label" for="use-scraped-images">
                                        Use Scraped Images
                                    </label>
                                    <div class="form-text">Use images scraped from the original article.</div>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="image_source" id="use-ai-images" value="ai" <?php checked($use_ai_images, true); ?>>
                                    <label class="form-check-label" for="use-ai-images">
                                        Use AI-Generated Images
                                    </label>
                                    <div class="form-text">Generate images using AI based on article content.</div>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="image_source" id="use-both-images" value="both" <?php checked(!$use_scraped_images && !$use_ai_images, true); ?>>
                                    <label class="form-check-label" for="use-both-images">
                                        Try Scraped Images First, Then AI
                                    </label>
                                    <div class="form-text">Use scraped images if available, otherwise generate using AI.</div>
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <h3>Image Processing</h3>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="add-watermark" name="add_watermark">
                                    <label class="form-check-label" for="add-watermark">Add Watermark</label>
                                    <div class="form-text">Add a watermark to scraped or AI-generated images.</div>
                                </div>
                            </div>
                            
                            <div class="mb-3 watermark-settings" style="display: none;">
                                <label for="watermark-text" class="form-label">Watermark Text</label>
                                <input type="text" class="form-control" id="watermark-text" name="watermark_text" value="<?php echo esc_attr(get_bloginfo('name')); ?>">
                                <div class="form-text">Text to add as watermark on images.</div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="optimize-images" name="optimize_images" checked>
                                    <label class="form-check-label" for="optimize-images">Optimize Images</label>
                                    <div class="form-text">Optimize images for web (resize, compress).</div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image-size" class="form-label">Image Size</label>
                                <select class="form-select" id="image-size" name="image_size">
                                    <option value="thumbnail">Thumbnail (150×150)</option>
                                    <option value="medium">Medium (300×300)</option>
                                    <option value="medium_large">Medium Large (768×0)</option>
                                    <option value="large" selected>Large (1024×1024)</option>
                                    <option value="full">Full Size (Original)</option>
                                </select>
                                <div class="form-text">Size to use for featured images. Larger images may slow down your site.</div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <h3>AI Image Generation</h3>
                            
                            <div class="mb-3">
                                <label for="ai-image-model" class="form-label">AI Image Model</label>
                                <select class="form-select" id="ai-image-model" name="ai_image_model">
                                    <option value="dall-e">DALL-E (OpenAI)</option>
                                    <option value="stable-diffusion">Stable Diffusion</option>
                                    <option value="midjourney">Midjourney-style</option>
                                </select>
                                <div class="form-text">AI model to use for generating images.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image-style" class="form-label">Image Style</label>
                                <select class="form-select" id="image-style" name="image_style">
                                    <option value="realistic">Realistic</option>
                                    <option value="digital-art" selected>Digital Art</option>
                                    <option value="cartoon">Cartoon</option>
                                    <option value="3d-render">3D Render</option>
                                    <option value="sketch">Sketch</option>
                                    <option value="watercolor">Watercolor</option>
                                    <option value="oil-painting">Oil Painting</option>
                                    <option value="minimalist">Minimalist</option>
                                </select>
                                <div class="form-text">Style to apply to AI-generated images.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="prompt-template" class="form-label">Prompt Template</label>
                                <textarea class="form-control" id="prompt-template" name="prompt_template" rows="3">Create a {style} image that represents: {title}</textarea>
                                <div class="form-text">
                                    Template for generating AI image prompts. Available variables: {title}, {style}, {keywords}
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Test AI Image Generation</h5>
                </div>
                <div class="card-body">
                    <form id="test-ai-image-form">
                        <div class="mb-3">
                            <label for="test-prompt" class="form-label">Image Prompt</label>
                            <textarea class="form-control" id="test-prompt" rows="3" placeholder="Enter a description for the image you want to generate..."></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="test-model" class="form-label">AI Model</label>
                                    <select class="form-select" id="test-model">
                                        <option value="dall-e">DALL-E (OpenAI)</option>
                                        <option value="stable-diffusion">Stable Diffusion</option>
                                        <option value="midjourney">Midjourney-style</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="test-style" class="form-label">Image Style</label>
                                    <select class="form-select" id="test-style">
                                        <option value="realistic">Realistic</option>
                                        <option value="digital-art" selected>Digital Art</option>
                                        <option value="cartoon">Cartoon</option>
                                        <option value="3d-render">3D Render</option>
                                        <option value="sketch">Sketch</option>
                                        <option value="watercolor">Watercolor</option>
                                        <option value="oil-painting">Oil Painting</option>
                                        <option value="minimalist">Minimalist</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" id="generate-image-button">Generate Test Image</button>
                    </form>
                    
                    <div id="test-image-result" class="mt-4 text-center"></div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Image Library Stats</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Get total number of images in the media library
                    $total_images = wp_count_attachments();
                    $image_count = 0;
                    foreach (array('image/jpeg', 'image/png', 'image/gif', 'image/webp') as $mime_type) {
                        if (isset($total_images->$mime_type)) {
                            $image_count += $total_images->$mime_type;
                        }
                    }
                    
                    // Get total size of images
                    global $wpdb;
                    $image_size_query = $wpdb->prepare(
                        "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_wp_attachment_metadata'"
                    );
                    $image_size = $wpdb->get_var($image_size_query);
                    $image_size_formatted = size_format($image_size, 2);
                    
                    // Get AI-generated images count
                    $ai_generated_count = $wpdb->get_var(
                        "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = '_ai_generated_image' AND meta_value = '1'"
                    );
                    ?>
                    
                    <div class="list-group">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Total Images
                            <span class="badge bg-primary rounded-pill"><?php echo esc_html($image_count); ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            AI-Generated Images
                            <span class="badge bg-success rounded-pill"><?php echo esc_html($ai_generated_count); ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Total Storage Used
                            <span class="badge bg-info"><?php echo esc_html($image_size_formatted); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Image Quality Guidelines</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> <strong>Resolution:</strong> 1200×628 pixels is optimal for social sharing
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> <strong>Aspect Ratio:</strong> 16:9 for blog posts, 1:1 for social media
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> <strong>File Size:</strong> Keep below 100KB for optimal page load speed
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> <strong>Format:</strong> JPEG for photos, PNG for graphics, WebP for both
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> <strong>Compression:</strong> Use 70-80% quality for optimal balance
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">AI Image Tips</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Creating effective AI image prompts requires specific details and style instructions.
                    </div>
                    
                    <h6>Effective Prompt Structure:</h6>
                    <ol>
                        <li>Subject: What you want to see</li>
                        <li>Setting: Where the subject is</li>
                        <li>Style: Artistic style, lighting, mood</li>
                        <li>Technical details: Resolution, aspect ratio</li>
                    </ol>
                    
                    <h6>Example Prompt:</h6>
                    <p class="bg-light p-2 rounded">
                        "A modern smartphone displaying a news article about technology, on a wooden desk with coffee cup, digital art style, morning light, 16:9 aspect ratio"
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 text-center text-muted">
        <p>AI News Scraper & Auto Blogger Pro v<?php echo AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_VERSION; ?> | Developed by Satyendra Maurya | <a href="https://satyendramaurya.site" target="_blank">satyendramaurya.site</a></p>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle watermark settings visibility
    $('#add-watermark').on('change', function() {
        if ($(this).is(':checked')) {
            $('.watermark-settings').show();
        } else {
            $('.watermark-settings').hide();
        }
    });
    
    // Initialize watermark settings visibility
    if ($('#add-watermark').is(':checked')) {
        $('.watermark-settings').show();
    }
    
    // Handle test AI image generation
    $('#test-ai-image-form').on('submit', function(e) {
        e.preventDefault();
        
        const prompt = $('#test-prompt').val();
        const model = $('#test-model').val();
        const style = $('#test-style').val();
        
        if (!prompt) {
            alert('Please enter an image prompt.');
            return;
        }
        
        // Show loading state
        $('#generate-image-button').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...');
        $('#test-image-result').html(`
            <div class="d-flex justify-content-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <p class="mt-2">Generating image, this may take a minute...</p>
        `);
        
        // AJAX request to generate AI image
        $.ajax({
            url: aiNewsScraperParams.ajax_url,
            type: 'POST',
            data: {
                action: 'generate_ai_image',
                nonce: aiNewsScraperParams.nonce,
                prompt: prompt,
                model: model,
                style: style
            },
            success: function(response) {
                $('#generate-image-button').prop('disabled', false).html('Generate Test Image');
                
                if (response.success) {
                    // Display the generated image
                    $('#test-image-result').html(`
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Generated Image</h5>
                            </div>
                            <div class="card-body">
                                <img src="${response.data.url}" class="img-fluid rounded" alt="AI-generated image">
                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-primary save-image" data-url="${response.data.url}">
                                        <i class="fas fa-save"></i> Save to Media Library
                                    </button>
                                </div>
                            </div>
                        </div>
                    `);
                    
                    // Handle save to media library button
                    $('.save-image').on('click', function() {
                        const imageUrl = $(this).data('url');
                        
                        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
                        
                        // AJAX request to save image to media library
                        $.ajax({
                            url: aiNewsScraperParams.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'save_ai_image',
                                nonce: aiNewsScraperParams.nonce,
                                image_url: imageUrl,
                                prompt: prompt
                            },
                            success: function(response) {
                                $('.save-image').prop('disabled', false).html('<i class="fas fa-save"></i> Save to Media Library');
                                
                                if (response.success) {
                                    alert('Image saved successfully to media library!');
                                } else {
                                    alert('Error: ' + response.data.message);
                                }
                            },
                            error: function() {
                                $('.save-image').prop('disabled', false).html('<i class="fas fa-save"></i> Save to Media Library');
                                alert('An error occurred while saving the image. Please try again.');
                            }
                        });
                    });
                } else {
                    // Display error message
                    $('#test-image-result').html(`
                        <div class="alert alert-danger">
                            Error: ${response.data.message}
                        </div>
                    `);
                }
            },
            error: function() {
                $('#generate-image-button').prop('disabled', false).html('Generate Test Image');
                
                // Display error message
                $('#test-image-result').html(`
                    <div class="alert alert-danger">
                        An error occurred while generating the image. Please check your API keys and try again.
                    </div>
                `);
            }
        });
    });
});
</script>
