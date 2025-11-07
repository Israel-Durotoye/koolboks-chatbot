# Changelog

## Version 2.0.0 - Major UI Upgrade (2024)

### 🎨 Complete Admin Interface Redesign
- **Beautiful Modern Admin Dashboard**: Card-based layout with professional styling
- **4-Tab Admin Structure**: Settings, Chat Logs, Knowledge Base, and Custom Instructions
- **Settings Page**:
  - Connection Settings card (API URL, Enable/Disable toggle)
  - Appearance Settings card (Title, Welcome Message, Position, Brand Color, Chat Icon)
  - Custom toggle switches with smooth animations
  - Color picker for brand customization
  - Responsive grid layout with shadows and professional spacing

### 📊 New Chat Logs Feature
- **Database Integration**: Automatic conversation logging to wp_koolboks_chat_logs table
- **View Chat History**: See all recent conversations with timestamps
- **Session Tracking**: Each conversation tracked with unique session IDs
- **IP Logging**: Track user IP addresses for analytics
- **Table View**: Clean table interface showing date, session, messages, and IPs

### 📚 Knowledge Base Management
- **Document Upload Interface**: Upload PDF documents directly from WordPress admin
- **Progress Indicators**: Visual upload progress with status messages
- **Document List**: View all uploaded documents
- **Direct Backend Integration**: Uploads sent to FastAPI backend for processing

### ✏️ Custom Instructions Editor
- **Textarea Editor**: Large text area for custom chatbot instructions
- **Save Functionality**: Persist custom instructions to database
- **Example Instructions**: Built-in example to help users get started
- **Tips Section**: Helpful guidance on writing effective instructions

### 🏷️ Branding Updates
- **Plugin Name**: Changed from "Koolboks AI Chat Assistant" to "Koolboks Chat"
- **Removed AI References**: Professional branding without "AI-powered" mentions
- **Customizable Chat Title**: Admin can set custom chat window title
- **Custom Welcome Message**: Personalize the first message users see
- **Custom Chat Icon**: Choose your own chat button icon (default: 💬)

### 🔧 Technical Improvements
- **Settings Expansion**: 10+ new configurable options
- **Database Schema**: New chat logs table with auto-creation
- **AJAX Logging**: Non-blocking conversation logging via AJAX
- **Enhanced Error Handling**: Better validation and error messages
- **Modern CSS**: Box shadows, border-radius, custom animations

### 🎯 User Experience
- **Consistent Design Language**: All admin pages match modern card-based aesthetic
- **Responsive Layout**: Works on all screen sizes
- **Professional Polish**: Attention to spacing, colors, and visual hierarchy
- **Intuitive Navigation**: Clear tab structure for different functions

---

## Version 1.0.0 - Initial Release

### Core Features
- Floating chat widget with smooth animations
- AJAX-powered real-time chat
- Lead capture form with name, email, phone, message
- Shortcode support: `[koolboks_chat]`
- Position customization (bottom-right, bottom-left)
- Brand color customization
- CRM webhook integration
- Comprehensive error logging
- Mobile-responsive design

### Technical Stack
- WordPress plugin architecture
- jQuery AJAX for async communication
- Custom CSS with animations
- PHP 7.4+ compatible
- FastAPI backend integration
