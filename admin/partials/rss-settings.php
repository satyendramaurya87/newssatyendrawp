<?php
/**
 * Provide a admin area view for the RSS settings
 *
 * @link       https://satyendramaurya.site
 * @since      1.0.0
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/admin/partials
 */

// Get current options
$options = get_option('ai_news_scraper_options', array());
$rss_settings = isset($options['rss_settings']) ? $options['rss_settings'] : array();

// Get settings values or defaults
$rewrite_enabled = isset($rss_settings['rewrite_enabled']) ? $rss_settings['rewrite_enabled'] : true;
$keyword_filter = isset($rss_settings['keyword_filter']) ? $rss_settings['keyword_filter'] : '';
$feeds = isset($rss_settings['feeds']) ? $rss_settings['feeds'] : array();
?>

<div class="wrap ai-news-scraper-container">
    <!-- Alerts container for displaying messages -->
    <div id="alerts-container"></div>

    <div class="ai-news-scraper-header">
        <h1><i class="fas fa-rss"></i> RSS Feed Settings</h1>
        <p>Configure RSS feeds for automatic content scraping and rewriting</p>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Add New RSS Feed</h5>
                </div>
                <div class="card-body">
                    <form id="add-rss-feed-form" class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="rss-feed-url" class="form-label">RSS Feed URL</label>
                                    <input type="url" class="form-control" id="rss-feed-url" name="feed_url" placeholder="https://example.com/feed.xml" required>
                                    <div class="form-text">Enter the full URL of the RSS feed</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="rss-feed-name" class="form-label">Feed Name (Optional)</label>
                                    <input type="text" class="form-control" id="rss-feed-name" name="feed_name" placeholder="Tech News Feed">
                                    <div class="form-text">A descriptive name to identify this feed</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="rss-keywords" class="form-label">Keywords Filter (Optional)</label>
                            <input type="text" class="form-control" id="rss-keywords" name="keywords" placeholder="AI, machine learning, blockchain">
                            <div class="form-text">Only fetch articles containing these keywords (comma separated)</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fetch-limit" class="form-label">Fetch Limit</label>
                                    <select class="form-select" id="fetch-limit" name="fetch_limit">
                                        <option value="5">5 articles</option>
                                        <option value="10" selected>10 articles</option>
                                        <option value="20">20 articles</option>
                                        <option value="50">50 articles</option>
                                        <option value="100">100 articles</option>
                                    </select>
                                    <div class="form-text">Maximum number of articles to fetch per run</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fetch-frequency" class="form-label">Fetch Frequency</label>
                                    <select class="form-select" id="fetch-frequency" name="fetch_frequency">
                                        <option value="hourly">Hourly</option>
                                        <option value="twicedaily" selected>Twice Daily</option>
                                        <option value="daily">Daily</option>
                                    </select>
                                    <div class="form-text">How often to check for new content</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="add-feed-button">Add RSS Feed</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Manage RSS Feeds</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="rss-feeds-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>URL</th>
                                    <th>Keywords</th>
                                    <th>Fetch Limit</th>
                                    <th>Frequency</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($feeds)) : ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No RSS feeds configured yet.</td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($feeds as $index => $feed) : ?>
                                        <tr>
                                            <td><?php echo esc_html($index + 1); ?></td>
                                            <td><?php echo esc_html($feed['name'] ?? 'Unnamed Feed'); ?></td>
                                            <td>
                                                <a href="<?php echo esc_url($feed['url']); ?>" target="_blank">
                                                    <?php echo esc_html($feed['url']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo esc_html($feed['keywords'] ?? '-'); ?></td>
                                            <td><?php echo esc_html($feed['fetch_limit'] ?? '10'); ?></td>
                                            <td><?php echo esc_html($feed['fetch_frequency'] ?? 'twicedaily'); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info edit-feed" data-feed-index="<?php echo esc_attr($index); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger delete-feed" data-feed-index="<?php echo esc_attr($index); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-success test-feed" data-feed-url="<?php echo esc_attr($feed['url']); ?>">
                                                    <i class="fas fa-vial"></i>
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
            
            <div class="card mt-4" id="test-feed-results" style="display: none;">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Feed Test Results</h5>
                </div>
                <div class="card-body">
                    <div id="feed-test-content"></div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">RSS Settings</h5>
                </div>
                <div class="card-body">
                    <form id="rss-settings-form" class="settings-form">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="rewrite-enabled" name="rewrite_enabled" <?php checked($rewrite_enabled, true); ?>>
                                <label class="form-check-label" for="rewrite-enabled">AI Rewriting Enabled</label>
                                <div class="form-text">Automatically rewrite articles fetched from RSS feeds.</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="keyword-filter" class="form-label">Global Keyword Filter</label>
                            <input type="text" class="form-control" id="keyword-filter" name="keyword_filter" value="<?php echo esc_attr($keyword_filter); ?>" placeholder="AI, news, technology">
                            <div class="form-text">Only fetch articles containing these keywords (comma separated). Applied to all feeds.</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="auto-categorize" name="auto_categorize" checked>
                                <label class="form-check-label" for="auto-categorize">Auto-Categorize Articles</label>
                                <div class="form-text">Automatically assign categories based on content.</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="auto-tag" name="auto_tag" checked>
                                <label class="form-check-label" for="auto-tag">Auto-Generate Tags</label>
                                <div class="form-text">Automatically generate tags from content.</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="fetch-images" name="fetch_images" checked>
                                <label class="form-check-label" for="fetch-images">Fetch Featured Images</label>
                                <div class="form-text">Fetch and use featured images from the RSS feed.</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="link-to-source" name="link_to_source" checked>
                                <label class="form-check-label" for="link-to-source">Add Source Link</label>
                                <div class="form-text">Add a link to the original article at the end of the post.</div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">RSS Feed Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Total Feeds
                            <span class="badge bg-primary rounded-pill"><?php echo count($feeds); ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Articles Fetched Today
                            <span class="badge bg-success rounded-pill" id="articles-today">0</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Total Articles Fetched
                            <span class="badge bg-info rounded-pill" id="total-articles">0</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Last Fetch
                            <span class="badge bg-secondary" id="last-fetch">Never</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">RSS Feed Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> Add multiple feeds from different sources for diverse content
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> Use specific keywords to filter only relevant content
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> Set appropriate fetch frequency to avoid duplicate content
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> Enable AI rewriting to create unique content and avoid plagiarism
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> Test feeds before adding them to ensure they provide high-quality content
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

<!-- Edit Feed Modal -->
<div class="modal fade" id="edit-feed-modal" tabindex="-1" aria-labelledby="edit-feed-modal-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit-feed-modal-label">Edit RSS Feed</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-feed-form">
                    <input type="hidden" id="edit-feed-index" name="feed_index">
                    
                    <div class="mb-3">
                        <label for="edit-feed-name" class="form-label">Feed Name</label>
                        <input type="text" class="form-control" id="edit-feed-name" name="feed_name">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-feed-url" class="form-label">RSS Feed URL</label>
                        <input type="url" class="form-control" id="edit-feed-url" name="feed_url" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-feed-keywords" class="form-label">Keywords Filter</label>
                        <input type="text" class="form-control" id="edit-feed-keywords" name="keywords">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit-fetch-limit" class="form-label">Fetch Limit</label>
                                <select class="form-select" id="edit-fetch-limit" name="fetch_limit">
                                    <option value="5">5 articles</option>
                                    <option value="10">10 articles</option>
                                    <option value="20">20 articles</option>
                                    <option value="50">50 articles</option>
                                    <option value="100">100 articles</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit-fetch-frequency" class="form-label">Fetch Frequency</label>
                                <select class="form-select" id="edit-fetch-frequency" name="fetch_frequency">
                                    <option value="hourly">Hourly</option>
                                    <option value="twicedaily">Twice Daily</option>
                                    <option value="daily">Daily</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-feed-changes">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Load RSS feed statistics
    function loadRssStats() {
        $.ajax({
            url: aiNewsScraperParams.ajax_url,
            type: 'POST',
            data: {
                action: 'get_rss_stats',
                nonce: aiNewsScraperParams.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#articles-today').text(response.data.today_count);
                    $('#total-articles').text(response.data.total_count);
                    $('#last-fetch').text(response.data.last_fetch);
                }
            }
        });
    }
    
    // Load stats on page load
    loadRssStats();
    
    // Handle RSS feed form submission
    $('#add-rss-feed-form').on('submit', function(e) {
        e.preventDefault();
        
        const feedUrl = $('#rss-feed-url').val();
        const feedName = $('#rss-feed-name').val();
        const keywords = $('#rss-keywords').val();
        const fetchLimit = $('#fetch-limit').val();
        const fetchFrequency = $('#fetch-frequency').val();
        
        if (!feedUrl) {
            alert('Please enter an RSS feed URL.');
            return;
        }
        
        // Show loading state
        $('#add-feed-button').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...');
        
        // AJAX request to add RSS feed
        $.ajax({
            url: aiNewsScraperParams.ajax_url,
            type: 'POST',
            data: {
                action: 'add_rss_feed',
                nonce: aiNewsScraperParams.nonce,
                feed_url: feedUrl,
                feed_name: feedName,
                keywords: keywords,
                fetch_limit: fetchLimit,
                fetch_frequency: fetchFrequency
            },
            success: function(response) {
                $('#add-feed-button').prop('disabled', false).html('Add RSS Feed');
                
                if (response.success) {
                    // Show success alert
                    $('#alerts-container').html(`
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${response.data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                    
                    // Reset form
                    $('#add-rss-feed-form')[0].reset();
                    
                    // Reload page to show updated feed list
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    // Show error alert
                    $('#alerts-container').html(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${response.data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                }
            },
            error: function() {
                $('#add-feed-button').prop('disabled', false).html('Add RSS Feed');
                
                // Show error alert
                $('#alerts-container').html(`
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        An error occurred while adding the feed. Please try again.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);
            }
        });
    });
    
    // Handle edit feed button click
    $('.edit-feed').on('click', function() {
        const feedIndex = $(this).data('feed-index');
        
        // Get feed data from the table row
        const $row = $(this).closest('tr');
        const feedName = $row.find('td:eq(1)').text();
        const feedUrl = $row.find('td:eq(2) a').text();
        const keywords = $row.find('td:eq(3)').text() === '-' ? '' : $row.find('td:eq(3)').text();
        const fetchLimit = $row.find('td:eq(4)').text();
        const fetchFrequency = $row.find('td:eq(5)').text();
        
        // Populate the edit modal
        $('#edit-feed-index').val(feedIndex);
        $('#edit-feed-name').val(feedName);
        $('#edit-feed-url').val(feedUrl);
        $('#edit-feed-keywords').val(keywords);
        $('#edit-fetch-limit').val(fetchLimit);
        $('#edit-fetch-frequency').val(fetchFrequency);
        
        // Show the modal
        const editModal = new bootstrap.Modal(document.getElementById('edit-feed-modal'));
        editModal.show();
    });
    
    // Handle save feed changes button click
    $('#save-feed-changes').on('click', function() {
        const feedIndex = $('#edit-feed-index').val();
        const feedName = $('#edit-feed-name').val();
        const feedUrl = $('#edit-feed-url').val();
        const keywords = $('#edit-feed-keywords').val();
        const fetchLimit = $('#edit-fetch-limit').val();
        const fetchFrequency = $('#edit-fetch-frequency').val();
        
        if (!feedUrl) {
            alert('Please enter an RSS feed URL.');
            return;
        }
        
        // Show loading state
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
        
        // AJAX request to update RSS feed
        $.ajax({
            url: aiNewsScraperParams.ajax_url,
            type: 'POST',
            data: {
                action: 'update_rss_feed',
                nonce: aiNewsScraperParams.nonce,
                feed_index: feedIndex,
                feed_name: feedName,
                feed_url: feedUrl,
                keywords: keywords,
                fetch_limit: fetchLimit,
                fetch_frequency: fetchFrequency
            },
            success: function(response) {
                $('#save-feed-changes').prop('disabled', false).html('Save Changes');
                
                if (response.success) {
                    // Close the modal
                    bootstrap.Modal.getInstance(document.getElementById('edit-feed-modal')).hide();
                    
                    // Show success alert
                    $('#alerts-container').html(`
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${response.data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                    
                    // Reload page to show updated feed
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    // Show error alert inside modal
                    $('#edit-feed-form').before(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${response.data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                }
            },
            error: function() {
                $('#save-feed-changes').prop('disabled', false).html('Save Changes');
                
                // Show error alert inside modal
                $('#edit-feed-form').before(`
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        An error occurred while updating the feed. Please try again.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);
            }
        });
    });
    
    // Handle delete feed button click
    $('.delete-feed').on('click', function() {
        if (confirm('Are you sure you want to delete this RSS feed?')) {
            const feedIndex = $(this).data('feed-index');
            
            // AJAX request to delete RSS feed
            $.ajax({
                url: aiNewsScraperParams.ajax_url,
                type: 'POST',
                data: {
                    action: 'delete_rss_feed',
                    nonce: aiNewsScraperParams.nonce,
                    feed_index: feedIndex
                },
                success: function(response) {
                    if (response.success) {
                        // Show success alert
                        $('#alerts-container').html(`
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                ${response.data.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `);
                        
                        // Reload page to show updated feed list
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        // Show error alert
                        $('#alerts-container').html(`
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                ${response.data.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `);
                    }
                },
                error: function() {
                    // Show error alert
                    $('#alerts-container').html(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            An error occurred while deleting the feed. Please try again.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                }
            });
        }
    });
    
    // Handle test feed button click
    $('.test-feed').on('click', function() {
        const feedUrl = $(this).data('feed-url');
        
        // Show loading state
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        
        // Show the test results card with loading indicator
        $('#test-feed-results').show();
        $('#feed-test-content').html(`
            <div class="d-flex justify-content-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <p class="text-center mt-2">Testing feed, please wait...</p>
        `);
        
        // Scroll to the test results
        $('html, body').animate({
            scrollTop: $('#test-feed-results').offset().top - 100
        }, 500);
        
        // AJAX request to test RSS feed
        $.ajax({
            url: aiNewsScraperParams.ajax_url,
            type: 'POST',
            data: {
                action: 'test_rss_feed',
                nonce: aiNewsScraperParams.nonce,
                feed_url: feedUrl
            },
            success: function(response) {
                $('.test-feed').prop('disabled', false).html('<i class="fas fa-vial"></i>');
                
                if (response.success) {
                    const items = response.data.items;
                    
                    if (items.length === 0) {
                        $('#feed-test-content').html(`
                            <div class="alert alert-warning">
                                Feed parsed successfully, but no items were found in the feed.
                            </div>
                        `);
                    } else {
                        // Display feed items
                        let itemsHtml = `
                            <div class="alert alert-success">
                                Feed parsed successfully! Found ${items.length} items.
                            </div>
                            <div class="list-group">
                        `;
                        
                        items.forEach(function(item, index) {
                            if (index < 5) { // Show only first 5 items
                                itemsHtml += `
                                    <div class="list-group-item">
                                        <h5><a href="${item.link}" target="_blank">${item.title}</a></h5>
                                        <p class="text-muted">${item.date ? 'Published: ' + item.date : ''}</p>
                                        <p>${item.description ? item.description.substring(0, 200) + '...' : 'No description available'}</p>
                                    </div>
                                `;
                            }
                        });
                        
                        itemsHtml += `</div>`;
                        
                        if (items.length > 5) {
                            itemsHtml += `<div class="mt-2 text-center text-muted">Showing 5 of ${items.length} items</div>`;
                        }
                        
                        $('#feed-test-content').html(itemsHtml);
                    }
                } else {
                    // Show error message
                    $('#feed-test-content').html(`
                        <div class="alert alert-danger">
                            Error: ${response.data.message}
                        </div>
                    `);
                }
            },
            error: function() {
                $('.test-feed').prop('disabled', false).html('<i class="fas fa-vial"></i>');
                
                // Show error message
                $('#feed-test-content').html(`
                    <div class="alert alert-danger">
                        An error occurred while testing the feed. Please try again.
                    </div>
                `);
            }
        });
    });
});
</script>
