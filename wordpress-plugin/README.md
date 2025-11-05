# Koolboks AI Chat Assistant - WordPress Plugin

A powerful WordPress plugin that integrates your Koolboks AI chatbot with lead capture and CRM functionality.

## Features

✅ **Floating Chat Widget** - Beautiful, customizable chat button and window  
✅ **Inline Chat Shortcode** - Embed chat anywhere with `[koolboks_chat]`  
✅ **Lead Capture Form** - Collect visitor information with CRM integration  
✅ **Real-time AI Responses** - Powered by your FastAPI backend  
✅ **Mobile Responsive** - Works perfectly on all devices  
✅ **Customizable Styling** - Match your brand colors and positioning  
✅ **Session Management** - Maintains conversation context  
✅ **AJAX-powered** - Smooth, fast interactions without page reloads  

## Installation

### Step 1: Upload Plugin

1. Download or copy the `wordpress-plugin` folder
2. Rename it to `koolboks-chat`
3. Upload to your WordPress site's `/wp-content/plugins/` directory
4. Or zip the folder and upload via WordPress admin: **Plugins > Add New > Upload Plugin**

### Step 2: Activate Plugin

1. Go to **Plugins** in your WordPress admin
2. Find "Koolboks AI Chat Assistant"
3. Click **Activate**

### Step 3: Configure Settings

1. Go to **Koolboks Chat** in the WordPress admin menu
2. Configure the following settings:

   - **API URL**: Your FastAPI backend URL (e.g., `https://api.koolboks.com` or `http://localhost:8000` for testing)
   - **Enable Chat Widget**: Check to show floating chat button on all pages
   - **Widget Position**: Choose bottom-right or bottom-left
   - **Primary Color**: Select your brand color

3. Click **Save Changes**

## Usage

### Floating Widget (Automatic)

When enabled in settings, the chat widget appears automatically on all pages as a floating button in the corner.

### Shortcode (Manual Placement)

Embed chat anywhere using the shortcode:

```php
[koolboks_chat]
```

**With custom options:**

```php
[koolboks_chat title="Chat with Us" height="500px"]
```

**In template files:**

```php
<?php echo do_shortcode('[koolboks_chat]'); ?>
```

## Backend Requirements

Your FastAPI backend must be running and accessible. The plugin expects these endpoints:

- `POST /chat/` - Process chat messages
- `POST /capture-lead/` - Handle lead submissions

### Expected Request/Response Format

**Chat Request:**
```json
{
  "query": "User message",
  "session_id": "wp_1234567890",
  "chat_history": [...],
  "settings": {
    "temperature": 0.7,
    "max_tokens": 500
  }
}
```

**Chat Response:**
```json
{
  "response": "Bot reply message"
}
```

**Lead Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "message": "Interested in products",
  "session_id": "wp_1234567890"
}
```

## Deployment Guide

### For Production Use

1. **Deploy FastAPI Backend**
   - Deploy your FastAPI app to a cloud service (e.g., Railway, Render, AWS, DigitalOcean)
   - Ensure it has a public HTTPS URL (e.g., `https://api.koolboks.com`)
   - Make sure CORS is configured to allow your WordPress domain

2. **Update Plugin Settings**
   - In WordPress admin, go to **Koolboks Chat**
   - Update **API URL** to your production backend URL
   - Save changes

3. **Test the Integration**
   - Visit your WordPress site
   - Click the chat button
   - Send a test message
   - Submit a test lead

### CORS Configuration

Make sure your FastAPI backend allows requests from your WordPress domain. In `main.py`:

```python
from fastapi.middleware.cors import CORSMiddleware

app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "https://yourdomain.com",
        "https://www.yourdomain.com",
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)
```

## Customization

### Custom Colors

The plugin uses your selected primary color for:
- Chat button background
- Chat header
- Send button
- User message bubbles

### Custom CSS

Add custom styles in your theme's CSS:

```css
/* Change chat button size */
.koolboks-chat-button {
    width: 70px !important;
    height: 70px !important;
}

/* Change chat window size */
.koolboks-chat-window {
    width: 400px !important;
    height: 650px !important;
}
```

### Position Override

Force position via CSS:

```css
/* Force bottom-left on mobile only */
@media (max-width: 768px) {
    .koolboks-chat-button,
    .koolboks-chat-window {
        right: auto !important;
        left: 20px !important;
    }
}
```

## Troubleshooting

### Chat not appearing

1. Check that plugin is activated
2. Verify "Enable Chat Widget" is checked in settings
3. Clear browser cache and hard refresh (Ctrl+Shift+R)

### Messages not sending

1. Check API URL is correct in plugin settings
2. Verify FastAPI backend is running and accessible
3. Check browser console (F12) for JavaScript errors
4. Ensure CORS is configured correctly on backend

### Lead form not submitting

1. Check network tab in browser DevTools
2. Verify `/capture-lead/` endpoint is working
3. Check backend logs for errors

### Connection timeout

1. Increase timeout in JavaScript if needed (edit `chat-widget.js`, line with `timeout: 60`)
2. Optimize backend response time
3. Check hosting server performance

## File Structure

```
koolboks-chat/
├── koolboks-chat.php          # Main plugin file
├── assets/
│   ├── css/
│   │   └── chat-widget.css    # Widget styles
│   └── js/
│       └── chat-widget.js     # Widget JavaScript
├── templates/
│   ├── chat-widget.php        # Floating widget template
│   └── chat-inline.php        # Shortcode template
└── README.md                  # This file
```

## Support

For issues or questions:
- Check the troubleshooting section above
- Review browser console and network tab for errors
- Check FastAPI backend logs
- Verify all settings are correct

## Changelog

### Version 1.0.0
- Initial release
- Floating chat widget
- Inline shortcode support
- Lead capture form
- CRM integration
- Mobile responsive design

## License

GPL v2 or later
