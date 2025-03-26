<?php
/**
 * Provide a admin area view for the post scheduler
 *
 * @link       https://satyendramaurya.site
 * @since      1.0.0
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/admin/partials
 */

// Get current options
$options = get_option('ai_news_scraper_options', array());
$scheduler_settings = isset($options['post_scheduler']) ? $options['post_scheduler'] : array();

// Get settings values or defaults
$bulk_posting_enabled = isset($scheduler_settings['bulk_posting_enabled']) ? $scheduler_settings['bulk_posting_enabled'] : false;
$post_schedule = isset($scheduler_settings['post_schedule']) ? $scheduler_settings['post_schedule'] : 'hourly';
$randomize_schedule = isset($scheduler_settings['randomize_schedule']) ? $scheduler_settings['randomize_schedule'] : true;
$auto_internal_linking = isset($scheduler_settings['auto_internal_linking']) ? $scheduler_settings['auto_internal_linking'] : true;

// Get all categories for selection
$categories = get_categories(array(
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC'
));

// Get scheduled posts
global $wpdb;
$scheduled_posts = $wpdb->get_results(
    "SELECT ID, post_title, post_date 
    FROM {$wpdb->posts} 
    WHERE post_type = 'post' 
    AND post_status = 'future' 
    ORDER BY post_date ASC"
);
?>

