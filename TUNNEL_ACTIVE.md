# 🌐 Your Backend is Now Public!

## ✅ LocalTunnel is Running

**Public URL:** `https://tame-regions-cheer.loca.lt`

This URL allows your WordPress site to access your local backend.

---

## 📋 Setup WordPress Plugin

### Step 1: Update API URL

1. Go to **WordPress Admin**
2. Click **Koolboks Chat** in the sidebar
3. Change **API URL** to:
   ```
   https://tame-regions-cheer.loca.lt
   ```
4. Click **Save Changes**

### Step 2: Test the Chat

1. Visit your WordPress site (frontend, not admin)
2. Look for the chat button in the bottom corner
3. Click it to open the chat
4. Send a test message: "What is Koolboks?"
5. You should get a response! 🎉

### Step 3: Test Lead Form

1. In the chat, click "📝 Leave your contact info"
2. Fill in:
   - Name: Test User
   - Email: test@example.com
   - Phone: (optional)
   - Message: (optional)
3. Click Submit
4. Should see: "Thank you! We've received your information..."

---

## ⚠️ Important Notes

### Keep These Running:
- ✅ **Terminal 1**: Backend (`uvicorn main:app --reload`)
- ✅ **Terminal 2**: LocalTunnel (`lt --port 8000`)

Both must stay running while you test!

### CORS is Already Configured
Your `main.py` has:
```python
allow_origins=["*"]  # Allows all domains
```

This is perfect for testing! For production, you'll want to specify exact domains.

---

## 🧪 Testing the Public URL

### Test Health Endpoint:

Open in browser or use curl:
```bash
curl https://tame-regions-cheer.loca.lt/health
```

Expected response:
```json
{
  "status": "healthy",
  "webhook_configured": true,
  "timestamp": 1234567890
}
```

### Test Chat Endpoint:

```bash
curl -X POST https://tame-regions-cheer.loca.lt/chat/ \
  -H "Content-Type: application/json" \
  -d '{
    "query": "What is Koolboks?",
    "session_id": "test",
    "chat_history": []
  }'
```

---

## 🔧 Troubleshooting

### Chat/Lead Form Not Working?

1. **Open Browser Console** (F12)
   - Look for errors
   - Check Network tab

2. **Verify URLs**:
   - WordPress plugin settings has correct URL
   - URL starts with `https://` (not `http://`)

3. **Check Terminals**:
   - Backend running? (Terminal 1)
   - Tunnel running? (Terminal 2)

4. **Test Health**:
   - Open: `https://tame-regions-cheer.loca.lt/health`
   - Should return JSON, not error

### LocalTunnel Shows Error Page?

First-time visitors to LocalTunnel URLs see a warning page:
- Click "Continue" or "Click to Continue"
- This is normal for LocalTunnel
- After clicking, your chat will work

---

## 📱 Current Setup

```
[WordPress Site] 
       ↓ HTTPS
[LocalTunnel: https://tame-regions-cheer.loca.lt]
       ↓ Local
[Your Backend: http://localhost:8000]
       ↓
[OpenAI API + ChromaDB]
```

---

## 🎯 Next Steps

After confirming it works:

### For Production:
1. Deploy backend to Railway/Render (see DEPLOYMENT.md)
2. Get permanent URL (e.g., `https://your-app.railway.app`)
3. Update WordPress plugin with production URL
4. Remove `allow_origins=["*"]` and specify your WordPress domain

### For Now (Testing):
Just keep the terminals running and enjoy testing your chatbot! 🚀

---

## 🆘 Need Help?

If something's not working:

1. Check `NGROK_SETUP.md` for detailed troubleshooting
2. Check `TROUBLESHOOTING.md` in wordpress-plugin folder
3. Run `python test_backend.py` to test endpoints
4. Look at browser console (F12) for errors

---

**Your backend is live at:** `https://tame-regions-cheer.loca.lt` ✨
