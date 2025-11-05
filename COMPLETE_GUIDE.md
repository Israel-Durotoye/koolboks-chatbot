# Koolboks AI Chat System - Complete Setup Guide

This is your complete Koolboks AI chatbot system with CRM integration and WordPress plugin.

## 🎯 What You Have

### 1. **FastAPI Backend** (Python)
- RAG-powered chatbot using OpenAI GPT-4o-mini
- ChromaDB vector database for document search
- Webhook integration with Zapier/Zoho CRM
- Lead capture and management
- **Location**: Root directory (main.py, query_llm.py, embedder.py, etc.)

### 2. **Streamlit Frontend** (Testing Interface)
- Beautiful UI for testing the chatbot
- Lead capture form
- Document upload capability
- **Location**: app.py

### 3. **WordPress Plugin** (Production Deployment)
- Floating chat widget
- Inline shortcode support
- Lead form integration
- Full CRM connectivity
- **Location**: wordpress-plugin/

## 🚀 Quick Start Guide

### Option 1: Test Locally (Streamlit)

1. **Start the backend**:
   ```bash
   python main.py
   ```
   Backend runs on: http://localhost:8000

2. **Start the frontend** (in a new terminal):
   ```bash
   streamlit run app.py
   ```
   Frontend opens at: http://localhost:8501

3. **Test the chatbot**:
   - Ask questions about Koolboks products
   - Try the lead capture form
   - Upload PDF documents (optional)

### Option 2: Deploy to WordPress (Production)

1. **Deploy the backend** (see DEPLOYMENT.md in wordpress-plugin/)
2. **Package the plugin**:
   ```bash
   ./package-plugin.sh
   ```
3. **Install on WordPress**:
   - Upload `dist/koolboks-chat-plugin.zip` via WordPress admin
   - Configure API URL in settings
   - Enable the widget

## 📁 Project Structure

```
RAG-System-main/
├── main.py                    # FastAPI backend server
├── query_llm.py              # OpenAI integration
├── embedder.py               # Vector search & RAG
├── pdf_extractor.py          # PDF processing
├── webhook_handler.py        # CRM webhook integration
├── app.py                    # Streamlit test interface
├── test_webhook.py           # Webhook testing tool
├── requirements.txt          # Python dependencies
├── .env                      # Environment variables
├── chroma_db/               # Vector database (auto-generated)
├── collection/              # Processed documents
├── rag_docs/               # Upload your PDFs here
├── wordpress-plugin/       # WordPress integration
│   ├── koolboks-chat.php  # Main plugin file
│   ├── assets/
│   │   ├── css/
│   │   │   └── chat-widget.css
│   │   └── js/
│   │       └── chat-widget.js
│   ├── templates/
│   │   ├── chat-widget.php    # Floating widget
│   │   └── chat-inline.php    # Shortcode template
│   ├── README.md
│   └── DEPLOYMENT.md
├── package-plugin.sh        # Plugin packager script
└── README.md
```

## 🔧 Configuration

### Environment Variables (.env)

```env
OPENAI_API_KEY=sk-proj-xxxxx
CRM_WEBHOOK_URL=https://hooks.zapier.com/hooks/catch/xxxxx
```

### System Prompt (Koolboks Persona)

The chatbot is configured as a Koolboks product specialist in `query_llm.py`. It:
- Uses simple, clear language
- Avoids complex terms
- Focuses on practical benefits
- Never uses idioms or metaphors
- Keeps responses concise

## 📊 Features

### ✅ Core Features
- **AI-Powered Responses**: GPT-4o-mini with RAG enhancement
- **Document Search**: ChromaDB vector database
- **Lead Capture**: Integrated form with CRM sync
- **Session Management**: Maintains conversation context
- **Webhook Integration**: Zapier → Zoho CRM
- **Multi-Platform**: Streamlit + WordPress

### ✅ WordPress Plugin Features
- Floating chat widget (customizable position)
- Inline shortcode: `[koolboks_chat]`
- Mobile responsive design
- Custom color theming
- AJAX-powered (no page reloads)
- Lead form with validation

### ✅ CRM Integration
- Automatic lead capture
- Name splitting (first/last)
- Zapier webhook delivery
- Zoho CRM synchronization
- Session tracking

## 🔌 API Endpoints

### Backend (FastAPI)

- `GET /` - Health check
- `POST /chat/` - Send message, get AI response
- `POST /capture-lead/` - Submit lead information
- `POST /upload/` - Upload PDF document
- `GET /health` - System health status

### Request Examples

**Chat Request:**
```bash
curl -X POST http://localhost:8000/chat/ \
  -H "Content-Type: application/json" \
  -d '{
    "query": "Tell me about Koolboks solar systems",
    "session_id": "test_123",
    "chat_history": []
  }'
```

**Lead Capture:**
```bash
curl -X POST http://localhost:8000/capture-lead/ \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "message": "Interested in solar solutions",
    "session_id": "test_123"
  }'
```

