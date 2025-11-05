<?php
/**
 * Plugin Name: Koolboks AI Chat Assistant
 * Plugin URI: https://koolboks.com
 * Description: AI-powered chatbot for Koolboks products with lead capture and CRM integration
 * Version: 1.0.0
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
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('koolboks_chat_settings', 'koolboks_api_url');
        register_setting('koolboks_chat_settings', 'koolboks_chat_enabled');
        register_setting('koolboks_chat_settings', 'koolboks_chat_position');
        register_setting('koolboks_chat_settings', 'koolboks_primary_color');
    }
    
    /**
     * Admin settings page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Koolboks AI Chat Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('koolboks_chat_settings');
                do_settings_sections('koolboks_chat_settings');
                ?>
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
                                   class="regular-text">
                            <p class="description">Your FastAPI backend URL (e.g., https://api.koolboks.com)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="koolboks_chat_enabled">Enable Chat Widget</label>
                        </th>
                        <td>
                            <input type="checkbox" 
                                   id="koolboks_chat_enabled" 
                                   name="koolboks_chat_enabled" 
                                   value="1" 
                                   <?php checked(get_option('koolboks_chat_enabled'), 1); ?>>
                            <label for="koolboks_chat_enabled">Show chat widget on all pages</label>
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
                            <label for="koolboks_primary_color">Primary Color</label>
                        </th>
                        <td>
                            <input type="color" 
                                   id="koolboks_primary_color" 
                                   name="koolboks_primary_color" 
                                   value="<?php echo esc_attr(get_option('koolboks_primary_color', '#0066cc')); ?>">
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
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
}

// Initialize plugin
function koolboks_chat_init() {
    return Koolboks_Chat::get_instance();
}
add_action('plugins_loaded', 'koolboks_chat_init');
