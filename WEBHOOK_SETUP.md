# CRM Webhook Integration Guide

## Overview
The chatbot now captures leads and sends them to your CRM via webhooks. This works with any CRM that accepts webhook data.

## Quick Setup Options

### Option 1: Make.com (Recommended - Free Tier Available)
1. Go to [Make.com](https://www.make.com)
2. Create a new scenario
3. Add "Webhooks" → "Custom Webhook" as trigger
4. Copy the webhook URL
5. Add your CRM module (HubSpot, Salesforce, Zoho, etc.)
6. Map the fields:
   - `lead.name` → Contact Name
   - `lead.email` → Email
   - `lead.phone` → Phone
   - `lead.message` → Description
   - `lead.interested_products` → Custom field
7. Paste webhook URL in `.env` file

### Option 2: Zapier
1. Go to [Zapier.com](https://zapier.com)
2. Create a new Zap
3. Trigger: "Webhooks by Zapier" → "Catch Hook"
4. Copy the webhook URL
5. Action: Choose your CRM (HubSpot, Salesforce, etc.)
6. Map fields from webhook payload
7. Paste webhook URL in `.env` file

### Option 3: n8n (Self-Hosted, Free)
1. Install n8n: `npm install -g n8n`
2. Run: `n8n start`
3. Create workflow with Webhook trigger
4. Add your CRM integration node
5. Copy webhook URL
6. Paste in `.env` file

### Option 4: Direct CRM Integration

#### HubSpot
```bash
# In .env file
CRM_WEBHOOK_URL=https://api.hubapi.com/contacts/v1/contact/
WEBHOOK_SECRET=your_hubspot_api_key
```

#### Zoho CRM
```bash
# In .env file
CRM_WEBHOOK_URL=https://www.zohoapis.com/crm/v2/Leads
WEBHOOK_SECRET=your_zoho_oauth_token
```

## Webhook Payload Structure

When a lead is captured, this JSON is sent:

```json
{
  "timestamp": "2025-11-05T10:30:00",
  "lead": {
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+234 800 000 0000",
    "message": "Interested in 600L freezer",
    "source": "Koolboks Chatbot",
    "interested_products": ["Solar Freezers"],
    "session_id": "abc-123-def"
  },
  "chat_summary": [
    {
      "role": "user",
      "content": "How much is the 600L freezer?",
      "timestamp": "2025-11-05T10:25:00"
    },
    {
      "role": "assistant",
      "content": "The 600L AC freezer costs ₦1,324,000...",
      "timestamp": "2025-11-05T10:25:05"
    }
  ],
  "metadata": {
    "channel": "web_chat",
    "platform": "koolboks_ai"
  }
}
```

## Configuration

1. **Set Webhook URL in `.env`:**
```bash
CRM_WEBHOOK_URL=https://your-webhook-url.com/endpoint
```

2. **Optional: Add Security Secret:**
```bash
WEBHOOK_SECRET=your_secret_key_here
```

3. **Restart the server:**
```bash
uvicorn main:app --reload
```

## Testing

### Test Webhook Locally
Use [webhook.site](https://webhook.site) to test:
1. Go to webhook.site
2. Copy the unique URL
3. Paste in `.env` as `CRM_WEBHOOK_URL`
4. Fill out the lead form in the chatbot
5. See the data arrive in webhook.site

### Test with Real CRM
1. Set up webhook in your CRM platform
2. Configure `.env` with webhook URL
3. Submit a test lead through the chatbot
4. Verify lead appears in your CRM

## Features

✅ **Automatic Lead Capture** - Collects name, email, phone, message  
✅ **Product Interest Tracking** - Detects which products users ask about  
✅ **Chat History** - Sends conversation context to CRM  
✅ **Multiple CRM Support** - Works with any webhook-enabled system  
✅ **Error Handling** - Gracefully handles webhook failures  
✅ **Security** - Optional webhook secret for authentication  

## Supported CRM Platforms

- HubSpot
- Salesforce
- Zoho CRM
- Pipedrive
- Monday.com
- Airtable
- Google Sheets (via Zapier/Make)
- Any custom CRM with webhook support

## Advanced: Custom Webhook Handler

For CRM-specific formatting, edit `webhook_handler.py`:

```python
# Example: Custom CRM format
class CustomCRMWebhook(WebhookHandler):
    def send_lead(self, name: str, email: str, phone: str, message: str, **kwargs):
        payload = {
            "contact_name": name,
            "contact_email": email,
            "contact_phone": phone,
            "notes": message,
            "source": "Chatbot"
        }
        
        response = requests.post(self.webhook_url, json=payload)
        return response.status_code == 200
```

## Troubleshooting

**Webhook not receiving data?**
- Check `.env` file has correct URL
- Verify server restarted after config change
- Test with webhook.site first
- Check webhook endpoint accepts POST requests

**CRM not creating leads?**
- Verify field mapping in Make/Zapier
- Check CRM API credentials
- Review webhook logs in your platform
- Ensure required CRM fields are mapped

**Security concerns?**
- Add `WEBHOOK_SECRET` to `.env`
- Webhook handler sends secret in `X-Webhook-Secret` header
- Configure your webhook platform to validate this header

## Support

Need help? Contact: israel@koolboks.com
