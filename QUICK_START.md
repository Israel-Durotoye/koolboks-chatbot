# 🚀 Quick Reference Card

## Start Local Testing

```bash
# Terminal 1: Start backend
python main.py
# Runs on http://localhost:8000

# Terminal 2: Start Streamlit UI
streamlit run app.py
# Opens on http://localhost:8501
```

## Package WordPress Plugin

```bash
# Create plugin zip file
./package-plugin.sh
# Output: dist/koolboks-chat-plugin.zip
```

## WordPress Installation

1. **Upload Plugin**
   - Go to: Plugins → Add New → Upload Plugin
   - Upload: dist/koolboks-chat-plugin.zip
   - Click: Activate

2. **Configure Settings**
   - Go to: Koolboks Chat (in sidebar)
   - Set API URL: Your backend URL
   - Enable: Chat Widget ✅
   - Save Changes

3. **Test**
   - Visit your site
   - Click chat button
   - Send test message

## Shortcode Usage

```
[koolboks_chat]

[koolboks_chat title="Custom Title" height="500px"]
```

## Deploy Backend

### Railway
```bash
1. Push code to GitHub
2. railway.app → New Project → Deploy from GitHub
3. Add env vars: OPENAI_API_KEY, CRM_WEBHOOK_URL
4. Copy deployment URL
5. Update WordPress plugin settings
```

### Render
```bash
1. render.com → New Web Service
2. Connect GitHub repo
3. Build: pip install -r requirements.txt
4. Start: uvicorn main:app --host 0.0.0.0 --port $PORT
5. Add env vars
6. Deploy
```

## Important URLs

- **Local Backend**: http://localhost:8000
- **Local Frontend**: http://localhost:8501
- **Backend Health**: http://localhost:8000/health
- **Zapier Webhook**: https://hooks.zapier.com/hooks/catch/15086232/us9v0tg/

## Test Commands

```bash
# Test backend health
curl http://localhost:8000/health

# Test chat
curl -X POST http://localhost:8000/chat/ \
  -H "Content-Type: application/json" \
  -d '{"query":"What is Koolboks?","session_id":"test","chat_history":[]}'

# Test webhook
python test_webhook.py
```

## File Locations

```
Main Backend ........... main.py
Streamlit UI ........... app.py
WordPress Plugin ....... wordpress-plugin/
Plugin Main File ....... wordpress-plugin/koolboks-chat.php
Plugin Package ......... dist/koolboks-chat-plugin.zip
Environment Vars ....... .env
```

## Common Issues

| Problem | Solution |
|---------|----------|
| Backend won't start | Check `.env` file exists, run `pip install -r requirements.txt` |
| Chat not responding | Verify backend running, check CORS settings |
| Plugin not visible | Enable in Koolboks Chat settings, clear cache |
| CORS error | Add WordPress domain to `allow_origins` in main.py |
| Webhook failing | Run `python test_webhook.py`, check Zapier Zap published |

## CORS Fix

Edit `main.py`, add your WordPress domain:

```python
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "https://yourdomain.com",  # Add your domain
        "http://localhost:8501",
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)
```

## Environment Variables

Required in `.env`:

```env
OPENAI_API_KEY=sk-proj-xxxxx
CRM_WEBHOOK_URL=https://hooks.zapier.com/hooks/catch/xxxxx
```

## Documentation Links

- **Complete Guide**: COMPLETE_GUIDE.md
- **WordPress README**: wordpress-plugin/README.md
- **Deployment Guide**: wordpress-plugin/DEPLOYMENT.md
- **Webhook Setup**: WEBHOOK_SETUP.md

## Support Checklist

Before asking for help:
- [ ] Check all .env variables are set
- [ ] Verify backend is running (curl health endpoint)
- [ ] Check browser console for JavaScript errors
- [ ] Review browser Network tab for failed requests
- [ ] Check backend terminal for error logs
- [ ] Verify CORS includes your domain
- [ ] Confirm OpenAI API key has billing enabled

---

**Need Help?** Check COMPLETE_GUIDE.md for detailed troubleshooting.
