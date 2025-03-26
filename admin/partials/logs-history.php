<?php
/**
 * Provide a admin area view for the logs and history
 *
 * @link       https://satyendramaurya.site
 * @since      1.0.0
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/admin/partials
 */

// Get all logs from the database
global $wpdb;
$table_name = $wpdb->prefix . 'ai_news_scraper_logs';
$per_page = 10;
$current_page = isset($_GET['log_page']) ? max(1, intval($_GET['log_page'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Get total count
$total_query = "SELECT COUNT(*) FROM $table_name";
$total_logs = $wpdb->get_var($total_query);
$total_pages = ceil($total_logs / $per_page);

// Get logs for the current page
$logs_query = $wpdb->prepare(
    "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
    $per_page,
    $offset
);
$logs = $wpdb->get_results($logs_query);

// Get statistics
$stats = array(
    'total' => $total_logs,
    'successful' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'success'"),
    'failed' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'error'"),
    'scrape' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE action = 'scrape'"),
    'generate' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE action = 'generate'"),
    'publish' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE action = 'publish' OR action = 'schedule'"),
    'rss' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE action = 'rss'"),
);

// Get the date range
$date_range = $wpdb->get_row("SELECT MIN(created_at) as oldest, MAX(created_at) as newest FROM $table_name");
?>

<div class="wrap ai-news-scraper-container">
    <!-- Alerts container for displaying messages -->
    <div id="alerts-container"></div>

    <div class="ai-news-scraper-header">
        <h1><i class="fas fa-history"></i> Logs & History</h1>
        <p>View activity logs and history of the AI News Scraper & Auto Blogger Pro plugin</p>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Activity Logs</h5>
                </div>
                <div class="card-body">
                    <!-- Filter controls -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <select class="form-select" id="action-filter">
                                <option value="">Filter by Action</option>
                                <option value="scrape">Scraping</option>
                                <option value="generate">Content Generation</option>
                                <option value="publish">Publishing</option>
                                <option value="schedule">Scheduling</option>
                                <option value="rss">RSS Processing</option>
                                <option value="settings">Settings</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="status-filter">
                                <option value="">Filter by Status</option>
                                <option value="success">Success</option>
                                <option value="error">Error</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" id="log-search" placeholder="Search logs...">
                                <button class="btn btn-outline-secondary" type="button" id="search-logs-button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Logs table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="logs-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Action</th>
                                    <th>Source URL</th>
                                    <th>Post ID</th>
                                    <th>Status</th>
                                    <th>Date/Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)) : ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No logs found.</td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($logs as $log) : ?>
                                        <tr class="log-row" data-log-id="<?php echo esc_attr($log->id); ?>">
                                            <td><?php echo esc_html($log->id); ?></td>
                                            <td><?php echo esc_html($log->action); ?></td>
                                            <td>
                                                <?php if (!empty($log->source_url)) : ?>
                                                    <a href="<?php echo esc_url($log->source_url); ?>" target="_blank" title="<?php echo esc_attr($log->source_url); ?>">
                                                        <?php echo esc_html(substr($log->source_url, 0, 30) . (strlen($log->source_url) > 30 ? '...' : '')); ?>
                                                    </a>
                                                <?php else : ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($log->post_id)) : ?>
                                                    <a href="<?php echo esc_url(get_edit_post_link($log->post_id)); ?>" target="_blank">
                                                        <?php echo esc_html($log->post_id); ?>
                                                    </a>
                                                <?php else : ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $log->status === 'success' ? 'success' : 'danger'; ?>">
                                                    <?php echo esc_html($log->status); ?>
                                                </span>
                                            </td>
                                            <td><?php echo esc_html(date('Y-m-d H:i:s', strtotime($log->created_at))); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1) : ?>
                        <nav aria-label="Logs pagination">
                            <ul class="pagination justify-content-center" id="logs-pagination">
                                <?php if ($current_page > 1) : ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo esc_url(add_query_arg('log_page', $current_page - 1)); ?>">Previous</a>
                                    </li>
                                <?php else : ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">Previous</span>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                    <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo esc_url(add_query_arg('log_page', $i)); ?>">
                                            <?php echo esc_html($i); ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($current_page < $total_pages) : ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo esc_url(add_query_arg('log_page', $current_page + 1)); ?>">Next</a>
                                    </li>
                                <?php else : ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">Next</span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Log Details</h5>
                </div>
                <div class="card-body">
                    <div id="log-details-container">
                        <p class="text-center text-muted">Click on a log entry to view details</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Activity Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="list-group mb-3">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Total Activities
                            <span class="badge bg-primary rounded-pill"><?php echo esc_html($stats['total']); ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Successful Operations
                            <span class="badge bg-success rounded-pill"><?php echo esc_html($stats['successful']); ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Failed Operations
                            <span class="badge bg-danger rounded-pill"><?php echo esc_html($stats['failed']); ?></span>
                        </div>
                    </div>

                    <div class="list-group">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Articles Scraped
                            <span class="badge bg-info rounded-pill"><?php echo esc_html($stats['scrape']); ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Content Generated
                            <span class="badge bg-info rounded-pill"><?php echo esc_html($stats['generate']); ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Posts Published/Scheduled
                            <span class="badge bg-info rounded-pill"><?php echo esc_html($stats['publish']); ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            RSS Articles Processed
                            <span class="badge bg-info rounded-pill"><?php echo esc_html($stats['rss']); ?></span>
                        </div>
                    </div>

                    <?php if (!empty($date_range)) : ?>
                        <div class="text-center mt-3">
                            <p class="text-muted">Log data from <?php echo esc_html(date('M j, Y', strtotime($date_range->oldest))); ?> to <?php echo esc_html(date('M j, Y', strtotime($date_range->newest))); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Export & Maintenance</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" id="export-logs-button">
                            <i class="fas fa-file-export"></i> Export Logs to CSV
                        </button>
                        
                        <button type="button" class="btn btn-outline-danger" id="clear-logs-button">
                            <i class="fas fa-trash-alt"></i> Clear Logs (Keep Last 100)
                        </button>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Troubleshooting Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <i class="fas fa-exclamation-circle text-danger"></i> <strong>Scraping Failed:</strong> Check if the website blocks scrapers or requires authentication.
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-exclamation-circle text-danger"></i> <strong>AI Generation Failed:</strong> Verify your API keys and check if you have sufficient credits.
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-exclamation-circle text-danger"></i> <strong>RSS Feed Issues:</strong> Ensure the RSS feed URL is valid and accessible.
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-exclamation-circle text-danger"></i> <strong>Scheduling Problems:</strong> Check if WP-Cron is working properly on your server.
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
    // Handle log row click to show details
    $('.log-row').on('click', function() {
        const logId = $(this).data('log-id');
        
        // Highlight selected row
        $('.log-row').removeClass('table-active');
        $(this).addClass('table-active');
        
        // Show loading state
        $('#log-details-container').html(`
            <div class="d-flex justify-content-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <p class="text-center mt-2">Loading log details...</p>
        `);
        
        // AJAX request to get log details
        $.ajax({
            url: aiNewsScraperParams.ajax_url,
            type: 'POST',
            data: {
                action: 'get_log_details',
                nonce: aiNewsScraperParams.nonce,
                log_id: logId
            },
            success: function(response) {
                if (response.success) {
                    const log = response.data.log;
                    
                    // Format log details
                    let detailsHtml = `
                        <div class="mb-3">
                            <h5>Log #${log.id} - ${log.action.charAt(0).toUpperCase() + log.action.slice(1)}</h5>
                            <span class="badge bg-${log.status === 'success' ? 'success' : 'danger'} mb-2">
                                ${log.status}
                            </span>
                            <p class="text-muted">
                                <small>${log.created_at}</small>
                            </p>
                        </div>
                    `;
                    
                    if (log.message) {
                        detailsHtml += `
                            <div class="mb-3">
                                <h6>Message:</h6>
                                <p>${log.message}</p>
                            </div>
                        `;
                    }
                    
                    if (log.source_url) {
                        detailsHtml += `
                            <div class="mb-3">
                                <h6>Source URL:</h6>
                                <a href="${log.source_url}" target="_blank">${log.source_url}</a>
                            </div>
                        `;
                    }
                    
                    if (log.post_id) {
                        detailsHtml += `
                            <div class="mb-3">
                                <h6>Related Post:</h6>
                                <a href="${log.post_edit_link}" target="_blank">${log.post_title}</a>
                            </div>
                        `;
                    }
                    
                    if (log.extra_data) {
                        detailsHtml += `
                            <div class="mb-3">
                                <h6>Additional Information:</h6>
                                <pre class="bg-light p-2 rounded">${log.extra_data}</pre>
                            </div>
                        `;
                    }
                    
                    // Set the log details
                    $('#log-details-container').html(detailsHtml);
                } else {
                    $('#log-details-container').html(`
                        <div class="alert alert-danger">
                            Error: ${response.data.message}
                        </div>
                    `);
                }
            },
            error: function() {
                $('#log-details-container').html(`
                    <div class="alert alert-danger">
                        An error occurred while loading log details. Please try again.
                    </div>
                `);
            }
        });
    });
    
    // Handle filter changes
    $('#action-filter, #status-filter').on('change', function() {
        filterLogs();
    });
    
    // Handle search button click
    $('#search-logs-button').on('click', function() {
        filterLogs();
    });
    
    // Handle search on Enter key
    $('#log-search').on('keyup', function(e) {
        if (e.key === 'Enter') {
            filterLogs();
        }
    });
    
    // Filter logs function
    function filterLogs() {
        const action = $('#action-filter').val();
        const status = $('#status-filter').val();
        const search = $('#log-search').val().toLowerCase();
        
        // Show loading state
        $('#logs-table tbody').html(`
            <tr>
                <td colspan="6" class="text-center">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    Filtering logs...
                </td>
            </tr>
        `);
        
        // AJAX request to get filtered logs
        $.ajax({
            url: aiNewsScraperParams.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_logs',
                nonce: aiNewsScraperParams.nonce,
                log_action: action,
                log_status: status,
                log_search: search
            },
            success: function(response) {
                if (response.success) {
                    const logs = response.data.logs;
                    
                    if (logs.length === 0) {
                        $('#logs-table tbody').html(`
                            <tr>
                                <td colspan="6" class="text-center">No logs found matching your filters.</td>
                            </tr>
                        `);
                    } else {
                        let tableHtml = '';
                        
                        logs.forEach(function(log) {
                            const sourceUrl = log.source_url 
                                ? `<a href="${log.source_url}" target="_blank" title="${log.source_url}">${log.source_url.substring(0, 30)}${log.source_url.length > 30 ? '...' : ''}</a>` 
                                : '-';
                                
                            const postId = log.post_id 
                                ? `<a href="${log.post_edit_link}" target="_blank">${log.post_id}</a>` 
                                : '-';
                                
                            tableHtml += `
                                <tr class="log-row" data-log-id="${log.id}">
                                    <td>${log.id}</td>
                                    <td>${log.action}</td>
                                    <td>${sourceUrl}</td>
                                    <td>${postId}</td>
                                    <td>
                                        <span class="badge bg-${log.status === 'success' ? 'success' : 'danger'}">
                                            ${log.status}
                                        </span>
                                    </td>
                                    <td>${log.created_at}</td>
                                </tr>
                            `;
                        });
                        
                        $('#logs-table tbody').html(tableHtml);
                        
                        // Reattach click event to new rows
                        $('.log-row').on('click', function() {
                            const logId = $(this).data('log-id');
                            
                            // Highlight selected row
                            $('.log-row').removeClass('table-active');
                            $(this).addClass('table-active');
                            
                            // Get log details (reuse existing AJAX call)
                            // ...
                        });
                    }
                    
                    // Update pagination
                    $('#logs-pagination').html(response.data.pagination);
                } else {
                    $('#logs-table tbody').html(`
                        <tr>
                            <td colspan="6" class="text-center text-danger">
                                Error: ${response.data.message}
                            </td>
                        </tr>
                    `);
                }
            },
            error: function() {
                $('#logs-table tbody').html(`
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            An error occurred while filtering logs. Please try again.
                        </td>
                    </tr>
                `);
            }
        });
    }
    
    // Handle export logs button click
    $('#export-logs-button').on('click', function() {
        // Show loading state
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Exporting...');
        
        // AJAX request to export logs
        $.ajax({
            url: aiNewsScraperParams.ajax_url,
            type: 'POST',
            data: {
                action: 'export_logs',
                nonce: aiNewsScraperParams.nonce
            },
            success: function(response) {
                $('#export-logs-button').prop('disabled', false).html('<i class="fas fa-file-export"></i> Export Logs to CSV');
                
                if (response.success) {
                    // Create a download link
                    const a = document.createElement('a');
                    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(response.data.csv);
                    a.download = 'ai-news-scraper-logs-' + new Date().toISOString().split('T')[0] + '.csv';
                    a.style.display = 'none';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function() {
                $('#export-logs-button').prop('disabled', false).html('<i class="fas fa-file-export"></i> Export Logs to CSV');
                alert('An error occurred while exporting logs. Please try again.');
            }
        });
    });
    
    // Handle clear logs button click
    $('#clear-logs-button').on('click', function() {
        if (confirm('Are you sure you want to clear logs? This will keep only the most recent 100 entries.')) {
            // Show loading state
            $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Clearing...');
            
            // AJAX request to clear logs
            $.ajax({
                url: aiNewsScraperParams.ajax_url,
                type: 'POST',
                data: {
                    action: 'clear_logs',
                    nonce: aiNewsScraperParams.nonce
                },
                success: function(response) {
                    $('#clear-logs-button').prop('disabled', false).html('<i class="fas fa-trash-alt"></i> Clear Logs (Keep Last 100)');
                    
                    if (response.success) {
                        alert('Logs cleared successfully. Reloading page...');
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    $('#clear-logs-button').prop('disabled', false).html('<i class="fas fa-trash-alt"></i> Clear Logs (Keep Last 100)');
                    alert('An error occurred while clearing logs. Please try again.');
                }
            });
        }
    });
});
</script>
