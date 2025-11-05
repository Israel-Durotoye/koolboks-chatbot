"""
Test script to trigger the Zapier webhook with sample lead data.
This will send a test lead to verify your Zapier connection.
"""

import requests
import json
from datetime import datetime

# Your Zapier webhook URL from .env
WEBHOOK_URL = "https://hooks.zapier.com/hooks/catch/15086232/us9v0tg/"

# Sample test data
test_lead = {
    "timestamp": datetime.now().isoformat(),
    "lead": {
        "first_name": "John",
        "last_name": "Doe",
        "email": "john.doe@test.com",
        "phone": "+234 800 123 4567",
        "message": "I'm interested in the 600L solar freezer with BNPL payment option",
        "source": "Koolboks Chatbot",
        "interested_products": ["600L AC Freezer", "Solar Panels"],
        "session_id": "test-session-123"
    },
    "chat_summary": [
        {
            "role": "user",
            "content": "How much is the 600L freezer?",
            "timestamp": "2025-11-05T10:25:00"
        },
        {
            "role": "assistant",
            "content": "The 600L AC freezer costs ₦1,324,000 outright or with BNPL...",
            "timestamp": "2025-11-05T10:25:05"
        }
    ],
    "metadata": {
        "channel": "web_chat",
        "platform": "koolboks_ai"
    }
}

print("🚀 Testing Zapier Webhook...")
print(f"📤 Sending to: {WEBHOOK_URL}")
print(f"\n📋 Test Lead Data:")
print(f"   Name: {test_lead['lead']['first_name']} {test_lead['lead']['last_name']}")
print(f"   Email: {test_lead['lead']['email']}")
print(f"   Phone: {test_lead['lead']['phone']}")
print(f"   Message: {test_lead['lead']['message']}")

try:
    response = requests.post(
        WEBHOOK_URL,
        json=test_lead,
        headers={
            "Content-Type": "application/json",
            "User-Agent": "Koolboks-Chatbot-Test/1.0"
        },
        timeout=10
    )
    
    print(f"\n✅ Response Status: {response.status_code}")
    print(f"📥 Response Body: {response.text}")
    
    if response.status_code in [200, 201, 202]:
        print("\n🎉 SUCCESS! Check your Zapier dashboard to see the trigger.")
        print("👉 Go to: https://zapier.com/app/history")
    else:
        print(f"\n⚠️ Unexpected status code: {response.status_code}")
        print("Check your Zapier webhook URL in the .env file")
        
except requests.exceptions.Timeout:
    print("\n❌ ERROR: Request timed out")
    print("Your webhook might be slow or unreachable")
    
except requests.exceptions.ConnectionError:
    print("\n❌ ERROR: Connection failed")
    print("Check your internet connection and webhook URL")
    
except Exception as e:
    print(f"\n❌ ERROR: {str(e)}")

print("\n" + "="*60)
print("Next Steps:")
print("1. Check Zapier dashboard: https://zapier.com/app/history")
print("2. You should see the test trigger in your Zap history")
print("3. Map the fields in Zapier to Zoho CRM")
print("4. Test again from the actual chatbot!")
print("="*60)
