# Koolboks Chat WordPress Plugin - Version 2.0

## 🎉 What's New in Version 2.0

### Complete Admin Interface Overhaul

The WordPress plugin has been upgraded with a beautiful, professional admin interface featuring:

## 📋 Admin Pages

### 1. Settings Page ⚙️
**Modern card-based layout with two main sections:**

#### Connection Settings Card
- **API URL**: Configure your backend endpoint
- **Enable Chat Toggle**: Custom animated toggle switch to enable/disable chat
- Clean, professional styling with smooth animations

#### Appearance Settings Card
- **Chat Title**: Customize the chat window title (default: "Koolboks Chat")
- **Welcome Message**: Set the first message users see
- **Chat Position**: Choose bottom-right or bottom-left
- **Brand Color**: Color picker for customizing your brand color
- **Chat Icon**: Set custom icon for chat button (default: 💬)

**Design Features:**
- Responsive grid layout
- Box shadows and modern spacing
- Custom toggle switches with animations
- Professional color scheme
- Success notifications

### 2. Chat Logs Page 📊
**View all conversations in one place:**

- **Automatic Logging**: All conversations saved to database
- **Table View**: Clean table showing:
  - Date and time (formatted)
  - Session ID (truncated)
  - User message (first 100 chars)
  - Bot response (first 100 chars)
  - IP address
- **Refresh Button**: Reload latest conversations
- **Database**: Auto-creates `wp_koolboks_chat_logs` table
- **Session Tracking**: Unique session ID for each conversation

### 3. Knowledge Base Page 📚
**Upload and manage PDF documents:**

- **File Upload Interface**: Drag-and-drop or click to upload
- **Multiple File Support**: Upload multiple PDFs at once
- **Progress Indicators**: Visual progress bar with status messages
- **Document List**: View all uploaded documents
- **Backend Integration**: Direct upload to FastAPI backend
- **Real-time Feedback**: Success/error messages

### 4. Instructions Page ✏️
**Customize chatbot behavior:**

- **Large Textarea Editor**: Write custom instructions for the chatbot
- **Save Functionality**: Persist instructions to database
- **Example Template**: Built-in example to get started:
  ```
  You are a helpful Koolboks product specialist.
  
  Guidelines:
  - Use simple, clear language
  - Focus on solar solutions and refrigeration products
  - Be friendly and professional
  - Recommend products based on customer needs
  - If unsure, offer to connect them with a sales representative
  
  Always prioritize customer satisfaction and accurate information.
  ```
- **Tips Section**: Helpful guidance on writing effective instructions

## 🏷️ Branding Changes

### Removed AI References
- Plugin name: "Koolboks AI Chat Assistant" → **"Koolboks Chat"**
- Removed "AI-powered" from descriptions
- Professional, clean branding
- Focus on Koolboks brand identity

### Customizable UI Elements
- Custom chat window title
- Custom welcome message
- Custom chat button icon
- Fully branded experience

## 🔧 Technical Improvements

### Database Integration
**New table:** `wp_koolboks_chat_logs`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint(20) | Primary key |
| session_id | varchar(255) | Session identifier |
| user_message | text | User's query |
| bot_response | text | Bot's answer |
| ip_address | varchar(100) | User IP |
| created_at | datetime | Timestamp |

### New AJAX Endpoints
- `koolboks_log_conversation` - Save conversations to database
- Non-blocking, async logging
- Error handling and validation

### Enhanced Settings System
**New Settings:**
- `koolboks_chat_title` - Chat window title
- `koolboks_welcome_message` - Initial greeting
- `koolboks_chat_icon` - Chat button icon
- `koolboks_custom_instructions` - Chatbot behavior instructions
- `koolboks_knowledge_base_docs` - Uploaded documents metadata

### Frontend Enhancements
- Welcome message now customizable from settings
- Chat title dynamically loaded
- Icon customization supported
- Better error handling

## 🎨 Design Language

### Consistent Modern Aesthetic
All admin pages feature:
- **Card-based layout**: Clean, organized sections
- **Professional spacing**: Consistent padding and margins
- **Box shadows**: Subtle depth and hierarchy
- **Smooth animations**: Toggle switches, hover effects
- **Responsive design**: Works on all screen sizes
- **Brand color integration**: Your color throughout the UI

### Custom CSS Features
```css
/* Toggle switches */
.koolboks-toggle {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

/* Cards */
.koolboks-settings-card {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* Grid layout */
.koolboks-settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}
```

## 📦 Installation & Upgrade

### For New Installations
1. Upload plugin to `/wp-content/plugins/koolboks-chat/`
2. Activate in WordPress admin
3. Go to **Koolboks Chat → Settings**
4. Configure API URL and appearance
5. Enable chat and save

