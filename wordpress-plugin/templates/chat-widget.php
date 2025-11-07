<!-- Floating Chat Widget Template -->
<div class="koolboks-chat-container">
    <!-- Chat Button -->
    <button class="koolboks-chat-button <?php echo esc_attr(get_option('koolboks_chat_position', 'bottom-right')); ?>" 
            aria-label="Open chat">
        <?php echo esc_html(get_option('koolboks_chat_icon', '💬')); ?>
    </button>

    <!-- Chat Window -->
    <div class="koolboks-chat-window <?php echo esc_attr(get_option('koolboks_chat_position', 'bottom-right')); ?>">
        <!-- Header -->
        <div class="koolboks-chat-header">
            <h3><?php echo esc_html(get_option('koolboks_chat_title', 'Koolboks Chat')); ?></h3>
            <button class="koolboks-chat-close" aria-label="Close chat">&times;</button>
        </div>

        <!-- Messages Area -->
        <div class="koolboks-chat-messages">
            <!-- Messages will be added here dynamically -->
        </div>

        <!-- Input Area -->
        <div class="koolboks-chat-input-container">
            <div class="koolboks-chat-input-wrapper">
                <textarea class="koolboks-chat-input" 
                          placeholder="Type your message..." 
                          rows="1"></textarea>
                <button class="koolboks-send-btn">Send</button>
            </div>
            <a href="#" class="koolboks-lead-toggle">📝 Leave your contact info</a>
        </div>

        <!-- Lead Form (Slides in) -->
        <div class="koolboks-lead-form-container">
            <button class="koolboks-lead-form-back koolboks-lead-toggle">← Back to chat</button>
            <form class="koolboks-lead-form">
                <h3>Get in Touch</h3>
                <div class="form-group">
                    <label for="koolboks-lead-name">Name *</label>
                    <input type="text" id="koolboks-lead-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="koolboks-lead-email">Email *</label>
                    <input type="email" id="koolboks-lead-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="koolboks-lead-phone">Phone</label>
                    <input type="tel" id="koolboks-lead-phone" name="phone">
                </div>
                <div class="form-group">
                    <label for="koolboks-lead-message">Message</label>
                    <textarea id="koolboks-lead-message" name="message" rows="4"></textarea>
                </div>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>
</div>
