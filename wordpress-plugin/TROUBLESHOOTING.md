# WordPress Plugin Troubleshooting Guide

## Common Issues

### Issue 1: "Sorry, I couldn't process your request. Please try again."

This error means the WordPress plugin is not receiving a valid response from your backend for chat messages.

### Issue 2: Lead Form Not Submitting

If the lead capture form shows an error or doesn't submit, follow the debugging steps below.

---

## Debugging Chat Issues

### Step 1: Check Browser Console

1. Open your WordPress site with the chat widget
2. Press **F12** to open DevTools
3. Go to the **Console** tab
4. Send a chat message
5. Look for these log messages:

```
Full response: {...}
Response data: {...}
```

If you see errors instead, that tells us what's wrong.

### Step 2: Check Network Tab

1. In DevTools, go to the **Network** tab
2. Send a chat message
3. Look for the request to `admin-ajax.php`
4. Click on it to see:
   - **Request Payload**: What WordPress is sending
   - **Response**: What it's receiving

### Step 3: Check WordPress Debug Log

Enable WordPress debugging to see PHP errors:

1. Edit `wp-config.php` in your WordPress root
2. Add these lines before `/* That's all, stop editing! */`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

3. Check the log file at `wp-content/debug.log`
4. Look for lines containing "Koolboks"

### Step 4: Test Backend Directly

Test if your backend is working:

```bash
# Test from your server/local machine
curl -X POST http://localhost:8000/chat/ \
  -H "Content-Type: application/json" \
  -d '{
    "query": "What is Koolboks?",
    "session_id": "test_123",
    "chat_history": [],
    "settings": {
      "temperature": 0.7,
      "max_tokens": 500
    }
  }'
```

Expected response:
```json
{
  "response": "Koolboks is...",
  "context": "...",
  "processing_time": 1.23
}
```

### Step 5: Check CORS Settings

If your WordPress site is on a different domain than your backend, you need CORS configured.

In `main.py`, make sure your WordPress domain is in `allow_origins`:

```python
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "https://your-wordpress-site.com",  # Add this!
        "https://www.your-wordpress-site.com",
        "http://localhost:8501",
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)
```

Restart your backend after changing this.

### Step 6: Verify Plugin Settings

1. Go to WordPress Admin → **Koolboks Chat**
2. Check **API URL** is correct:
   - For local testing: `http://localhost:8000`
   - For production: `https://your-backend-url.com`
3. Make sure **Enable Chat Widget** is checked
4. Click **Save Changes**

## Common Issues and Solutions

### Issue: CORS Error in Console

**Error**: `Access to XMLHttpRequest blocked by CORS policy`

**Solution**: 
1. Add your WordPress domain to `allow_origins` in `main.py`
2. Restart your backend
3. Clear browser cache

### Issue: Connection Timeout

**Error**: `Connection error. Please check your internet`

**Solution**:
1. Check if backend is running: `curl http://localhost:8000/health`
2. Increase timeout in `koolboks-chat.php` (currently 60 seconds)
3. Check your hosting allows outbound connections

### Issue: 404 Error

**Error**: Network tab shows 404 for `/chat/`

**Solution**:
1. Verify API URL in plugin settings
2. Make sure backend is running
3. Check backend logs for errors

### Issue: 500 Internal Server Error

**Error**: Backend returns 500 status

**Solution**:
1. Check backend terminal/logs for Python errors
2. Verify OpenAI API key is valid
3. Check if ChromaDB is initialized properly
4. Run: `python main.py` and look for startup errors

### Issue: Empty Response

**Error**: Console shows `Response data: {}`

**Solution**:
The backend returned an empty object. Check:
1. Backend logs for errors
2. OpenAI API quota/billing
3. Network connectivity between WordPress and backend

### Issue: Invalid JSON

**Error**: `Invalid JSON response from API`

**Solution**:
1. Backend is returning non-JSON (maybe HTML error page)
2. Check `wp-content/debug.log` for the actual response
3. Test backend directly with curl

## Debugging Checklist

Use this checklist to systematically debug:

- [ ] Backend is running (`curl http://localhost:8000/health` returns 200)
- [ ] Backend logs show no errors
- [ ] API URL in WordPress settings is correct
- [ ] WordPress debug.log shows no PHP errors
- [ ] Browser console shows the AJAX request
- [ ] Network tab shows 200 response from admin-ajax.php
- [ ] Console logs show the response data structure
- [ ] CORS is configured if WordPress and backend are on different domains
- [ ] OpenAI API key is valid and has billing enabled
- [ ] Chat history format is compatible

## Testing the Fix

After making changes:

