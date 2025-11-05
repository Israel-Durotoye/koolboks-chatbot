# Expose Backend with ngrok or LocalTunnel

Your WordPress site needs to access your local backend. Here are two options:

## Option 1: ngrok (Recommended - More Stable)

### Step 1: Sign up for ngrok (Free)
1. Go to https://dashboard.ngrok.com/signup
2. Sign up with Google/GitHub or email
3. It's completely free for basic use

### Step 2: Get your authtoken
1. After signup, go to https://dashboard.ngrok.com/get-started/your-authtoken
2. Copy your authtoken (looks like: `2abc123...`)

### Step 3: Configure ngrok
```bash
ngrok config add-authtoken YOUR_TOKEN_HERE
```

### Step 4: Start ngrok tunnel
```bash
ngrok http 8000
```

You'll see output like:
```
Forwarding   https://abc123.ngrok-free.app -> http://localhost:8000
```

### Step 5: Use the HTTPS URL
Copy the `https://` URL (e.g., `https://abc123.ngrok-free.app`)

**Important**: Use the HTTPS URL, not HTTP!

---

## Option 2: LocalTunnel (No Signup Required)

### Step 1: Install LocalTunnel
```bash
npm install -g localtunnel
```

Or if you have Homebrew:
```bash
brew install localtunnel
```

### Step 2: Start tunnel
```bash
lt --port 8000
```

You'll see:
```
your url is: https://rare-cat-12.loca.lt
```

### Step 3: Use the URL
Copy the URL shown (e.g., `https://rare-cat-12.loca.lt`)

**Note**: LocalTunnel URLs change each time you restart, ngrok keeps the same URL (with free plan, it changes on restart too).

---

## Option 3: Use Both (Testing vs Production)

For **local testing**:
- Use LocalTunnel (quick, no signup)

For **stable development**:
- Use ngrok (more reliable, same URL)

---

## After Getting Your Public URL

### Step 1: Update WordPress Plugin Settings
1. Go to WordPress Admin → **Koolboks Chat**
2. Change **API URL** to your ngrok/LocalTunnel URL
   - Example: `https://abc123.ngrok-free.app`
   - Or: `https://rare-cat-12.loca.lt`
3. Click **Save Changes**

### Step 2: Update CORS in main.py

Since your WordPress site can now access the backend, you need to allow CORS:

```python
# In main.py, find the CORS middleware section and update:

app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "https://your-wordpress-domain.com",  # Your WordPress site
        "http://localhost:8501",              # Streamlit
        "*"  # Allow all (for testing only - remove in production!)
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)
```

**For testing**, you can use `"*"` to allow all origins. **For production**, specify exact domains!

### Step 3: Restart Backend
After updating CORS:
```bash
# Stop the backend (Ctrl+C in the terminal running uvicorn)
# Then restart:
uvicorn main:app --reload
```

### Step 4: Keep ngrok/LocalTunnel Running
The tunnel must stay running while you test WordPress. Don't close that terminal!

---

## Quick Start Commands

### Using ngrok (after setup):
```bash
# Terminal 1: Start backend
uvicorn main:app --reload

# Terminal 2: Start ngrok
ngrok http 8000

# Copy the https URL from ngrok
# Paste into WordPress plugin settings
```

### Using LocalTunnel:
```bash
# Terminal 1: Start backend
uvicorn main:app --reload

# Terminal 2: Start LocalTunnel
lt --port 8000

# Copy the URL shown
# Paste into WordPress plugin settings
```

---

## Testing the Setup

### Step 1: Verify tunnel is working
Open your ngrok/LocalTunnel URL in browser:
- `https://your-url/health`
- You should see: `{"status":"healthy","webhook_configured":true}`

### Step 2: Test in WordPress
1. Open your WordPress site
2. Click the chat widget
3. Send a test message
4. It should work! 🎉

### Step 3: Test lead form
1. Click "📝 Leave your contact info"
2. Fill in details
3. Submit
4. Should see success message

---

## Troubleshooting

### Issue: ngrok shows "ERR_NGROK_4018"
**Solution**: You need to sign up and add authtoken (see Option 1 above)

### Issue: LocalTunnel not found
**Solution**: Install with `npm install -g localtunnel`

### Issue: WordPress shows CORS error
**Solution**: 
1. Update `allow_origins` in `main.py` to include `"*"` for testing
2. Restart backend
3. Refresh WordPress page

### Issue: "Connection refused" in WordPress
**Solution**:
1. Make sure backend is running
2. Make sure ngrok/LocalTunnel is running
3. Check you're using HTTPS URL (not HTTP)
4. Test the health endpoint: `https://your-url/health`

### Issue: ngrok URL keeps changing
**Solution**: 
- Free ngrok changes URL on restart
- Paid ngrok ($8/month) gives you a permanent URL
- Or use a free deployment service (Railway, Render) for a permanent URL

---

## Recommended Workflow

**For Quick Testing** (Now):
1. Use LocalTunnel (no signup needed)
2. `lt --port 8000`
3. Copy URL to WordPress settings
4. Test your chat widget

**For Longer Development**:
1. Sign up for ngrok (free)
2. Configure authtoken
3. `ngrok http 8000`
4. More stable than LocalTunnel

**For Production**:
1. Deploy to Railway/Render (see DEPLOYMENT.md)
2. Get permanent HTTPS URL
3. No need to keep laptop running
4. More reliable for real users

---

## What URL to Use Where

| Location | URL Type | Example |
|----------|----------|---------|
| WordPress Plugin Settings | ngrok/LocalTunnel HTTPS | `https://abc123.ngrok-free.app` |
| Streamlit (local testing) | localhost | `http://localhost:8000` |
| Browser testing | ngrok/LocalTunnel HTTPS | `https://abc123.ngrok-free.app/health` |
| Production | Deployed URL | `https://api.koolboks.com` |

---

## Next Steps

Choose your option:
1. **ngrok** - Go to https://dashboard.ngrok.com/signup
2. **LocalTunnel** - Run `npm install -g localtunnel` then `lt --port 8000`

Then update WordPress plugin settings with your public URL! 🚀
