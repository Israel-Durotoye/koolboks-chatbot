# Zoho CRM + Zapier Setup Guide

## Setup Instructions

### Option 1: Zapier (Easiest - Recommended)

#### Step 1: Create Zapier Webhook
1. Go to [zapier.com](https://zapier.com) and sign in
2. Click "Create Zap"
3. **Trigger**: Search for "Webhooks by Zapier"
4. Choose "Catch Hook"
5. Click "Continue"
6. **Copy the webhook URL** (looks like: `https://hooks.zapier.com/hooks/catch/xxxxx/yyyyy/`)

#### Step 2: Configure in Your Chatbot
1. Open `.env` file in your project
2. Add the webhook URL:
   ```bash
   CRM_WEBHOOK_URL=https://hooks.zapier.com/hooks/catch/xxxxx/yyyyy/
   ```
3. Save the file
4. Restart your server (it should auto-reload)

#### Step 3: Test the Webhook
1. In the chatbot, click "📞 Request Callback"
2. Fill in the form with test data:
   - Name: `John Doe`
   - Email: `john@test.com`
   - Phone: `+234 800 000 0000`
3. Submit the form
4. Go back to Zapier - you should see the test data arrive

#### Step 4: Connect to Zoho CRM
1. In Zapier, click "Continue"
2. **Action**: Search for "Zoho CRM"
3. Choose "Create Lead" or "Create Contact"
4. Connect your Zoho CRM account
5. Map the fields:
   - **First Name**: `lead.first_name`
   - **Last Name**: `lead.last_name`
   - **Email**: `lead.email`
   - **Phone**: `lead.phone`
   - **Description**: `lead.message`
   - **Lead Source**: `lead.source` (or set to "Chatbot")
6. Click "Continue" and then "Test & Continue"
7. Turn on your Zap!

#### Field Mapping Reference
Your webhook sends this data structure:
```json
{
  "lead": {
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@test.com",
    "phone": "+234 800 000 0000",
    "message": "I'm interested in 600L freezer",
    "source": "Koolboks Chatbot",
    "interested_products": ["Solar Freezers"]
  }
}
```

Map in Zapier like this:
- **First Name** → `lead first_name`
- **Last Name** → `lead last_name`
- **Email** → `lead email`
- **Phone** → `lead phone`
- **Notes/Description** → `lead message`
- **Lead Source** → `lead source` or type "Chatbot"

---

### Option 2: Direct Zoho CRM Integration (Advanced)

#### Step 1: Get Zoho OAuth Token
1. Go to [Zoho API Console](https://api-console.zoho.com/)
2. Click "Add Client"
3. Choose "Server-based Applications"
4. Fill in details:
   - Client Name: `Koolboks Chatbot`
   - Homepage URL: `http://localhost:8000`
   - Authorized Redirect URI: `http://localhost:8000/callback`
5. Click "Create"
6. Copy the **Client ID** and **Client Secret**

#### Step 2: Generate Access Token
1. Use this URL (replace CLIENT_ID):
   ```
   https://accounts.zoho.com/oauth/v2/auth?scope=ZohoCRM.modules.ALL&client_id=YOUR_CLIENT_ID&response_type=code&access_type=offline&redirect_uri=http://localhost:8000/callback
   ```
2. Authorize the app
3. Copy the **code** from the URL
4. Make a POST request (use Postman or curl):
   ```bash
   curl -X POST https://accounts.zoho.com/oauth/v2/token \
     -d "code=YOUR_CODE" \
     -d "client_id=YOUR_CLIENT_ID" \
     -d "client_secret=YOUR_CLIENT_SECRET" \
     -d "redirect_uri=http://localhost:8000/callback" \
     -d "grant_type=authorization_code"
   ```
5. Copy the **access_token** from the response

#### Step 3: Configure in .env
```bash
CRM_WEBHOOK_URL=https://www.zohoapis.com/crm/v2/Leads
WEBHOOK_SECRET=your_access_token_here
```

#### Step 4: Update Code to Use Zoho Handler
In `main.py`, replace the webhook handler import:
```python
from webhook_handler import ZohoCRMWebhook
webhook_handler = ZohoCRMWebhook()
```

---

## Testing Your Setup

### Test with Zapier:
1. Submit a test lead through the chatbot
2. Check your Zapier dashboard for activity
3. Verify the lead appears in Zoho CRM
4. Check all fields are mapped correctly

### Test Payload Example:
When you submit the form, this is sent:
```json
{
  "timestamp": "2025-11-05T10:30:00",
  "lead": {
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone": "+234 800 000 0000",
    "message": "Interested in solar freezers",
    "source": "Koolboks Chatbot",
    "interested_products": ["Solar Freezers"],
    "session_id": "abc-123-def"
  }
}
```

---

## Zapier Multi-Step Setup (Bonus)

You can create a multi-step Zap:

**Step 1**: Webhook (Catch Hook)  
**Step 2**: Zoho CRM (Create Lead)  
**Step 3**: Gmail (Send notification email)  
**Step 4**: Slack (Post to sales channel)  

---

## Troubleshooting

### Webhook not working?
1. Check `.env` file has the correct URL
2. Restart the server: `uvicorn main:app --reload`
3. Test with [webhook.site](https://webhook.site) first
4. Check Zapier task history for errors

### Name not splitting correctly?
- Make sure users enter "First Last" format
- We automatically split on first space
- Example: "John Doe" → First: "John", Last: "Doe"

### Zoho CRM not receiving leads?
- Verify OAuth token is valid
- Check Zoho CRM permissions
- Ensure required fields are mapped
- Review Zoho API logs

### Phone format issues?
- We send phone as-is from the form
- To format: Update `webhook_handler.py` line with phone cleaning
- Example: Remove spaces, add country code

---

## Current Configuration

Your webhook now sends these exact fields:
- ✅ `first_name` - First name only
- ✅ `last_name` - Last name only  
- ✅ `email` - Email address
- ✅ `phone` - Phone number

Perfect for Zoho CRM field mapping! 🎉

---

## Support

Need help?
- Zapier support: [zapier.com/help](https://zapier.com/help)
- Zoho CRM API docs: [zoho.com/crm/developer](https://www.zoho.com/crm/developer/)
- Contact: israel@koolboks.com
