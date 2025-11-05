# 🚀 Deploy Koolboks Backend to Production

## Option 1: Railway (Recommended - Easiest)

### Why Railway?
- ✅ Free tier (500 hours/month + $5 credit)
- ✅ Auto-deploy from GitHub
- ✅ Built-in HTTPS
- ✅ Easy environment variables
- ✅ No credit card needed initially

### Step-by-Step Guide

#### 1. Create a Railway Account

1. Go to https://railway.app
2. Click "Sign up with GitHub"
3. Authorize Railway to access your GitHub

#### 2. Prepare Your Project

Create these files in your project root:

**`Procfile`** (tells Railway how to start your app):
```
web: uvicorn main:app --host 0.0.0.0 --port $PORT
```

**`runtime.txt`** (optional, specifies Python version):
```
python-3.10.12
```

**`.railwayignore`** (files to exclude):
```
__pycache__/
*.pyc
*.pyo
*.log
.env
.DS_Store
chroma_db/
collection/
rag_docs/
dist/
wordpress-plugin/
node_modules/
```

#### 3. Push to GitHub

```bash
# Initialize git if you haven't
git init

# Add all files
git add .

# Commit
git commit -m "Prepare for Railway deployment"

# Create a GitHub repo and push
# (Follow GitHub's instructions to create a new repo)
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git
git branch -M main
git push -u origin main
```

#### 4. Deploy to Railway

1. Go to https://railway.app/new
2. Click **"Deploy from GitHub repo"**
3. Select your repository
4. Railway will auto-detect Python and deploy!

#### 5. Add Environment Variables

In Railway dashboard:

1. Click your project
2. Go to **Variables** tab
3. Add these:

```
OPENAI_API_KEY=sk-proj-your-key-here
CRM_WEBHOOK_URL=https://hooks.zapier.com/hooks/catch/15086232/us9v0tg/
PORT=8000
```

4. Click **Redeploy** after adding variables

#### 6. Get Your Public URL

1. In Railway, go to **Settings** tab
2. Scroll to **Domains**
3. Click **Generate Domain**
4. You'll get a URL like: `https://your-app.up.railway.app`

#### 7. Update WordPress Plugin

1. Go to WordPress Admin → **Koolboks Chat**
2. Update **API URL** to: `https://your-app.up.railway.app`
3. Click **Save Changes**

#### 8. Test Your Deployment

```bash
curl https://your-app.up.railway.app/health
```

Should return:
```json
{"status":"healthy","webhook_configured":true}
```

---

## Option 2: Render (Alternative Free Option)

### Why Render?
- ✅ Free tier (750 hours/month)
- ✅ Auto-deploy from GitHub
- ✅ Built-in HTTPS
- ⚠️ Spins down after 15 min inactivity (slower first request)

### Quick Setup

1. Go to https://render.com
2. Sign up with GitHub
3. Click **New +** → **Web Service**
4. Connect your repository
5. Configure:
   - **Name**: koolboks-chatbot
   - **Build Command**: `pip install -r requirements.txt`
   - **Start Command**: `uvicorn main:app --host 0.0.0.0 --port $PORT`
   - **Environment Variables**: Add OPENAI_API_KEY and CRM_WEBHOOK_URL
6. Click **Create Web Service**
7. Get your URL: `https://koolboks-chatbot.onrender.com`

---

## Option 3: Fly.io (For More Control)

### Setup

1. Install Fly CLI:
```bash
brew install flyctl
```

2. Login:
```bash
fly auth login
```

3. Launch your app:
```bash
fly launch
```

4. Set secrets:
```bash
fly secrets set OPENAI_API_KEY=your-key
fly secrets set CRM_WEBHOOK_URL=your-webhook-url
```

5. Deploy:
```bash
fly deploy
```

---

## Option 4: DigitalOcean App Platform

### Setup

1. Go to https://www.digitalocean.com/products/app-platform
2. Sign up (needs credit card but $200 free credit)
3. Create app from GitHub
4. Configure:
   - **Run Command**: `uvicorn main:app --host 0.0.0.0 --port $PORT`
   - Add environment variables
5. Deploy
6. Get URL: `https://your-app.ondigitalocean.app`

---

## Comparison

| Service | Free Tier | Cold Starts | Speed | Ease |
|---------|-----------|-------------|-------|------|
| **Railway** | 500h/month + $5 | ❌ No | ⚡ Fast | 🟢 Easiest |
| **Render** | 750h/month | ✅ Yes (15min) | 🐌 Slow first | 🟢 Easy |
| **Fly.io** | 3 VMs free | ❌ No | ⚡ Fast | 🟡 Medium |
| **DigitalOcean** | $200 credit | ❌ No | ⚡ Fast | 🟡 Medium |

**Recommendation**: Start with **Railway** for simplest deployment!

---

## After Deployment

### 1. Update CORS (Important!)

In `main.py`, update CORS to use your WordPress domain:

```python
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "https://your-wordpress-site.com",
        "https://www.your-wordpress-site.com",
        # Remove "*" for security in production!
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)
```

### 2. Update WordPress Plugin

1. WordPress Admin → Koolboks Chat
2. API URL: `https://your-app.railway.app` (or your deployed URL)
3. Save Changes

### 3. Test Everything

- Health check: `https://your-app.railway.app/health`
- Chat from WordPress site
- Lead capture form
- Check Zoho CRM for leads

---

## Monitoring & Logs

### Railway
- Dashboard → Your Project → Logs tab
- Real-time logs of requests and errors

### Render
- Dashboard → Your Service → Logs
- Shows deployment and runtime logs

### Fly.io
```bash
fly logs
```

---

## Cost Estimates

### Free Tier Usage:
- **Railway**: ~$2-5/month in OpenAI costs (backend hosting free)
- **Render**: ~$2-5/month in OpenAI costs (backend hosting free)

### After Free Tier:
- **Railway**: $5/month + OpenAI costs
- **Render**: $7/month + OpenAI costs
- **Fly.io**: $5-10/month + OpenAI costs

**OpenAI costs** (gpt-4o-mini):
- ~1000 messages: $0.50-$2
- Very affordable for most use cases

---

## Troubleshooting

### Build Fails
- Check `requirements.txt` has all dependencies
- Make sure Python version matches (3.10+)

### App Crashes
- Check environment variables are set
- View logs in platform dashboard
- Test locally first: `uvicorn main:app --reload`

### CORS Errors
- Add WordPress domain to `allow_origins`
- Restart app after changing CORS

### Database Issues
- ChromaDB will auto-create on first start
- Upload initial PDFs after deployment

---

## Next Steps

1. **Choose a platform** (Railway recommended)
2. **Push code to GitHub**
3. **Deploy to platform**
4. **Add environment variables**
5. **Get public URL**
6. **Update WordPress plugin**
7. **Test everything**
8. **You're live!** 🎉

Need help? Check the platform-specific docs or ask me!