1. **Clear all caches**:
   - Browser cache (Ctrl+Shift+Delete)
   - WordPress cache (if using caching plugin)
   - CDN cache (if using CDN)

2. **Hard refresh**: Ctrl+Shift+R (or Cmd+Shift+R on Mac)

3. **Test in incognito**: Open in private/incognito window

4. **Check logs again**: Look at both WordPress debug.log and backend logs

## Getting More Help

If still not working:

1. Collect these details:
   - Browser console errors (screenshot)
   - Network tab response (screenshot)
   - WordPress debug.log (last 50 lines)
   - Backend logs (last 50 lines)
   - Plugin settings (API URL, etc.)

2. Check that:
   - Backend URL is accessible from WordPress server
   - No firewall blocking the connection
   - SSL certificates are valid (if using HTTPS)

## Quick Fixes

Try these quick fixes in order:

1. **Restart backend**: Stop and start `python main.py`
2. **Deactivate/reactivate plugin**: WordPress Admin → Plugins
3. **Clear WordPress cache**: If using caching plugin
4. **Test with simple query**: Just send "hi"
5. **Check backend health**: Visit `http://your-backend/health`

## Success Indicators

You'll know it's working when:

- ✅ Console shows: `Full response: {success: true, data: {...}}`
- ✅ Console shows: `Response data: {response: "...", context: "...", ...}`
- ✅ Chat shows the bot's response
- ✅ No errors in browser console
- ✅ No errors in WordPress debug.log
- ✅ No errors in backend logs

---

**Still stuck?** Check the main README.md and DEPLOYMENT.md for additional guidance.

## Debugging Lead Form Issues

### Step 1: Check Browser Console

1. Open DevTools (F12)
2. Go to Console tab
3. Submit the lead form
4. Look for these logs:

```
Submitting lead: {name: "...", email: "...", ...}
Lead submission response: {...}
```

### Step 2: Check for Validation Errors

The form requires:
- **Name**: Must not be empty
- **Email**: Must be a valid email address

If you see: "Please fill in your name and email address" - fill in required fields.

### Step 3: Check WordPress Debug Log

Enable debugging in `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check `wp-content/debug.log` for entries like:

```
Koolboks Lead Payload: {"name":"...","email":"..."}
Koolboks Lead API Status: 200
Koolboks Lead API Response: {...}
```

### Step 4: Test Backend Lead Endpoint Directly

```bash
curl -X POST http://localhost:8000/capture-lead/ \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "phone": "+1234567890",
    "message": "Test message",
    "session_id": "test_123",
    "interested_products": [],
    "chat_history": []
  }'
```

Expected response:
```json
{
  "status": "success",
  "message": "Thank you! We'll contact you shortly.",
  "lead_id": "test_123"
}
```

### Step 5: Run Backend Test Suite

From your project directory:

```bash
python test_backend.py
```

This will test all endpoints including lead capture.

## Common Lead Form Errors

### Error: "Name and email are required"

**Cause**: Form submitted with empty required fields

**Solution**: Fill in both name and email before submitting

### Error: "Failed to submit lead: ..."

**Cause**: Cannot connect to backend

**Solution**:
1. Verify backend is running
2. Check API URL in plugin settings
3. Test with `curl http://localhost:8000/health`

### Error: "API returned error status: 422"

**Cause**: Backend validation failed (invalid data format)

**Solution**:
1. Check WordPress debug.log for payload
2. Ensure email is valid format
3. Check backend terminal for validation errors

### Error: "Invalid JSON response from API"

**Cause**: Backend returned non-JSON response

**Solution**:
1. Check backend is running correctly
2. Look at debug.log for actual response
3. Test backend with curl command above

## Lead Form Best Practices

1. **Always fill required fields**: Name and email are mandatory
2. **Use valid email**: Format like `user@domain.com`
3. **Phone is optional**: Can be left blank
4. **Message is optional**: Can be left blank
5. **Check console**: Always check browser console for errors

## Testing Lead Integration

### Test Locally

1. Start backend: `python main.py`
2. Open WordPress site
3. Click chat widget
4. Click "📝 Leave your contact info"
5. Fill in test data:
   - Name: Test User
   - Email: test@example.com
   - Phone: +1234567890
   - Message: Testing lead capture
6. Click Submit
7. Look for success message
8. Check Zoho CRM for new lead (if webhook configured)

### Verify Webhook Delivery

1. Check backend terminal for webhook logs
2. Go to Zapier dashboard
3. Check Zap history for recent triggers
4. Verify lead appears in Zoho CRM

---

**Still stuck?** Check the main README.md and DEPLOYMENT.md for additional guidance.