<div class="wrap ai-news-scraper-container">
    <!-- Alerts container for displaying messages -->
    <div id="alerts-container"></div>

    <div class="ai-news-scraper-header">
        <h1><i class="fas fa-calendar-alt"></i> Post Scheduler</h1>
        <p>Manage bulk article scheduling and posting options</p>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Bulk Scheduling Tool</h5>
                </div>
                <div class="card-body">
                    <div class="settings-section">
                        <h3>Create Bulk Post Schedule</h3>
                        
                        <form id="bulk-schedule-form">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="bulk-source" class="form-label">Content Source</label>
                                    <select class="form-select" id="bulk-source" name="source">
                                        <option value="rss">RSS Feeds</option>
                                        <option value="urls">URL List</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="bulk-count" class="form-label">Number of Articles</label>
                                    <select class="form-select" id="bulk-count" name="count">
                                        <option value="10">10 Articles</option>
                                        <option value="20">20 Articles</option>
                                        <option value="30">30 Articles</option>
                                        <option value="50">50 Articles</option>
                                        <option value="100">100 Articles</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3" id="urls-container" style="display: none;">
                                <label for="url-list" class="form-label">URLs (One per line)</label>
                                <textarea class="form-control" id="url-list" name="urls" rows="5" placeholder="https://example.com/article-1&#10;https://example.com/article-2"></textarea>
                            </div>
                            
                            <div class="mb-3" id="rss-container">
                                <label class="form-label">Select RSS Feeds</label>
                                <div id="rss-feeds-checkboxes" class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all-feeds" checked>
                                        <label class="form-check-label" for="select-all-feeds">
                                            <strong>Select All Feeds</strong>
                                        </label>
                                    </div>
                                    <hr>
                                    <!-- RSS feeds will be populated dynamically -->
                                    <div class="text-center">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        Loading RSS feeds...
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="ai-model-bulk" class="form-label">AI Model</label>
                                    <select class="form-select" id="ai-model-bulk" name="ai_model">
                                        <option value="openai">OpenAI GPT</option>
                                        <option value="gemini">Google Gemini</option>
                                        <option value="claude">Anthropic Claude</option>
                                        <option value="deepseek">DeepSeek AI</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="language-bulk" class="form-label">Language</label>
                                    <select class="form-select" id="language-bulk" name="language">
                                        <option value="english">English</option>
                                        <option value="hindi">Hindi</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="tone-bulk" class="form-label">Writing Tone</label>
                                    <select class="form-select" id="tone-bulk" name="tone">
                                        <option value="default">Default</option>
                                        <option value="banarasi">Banarasi</option>
                                        <option value="lucknow">Lucknow</option>
                                        <option value="delhi">Delhi</option>
                                        <option value="indore">Indore</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Categories</label>
                                    <div class="categories-container">
                                        <?php foreach ($categories as $category) : ?>
                                            <div class="form-check">
                                                <input class="form-check-input category-checkbox" type="checkbox" value="<?php echo esc_attr($category->term_id); ?>" id="category-<?php echo esc_attr($category->term_id); ?>" name="categories[]">
                                                <label class="form-check-label" for="category-<?php echo esc_attr($category->term_id); ?>">
                                                    <?php echo esc_html($category->name); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="schedule-interval" class="form-label">Post Interval</label>
                                <select class="form-select" id="schedule-interval" name="interval">
                                    <option value="30">Every 30 minutes</option>
                                    <option value="60" selected>Every 1 hour</option>
                                    <option value="120">Every 2 hours</option>
                                    <option value="360">Every 6 hours</option>
                                    <option value="720">Every 12 hours</option>
                                    <option value="1440">Every 24 hours</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="randomize-bulk" name="randomize" checked>
                                    <label class="form-check-label" for="randomize-bulk">Randomize Schedule (Adds/subtracts 1-15 minutes randomly)</label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="start-date" class="form-label">Start Date/Time</label>
                                <input type="datetime-local" class="form-control" id="start-date" name="start_date">
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="auto-internal-linking-bulk" name="auto_internal_linking" checked>
                                    <label class="form-check-label" for="auto-internal-linking-bulk">Enable Auto Internal Linking</label>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary" id="create-schedule-button">Create Bulk Schedule</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Scheduled Posts</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="scheduled-posts-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Scheduled For</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($scheduled_posts)) : ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No scheduled posts found.</td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($scheduled_posts as $post) : ?>
                                        <tr>
                                            <td><?php echo esc_html($post->ID); ?></td>
                                            <td><?php echo esc_html($post->post_title); ?></td>
                                            <td><?php echo esc_html(get_date_from_gmt($post->post_date, 'F j, Y, g:i a')); ?></td>
                                            <td>
                                                <a href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>" class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger delete-scheduled-post" data-post-id="<?php echo esc_attr($post->ID); ?>">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Scheduler Settings</h5>
                </div>
                <div class="card-body">
                    <form id="scheduler-settings-form" class="settings-form">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="bulk-posting-enabled" name="bulk_posting_enabled" <?php checked($bulk_posting_enabled, true); ?>>
                                <label class="form-check-label" for="bulk-posting-enabled">Enable Bulk Posting</label>
                                <div class="form-text">Enable or disable the bulk posting feature.</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="post-schedule" class="form-label">Default Post Schedule</label>
                            <select class="form-select" id="post-schedule" name="post_schedule">
                                <option value="minutes" <?php selected($post_schedule, 'minutes'); ?>>Minutes</option>
                                <option value="hourly" <?php selected($post_schedule, 'hourly'); ?>>Hours</option>
                                <option value="daily" <?php selected($post_schedule, 'daily'); ?>>Days</option>
                            </select>
                            <div class="form-text">Select the default time unit for post scheduling.</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="randomize-schedule" name="randomize_schedule" <?php checked($randomize_schedule, true); ?>>
                                <label class="form-check-label" for="randomize-schedule">Randomize Post Timings</label>
                                <div class="form-text">Randomize post times to appear more natural.</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="auto-internal-linking" name="auto_internal_linking" <?php checked($auto_internal_linking, true); ?>>
                                <label class="form-check-label" for="auto-internal-linking">Enable Auto Internal Linking</label>
                                <div class="form-text">Automatically add internal links to related content.</div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Scheduling Calendar</h5>
                </div>
                <div class="card-body">
                    <div id="scheduling-calendar">
                        <!-- Calendar will be added using JavaScript -->
                        <div class="text-center">
                            <p>Calendar loading...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Scheduling Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> Schedule posts during high traffic hours for maximum visibility
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> Use randomized posting times to appear more natural to search engines
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> Maintain a consistent posting schedule for better SEO results
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> For news sites, schedule 2-3 posts per day for optimal results
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> Enable auto internal linking to boost SEO performance
                        </li>
                    </ul>
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
    // Set default start date to current time
    const now = new Date();
    const nowFormatted = now.toISOString().slice(0, 16);
    $('#start-date').val(nowFormatted);
    
    // Toggle URL/RSS containers based on source selection
    $('#bulk-source').on('change', function() {
        const source = $(this).val();
        if (source === 'urls') {
            $('#urls-container').show();
            $('#rss-container').hide();
        } else {
            $('#urls-container').hide();
            $('#rss-container').show();
        }
    });
    
    // Select all RSS feeds checkbox
    $('#select-all-feeds').on('change', function() {
        $('.rss-feed-checkbox').prop('checked', $(this).is(':checked'));
    });
    
    // Load RSS feeds
    function loadRssFeeds() {
        // Normally this would be an AJAX call to get the feeds from the server
        // For now, we'll use the feeds from the aiNewsScraperFeeds variable defined in the main page
        if (typeof aiNewsScraperFeeds !== 'undefined') {
            const feeds = aiNewsScraperFeeds;
            let feedsHtml = '';
            
            if (feeds.length === 0) {
                feedsHtml = '<div class="alert alert-info">No RSS feeds configured. Add feeds in the RSS Settings page.</div>';
            } else {
                feeds.forEach(function(feed, index) {
                    feedsHtml += `
                        <div class="form-check">
                            <input class="form-check-input rss-feed-checkbox" type="checkbox" value="${index}" id="feed-${index}" name="feeds[]" checked>
                            <label class="form-check-label" for="feed-${index}">
                                ${feed.name || feed.url}
                            </label>
                        </div>
                    `;
                });
            }
            
            $('#rss-feeds-checkboxes').html(feedsHtml);
            
            // Add the select all checkbox back
            $('#rss-feeds-checkboxes').prepend(`
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="select-all-feeds" checked>
                    <label class="form-check-label" for="select-all-feeds">
                        <strong>Select All Feeds</strong>
                    </label>
                </div>
                <hr>
            `);
            
            // Reattach the select all event
            $('#select-all-feeds').on('change', function() {
                $('.rss-feed-checkbox').prop('checked', $(this).is(':checked'));
            });
        } else {
            $('#rss-feeds-checkboxes').html('<div class="alert alert-danger">Error loading RSS feeds.</div>');
        }
    }
    
    // Load RSS feeds when the page loads
    loadRssFeeds();
    
    // Handle bulk schedule form submission
    $('#bulk-schedule-form').on('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        const source = $('#bulk-source').val();
        if (source === 'urls' && !$('#url-list').val()) {
            alert('Please enter at least one URL.');
            return;
        } else if (source === 'rss' && $('.rss-feed-checkbox:checked').length === 0) {
            alert('Please select at least one RSS feed.');
            return;
        }
        
        if ($('.category-checkbox:checked').length === 0) {
            alert('Please select at least one category.');
            return;
        }
        
        if (!$('#start-date').val()) {
            alert('Please select a start date/time.');
            return;
        }
        
        // Show loading state
        $('#create-schedule-button').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating Schedule...');
        
        // Collect form data
        const formData = $(this).serialize();
        
        // AJAX request to create bulk schedule
        $.ajax({
            url: aiNewsScraperParams.ajax_url,
            type: 'POST',
            data: {
                action: 'create_bulk_schedule',
                nonce: aiNewsScraperParams.nonce,
                form_data: formData
            },
            success: function(response) {
                $('#create-schedule-button').prop('disabled', false).html('Create Bulk Schedule');
                
                if (response.success) {
                    // Show success message
                    $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                      response.data.message +
                      '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                      '</div>').insertBefore('#bulk-schedule-form');
                    
                    // Refresh scheduled posts table
                    location.reload();
                } else {
                    // Show error message
                    $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                      response.data.message +
                      '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                      '</div>').insertBefore('#bulk-schedule-form');
                }
            },
            error: function() {
                $('#create-schedule-button').prop('disabled', false).html('Create Bulk Schedule');
                
                // Show error message
                $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                  'An error occurred while creating the schedule. Please try again.' +
                  '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                  '</div>').insertBefore('#bulk-schedule-form');
            }
        });
    });
    
    // Handle delete scheduled post
    $('.delete-scheduled-post').on('click', function() {
        if (confirm('Are you sure you want to delete this scheduled post?')) {
            const postId = $(this).data('post-id');
            
            // AJAX request to delete scheduled post
            $.ajax({
                url: aiNewsScraperParams.ajax_url,
                type: 'POST',
                data: {
                    action: 'delete_scheduled_post',
                    nonce: aiNewsScraperParams.nonce,
                    post_id: postId
                },
                success: function(response) {
                    if (response.success) {
                        // Remove the row from the table
                        $(`button[data-post-id="${postId}"]`).closest('tr').remove();
                        
                        // If no more rows, add the empty message
                        if ($('#scheduled-posts-table tbody tr').length === 0) {
                            $('#scheduled-posts-table tbody').html('<tr><td colspan="4" class="text-center">No scheduled posts found.</td></tr>');
                        }
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred while deleting the post. Please try again.');
                }
            });
        }
    });
});
</script>
