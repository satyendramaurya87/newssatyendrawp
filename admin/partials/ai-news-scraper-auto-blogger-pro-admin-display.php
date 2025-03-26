<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://satyendramaurya.site
 * @since      1.0.0
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/admin/partials
 */
?>

<div class="wrap ai-news-scraper-container">
    <!-- Alerts container for displaying messages -->
    <div id="alerts-container"></div>

    <div class="ai-news-scraper-header">
        <h1><i class="fas fa-robot"></i> AI News Scraper & Auto Blogger Pro</h1>
        <p>Automatically scrape, rewrite, and publish news articles with AI-powered content generation</p>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card feature-card">
                <div class="card-body">
                    <i class="fas fa-search"></i>
                    <h3>Visual Scraper</h3>
                    <p>Extract content using visual selectors</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card feature-card">
                <div class="card-body">
                    <i class="fas fa-brain"></i>
                    <h3>AI Rewriting</h3>
                    <p>Generate unique content with multiple AI models</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card feature-card">
                <div class="card-body">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>Auto Scheduling</h3>
                    <p>Schedule posts for automatic publishing</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card feature-card">
                <div class="card-body">
                    <i class="fas fa-rss"></i>
                    <h3>RSS Integration</h3>
                    <p>Automatically fetch content from RSS feeds</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main tabs navigation -->
    <ul class="nav nav-tabs mb-4" id="main-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="scrape-tab" data-bs-toggle="tab" href="#scrape" role="tab" aria-controls="scrape" aria-selected="true">
                <i class="fas fa-search"></i> Scrape Article
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="visual-tab" data-bs-toggle="tab" href="#visual" role="tab" aria-controls="visual" aria-selected="false">
                <i class="fas fa-eye"></i> Visual Selector
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="rss-tab" data-bs-toggle="tab" href="#rss" role="tab" aria-controls="rss" aria-selected="false">
                <i class="fas fa-rss"></i> RSS Feeds
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="bulk-tab" data-bs-toggle="tab" href="#bulk" role="tab" aria-controls="bulk" aria-selected="false">
                <i class="fas fa-tasks"></i> Bulk Processing
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="settings-tab" data-bs-toggle="tab" href="#settings" role="tab" aria-controls="settings" aria-selected="false">
                <i class="fas fa-cog"></i> Settings
            </a>
        </li>
    </ul>

    <!-- Tab content -->
    <div class="tab-content">
        <!-- Scrape Article Tab -->
        <div class="tab-pane fade show active" id="scrape" role="tabpanel" aria-labelledby="scrape-tab">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Scrape News Article</h5>
                </div>
                <div class="card-body">
                    <form id="scrape-article-form">
                        <div class="mb-3">
                            <label for="article-url" class="form-label">Article URL</label>
                            <div class="input-group">
                                <input type="url" class="form-control" id="article-url" placeholder="https://example.com/news-article" required>
                                <button type="submit" class="btn btn-primary" id="scrape-button">Scrape Article</button>
                            </div>
                            <div class="form-text">Enter the URL of the news article you want to scrape</div>
                        </div>
                    </form>
                    
                    <!-- Scrape result will be displayed here -->
                    <div id="scrape-result"></div>
                    
                    <!-- AI result will be displayed here -->
                    <div id="ai-result"></div>
                </div>
            </div>
        </div>

        <!-- Visual Selector Tab -->
        <div class="tab-pane fade" id="visual" role="tabpanel" aria-labelledby="visual-tab">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Visual Selector</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Use visual selector to point and click on elements you want to extract from a webpage.</p>
                    
                    <form id="visual-selector-url-form" class="mb-4">
                        <div class="mb-3">
                            <label for="visual-selector-url" class="form-label">Website URL</label>
                            <div class="input-group">
                                <input type="url" class="form-control" id="visual-selector-url" placeholder="https://example.com" required>
                                <button type="submit" class="btn btn-primary" id="load-url-button">Load URL</button>
                            </div>
                        </div>
                    </form>
                    
                    <div id="visual-selector-container">
                        <iframe id="visual-selector-iframe" src="about:blank"></iframe>
                        
                        <div id="visual-selector-tools" class="d-none mt-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Title Selector</label>
                                        <div class="input-group">
                                            <button class="btn btn-outline-primary" id="select-title-button">Select Title</button>
                                            <input type="text" class="form-control" id="title-selector" placeholder=".article-title">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Content Selector</label>
                                        <div class="input-group">
                                            <button class="btn btn-outline-primary" id="select-content-button">Select Content</button>
                                            <input type="text" class="form-control" id="content-selector" placeholder=".article-content">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Author Selector</label>
                                        <div class="input-group">
                                            <button class="btn btn-outline-primary" id="select-author-button">Select Author</button>
                                            <input type="text" class="form-control" id="author-selector" placeholder=".author-name">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-success" id="save-selectors-button">Save Selectors</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RSS Feeds Tab -->
        <div class="tab-pane fade" id="rss" role="tabpanel" aria-labelledby="rss-tab">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">RSS Feed Manager</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Add RSS feeds to automatically scrape and rewrite articles.</p>
                    
                    <form id="add-rss-feed-form" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="rss-feed-url" class="form-label">RSS Feed URL</label>
                                    <input type="url" class="form-control" id="rss-feed-url" placeholder="https://example.com/feed" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="rss-feed-name" class="form-label">Feed Name (Optional)</label>
                                    <input type="text" class="form-control" id="rss-feed-name" placeholder="Technology News">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="rss-keywords" class="form-label">Keywords Filter (Optional)</label>
                                    <input type="text" class="form-control" id="rss-keywords" placeholder="AI, machine learning">
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="add-feed-button">Add Feed</button>
                        </div>
                    </form>
                    
                    <h5 class="border-bottom pb-2 mb-3">Existing RSS Feeds</h5>
                    <div class="table-responsive">
                        <table class="table table-hover" id="rss-feeds-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>URL</th>
                                    <th>Keywords</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center">Loading RSS feeds...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Processing Tab -->
        <div class="tab-pane fade" id="bulk" role="tabpanel" aria-labelledby="bulk-tab">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Bulk Processing</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Set up bulk article processing and scheduling.</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">Bulk Article Queue</div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="bulk-count" class="form-label">Number of Articles to Process</label>
                                        <select class="form-select" id="bulk-count">
                                            <option value="10">10 Articles</option>
                                            <option value="20">20 Articles</option>
                                            <option value="30">30 Articles</option>
                                            <option value="50">50 Articles</option>
                                            <option value="100">100 Articles</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="bulk-source" class="form-label">Source</label>
                                        <select class="form-select" id="bulk-source">
                                            <option value="rss">RSS Feeds</option>
                                            <option value="urls">URL List</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3 d-none" id="bulk-urls-container">
                                        <label for="bulk-urls" class="form-label">URL List (One per line)</label>
                                        <textarea class="form-control" id="bulk-urls" rows="5"></textarea>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-primary" id="start-bulk-button">Start Bulk Processing</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">Scheduling Options</div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="enable-scheduling" checked>
                                            <label class="form-check-label" for="enable-scheduling">Enable Scheduling</label>
                                        </div>
                                    </div>
                                    
                                    <div id="scheduling-options">
                                        <div class="mb-3">
                                            <label for="schedule-interval" class="form-label">Post Interval</label>
                                            <select class="form-select" id="schedule-interval">
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
                                                <input class="form-check-input" type="checkbox" id="randomize-schedule" checked>
                                                <label class="form-check-label" for="randomize-schedule">Randomize Schedule (SEO-friendly)</label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="start-date" class="form-label">Start Date/Time</label>
                                            <input type="datetime-local" class="form-control" id="start-date">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Tab -->
        <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Plugin Settings</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills mb-3" id="settings-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="scraper-settings-tab" data-bs-toggle="pill" href="#scraper-settings" role="tab" aria-controls="scraper-settings" aria-selected="true">Scraper</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="ai-settings-tab" data-bs-toggle="pill" href="#ai-settings" role="tab" aria-controls="ai-settings" aria-selected="false">AI</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="post-settings-tab" data-bs-toggle="pill" href="#post-settings" role="tab" aria-controls="post-settings" aria-selected="false">Post</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="image-settings-tab" data-bs-toggle="pill" href="#image-settings" role="tab" aria-controls="image-settings" aria-selected="false">Images</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content">
                        <!-- Scraper Settings -->
                        <div class="tab-pane fade show active" id="scraper-settings" role="tabpanel" aria-labelledby="scraper-settings-tab">
                            <form id="scraper-settings-form" class="settings-form">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="visual-selector-enabled" name="visual_selector_enabled" checked>
                                        <label class="form-check-label" for="visual-selector-enabled">Enable Visual Selector</label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="auto-scraping-enabled" name="auto_scraping_enabled">
                                        <label class="form-check-label" for="auto-scraping-enabled">Enable Auto-Scraping for Future Articles</label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </form>
                        </div>
                        
                        <!-- AI Settings -->
                        <div class="tab-pane fade" id="ai-settings" role="tabpanel" aria-labelledby="ai-settings-tab">
                            <form id="ai-settings-form" class="settings-form">
                                <div class="mb-3">
                                    <label for="ai-model-setting" class="form-label">Default AI Model</label>
                                    <select class="form-select" id="ai-model-setting" name="ai_model">
                                        <option value="openai">OpenAI GPT</option>
                                        <option value="gemini">Google Gemini</option>
                                        <option value="claude">Anthropic Claude</option>
                                        <option value="deepseek">DeepSeek AI</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="language-setting" class="form-label">Default Language</label>
                                    <select class="form-select" id="language-setting" name="language">
                                        <option value="english">English</option>
                                        <option value="hindi">Hindi</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="tone-setting" class="form-label">Default Writing Tone</label>
                                    <select class="form-select" id="tone-setting" name="writing_tone">
                                        <option value="default">Default</option>
                                        <option value="banarasi">Banarasi</option>
                                        <option value="lucknow">Lucknow</option>
                                        <option value="delhi">Delhi</option>
                                        <option value="indore">Indore</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="social-media-embed" name="social_media_embed" checked>
                                        <label class="form-check-label" for="social-media-embed">Enable Social Media Embed Fetching</label>
                                    </div>
                                </div>
                                
                                <h4 class="mt-4 mb-3">API Keys</h4>
                                
                                <div class="mb-3">
                                    <label for="openai-api-key" class="form-label">OpenAI API Key</label>
                                    <div class="input-group api-key-field">
                                        <input type="password" class="form-control" id="openai-api-key" name="api_keys[openai]">
                                        <span class="input-group-text"><i class="fas fa-eye"></i></span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="gemini-api-key" class="form-label">Google Gemini API Key</label>
                                    <div class="input-group api-key-field">
                                        <input type="password" class="form-control" id="gemini-api-key" name="api_keys[gemini]">
                                        <span class="input-group-text"><i class="fas fa-eye"></i></span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="claude-api-key" class="form-label">Anthropic Claude API Key</label>
                                    <div class="input-group api-key-field">
                                        <input type="password" class="form-control" id="claude-api-key" name="api_keys[claude]">
                                        <span class="input-group-text"><i class="fas fa-eye"></i></span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="deepseek-api-key" class="form-label">DeepSeek AI API Key</label>
                                    <div class="input-group api-key-field">
                                        <input type="password" class="form-control" id="deepseek-api-key" name="api_keys[deepseek]">
                                        <span class="input-group-text"><i class="fas fa-eye"></i></span>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </form>
                        </div>
                        
                        <!-- Post Settings -->
                        <div class="tab-pane fade" id="post-settings" role="tabpanel" aria-labelledby="post-settings-tab">
                            <form id="post-settings-form" class="settings-form">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="bulk-posting-enabled" name="bulk_posting_enabled">
                                        <label class="form-check-label" for="bulk-posting-enabled">Enable Bulk Posting</label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="post-schedule" class="form-label">Default Post Schedule</label>
                                    <select class="form-select" id="post-schedule" name="post_schedule">
                                        <option value="minutes">Minutes</option>
                                        <option value="hourly" selected>Hours</option>
                                        <option value="daily">Days</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="randomize-post-timing" name="randomize_schedule" checked>
                                        <label class="form-check-label" for="randomize-post-timing">Randomize Post Timings (Google Safe)</label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="auto-internal-linking" name="auto_internal_linking" checked>
                                        <label class="form-check-label" for="auto-internal-linking">Enable Auto Internal Linking</label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </form>
                        </div>
                        
                        <!-- Image Settings -->
                        <div class="tab-pane fade" id="image-settings" role="tabpanel" aria-labelledby="image-settings-tab">
                            <form id="image-settings-form" class="settings-form">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="image_source" id="use-scraped-images" value="scraped" checked>
                                        <label class="form-check-label" for="use-scraped-images">
                                            Use Scraped Images
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="image_source" id="use-ai-images" value="ai">
                                        <label class="form-check-label" for="use-ai-images">
                                            Use AI-Generated Images
                                        </label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 text-center text-muted">
        <p>AI News Scraper & Auto Blogger Pro v<?php echo AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_VERSION; ?> | Developed by Satyendra Maurya | <a href="https://satyendramaurya.site" target="_blank">satyendramaurya.site</a></p>
    </div>
</div>

<script>
    // Initialize JavaScript variables for RSS feeds
    var aiNewsScraperFeeds = <?php 
        $options = get_option('ai_news_scraper_options', array());
        $feeds = isset($options['rss_settings']['feeds']) ? $options['rss_settings']['feeds'] : array();
        echo json_encode($feeds);
    ?>;
</script>