### Upgrading from v1.0
1. Deactivate old plugin
2. Upload new v2.0 files
3. Reactivate plugin
4. Database table will auto-create on first use
5. Review new settings and customize

## 🚀 Quick Start Guide

### Step 1: Configure Connection
1. Go to **Koolboks Chat → Settings**
2. Enter your API URL (e.g., `https://f1686ff58019.ngrok-free.app`)
3. Toggle "Enable Chat" ON
4. Save changes

### Step 2: Customize Appearance
1. Set **Chat Title**: "Koolboks Support" or your preference
2. Set **Welcome Message**: "Hello! How can we help you today?"
3. Choose **Position**: bottom-right or bottom-left
4. Pick **Brand Color**: Your company color
5. Set **Chat Icon**: 💬 or any emoji
6. Save changes

### Step 3: Add Instructions (Optional)
1. Go to **Koolboks Chat → Instructions**
2. Write custom instructions for chatbot behavior
3. Use the example template as a starting point
4. Save instructions

### Step 4: Upload Documents (Optional)
1. Go to **Koolboks Chat → Knowledge Base**
2. Click "Choose Files" and select PDFs
3. Click "Upload Documents"
4. Wait for confirmation

### Step 5: Monitor Conversations
1. Go to **Koolboks Chat → Chat Logs**
2. View all conversations with timestamps
3. Click "Refresh" to see latest chats
4. Review user queries and bot responses

## 🔍 What Happens Behind the Scenes

### When a User Sends a Message:
1. Message sent via AJAX to WordPress
2. WordPress forwards to FastAPI backend
3. Backend processes with custom instructions (if set)
4. Response returned to WordPress
5. **Conversation logged to database** (NEW!)
6. Response displayed to user
7. Chat history maintained in session

### When Viewing Chat Logs:
1. Query `wp_koolboks_chat_logs` table
2. Sort by `created_at` DESC (newest first)
3. Limit to 100 recent conversations
4. Display in formatted table
5. Truncate long messages for readability

### When Uploading Documents:
1. Files selected in browser
2. FormData created with files
3. AJAX POST to backend `/upload/` endpoint
4. Backend processes PDFs with ChromaDB
5. Success/error response shown
6. Documents available to chatbot immediately

## 📊 Feature Comparison

| Feature | v1.0 | v2.0 |
|---------|------|------|
| Floating chat widget | ✅ | ✅ |
| Lead capture form | ✅ | ✅ |
| Settings page | ✅ | ✅ Enhanced |
| Chat logs | ❌ | ✅ NEW |
| Knowledge base management | ❌ | ✅ NEW |
| Custom instructions editor | ❌ | ✅ NEW |
| Modern admin UI | ❌ | ✅ NEW |
| Database logging | ❌ | ✅ NEW |
| Customizable title/message | ❌ | ✅ NEW |
| Custom chat icon | ❌ | ✅ NEW |
| Professional branding | ❌ | ✅ NEW |
| Card-based layout | ❌ | ✅ NEW |
| Toggle switches | ❌ | ✅ NEW |

## 🎯 Benefits of v2.0

### For Administrators
- **Better visibility**: See all conversations at a glance
- **Easy customization**: No code needed to change appearance
- **Document management**: Upload knowledge base from WordPress
- **Professional UI**: Modern, intuitive interface
- **Full control**: Customize every aspect of the chat

### For Users
- **Personalized experience**: Custom welcome messages
- **Branded interface**: Matches your company identity
- **Better responses**: Custom instructions improve accuracy
- **Richer knowledge**: Uploaded documents enhance chatbot knowledge

### For Developers
- **Clean code**: Well-organized, documented
- **Extensible**: Easy to add new features
- **Database integration**: Proper WordPress database handling
- **Security**: Nonces, sanitization, validation
- **Modern patterns**: AJAX, responsive design, modular code

## 🔐 Security Enhancements

- All AJAX requests protected with nonces
- Input sanitization on all fields
- SQL injection prevention (wpdb prepared statements)
- XSS protection (esc_html, esc_attr, esc_textarea)
- CSRF protection on all forms
- IP logging for security auditing

## 📝 Next Steps

1. **Test the new admin interface**: Explore all 4 tabs
2. **Customize your chat**: Set title, message, colors
3. **Upload documents**: Add your product PDFs
4. **Write instructions**: Guide chatbot behavior
5. **Monitor conversations**: Check chat logs regularly
6. **Gather feedback**: See what users are asking
7. **Refine instructions**: Improve responses based on logs

---

**Version 2.0.0** brings professional-grade chat management to your WordPress site. The beautiful admin interface, comprehensive logging, and customization options make it easier than ever to provide excellent customer support through your chat assistant.

Enjoy the upgrade! 🚀
