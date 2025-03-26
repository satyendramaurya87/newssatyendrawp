<?php
/**
 * Provide a admin area view for the scraper settings
 *
 * @link       https://satyendramaurya.site
 * @since      1.0.0
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/admin/partials
 */

// Get current options
$options = get_option('ai_news_scraper_options', array());
$scraper_settings = isset($options['scraper_settings']) ? $options['scraper_settings'] : array();

// Get settings values or defaults
$visual_selector_enabled = isset($scraper_settings['visual_selector_enabled']) ? $scraper_settings['visual_selector_enabled'] : true;
$auto_scraping_enabled = isset($scraper_settings['auto_scraping_enabled']) ? $scraper_settings['auto_scraping_enabled'] : false;
?>

<div class="wrap ai-news-scraper-container">
    <!-- Alerts container for displaying messages -->
    <div id="alerts-container"></div>

    <div class="ai-news-scraper-header">
        <h1><i class="fas fa-search"></i> Scraper Settings</h1>
        <p>Configure how the plugin scrapes content from websites</p>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Scraper Configuration</h5>
        </div>
        <div class="card-body">
            <form id="scraper-settings-form" class="settings-form">
                <div class="settings-section">
                    <h3>General Scraper Settings</h3>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="visual-selector-enabled" name="visual_selector_enabled" <?php echo $visual_selector_enabled ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="visual-selector-enabled">Enable Visual Selector</label>
                            <div class="form-text">Allow selection of webpage elements by clicking directly on them.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto-scraping-enabled" name="auto_scraping_enabled" <?php echo $auto_scraping_enabled ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="auto-scraping-enabled">Enable Auto-Scraping for Future Articles</label>
                            <div class="form-text">Automatically use saved selectors for future articles from the same website.</div>
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>Content Extraction</h3>
                    
                    <div class="mb-3">
                        <label for="content-selectors" class="form-label">Default Selectors</label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Title</span>
                                    <input type="text" class="form-control" id="default-title-selector" name="default_selectors[title]" value="h1, .entry-title, .article-title" placeholder="h1, .entry-title">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Content</span>
                                    <input type="text" class="form-control" id="default-content-selector" name="default_selectors[content]" value=".entry-content, article, .post-content" placeholder=".entry-content, article">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Author</span>
                                    <input type="text" class="form-control" id="default-author-selector" name="default_selectors[author]" value=".author, .byline" placeholder=".author, .byline">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Date</span>
                                    <input type="text" class="form-control" id="default-date-selector" name="default_selectors[date]" value=".published, .post-date, time" placeholder=".published, time">
                                </div>
                            </div>
                        </div>
                        <div class="form-text">Default CSS selectors to use when visual selector is not enabled.</div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>Social Media Embed</h3>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="twitter-embed-enabled" name="social_embed[twitter]" checked>
                            <label class="form-check-label" for="twitter-embed-enabled">Auto-Fetch Twitter Embeds</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="instagram-embed-enabled" name="social_embed[instagram]" checked>
                            <label class="form-check-label" for="instagram-embed-enabled">Auto-Fetch Instagram Embeds</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="youtube-embed-enabled" name="social_embed[youtube]" checked>
                            <label class="form-check-label" for="youtube-embed-enabled">Auto-Fetch YouTube Embeds</label>
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>Saved Website Configurations</h3>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="saved-websites-table">
                            <thead>
                                <tr>
                                    <th>Domain</th>
                                    <th>Title Selector</th>
                                    <th>Content Selector</th>
                                    <th>Author Selector</th>
                                    <th>Last Used</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center">No saved website configurations yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Visual Selector Tool</h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Use this tool to test and create content selectors for websites.</p>
            
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
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Title Selector</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-primary" id="select-title-button">Select Title</button>
                                    <input type="text" class="form-control" id="title-selector" placeholder=".article-title">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Content Selector</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-primary" id="select-content-button">Select Content</button>
                                    <input type="text" class="form-control" id="content-selector" placeholder=".article-content">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Author Selector</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-primary" id="select-author-button">Select Author</button>
                                    <input type="text" class="form-control" id="author-selector" placeholder=".author-name">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Date Selector</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-primary" id="select-date-button">Select Date</button>
                                    <input type="text" class="form-control" id="date-selector" placeholder=".publish-date">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" id="test-selectors-button">Test Selectors</button>
                        <button class="btn btn-primary" id="save-selectors-button">Save Website Configuration</button>
                    </div>
                    
                    <div id="selector-test-results" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 text-center text-muted">
        <p>AI News Scraper & Auto Blogger Pro v<?php echo AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_VERSION; ?> | Developed by Satyendra Maurya | <a href="https://satyendramaurya.site" target="_blank">satyendramaurya.site</a></p>
    </div>
</div>
