<?php
/**
 * Plugin Name: Koolboks Chat
 * Plugin URI: https://koolboks.com
 * Description: Smart chat assistant for Koolboks products with lead capture and CRM integration
 * Version: 2.0.0
 * Author: Israel Durotoye
 * Author URI: https://koolboks.com
 * License: GPL v2 or later
 * Text Domain: koolboks-chat
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('KOOLBOKS_CHAT_VERSION', '1.0.0');
define('KOOLBOKS_CHAT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KOOLBOKS_CHAT_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class Koolboks_Chat {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'render_chat_widget'));
        
        // Shortcode
        add_shortcode('koolboks_chat', array($this, 'chat_shortcode'));
        
        // AJAX handlers
        add_action('wp_ajax_koolboks_chat', array($this, 'handle_chat_ajax'));
        add_action('wp_ajax_nopriv_koolboks_chat', array($this, 'handle_chat_ajax'));
        add_action('wp_ajax_koolboks_capture_lead', array($this, 'handle_lead_ajax'));
        add_action('wp_ajax_nopriv_koolboks_capture_lead', array($this, 'handle_lead_ajax'));
        add_action('wp_ajax_koolboks_log_conversation', array($this, 'handle_log_conversation'));
        add_action('wp_ajax_nopriv_koolboks_log_conversation', array($this, 'handle_log_conversation'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Koolboks Chat',
            'Koolboks Chat',
            'manage_options',
            'koolboks-chat',
            array($this, 'admin_page'),
            'dashicons-format-chat',
            30
        );
        
        // Add submenu pages
        add_submenu_page(
            'koolboks-chat',
            'Settings',
            'Settings',
            'manage_options',
            'koolboks-chat',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'koolboks-chat',
            'Chat Logs',
            'Chat Logs',
            'manage_options',
            'koolboks-chat-logs',
            array($this, 'chat_logs_page')
        );
        
        add_submenu_page(
            'koolboks-chat',
            'Knowledge Base',
            'Knowledge Base',
            'manage_options',
            'koolboks-chat-knowledge',
            array($this, 'knowledge_base_page')
        );
        
        add_submenu_page(
            'koolboks-chat',
            'Instructions',
            'Instructions',
            'manage_options',
            'koolboks-chat-instructions',
            array($this, 'instructions_page')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        // Connection settings
        register_setting('koolboks_chat_settings', 'koolboks_api_url');
        register_setting('koolboks_chat_settings', 'koolboks_chat_enabled');
        
        // Appearance settings
        register_setting('koolboks_chat_settings', 'koolboks_chat_position');
        register_setting('koolboks_chat_settings', 'koolboks_primary_color');
        register_setting('koolboks_chat_settings', 'koolboks_chat_title');
        register_setting('koolboks_chat_settings', 'koolboks_welcome_message');
        register_setting('koolboks_chat_settings', 'koolboks_chat_icon');
        
        // Custom instructions
        register_setting('koolboks_chat_settings', 'koolboks_custom_instructions');
        
        // Knowledge base
        register_setting('koolboks_chat_settings', 'koolboks_knowledge_base_docs');
    }
    
    /**
     * Admin settings page
     */
    public function admin_page() {
        ?>
        <div class="wrap koolboks-admin-wrap">
            <h1>
                <span class="dashicons dashicons-format-chat" style="color: <?php echo esc_attr(get_option('koolboks_primary_color', '#0066cc')); ?>;"></span>
                Koolboks Chat Settings
            </h1>
            
            <div class="koolboks-admin-container">
                <form method="post" action="options.php" class="koolboks-settings-form">
                    <?php
                    settings_fields('koolboks_chat_settings');
                    do_settings_sections('koolboks_chat_settings');
                    ?>
                    
                    <div class="koolboks-settings-grid">
                        <!-- Connection Settings -->
                        <div class="koolboks-settings-card">
                            <h2><span class="dashicons dashicons-admin-links"></span> Connection Settings</h2>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="koolboks_api_url">API URL</label>
                                    </th>
                                    <td>
                                        <input type="text" 
                                               id="koolboks_api_url" 
                                               name="koolboks_api_url" 
                                               value="<?php echo esc_attr(get_option('koolboks_api_url', 'http://localhost:8000')); ?>" 
                                               class="regular-text"
                                               placeholder="https://your-api-url.com">
                                        <p class="description">Your backend API URL (Railway, Render, or ngrok)</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="koolboks_chat_enabled">Enable Chat</label>
                                    </th>
                                    <td>
                                        <label class="koolboks-toggle">
                                            <input type="checkbox" 
                                                   id="koolboks_chat_enabled" 
                                                   name="koolboks_chat_enabled" 
                                                   value="1" 
                                                   <?php checked(get_option('koolboks_chat_enabled'), 1); ?>>
                                            <span class="koolboks-toggle-slider"></span>
                                        </label>
                                        <p class="description">Show chat widget on your website</p>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Appearance Settings -->
                        <div class="koolboks-settings-card">
                            <h2><span class="dashicons dashicons-admin-appearance"></span> Appearance</h2>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="koolboks_chat_title">Chat Title</label>
                                    </th>
                                    <td>
                                        <input type="text" 
                                               id="koolboks_chat_title" 
                                               name="koolboks_chat_title" 
                                               value="<?php echo esc_attr(get_option('koolboks_chat_title', 'Koolboks Assistant')); ?>" 
                                               class="regular-text">
                                        <p class="description">Title shown in the chat header</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="koolboks_welcome_message">Welcome Message</label>
                                    </th>
                                    <td>
                                        <textarea id="koolboks_welcome_message" 
                                                  name="koolboks_welcome_message" 
                                                  rows="3" 
                                                  class="large-text"><?php echo esc_textarea(get_option('koolboks_welcome_message', 'Hello! How can I help you today?')); ?></textarea>
                                        <p class="description">First message shown when chat opens</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="koolboks_chat_position">Widget Position</label>
                                    </th>
                                    <td>
                                        <select id="koolboks_chat_position" name="koolboks_chat_position">
                                            <option value="bottom-right" <?php selected(get_option('koolboks_chat_position', 'bottom-right'), 'bottom-right'); ?>>Bottom Right</option>
                                            <option value="bottom-left" <?php selected(get_option('koolboks_chat_position'), 'bottom-left'); ?>>Bottom Left</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="koolboks_primary_color">Brand Color</label>
                                    </th>
                                    <td>
                                        <input type="color" 
                                               id="koolboks_primary_color" 
                                               name="koolboks_primary_color" 
                                               value="<?php echo esc_attr(get_option('koolboks_primary_color', '#0066cc')); ?>">
                                        <p class="description">Primary color for chat widget</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="koolboks_chat_icon">Chat Icon</label>
                                    </th>
                                    <td>
                                        <select id="koolboks_chat_icon" name="koolboks_chat_icon">
                                            <option value="💬" <?php selected(get_option('koolboks_chat_icon', '💬'), '💬'); ?>>💬 Speech Bubble</option>
                                            <option value="💡" <?php selected(get_option('koolboks_chat_icon'), '💡'); ?>>💡 Light Bulb</option>
                                            <option value="🔆" <?php selected(get_option('koolboks_chat_icon'), '🔆'); ?>>🔆 Solar</option>
                                            <option value="⚡" <?php selected(get_option('koolboks_chat_icon'), '⚡'); ?>>⚡ Energy</option>
                                            <option value="🛟" <?php selected(get_option('koolboks_chat_icon'), '🛟'); ?>>🛟 Support</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="koolboks-submit-container">
                        <?php submit_button('Save Settings', 'primary large'); ?>
                    </div>
                </form>
            </div>
            
            <style>
                .koolboks-admin-wrap {
                    background: #f5f5f5;
                    margin-left: -20px;
                    padding: 30px 40px;
                }
                .koolboks-admin-wrap h1 {
                    background: white;
                    padding: 20px 30px;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                    margin-bottom: 30px;
                }
                .koolboks-settings-grid {
                    display: grid;
                    gap: 20px;
                    margin-bottom: 20px;
                }
                .koolboks-settings-card {
                    background: white;
                    padding: 25px;
                    border-radius: 8px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                }
                .koolboks-settings-card h2 {
                    margin-top: 0;
                    padding-bottom: 15px;
                    border-bottom: 2px solid #f0f0f0;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                .koolboks-toggle {
                    position: relative;
                    display: inline-block;
                    width: 60px;
                    height: 30px;
                }
                .koolboks-toggle input {
                    opacity: 0;
                    width: 0;
                    height: 0;
                }
                .koolboks-toggle-slider {
                    position: absolute;
                    cursor: pointer;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: #ccc;
                    transition: 0.4s;
                    border-radius: 30px;
                }
                .koolboks-toggle-slider:before {
                    position: absolute;
                    content: "";
                    height: 22px;
                    width: 22px;
                    left: 4px;
                    bottom: 4px;
                    background-color: white;
                    transition: 0.4s;
                    border-radius: 50%;
                }
                .koolboks-toggle input:checked + .koolboks-toggle-slider {
                    background-color: <?php echo esc_attr(get_option('koolboks_primary_color', '#0066cc')); ?>;
                }
                .koolboks-toggle input:checked + .koolboks-toggle-slider:before {
                    transform: translateX(30px);
                }
                .koolboks-submit-container {
                    background: white;
                    padding: 20px 30px;
                    border-radius: 8px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                    text-align: right;
                }
            </style>
        </div>
        <?php
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        if (!get_option('koolboks_chat_enabled')) {
            return;
        }
        
        wp_enqueue_style(
            'koolboks-chat-style',
            KOOLBOKS_CHAT_PLUGIN_URL . 'assets/css/chat-widget.css',
            array(),
            KOOLBOKS_CHAT_VERSION
        );
        
        wp_enqueue_script(
            'koolboks-chat-script',
            KOOLBOKS_CHAT_PLUGIN_URL . 'assets/js/chat-widget.js',
            array('jquery'),
            KOOLBOKS_CHAT_VERSION,
            true
        );
        
        wp_localize_script('koolboks-chat-script', 'koolboksChat', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'apiUrl' => get_option('koolboks_api_url', 'http://localhost:8000'),
            'position' => get_option('koolboks_chat_position', 'bottom-right'),
            'primaryColor' => get_option('koolboks_primary_color', '#0066cc'),
            'welcomeMessage' => get_option('koolboks_welcome_message', 'Hello! How can I help you today?'),
            'nonce' => wp_create_nonce('koolboks_chat_nonce')
        ));
    }
    
    /**
     * Render chat widget HTML
     */
    public function render_chat_widget() {
        if (!get_option('koolboks_chat_enabled')) {
            return;
        }
        
        include KOOLBOKS_CHAT_PLUGIN_DIR . 'templates/chat-widget.php';
    }
    
    /**
     * Shortcode for embedding chat
     */
    public function chat_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => 'Chat with Koolboks',
            'height' => '600px'
        ), $atts);
        
        ob_start();
        include KOOLBOKS_CHAT_PLUGIN_DIR . 'templates/chat-inline.php';
        return ob_get_clean();
    }
    
    /**
     * Handle chat AJAX requests
     */
    public function handle_chat_ajax() {
        check_ajax_referer('koolboks_chat_nonce', 'nonce');
        
        $query = sanitize_text_field($_POST['query']);
        $session_id = sanitize_text_field($_POST['session_id']);
        $chat_history = isset($_POST['chat_history']) ? json_decode(stripslashes($_POST['chat_history']), true) : array();
        
        $api_url = get_option('koolboks_api_url', 'http://localhost:8000');
        
        // Convert chat_history format from WordPress to backend format
        $formatted_history = array();
        if (!empty($chat_history)) {
            foreach ($chat_history as $msg) {
                if (isset($msg['role']) && isset($msg['content'])) {
                    // Already in correct format
                    continue;
                }
                // Convert to backend expected format
                if (isset($msg['user']) && isset($msg['assistant'])) {
                    $formatted_history[] = array(
                        'user' => $msg['user'],
                        'assistant' => $msg['assistant']
                    );
                }
            }
        }
        
        $response = wp_remote_post($api_url . '/chat/', array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode(array(
                'query' => $query,
                'session_id' => $session_id,
                'chat_history' => $formatted_history,
                'settings' => array(
                    'temperature' => 0.7,
                    'max_tokens' => 500
                )
            )),
            'timeout' => 60
        ));
        
        if (is_wp_error($response)) {
            error_log('Koolboks Chat Error: ' . $response->get_error_message());
            wp_send_json_error(array('message' => 'Failed to connect to chat service: ' . $response->get_error_message()));
            return;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        // Log for debugging
        error_log('Koolboks API Status: ' . $status_code);
        error_log('Koolboks API Response: ' . $body);
        
        if ($status_code !== 200) {
            wp_send_json_error(array(
                'message' => 'API returned error status: ' . $status_code,
                'details' => $body
            ));
            return;
        }
        
        $decoded = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Koolboks JSON Error: ' . json_last_error_msg());
            wp_send_json_error(array('message' => 'Invalid JSON response from API'));
            return;
        }
        
        wp_send_json_success($decoded);
    }
    
    /**
     * Handle lead capture AJAX requests
     */
    public function handle_lead_ajax() {
        check_ajax_referer('koolboks_chat_nonce', 'nonce');
        
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $message = sanitize_textarea_field($_POST['message']);
        $session_id = sanitize_text_field($_POST['session_id']);
        
        // Validate required fields
        if (empty($name) || empty($email)) {
            error_log('Koolboks Lead Error: Missing required fields');
            wp_send_json_error(array('message' => 'Name and email are required'));
            return;
        }
        
        $api_url = get_option('koolboks_api_url', 'http://localhost:8000');
        
        $payload = array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
            'session_id' => $session_id,
            'interested_products' => array(),
            'chat_history' => array()
        );
        
        error_log('Koolboks Lead Payload: ' . json_encode($payload));
        
        $response = wp_remote_post($api_url . '/capture-lead/', array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode($payload),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            error_log('Koolboks Lead Error: ' . $response->get_error_message());
            wp_send_json_error(array(
                'message' => 'Failed to submit lead: ' . $response->get_error_message()
            ));
            return;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        error_log('Koolboks Lead API Status: ' . $status_code);
        error_log('Koolboks Lead API Response: ' . $body);
        
        if ($status_code !== 200) {
            wp_send_json_error(array(
                'message' => 'API returned error status: ' . $status_code,
                'details' => $body
            ));
            return;
        }
        
        $decoded = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Koolboks Lead JSON Error: ' . json_last_error_msg());
            wp_send_json_error(array('message' => 'Invalid JSON response from API'));
            return;
        }
        
        wp_send_json_success($decoded);
    }
    
    /**
     * Handle conversation logging
     */
    public function handle_log_conversation() {
        check_ajax_referer('koolboks_chat_nonce', 'nonce');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'koolboks_chat_logs';
        
        // Create table if it doesn't exist
        $this->create_logs_table();
        
        $session_id = sanitize_text_field($_POST['session_id']);
        $user_message = sanitize_textarea_field($_POST['user_message']);
        $bot_response = sanitize_textarea_field($_POST['bot_response']);
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'session_id' => $session_id,
                'user_message' => $user_message,
                'bot_response' => $bot_response,
                'ip_address' => $ip_address
            ),
            array('%s', '%s', '%s', '%s')
        );
        
        if ($result) {
            wp_send_json_success(array('message' => 'Conversation logged'));
        } else {
            wp_send_json_error(array('message' => 'Failed to log conversation'));
        }
    }
    
    /**
     * Chat Logs Page
     */
    public function chat_logs_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'koolboks_chat_logs';
        
        // Create table if it doesn't exist
        $this->create_logs_table();
        
        // Get logs
        $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 100");
        
        ?>
        <div class="wrap koolboks-admin-wrap">
            <h1>
                <span class="dashicons dashicons-book-alt" style="color: <?php echo esc_attr(get_option('koolboks_primary_color', '#0066cc')); ?>;"></span>
                Chat Logs
            </h1>
            
            <div class="koolboks-admin-container">
                <div class="koolboks-logs-card">
                    <div class="koolboks-logs-header">
                        <h2>Recent Conversations</h2>
                        <button class="button" onclick="location.reload()">Refresh</button>
                    </div>
                    
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Session ID</th>
                                <th>User Message</th>
                                <th>Bot Response</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px;">
                                        No chat logs yet. Conversations will appear here once users start chatting.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo esc_html(date('M j, Y g:i A', strtotime($log->created_at))); ?></td>
                                        <td><code><?php echo esc_html(substr($log->session_id, 0, 12)); ?>...</code></td>
                                        <td><?php echo esc_html(substr($log->user_message, 0, 100)); ?>...</td>
                                        <td><?php echo esc_html(substr($log->bot_response, 0, 100)); ?>...</td>
                                        <td><?php echo esc_html($log->ip_address); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <style>
                .koolboks-logs-card {
                    background: white;
                    padding: 25px;
                    border-radius: 8px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                }
                .koolboks-logs-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 20px;
                    padding-bottom: 15px;
                    border-bottom: 2px solid #f0f0f0;
                }
                .koolboks-logs-header h2 {
                    margin: 0;
                }
            </style>
        </div>
        <?php
    }
    
    /**
     * Knowledge Base Page
     */
    public function knowledge_base_page() {
        ?>
        <div class="wrap koolboks-admin-wrap">
            <h1>
                <span class="dashicons dashicons-media-document" style="color: <?php echo esc_attr(get_option('koolboks_primary_color', '#0066cc')); ?>;"></span>
                Knowledge Base
            </h1>
            
            <div class="koolboks-admin-container">
                <div class="koolboks-settings-card">
                    <h2><span class="dashicons dashicons-upload"></span> Upload Documents</h2>
                    <p>Upload PDF documents to enhance the chat assistant's knowledge about your products and services.</p>
                    
                    <form id="koolboks-upload-form" enctype="multipart/form-data">
                        <input type="file" id="koolboks-pdf-file" accept=".pdf" multiple>
                        <button type="submit" class="button button-primary">Upload Documents</button>
                    </form>
                    
                    <div id="upload-progress" style="margin-top: 20px; display: none;">
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                        <p id="upload-status"></p>
                    </div>
                </div>
                
                <div class="koolboks-settings-card" style="margin-top: 20px;">
                    <h2><span class="dashicons dashicons-text-page"></span> Uploaded Documents</h2>
                    <div id="documents-list">
                        <p>No documents uploaded yet.</p>
                    </div>
                </div>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                $('#koolboks-upload-form').on('submit', function(e) {
                    e.preventDefault();
                    
                    var files = $('#koolboks-pdf-file')[0].files;
                    if (files.length === 0) {
                        alert('Please select at least one PDF file');
                        return;
                    }
                    
                    var formData = new FormData();
                    for (var i = 0; i < files.length; i++) {
                        formData.append('files[]', files[i]);
                    }
                    
                    $('#upload-progress').show();
                    $('#upload-status').text('Uploading...');
                    
                    $.ajax({
                        url: '<?php echo esc_js(get_option('koolboks_api_url')); ?>/upload/',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('#upload-status').text('Upload successful!');
                            $('#koolboks-pdf-file').val('');
                            setTimeout(function() {
                                $('#upload-progress').hide();
                            }, 2000);
                        },
                        error: function() {
                            $('#upload-status').text('Upload failed. Please try again.');
                        }
                    });
                });
            });
            </script>
            
            <style>
                .progress-bar {
                    width: 100%;
                    height: 30px;
                    background: #f0f0f0;
                    border-radius: 15px;
                    overflow: hidden;
                }
                .progress-fill {
                    height: 100%;
                    background: <?php echo esc_attr(get_option('koolboks_primary_color', '#0066cc')); ?>;
                    width: 50%;
                    animation: progress 2s infinite;
                }
                @keyframes progress {
                    0% { width: 0%; }
                    50% { width: 100%; }
                    100% { width: 0%; }
                }
            </style>
        </div>
        <?php
    }
    
    /**
     * Instructions Page
     */
    public function instructions_page() {
        if (isset($_POST['koolboks_save_instructions'])) {
            update_option('koolboks_custom_instructions', sanitize_textarea_field($_POST['koolboks_custom_instructions']));
            echo '<div class="notice notice-success"><p>Instructions saved successfully!</p></div>';
        }
        
        $instructions = get_option('koolboks_custom_instructions', '');
        ?>
        <div class="wrap koolboks-admin-wrap">
            <h1>
                <span class="dashicons dashicons-welcome-write-blog" style="color: <?php echo esc_attr(get_option('koolboks_primary_color', '#0066cc')); ?>;"></span>
                Custom Instructions
            </h1>
            
            <div class="koolboks-admin-container">
                <div class="koolboks-settings-card">
                    <h2><span class="dashicons dashicons-edit"></span> Chat Assistant Instructions</h2>
                    <p>Customize how the chat assistant responds to users. These instructions will guide the assistant's behavior and tone.</p>
                    
                    <form method="post">
                        <textarea name="koolboks_custom_instructions" 
                                  rows="15" 
                                  class="large-text code"
                                  style="width: 100%; font-family: monospace;"><?php echo esc_textarea($instructions); ?></textarea>
                        
                        <p class="description">
                            <strong>Tips:</strong><br>
                            • Define the assistant's personality and tone<br>
                            • Specify which topics to focus on<br>
                            • Set guidelines for product recommendations<br>
                            • Include any special handling for common questions
                        </p>
                        
                        <p style="margin-top: 20px;">
                            <button type="submit" name="koolboks_save_instructions" class="button button-primary button-large">
                                Save Instructions
                            </button>
                        </p>
                    </form>
                </div>
                
                <div class="koolboks-settings-card" style="margin-top: 20px;">
                    <h2><span class="dashicons dashicons-info"></span> Example Instructions</h2>
                    <pre style="background: #f9f9f9; padding: 15px; border-radius: 5px; overflow-x: auto;">
You are a helpful Koolboks product specialist.

Guidelines:
- Use simple, clear language
- Focus on the practical benefits of solar solutions
- Be friendly and professional
- Recommend products based on customer needs
- If unsure, offer to connect them with a sales representative

Key Products:
- Koolboks Solar Refrigerators
- Solar Power Systems
- Off-grid Energy Solutions

Always prioritize customer satisfaction and accurate information.
                    </pre>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Create logs table
     */
    private function create_logs_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'koolboks_chat_logs';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL,
            user_message text NOT NULL,
            bot_response text NOT NULL,
            ip_address varchar(100),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Initialize plugin
function koolboks_chat_init() {
    return Koolboks_Chat::get_instance();
}
add_action('plugins_loaded', 'koolboks_chat_init');