## 🧪 Testing

### Test the Backend

```bash
# Start the server
python main.py

# In another terminal, test the webhook
python test_webhook.py
```

### Test the Chatbot

1. Open http://localhost:8501 (Streamlit)
2. Ask: "What is Koolboks?"
3. Try: "Tell me about your solar products"
4. Submit a test lead

### Test WordPress Plugin (Local)

1. Set up local WordPress (XAMPP, Local, or Docker)
2. Install the plugin
3. Set API URL to `http://localhost:8000`
4. Enable CORS for `http://localhost:8080` in main.py
5. Test the chat widget

## 🌐 Deployment Options

### Backend Deployment

1. **Railway** (Easiest)
   - Auto-detects Python
   - Free tier: 500 hours/month
   - One-click GitHub deployment

2. **Render**
   - Free tier: 750 hours/month
   - Auto-sleep after 15 min inactivity
   - Simple configuration

3. **DigitalOcean App Platform**
   - $5/month minimum
   - Better performance
   - No auto-sleep

4. **AWS/GCP/Azure**
   - Most scalable
   - More complex setup
   - Pay-as-you-go

### Frontend Options

1. **WordPress Plugin** (Recommended for production)
   - Professional integration
   - Works on any WordPress site
   - Easy for clients to use

2. **Streamlit Cloud**
   - Good for internal tools
   - Free tier available
   - Limited customization

3. **Custom React/Next.js App**
   - Full control
   - More development time
   - Better performance

## 🛠️ Maintenance

### Updating Documents

1. Add PDFs to `rag_docs/` folder
2. Restart the backend (it auto-processes on startup)
3. Or use the upload endpoint/Streamlit interface

### Updating System Prompt

Edit the system message in `query_llm.py`:

```python
system_message = """
Your new instructions here...
"""
```

### Monitoring

- Check backend logs for errors
- Monitor OpenAI API usage in dashboard
- Review Zapier task history
- Check Zoho CRM for lead delivery

## 💰 Cost Breakdown

### Monthly Costs (Estimated)

- **OpenAI API**: $2-10 (depends on usage, gpt-4o-mini is very cheap)
- **Backend Hosting**: $0-7 (free tier or paid)
- **WordPress Hosting**: Your existing cost
- **Zapier**: Free tier (100 tasks/month) or $20/month
- **Total**: $2-40/month depending on traffic

### Free Tier Limits

- Railway: 500 hours/month, $5 credit/month
- Render: 750 hours/month
- Zapier: 100 tasks/month
- OpenAI: Pay-per-use (no free tier, but very affordable)

## 🐛 Troubleshooting

### Backend won't start

```bash
# Check dependencies
pip install -r requirements.txt

# Check .env file exists
ls -la .env

# Check port availability
lsof -i :8000
```

### Chat not responding

1. Check backend is running: `curl http://localhost:8000/health`
2. Check OpenAI API key is valid
3. Check browser console for errors
4. Verify CORS settings

### Webhook failing

1. Test webhook: `python test_webhook.py`
2. Check Zapier Zap is published
3. Verify webhook URL in .env
4. Check backend logs for errors

### WordPress plugin issues

1. Verify API URL in plugin settings
2. Check CORS allows WordPress domain
3. Open browser DevTools → Console
4. Check Network tab for failed requests

## 📚 Documentation

- **Main README**: This file
- **WordPress Plugin README**: wordpress-plugin/README.md
- **Deployment Guide**: wordpress-plugin/DEPLOYMENT.md
- **Webhook Setup**: WEBHOOK_SETUP.md
- **Zoho Integration**: ZOHO_ZAPIER_SETUP.md

## 🎓 Learning Resources

- [FastAPI Documentation](https://fastapi.tiangolo.com/)
- [Streamlit Documentation](https://docs.streamlit.io/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [OpenAI API Docs](https://platform.openai.com/docs/)
- [ChromaDB Docs](https://docs.trychroma.com/)

## ✅ Production Checklist

Before going live:

- [ ] Environment variables configured
- [ ] Backend deployed with HTTPS
- [ ] CORS configured for production domain
- [ ] OpenAI API key has billing enabled
- [ ] Zapier Zap is published
- [ ] WordPress plugin installed and configured
- [ ] Test chat functionality end-to-end
- [ ] Test lead capture and CRM delivery
- [ ] Monitor logs for first 24 hours
- [ ] Set up error alerting

## 🎉 Success Metrics

Track these to measure success:

- Number of chat interactions
- Lead conversion rate
- Average response time
- User satisfaction (add rating feature)
- CRM lead quality
- API costs vs value

## 📧 Support

For issues:
1. Check the troubleshooting sections
2. Review browser console/network errors
3. Check backend logs
4. Verify all configurations

---

**You now have a complete, production-ready AI chatbot system!** 🚀

Start with local testing, then deploy to WordPress when ready.
