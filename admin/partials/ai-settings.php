<?php
/**
 * Provide a admin area view for the AI settings
 *
 * @link       https://satyendramaurya.site
 * @since      1.0.0
 *
 * @package    AI_News_Scraper_Auto_Blogger_Pro
 * @subpackage AI_News_Scraper_Auto_Blogger_Pro/admin/partials
 */

// Get current options
$options = get_option('ai_news_scraper_options', array());
$ai_settings = isset($options['ai_settings']) ? $options['ai_settings'] : array();

// Get settings values or defaults
$ai_model = isset($ai_settings['ai_model']) ? $ai_settings['ai_model'] : 'openai';
$language = isset($ai_settings['language']) ? $ai_settings['language'] : 'english';
$writing_tone = isset($ai_settings['writing_tone']) ? $ai_settings['writing_tone'] : 'default';
$social_media_embed = isset($ai_settings['social_media_embed']) ? $ai_settings['social_media_embed'] : true;

// API keys (these should be masked in the UI)
$api_keys = isset($ai_settings['api_keys']) ? $ai_settings['api_keys'] : array(
    'openai' => '',
    'gemini' => '',
    'claude' => '',
    'deepseek' => '',
);
?>

<div class="wrap ai-news-scraper-container">
    <!-- Alerts container for displaying messages -->
    <div id="alerts-container"></div>

    <div class="ai-news-scraper-header">
        <h1><i class="fas fa-brain"></i> AI Settings</h1>
        <p>Configure AI models and content generation settings</p>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">AI Configuration</h5>
        </div>
        <div class="card-body">
            <form id="ai-settings-form" class="settings-form">
                <div class="settings-section">
                    <h3>AI Model Settings</h3>
                    
                    <div class="mb-3">
                        <label for="ai-model-setting" class="form-label">Default AI Model</label>
                        <select class="form-select" id="ai-model-setting" name="ai_model">
                            <option value="openai" <?php selected($ai_model, 'openai'); ?>>OpenAI GPT</option>
                            <option value="gemini" <?php selected($ai_model, 'gemini'); ?>>Google Gemini</option>
                            <option value="claude" <?php selected($ai_model, 'claude'); ?>>Anthropic Claude</option>
                            <option value="deepseek" <?php selected($ai_model, 'deepseek'); ?>>DeepSeek AI</option>
                        </select>
                        <div class="form-text">Choose the default AI model for content generation.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="language-setting" class="form-label">Default Language</label>
                        <select class="form-select" id="language-setting" name="language">
                            <option value="english" <?php selected($language, 'english'); ?>>English</option>
                            <option value="hindi" <?php selected($language, 'hindi'); ?>>Hindi</option>
                        </select>
                        <div class="form-text">Choose the default language for generated content.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tone-setting" class="form-label">Default Writing Tone</label>
                        <select class="form-select" id="tone-setting" name="writing_tone">
                            <option value="default" <?php selected($writing_tone, 'default'); ?>>Default</option>
                            <option value="banarasi" <?php selected($writing_tone, 'banarasi'); ?>>Banarasi</option>
                            <option value="lucknow" <?php selected($writing_tone, 'lucknow'); ?>>Lucknow</option>
                            <option value="delhi" <?php selected($writing_tone, 'delhi'); ?>>Delhi</option>
                            <option value="indore" <?php selected($writing_tone, 'indore'); ?>>Indore</option>
                        </select>
                        <div class="form-text">Choose the default writing tone/style for generated content.</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="social-media-embed" name="social_media_embed" <?php checked($social_media_embed, true); ?>>
                            <label class="form-check-label" for="social-media-embed">Enable Social Media Embed Fetching</label>
                            <div class="form-text">Automatically include Twitter and Instagram embeds from the original article.</div>
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>Content Generation Settings</h3>
                    
                    <div class="mb-3">
                        <label for="min-word-count" class="form-label">Minimum Word Count</label>
                        <input type="number" class="form-control" id="min-word-count" name="min_word_count" value="500" min="100" step="50">
                        <div class="form-text">Minimum number of words for generated articles.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="keyword-density" class="form-label">Target Keyword Density (%)</label>
                        <input type="number" class="form-control" id="keyword-density" name="keyword_density" value="2.5" min="0.5" max="5" step="0.1">
                        <div class="form-text">Target keyword density percentage for SEO optimization (2-3% recommended).</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto-headings" name="auto_headings" checked>
                            <label class="form-check-label" for="auto-headings">Auto-Generate H2/H3 Headings</label>
                            <div class="form-text">Automatically create SEO-friendly heading structure in articles.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="use-lists" name="use_lists" checked>
                            <label class="form-check-label" for="use-lists">Include Bullet Points & Lists</label>
                            <div class="form-text">Include bulleted and numbered lists in content when appropriate.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="add-faq" name="add_faq" checked>
                            <label class="form-check-label" for="add-faq">Add FAQ Section</label>
                            <div class="form-text">Include an FAQ section at the end of articles for better SEO.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="add-conclusion" name="add_conclusion" checked>
                            <label class="form-check-label" for="add-conclusion">Add Conclusion Section</label>
                            <div class="form-text">Include a conclusion section at the end of articles.</div>
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>API Keys</h3>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> API keys are required for each AI model you want to use. Leave blank if you don't plan to use a particular model.
                    </div>
                    
                    <div id="api-keys-accordion" class="accordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="openai-heading">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#openai-keys" aria-expanded="true" aria-controls="openai-keys">
                                    OpenAI API Keys
                                </button>
                            </h2>
                            <div id="openai-keys" class="accordion-collapse collapse show" aria-labelledby="openai-heading">
                                <div class="accordion-body">
                                    <div class="mb-3">
                                        <label for="openai-api-key" class="form-label">OpenAI API Key</label>
                                        <div class="input-group api-key-field">
                                            <input type="password" class="form-control" id="openai-api-key" name="api_keys[openai]" value="<?php echo esc_attr($api_keys['openai']); ?>">
                                            <span class="input-group-text toggle-password" data-target="openai-api-key"><i class="fas fa-eye"></i></span>
                                        </div>
                                        <div class="form-text">Get your API key from <a href="https://platform.openai.com/account/api-keys" target="_blank">OpenAI Dashboard</a>.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="openai-org-id" class="form-label">OpenAI Organization ID (Optional)</label>
                                        <input type="text" class="form-control" id="openai-org-id" name="api_keys[openai_org]" value="<?php echo isset($api_keys['openai_org']) ? esc_attr($api_keys['openai_org']) : ''; ?>">
                                        <div class="form-text">If you're using an organization account, enter the Organization ID here.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="gemini-heading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gemini-keys" aria-expanded="false" aria-controls="gemini-keys">
                                    Google Gemini API Keys
                                </button>
                            </h2>
                            <div id="gemini-keys" class="accordion-collapse collapse" aria-labelledby="gemini-heading">
                                <div class="accordion-body">
                                    <div class="mb-3">
                                        <label for="gemini-api-key" class="form-label">Google Gemini API Key</label>
                                        <div class="input-group api-key-field">
                                            <input type="password" class="form-control" id="gemini-api-key" name="api_keys[gemini]" value="<?php echo esc_attr($api_keys['gemini']); ?>">
                                            <span class="input-group-text toggle-password" data-target="gemini-api-key"><i class="fas fa-eye"></i></span>
                                        </div>
                                        <div class="form-text">Get your API key from <a href="https://ai.google.dev/" target="_blank">Google AI Studio</a>.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="claude-heading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#claude-keys" aria-expanded="false" aria-controls="claude-keys">
                                    Anthropic Claude API Keys
                                </button>
                            </h2>
                            <div id="claude-keys" class="accordion-collapse collapse" aria-labelledby="claude-heading">
                                <div class="accordion-body">
                                    <div class="mb-3">
                                        <label for="claude-api-key" class="form-label">Anthropic Claude API Key</label>
                                        <div class="input-group api-key-field">
                                            <input type="password" class="form-control" id="claude-api-key" name="api_keys[claude]" value="<?php echo esc_attr($api_keys['claude']); ?>">
                                            <span class="input-group-text toggle-password" data-target="claude-api-key"><i class="fas fa-eye"></i></span>
                                        </div>
                                        <div class="form-text">Get your API key from <a href="https://console.anthropic.com/keys" target="_blank">Anthropic Console</a>.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="deepseek-heading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#deepseek-keys" aria-expanded="false" aria-controls="deepseek-keys">
                                    DeepSeek AI API Keys
                                </button>
                            </h2>
                            <div id="deepseek-keys" class="accordion-collapse collapse" aria-labelledby="deepseek-heading">
                                <div class="accordion-body">
                                    <div class="mb-3">
                                        <label for="deepseek-api-key" class="form-label">DeepSeek AI API Key</label>
                                        <div class="input-group api-key-field">
                                            <input type="password" class="form-control" id="deepseek-api-key" name="api_keys[deepseek]" value="<?php echo esc_attr($api_keys['deepseek']); ?>">
                                            <span class="input-group-text toggle-password" data-target="deepseek-api-key"><i class="fas fa-eye"></i></span>
                                        </div>
                                        <div class="form-text">Get your API key from <a href="https://platform.deepseek.com/" target="_blank">DeepSeek Platform</a>.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary mt-3">Save Settings</button>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Test AI Content Generation</h5>
        </div>
        <div class="card-body">
            <form id="test-ai-form">
                <div class="mb-3">
                    <label for="test-ai-model" class="form-label">AI Model</label>
                    <select class="form-select" id="test-ai-model">
                        <option value="openai">OpenAI GPT</option>
                        <option value="gemini">Google Gemini</option>
                        <option value="claude">Anthropic Claude</option>
                        <option value="deepseek">DeepSeek AI</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="test-language" class="form-label">Language</label>
                    <select class="form-select" id="test-language">
                        <option value="english">English</option>
                        <option value="hindi">Hindi</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="test-tone" class="form-label">Writing Tone</label>
                    <select class="form-select" id="test-tone">
                        <option value="default">Default</option>
                        <option value="banarasi">Banarasi</option>
                        <option value="lucknow">Lucknow</option>
                        <option value="delhi">Delhi</option>
                        <option value="indore">Indore</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="test-content" class="form-label">Test Content</label>
                    <textarea class="form-control" id="test-content" rows="5" placeholder="Enter some sample content to rewrite..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" id="test-ai-button">Generate Test Content</button>
            </form>
            
            <div id="test-ai-result" class="mt-4"></div>
        </div>
    </div>

    <div class="mt-4 text-center text-muted">
        <p>AI News Scraper & Auto Blogger Pro v<?php echo AI_NEWS_SCRAPER_AUTO_BLOGGER_PRO_VERSION; ?> | Developed by Satyendra Maurya | <a href="https://satyendramaurya.site" target="_blank">satyendramaurya.site</a></p>
    </div>
