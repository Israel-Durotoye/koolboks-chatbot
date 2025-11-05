<!-- Inline Chat Template (for shortcode usage) -->
<div class="koolboks-chat-inline" style="height: <?php echo esc_attr($atts['height']); ?>; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden;">
    <div class="koolboks-chat-header">
        <h3><?php echo esc_html($atts['title']); ?></h3>
    </div>

    <div class="koolboks-chat-messages" style="height: calc(100% - 180px);">
        <!-- Messages will be added here dynamically -->
    </div>

    <div class="koolboks-chat-input-container">
        <div class="koolboks-chat-input-wrapper">
            <textarea class="koolboks-chat-input" 
                      placeholder="Type your message..." 
                      rows="1"></textarea>
            <button class="koolboks-send-btn">Send</button>
        </div>
        <a href="#" class="koolboks-lead-toggle">📝 Leave your contact info</a>
    </div>

    <div class="koolboks-lead-form-container">
        <button class="koolboks-lead-form-back koolboks-lead-toggle">← Back to chat</button>
        <form class="koolboks-lead-form">
            <h3>Get in Touch</h3>
            <div class="form-group">
                <label for="koolboks-inline-lead-name">Name *</label>
                <input type="text" id="koolboks-inline-lead-name" name="name" required>
            </div>
            <div class="form-group">
                <label for="koolboks-inline-lead-email">Email *</label>
                <input type="email" id="koolboks-inline-lead-email" name="email" required>
            </div>
            <div class="form-group">
                <label for="koolboks-inline-lead-phone">Phone</label>
                <input type="tel" id="koolboks-inline-lead-phone" name="phone">
            </div>
            <div class="form-group">
                <label for="koolboks-inline-lead-message">Message</label>
                <textarea id="koolboks-inline-lead-message" name="message" rows="4"></textarea>
            </div>
            <button type="submit">Submit</button>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize inline chat with welcome message
    $('.koolboks-chat-inline .koolboks-chat-messages').append(
        '<div class="message koolboks-bot-message">' +
        '<div class="message-content">Hello! I\'m the Koolboks AI assistant. How can I help you today?</div>' +
        '<div class="message-time">' + new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) + '</div>' +
        '</div>'
    );
});
</script>
