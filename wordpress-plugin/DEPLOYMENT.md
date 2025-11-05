# WordPress Plugin Deployment Guide

This guide walks you through deploying your Koolboks chatbot to WordPress.

## Quick Start (5 Steps)

### 1. Prepare the Plugin

The plugin is ready to use in the `wordpress-plugin` folder. To install:

```bash
# Navigate to the plugin folder
cd /Users/durotoyejoshua/Desktop/RAG-System-main

# Create a zip file for upload
zip -r koolboks-chat.zip wordpress-plugin/
```

### 2. Deploy Your FastAPI Backend

Before installing the WordPress plugin, you need a publicly accessible backend.

#### Option A: Deploy to Railway (Recommended)

1. Go to [railway.app](https://railway.app)
2. Sign up/login with GitHub
3. Click "New Project" → "Deploy from GitHub repo"
4. Select your repository
5. Railway will auto-detect Python and deploy
6. Add environment variables:
   - `OPENAI_API_KEY`: Your OpenAI key
   - `CRM_WEBHOOK_URL`: Your Zapier webhook URL
7. Get your deployment URL (e.g., `https://your-app.railway.app`)

#### Option B: Deploy to Render

1. Go to [render.com](https://render.com)
2. Create a new "Web Service"
3. Connect your GitHub repository
4. Configure:
   - **Build Command**: `pip install -r requirements.txt`
   - **Start Command**: `uvicorn main:app --host 0.0.0.0 --port $PORT`
5. Add environment variables (same as above)
6. Deploy and get your URL

#### Option C: Deploy to DigitalOcean App Platform

1. Go to [digitalocean.com/products/app-platform](https://www.digitalocean.com/products/app-platform)
2. Create app from GitHub
3. Configure Python app
4. Add environment variables
5. Deploy

### 3. Update CORS Settings

Edit your `main.py` to allow WordPress domain:

```python
from fastapi.middleware.cors import CORSMiddleware

app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "https://yourdomain.com",        # Your WordPress site
        "https://www.yourdomain.com",
        "http://localhost:3000",         # For testing
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)
```

Redeploy after making this change.

### 4. Install WordPress Plugin

#### Method 1: Upload via WordPress Admin (Easy)

1. Log into your WordPress admin panel
2. Go to **Plugins → Add New → Upload Plugin**
3. Upload the `koolboks-chat.zip` file
4. Click **Install Now**
5. Click **Activate Plugin**

#### Method 2: FTP/SFTP Upload

1. Connect to your WordPress site via FTP
2. Navigate to `/wp-content/plugins/`
3. Upload the entire `wordpress-plugin` folder
4. Rename it to `koolboks-chat`
5. Go to WordPress admin → **Plugins** → Find "Koolboks AI Chat Assistant" → **Activate**

#### Method 3: cPanel File Manager

1. Log into cPanel
2. Go to **File Manager**
3. Navigate to `public_html/wp-content/plugins/`
4. Upload the zip file and extract it
5. Rename folder to `koolboks-chat`
6. Activate in WordPress admin

### 5. Configure Plugin Settings

1. In WordPress admin, go to **Koolboks Chat** in the sidebar
2. Configure:
   - **API URL**: Your deployed backend URL (e.g., `https://your-app.railway.app`)
   - **Enable Chat Widget**: ✅ Check this
   - **Widget Position**: Bottom Right (or your preference)
   - **Primary Color**: #0066cc (or your brand color)
3. Click **Save Changes**

## Testing

### Test the Chat Widget

1. Visit your WordPress site (not the admin)
2. Look for the chat button in the bottom corner
3. Click it to open the chat window
4. Send a test message like "What is Koolboks?"
5. Verify you get a response from the AI

### Test Lead Capture

1. Click the chat button
2. Click "📝 Leave your contact info"
3. Fill out the form with test data
4. Submit
5. Check your Zoho CRM to verify the lead was captured

### Troubleshooting

If chat doesn't work:

1. **Open browser DevTools** (F12)
2. Go to **Console** tab - check for JavaScript errors
3. Go to **Network** tab - send a message and look for failed requests
4. Common issues:
   - CORS error → Update `allow_origins` in `main.py`
   - 404 error → Check API URL in plugin settings
   - Timeout → Backend might be sleeping (free tier) or slow

## Using the Shortcode

Add chat to any page/post:

```
[koolboks_chat]
```

Or with custom options:

```
[koolboks_chat title="Ask Us Anything" height="500px"]
```

## Production Checklist

- [ ] FastAPI backend deployed with HTTPS
- [ ] Environment variables configured (OPENAI_API_KEY, CRM_WEBHOOK_URL)
- [ ] CORS configured for WordPress domain
- [ ] Plugin installed and activated
- [ ] API URL updated in plugin settings
- [ ] Test chat functionality
- [ ] Test lead capture
- [ ] Verify CRM integration
- [ ] Check mobile responsiveness
- [ ] Monitor backend performance

## Maintenance

### Updating the Plugin

1. Make changes to plugin files
2. Increase version number in `koolboks-chat.php`
3. Zip the updated folder
4. In WordPress: Deactivate → Delete → Upload new version → Activate

### Monitoring

Keep an eye on:
- Backend uptime and response times
- Error logs in your hosting platform
- CRM lead submissions
- User feedback on chat quality

## Cost Estimates

### Free Tier Options

- **Backend**: Railway (500 hours/month free) or Render (750 hours/month free)
- **OpenAI**: Pay-per-use (gpt-4o-mini is very cheap, ~$0.15 per 1M tokens)
- **WordPress**: Your existing hosting

### Expected Costs (Low Traffic)

- 1000 messages/month: ~$2-5 in OpenAI costs
- Backend hosting: Free (on Railway/Render free tier)
- **Total**: ~$2-5/month

### Scaling Up

When you outgrow free tiers:
- Railway Pro: $5/month
- Render Standard: $7/month
- DigitalOcean: $5/month

## Support

If you encounter issues:

1. Check the README.md troubleshooting section
2. Review browser console errors
3. Check backend logs in your hosting dashboard
4. Verify all URLs and API keys are correct

## Next Steps

After successful deployment:

1. **Customize branding** - Update colors, welcome message
2. **Add FAQs** - Upload PDF documents with common questions
3. **Monitor performance** - Track response times and user satisfaction
4. **Iterate** - Improve based on real user interactions
5. **Scale** - Upgrade hosting as traffic grows

---

**Congratulations!** 🎉 Your Koolboks AI chatbot is now live on WordPress!