</div>

<script>
// Toggle password visibility
jQuery(document).ready(function($) {
    $('.toggle-password').on('click', function() {
        const target = $(this).data('target');
        const input = $('#' + target);
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Test AI form submission
    $('#test-ai-form').on('submit', function(e) {
        e.preventDefault();
        
        const aiModel = $('#test-ai-model').val();
        const language = $('#test-language').val();
        const tone = $('#test-tone').val();
        const content = $('#test-content').val();
        
        if (!content) {
            alert('Please enter some content to test.');
            return;
        }
        
        // Show loading state
        $('#test-ai-button').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...');
        $('#test-ai-result').html('');
        
        // AJAX request to generate content
        $.ajax({
            url: aiNewsScraperParams.ajax_url,
            type: 'POST',
            data: {
                action: 'generate_content',
                nonce: aiNewsScraperParams.nonce,
                content: content,
                title: 'Test Content',
                ai_model: aiModel,
                language: language,
                tone: tone
            },
            success: function(response) {
                $('#test-ai-button').prop('disabled', false).html('Generate Test Content');
                
                if (response.success) {
                    const data = response.data;
                    
                    // Display the generated content
                    let resultHtml = `
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">AI-Generated Content</h5>
                            </div>
                            <div class="card-body">
                                <h3>${data.title}</h3>
                                <div class="ai-content mb-4">
                                    ${data.content}
                                </div>
                            </div>
                        </div>
                    `;
                    
                    $('#test-ai-result').html(resultHtml);
                } else {
                    // Display error
                    $('#test-ai-result').html(`
                        <div class="alert alert-danger">
                            Error: ${response.data.message}
                        </div>
                    `);
                }
            },
            error: function() {
                $('#test-ai-button').prop('disabled', false).html('Generate Test Content');
                $('#test-ai-result').html(`
                    <div class="alert alert-danger">
                        An error occurred during the API request. Please check your API keys.
                    </div>
                `);
            }
        });
    });
});
</script>
