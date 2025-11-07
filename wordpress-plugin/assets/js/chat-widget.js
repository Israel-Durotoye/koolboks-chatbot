/**
 * Koolboks Chat Widget JavaScript
 * Handles chat interactions, lead capture, and UI updates
 */

(function($) {
    'use strict';
    
    let chatHistory = [];
    let sessionId = 'wp_' + Date.now() + '_' + Math.random().toString(36).substring(7);
    let isTyping = false;
    
    const KoolboksChat = {
        
        init: function() {
            this.bindEvents();
            this.applyCustomColors();
        },
        
        bindEvents: function() {
            // Toggle chat window
            $(document).on('click', '.koolboks-chat-button', this.toggleChat);
            $(document).on('click', '.koolboks-chat-close', this.closeChat);
            
            // Send message
            $(document).on('click', '.koolboks-send-btn', this.sendMessage);
            $(document).on('keypress', '.koolboks-chat-input', function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    KoolboksChat.sendMessage();
                }
            });
            
            // Lead form
            $(document).on('click', '.koolboks-lead-toggle', this.toggleLeadForm);
            $(document).on('submit', '.koolboks-lead-form', this.submitLead);
        },
        
        applyCustomColors: function() {
            const primaryColor = koolboksChat.primaryColor || '#0066cc';
            const styleElement = $('<style>')
                .text(`
                    .koolboks-chat-button { background-color: ${primaryColor} !important; }
                    .koolboks-chat-header { background-color: ${primaryColor} !important; }
                    .koolboks-send-btn { background-color: ${primaryColor} !important; }
                    .koolboks-bot-message { background-color: ${primaryColor}15 !important; }
                `);
            $('head').append(styleElement);
        },
        
        toggleChat: function(e) {
            e.preventDefault();
            const chatWindow = $('.koolboks-chat-window');
            
            if (chatWindow.hasClass('open')) {
                chatWindow.removeClass('open');
            } else {
                chatWindow.addClass('open');
                
                // Show welcome message if first time
                if ($('.koolboks-chat-messages .message').length === 0) {
                    const welcomeMsg = koolboksChat.welcomeMessage || 'Hello! How can I help you today?';
                    KoolboksChat.addMessage('bot', welcomeMsg);
                }
                
                // Focus on input
                $('.koolboks-chat-input').focus();
            }
        },
        
        closeChat: function(e) {
            e.preventDefault();
            $('.koolboks-chat-window').removeClass('open');
        },
        
        sendMessage: function() {
            const input = $('.koolboks-chat-input');
            const query = input.val().trim();
            
            if (!query || isTyping) return;
            
            // Add user message
            KoolboksChat.addMessage('user', query);
            input.val('');
            
            // Show typing indicator
            KoolboksChat.showTyping();
            isTyping = true;
            
            // Send to backend
            $.ajax({
                url: koolboksChat.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'koolboks_chat',
                    nonce: koolboksChat.nonce,
                    query: query,
                    session_id: sessionId,
                    chat_history: JSON.stringify(chatHistory)
                },
                success: function(response) {
                    isTyping = false;
                    KoolboksChat.hideTyping();
                    
                    console.log('Full response:', response);
                    
                    if (response.success && response.data) {
                        console.log('Response data:', response.data);
                        
                        // Try multiple possible response field names
                        const botResponse = response.data.response || 
                                          response.data.answer || 
                                          response.data.message ||
                                          (typeof response.data === 'string' ? response.data : null);
                        
                        if (botResponse) {
                            KoolboksChat.addMessage('bot', botResponse);
                            
                            // Log conversation to database
                            KoolboksChat.logConversation(query, botResponse);
                            
                            // Update chat history
                            chatHistory.push({
                                role: 'user',
                                content: query
                            });
                            chatHistory.push({
                                role: 'assistant',
                                content: botResponse
                            });
                        } else {
                            console.error('No response field found in:', response.data);
                            KoolboksChat.addMessage('bot', 'Sorry, I received an invalid response. Please try again.');
                        }
                    } else {
                        console.error('Invalid response format:', response);
                        KoolboksChat.addMessage('bot', 'Sorry, I couldn\'t process your request. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    isTyping = false;
                    KoolboksChat.hideTyping();
                    console.error('Chat error:', error);
                    console.error('XHR:', xhr);
                    console.error('Status:', status);
                    KoolboksChat.addMessage('bot', 'Connection error. Please check your internet and try again.');
                }
            });
        },
        
        addMessage: function(sender, text) {
            const messagesContainer = $('.koolboks-chat-messages');
            const messageClass = sender === 'user' ? 'koolboks-user-message' : 'koolboks-bot-message';
            const timestamp = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            
            const messageHtml = `
                <div class="message ${messageClass}">
                    <div class="message-content">${this.escapeHtml(text)}</div>
                    <div class="message-time">${timestamp}</div>
                </div>
            `;
            
            messagesContainer.append(messageHtml);
            messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
        },
        
        showTyping: function() {
            const typingHtml = `
                <div class="koolboks-typing-indicator">
                    <span></span><span></span><span></span>
                </div>
            `;
            $('.koolboks-chat-messages').append(typingHtml);
            $('.koolboks-chat-messages').scrollTop($('.koolboks-chat-messages')[0].scrollHeight);
        },
        
        hideTyping: function() {
            $('.koolboks-typing-indicator').remove();
        },
        
        logConversation: function(userMessage, botResponse) {
            $.ajax({
                url: koolboksChat.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'koolboks_log_conversation',
                    nonce: koolboksChat.nonce,
                    session_id: sessionId,
                    user_message: userMessage,
                    bot_response: botResponse
                },
                error: function(error) {
                    console.error('Failed to log conversation:', error);
                }
            });
        },
        
        toggleLeadForm: function(e) {
            e.preventDefault();
            $('.koolboks-lead-form-container').toggleClass('show');
        },
        
        submitLead: function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.text();
            
            const formData = {
                action: 'koolboks_capture_lead',
                nonce: koolboksChat.nonce,
                name: form.find('input[name="name"]').val(),
                email: form.find('input[name="email"]').val(),
                phone: form.find('input[name="phone"]').val() || '',
                message: form.find('textarea[name="message"]').val() || '',
                session_id: sessionId
            };
            
            // Validate required fields
            if (!formData.name || !formData.email) {
                alert('Please fill in your name and email address.');
                return;
            }
            
            console.log('Submitting lead:', formData);
            submitBtn.prop('disabled', true).text('Sending...');
            
            $.ajax({
                url: koolboksChat.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    console.log('Lead submission response:', response);
                    
                    if (response.success) {
                        KoolboksChat.addMessage('bot', 'Thank you! We\'ve received your information and will get back to you soon.');
                        form[0].reset();
                        $('.koolboks-lead-form-container').removeClass('show');
                    } else {
                        console.error('Lead submission failed:', response);
                        const errorMsg = response.data && response.data.message 
                            ? response.data.message 
                            : 'There was an error submitting your information. Please try again.';
                        alert(errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Lead submission error:', error);
                    console.error('XHR:', xhr);
                    console.error('Status:', status);
                    
                    let errorMsg = 'Connection error. Please try again later.';
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errorMsg = xhr.responseJSON.data.message;
                    }
                    alert(errorMsg);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        },
        
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML.replace(/\n/g, '<br>');
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        KoolboksChat.init();
    });
    
})(jQuery);
