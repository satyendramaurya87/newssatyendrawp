(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize Bootstrap tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize Bootstrap tabs
        const triggerTabList = [].slice.call(document.querySelectorAll('#main-tabs a'));
        triggerTabList.forEach(function(triggerEl) {
            const tabTrigger = new bootstrap.Tab(triggerEl);
            triggerEl.addEventListener('click', function(event) {
                event.preventDefault();
                tabTrigger.show();
            });
        });

        // Handle article scraping
        $('#scrape-article-form').on('submit', function(e) {
            e.preventDefault();
            const url = $('#article-url').val();
            
            if (!url) {
                showAlert('error', 'Please enter a URL to scrape.');
                return;
            }
            
            // Show loading state
            $('#scrape-button').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Scraping...');
            $('#scrape-result').html('');
            
            // AJAX request to scrape article
            $.ajax({
                url: aiNewsScraperParams.ajax_url,
                type: 'POST',
                data: {
                    action: 'scrape_article',
                    nonce: aiNewsScraperParams.nonce,
                    url: url
                },
                success: function(response) {
                    $('#scrape-button').prop('disabled', false).html('Scrape Article');
                    
                    if (response.success) {
                        const data = response.data;
                        
                        // Display the scraped content
                        let resultHtml = `
                            <div class="card mt-4">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">Article Scraped Successfully</h5>
                                </div>
                                <div class="card-body">
                                    <h3>${data.title}</h3>
                                    <p class="text-muted">Author: ${data.author || 'Unknown'} | Date: ${data.date || 'Unknown'}</p>
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-primary btn-sm edit-title">Edit Title</button>
                                        <button type="button" class="btn btn-info btn-sm save-title" style="display:none;">Save Title</button>
                                    </div>
                                    <div class="scraped-content mb-4">
                                        ${data.content}
                                    </div>
                                    
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h5 class="mb-0">AI Processing Options</h5>
                                        </div>
                                        <div class="card-body">
                                            <form id="ai-generation-form">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label for="ai-model" class="form-label">AI Model</label>
                                                            <select class="form-select" id="ai-model">
                                                                <option value="openai">OpenAI GPT</option>
                                                                <option value="gemini">Google Gemini</option>
                                                                <option value="claude">Anthropic Claude</option>
                                                                <option value="deepseek">DeepSeek AI</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label for="language" class="form-label">Language</label>
                                                            <select class="form-select" id="language">
                                                                <option value="english">English</option>
                                                                <option value="hindi">Hindi</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label for="tone" class="form-label">Writing Tone</label>
                                                            <select class="form-select" id="tone">
                                                                <option value="default">Default</option>
                                                                <option value="banarasi">Banarasi</option>
                                                                <option value="lucknow">Lucknow</option>
                                                                <option value="delhi">Delhi</option>
                                                                <option value="indore">Indore</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" id="original-content" value="${encodeURIComponent(data.content)}">
                                                <input type="hidden" id="original-title" value="${encodeURIComponent(data.title)}">
                                                <button type="submit" class="btn btn-primary" id="generate-ai-content">Generate AI Content</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        $('#scrape-result').html(resultHtml);
                        
                        // Enable title editing
                        $('.edit-title').on('click', function() {
                            const title = $('#scrape-result h3').text();
                            $('#scrape-result h3').html(`<input type="text" class="form-control" id="edit-title-input" value="${title}">`);
                            $(this).hide();
                            $('.save-title').show();
                        });
                        
                        $('.save-title').on('click', function() {
                            const newTitle = $('#edit-title-input').val();
                            $('#scrape-result h3').text(newTitle);
                            $(this).hide();
                            $('.edit-title').show();
                            $('#original-title').val(encodeURIComponent(newTitle));
                        });
                        
                        // Handle AI content generation
                        $('#ai-generation-form').on('submit', function(e) {
                            e.preventDefault();
                            
                            const aiModel = $('#ai-model').val();
                            const language = $('#language').val();
                            const tone = $('#tone').val();
                            const content = decodeURIComponent($('#original-content').val());
                            const title = decodeURIComponent($('#original-title').val());
                            
                            // Show loading state
                            $('#generate-ai-content').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...');
                            
                            // AJAX request to generate AI content
                            $.ajax({
                                url: aiNewsScraperParams.ajax_url,
                                type: 'POST',
                                data: {
                                    action: 'generate_content',
                                    nonce: aiNewsScraperParams.nonce,
                                    content: content,
                                    title: title,
                                    ai_model: aiModel,
                                    language: language,
                                    tone: tone
                                },
                                success: function(response) {
                                    $('#generate-ai-content').prop('disabled', false).html('Generate AI Content');
                                    
                                    if (response.success) {
                                        const data = response.data;
                                        
                                        // Display the generated content
                                        let aiResultHtml = `
                                            <div class="card mt-4">
                                                <div class="card-header bg-primary text-white">
                                                    <h5 class="mb-0">AI-Generated Content</h5>
                                                </div>
                                                <div class="card-body">
                                                    <h3>${data.title}</h3>
                                                    <div class="ai-content mb-4">
                                                        ${data.content}
                                                    </div>
                                                    
                                                    <div class="card mb-3">
                                                        <div class="card-header">
                                                            <h5 class="mb-0">Publishing Options</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <form id="publishing-form">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Categories</label>
                                                                            <div class="categories-container">
                                                                                ${generateCategoriesCheckboxes()}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label for="tags" class="form-label">Tags (comma separated)</label>
                                                                            <input type="text" class="form-control" id="tags" value="${data.suggested_tags || ''}">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label for="featured-image" class="form-label">Featured Image URL</label>
                                                                    <input type="text" class="form-control" id="featured-image" value="${data.featured_image || ''}">
                                                                </div>
                                                                
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <button type="button" class="btn btn-success" id="publish-now">Publish Now</button>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="input-group">
                                                                            <input type="datetime-local" class="form-control" id="schedule-date">
                                                                            <button type="button" class="btn btn-info" id="schedule-post">Schedule</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        `;
                                        
                                        $('#ai-result').html(aiResultHtml);
                                        
                                        // Set default schedule date to 1 hour from now
                                        const now = new Date();
                                        now.setHours(now.getHours() + 1);
                                        const scheduleDate = now.toISOString().slice(0, 16);
                                        $('#schedule-date').val(scheduleDate);
                                        
                                        // Handle publish now button
                                        $('#publish-now').on('click', function() {
                                            publishPost(false);
                                        });
                                        
                                        // Handle schedule button
                                        $('#schedule-post').on('click', function() {
                                            publishPost(true);
                                        });
                                    } else {
                                        showAlert('error', response.data.message);
                                    }
                                },
                                error: function() {
                                    $('#generate-ai-content').prop('disabled', false).html('Generate AI Content');
                                    showAlert('error', 'An error occurred while generating content.');
                                }
                            });
                        });
                    } else {
                        showAlert('error', response.data.message);
                    }
                },
                error: function() {
                    $('#scrape-button').prop('disabled', false).html('Scrape Article');
                    showAlert('error', 'An error occurred while scraping the article.');
                }
            });
        });

        // Handle RSS feed form submission
        $('#add-rss-feed-form').on('submit', function(e) {
            e.preventDefault();
            const feedUrl = $('#rss-feed-url').val();
            const feedName = $('#rss-feed-name').val();
            const keywords = $('#rss-keywords').val();
            
            if (!feedUrl) {
                showAlert('error', 'Please enter an RSS feed URL.');
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
                    keywords: keywords
                },
                success: function(response) {
                    $('#add-feed-button').prop('disabled', false).html('Add Feed');
                    
                    if (response.success) {
                        showAlert('success', response.data.message);
                        
                        // Clear form
                        $('#rss-feed-url').val('');
                        $('#rss-feed-name').val('');
                        $('#rss-keywords').val('');
                        
                        // Refresh RSS feeds list
                        loadRssFeeds();
                    } else {
                        showAlert('error', response.data.message);
                    }
                },
                error: function() {
                    $('#add-feed-button').prop('disabled', false).html('Add Feed');
                    showAlert('error', 'An error occurred while adding the RSS feed.');
                }
            });
        });

        // Handle settings form submissions
        $('.settings-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const formId = form.attr('id');
            const settingsType = formId.replace('-settings-form', '');
            const submitButton = form.find('button[type="submit"]');
            
            // Collect form data
            const formData = {};
            form.find('input, select, textarea').each(function() {
                const input = $(this);
                const name = input.attr('name');
                
                if (name) {
                    if (input.attr('type') === 'checkbox') {
                        formData[name] = input.is(':checked');
                    } else {
                        formData[name] = input.val();
                    }
                }
            });
            
            // Show loading state
            submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
            
            // AJAX request to save settings
            $.ajax({
                url: aiNewsScraperParams.ajax_url,
                type: 'POST',
                data: {
                    action: 'save_settings',
                    nonce: aiNewsScraperParams.nonce,
                    settings_type: settingsType,
                    settings: formData
                },
                success: function(response) {
                    submitButton.prop('disabled', false).html('Save Settings');
                    
                    if (response.success) {
                        showAlert('success', response.data.message);
                    } else {
                        showAlert('error', response.data.message);
                    }
                },
                error: function() {
                    submitButton.prop('disabled', false).html('Save Settings');
                    showAlert('error', 'An error occurred while saving settings.');
                }
            });
        });

        // Load logs for history page
        if ($('#logs-table').length) {
            loadLogs(1);
        }
        
        // Handle pagination for logs
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            loadLogs(page);
        });
        
        // Load RSS feeds for RSS settings page
        if ($('#rss-feeds-table').length) {
            loadRssFeeds();
        }
        
        // Visual selector initialization
        if ($('#visual-selector-container').length) {
            initVisualSelector();
        }
    });

    // Function to publish or schedule a post
    function publishPost(isScheduled) {
        const title = $('#ai-result h3').text();
        const content = $('#ai-result .ai-content').html();
        const categories = [];
        $('.category-checkbox:checked').each(function() {
            categories.push($(this).val());
        });
        const tags = $('#tags').val();
        const featuredImage = $('#featured-image').val();
        
        let scheduleDate = '';
        if (isScheduled) {
            scheduleDate = $('#schedule-date').val();
            if (!scheduleDate) {
                showAlert('error', 'Please select a schedule date.');
                return;
            }
        }
        
        const buttonId = isScheduled ? '#schedule-post' : '#publish-now';
        const buttonText = isScheduled ? 'Schedule' : 'Publish Now';
        const loadingText = isScheduled ? 'Scheduling...' : 'Publishing...';
        
        // Show loading state
        $(buttonId).prop('disabled', true).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ${loadingText}`);
        
        // AJAX request to publish or schedule post
        $.ajax({
            url: aiNewsScraperParams.ajax_url,
            type: 'POST',
            data: {
                action: isScheduled ? 'schedule_post' : 'publish_post',
                nonce: aiNewsScraperParams.nonce,
                title: title,
                content: content,
                categories: categories,
                tags: tags,
                featured_image: featuredImage,
                schedule_date: scheduleDate
            },
            success: function(response) {
                $(buttonId).prop('disabled', false).html(buttonText);
                
                if (response.success) {
                    showAlert('success', response.data.message);
                    
                    // Add view post link
                    $('#ai-result .card-body').append(`
                        <div class="mt-3 alert alert-success">
                            ${response.data.message}
                            <br>
                            <a href="${response.data.post_url}" target="_blank" class="btn btn-sm btn-outline-success mt-2">
                                <i class="fas fa-external-link-alt"></i> View Post
                            </a>
                        </div>
                    `);
                    
                    // Disable buttons to prevent duplicate submissions
                    $('#publish-now, #schedule-post').prop('disabled', true);
                } else {
                    showAlert('error', response.data.message);
                }
            },
            error: function() {
                $(buttonId).prop('disabled', false).html(buttonText);
                showAlert('error', `An error occurred while ${isScheduled ? 'scheduling' : 'publishing'} the post.`);
            }
        });
    }

    // Function to load logs with pagination
    function loadLogs(page) {
        const perPage = 10;
        
        // Show loading state
        $('#logs-table tbody').html('<tr><td colspan="6" class="text-center"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading logs...</td></tr>');
        $('#logs-pagination').html('');
        
        // AJAX request to get logs
        $.ajax({
            url: aiNewsScraperParams.ajax_url,
            type: 'POST',
            data: {
                action: 'get_logs',
                nonce: aiNewsScraperParams.nonce,
                page: page,
                per_page: perPage
            },
            success: function(response) {
                if (response.success) {
                    const logs = response.data.logs;
                    const total = response.data.total;
                    const pages = response.data.pages;
                    const currentPage = response.data.current_page;
                    
                    // Clear table
                    $('#logs-table tbody').html('');
                    
                    // Check if we have logs
                    if (logs.length === 0) {
                        $('#logs-table tbody').html('<tr><td colspan="6" class="text-center">No logs found.</td></tr>');
                    } else {
                        // Add logs to table
                        logs.forEach(function(log) {
                            const statusClass = log.status === 'success' ? 'success' : 'danger';
                            let postLink = '-';
                            if (log.post_id) {
                                postLink = `<a href="${window.location.origin}/wp-admin/post.php?post=${log.post_id}&action=edit" target="_blank">${log.post_id}</a>`;
                            }
                            
                            $('#logs-table tbody').append(`
                                <tr>
                                    <td>${log.id}</td>
                                    <td>${log.action}</td>
                                    <td>${log.source_url || '-'}</td>
                                    <td>${postLink}</td>
                                    <td><span class="badge bg-${statusClass}">${log.status}</span></td>
                                    <td>${log.created_at}</td>
                                </tr>
                            `);
                        });
                        
                        // Add pagination
                        let paginationHtml = '<ul class="pagination">';
                        
                        // Previous button
                        if (currentPage > 1) {
                            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a></li>`;
                        } else {
                            paginationHtml += '<li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>';
                        }
                        
                        // Page numbers
                        for (let i = 1; i <= pages; i++) {
                            if (i === currentPage) {
                                paginationHtml += `<li class="page-item active"><a class="page-link" href="#">${i}</a></li>`;
                            } else {
                                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                            }
                        }
                        
                        // Next button
                        if (currentPage < pages) {
                            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage + 1}">Next</a></li>`;
                        } else {
                            paginationHtml += '<li class="page-item disabled"><a class="page-link" href="#">Next</a></li>';
                        }
                        
                        paginationHtml += '</ul>';
                        $('#logs-pagination').html(paginationHtml);
                    }
                } else {
                    $('#logs-table tbody').html('<tr><td colspan="6" class="text-center text-danger">Error loading logs.</td></tr>');
                }
            },
            error: function() {
                $('#logs-table tbody').html('<tr><td colspan="6" class="text-center text-danger">An error occurred while loading logs.</td></tr>');
            }
        });
    }

    // Function to load RSS feeds
    function loadRssFeeds() {
        // We'll use the options directly from the WordPress database
        // This would typically be done through an AJAX call, but for simplicity
        // we'll rely on the PHP to output the feeds as a JavaScript variable
        if (typeof aiNewsScraperFeeds !== 'undefined') {
            const feeds = aiNewsScraperFeeds;
            
            // Clear table
            $('#rss-feeds-table tbody').html('');
            
            // Check if we have feeds
            if (feeds.length === 0) {
                $('#rss-feeds-table tbody').html('<tr><td colspan="4" class="text-center">No RSS feeds found.</td></tr>');
            } else {
                // Add feeds to table
                feeds.forEach(function(feed, index) {
                    $('#rss-feeds-table tbody').append(`
                        <tr>
                            <td>${index + 1}</td>
                            <td>${feed.name || 'Unnamed Feed'}</td>
                            <td><a href="${feed.url}" target="_blank">${feed.url}</a></td>
                            <td>${feed.keywords || '-'}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger delete-feed" data-feed-id="${index}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }
        } else {
            $('#rss-feeds-table tbody').html('<tr><td colspan="4" class="text-center text-danger">Error loading RSS feeds.</td></tr>');
        }
    }

    // Function to initialize the visual selector
    function initVisualSelector() {
        $('#visual-selector-url-form').on('submit', function(e) {
            e.preventDefault();
            const url = $('#visual-selector-url').val();
            
            if (!url) {
                showAlert('error', 'Please enter a URL.');
                return;
            }
            
            // Show loading state
            $('#load-url-button').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
            
            // Load the URL in an iframe
            $('#visual-selector-iframe').attr('src', url);
            
            // Wait for iframe to load
            $('#visual-selector-iframe').on('load', function() {
                $('#load-url-button').prop('disabled', false).html('Load URL');
                
                // Show selection tools
                $('#visual-selector-tools').removeClass('d-none');
                
                // Initialize the selector
                initializeSelector();
            });
        });
    }

    // Function to initialize the visual selector tools
    function initializeSelector() {
        // This would be a complex implementation with iframe interactions
        // For this example, we'll just stub it out
        console.log('Visual selector initialized');
    }

    // Function to generate categories checkboxes
    function generateCategoriesCheckboxes() {
        // In a real implementation, this would fetch categories from WordPress
        // For this example, we'll create some sample categories
        const categories = [
            { id: 1, name: 'News' },
            { id: 2, name: 'Technology' },
            { id: 3, name: 'Business' },
            { id: 4, name: 'Entertainment' },
            { id: 5, name: 'Sports' }
        ];
        
        let html = '';
        categories.forEach(function(category) {
            html += `
                <div class="form-check">
                    <input class="form-check-input category-checkbox" type="checkbox" value="${category.id}" id="category-${category.id}">
                    <label class="form-check-label" for="category-${category.id}">
                        ${category.name}
                    </label>
                </div>
            `;
        });
        
        return html;
    }

    // Function to show an alert
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Append alert to the alerts container
        $('#alerts-container').html(alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    }

})(jQuery);
