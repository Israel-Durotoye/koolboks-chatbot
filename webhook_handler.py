import requests
import json
from typing import Dict, Optional, List
from datetime import datetime
import os
from dotenv import load_dotenv

load_dotenv()

class WebhookHandler:
    """Handle webhook integrations for CRM and lead management."""
    
    def __init__(self):
        self.webhook_url = os.getenv("CRM_WEBHOOK_URL", "")
        self.webhook_secret = os.getenv("WEBHOOK_SECRET", "")
        
    def send_lead(
        self,
        name: str,
        email: str,
        phone: str,
        message: str,
        interested_products: List[str] = None,
        session_id: str = "",
        chat_history: List[Dict] = None
    ) -> bool:
        """Send lead data to CRM via webhook."""
        
        if not self.webhook_url:
            print("⚠️ No webhook URL configured")
            return False
        
        # Log what we received
        print(f"📝 Processing lead - Name: '{name}', Email: '{email}', Phone: '{phone}'")
        
        # Split name into first and last name
        name_parts = name.strip().split(maxsplit=1)
        first_name = name_parts[0] if name_parts else ""
        last_name = name_parts[1] if len(name_parts) > 1 else ""
        
        print(f"   Split into - First: '{first_name}', Last: '{last_name}'")
        
        # Clean phone number format
        phone_clean = phone.strip()
        
        payload = {
            "timestamp": datetime.now().isoformat(),
            "lead": {
                "first_name": first_name,
                "last_name": last_name,
                "email": email,
                "phone": phone_clean,
                "message": message,
                "source": "Koolboks Chatbot",
                "interested_products": interested_products or [],
                "session_id": session_id
            },
            "chat_summary": self._summarize_chat(chat_history) if chat_history else [],
            "metadata": {
                "channel": "web_chat",
                "platform": "koolboks_ai"
            }
        }
        
        headers = {
            "Content-Type": "application/json",
            "User-Agent": "Koolboks-Chatbot/1.0"
        }
        
        # Add authentication if secret is configured
        if self.webhook_secret:
            headers["X-Webhook-Secret"] = self.webhook_secret
        
        try:
            response = requests.post(
                self.webhook_url,
                json=payload,
                headers=headers,
                timeout=10
            )
            
            if response.status_code in [200, 201, 202]:
                print(f"✅ Lead sent to CRM successfully: {first_name} {last_name} ({email})")
                return True
            else:
                print(f"❌ CRM webhook failed: {response.status_code} - {response.text}")
                return False
                
        except requests.exceptions.Timeout:
            print("❌ CRM webhook timeout")
            return False
        except Exception as e:
            print(f"❌ CRM webhook error: {str(e)}")
            return False
    
    def _summarize_chat(self, chat_history: List[Dict]) -> List[Dict]:
        """Create a summary of the chat for CRM."""
        summary = []
        for msg in chat_history[-10:]:  # Last 10 messages
            summary.append({
                "role": msg.get("role", "unknown"),
                "content": msg.get("content", "")[:500],  # Limit length
                "timestamp": msg.get("timestamp", "")
            })
        return summary
    
    def send_chat_log(
        self,
        session_id: str,
        chat_history: List[Dict],
        user_info: Optional[Dict] = None
    ) -> bool:
        """Send full chat log to webhook for analytics."""
        
        if not self.webhook_url:
            return False
        
        payload = {
            "type": "chat_log",
            "timestamp": datetime.now().isoformat(),
            "session_id": session_id,
            "chat_history": chat_history,
            "user_info": user_info or {},
            "metadata": {
                "total_messages": len(chat_history),
                "duration": self._calculate_duration(chat_history)
            }
        }
        
        headers = {
            "Content-Type": "application/json",
            "X-Webhook-Secret": self.webhook_secret
        }
        
        try:
            response = requests.post(
                self.webhook_url,
                json=payload,
                headers=headers,
                timeout=10
            )
            return response.status_code in [200, 201, 202]
        except:
            return False
    
    def _calculate_duration(self, chat_history: List[Dict]) -> int:
        """Calculate chat duration in seconds."""
        if len(chat_history) < 2:
            return 0
        
        try:
            first = chat_history[0].get("timestamp", "")
            last = chat_history[-1].get("timestamp", "")
            if first and last:
                first_time = datetime.fromisoformat(first)
                last_time = datetime.fromisoformat(last)
                return int((last_time - first_time).total_seconds())
        except:
            pass
        
        return 0


# Webhook templates for popular CRMs

class HubSpotWebhook(WebhookHandler):
    """HubSpot-specific webhook handler."""
    
    def send_lead(self, name: str, email: str, phone: str, message: str, **kwargs):
        """Format lead data for HubSpot."""
        
        payload = {
            "fields": [
                {"name": "firstname", "value": name.split()[0] if name else ""},
                {"name": "lastname", "value": " ".join(name.split()[1:]) if len(name.split()) > 1 else ""},
                {"name": "email", "value": email},
                {"name": "phone", "value": phone},
                {"name": "message", "value": message},
                {"name": "lead_source", "value": "Koolboks Chatbot"}
            ]
        }
        
        # Add interested products as custom field
        if kwargs.get("interested_products"):
            payload["fields"].append({
                "name": "interested_products",
                "value": ", ".join(kwargs["interested_products"])
            })
        
        try:
            response = requests.post(
                self.webhook_url,
                json=payload,
                timeout=10
            )
            return response.status_code in [200, 201, 202]
        except:
            return False


class ZohoCRMWebhook(WebhookHandler):
    """Zoho CRM-specific webhook handler."""
    
    def send_lead(self, name: str, email: str, phone: str, message: str, **kwargs):
        """Format lead data for Zoho CRM."""
        
        # Split name into first and last name
        name_parts = name.strip().split(maxsplit=1)
        first_name = name_parts[0] if name_parts else ""
        last_name = name_parts[1] if len(name_parts) > 1 else ""
        
        payload = {
            "data": [{
                "First_Name": first_name,
                "Last_Name": last_name,
                "Email": email,
                "Phone": phone,
                "Description": message,
                "Lead_Source": "Koolboks Chatbot",
                "Company": kwargs.get("company", "Koolboks Customer")
            }]
        }
        
        # Add interested products if available
        if kwargs.get("interested_products"):
            payload["data"][0]["Product_Interest"] = ", ".join(kwargs["interested_products"])
        
        headers = {
            "Content-Type": "application/json"
        }
        
        # Add Zoho OAuth token if configured
        if self.webhook_secret:
            headers["Authorization"] = f"Zoho-oauthtoken {self.webhook_secret}"
        
        try:
            response = requests.post(
                self.webhook_url,
                json=payload,
                headers=headers,
                timeout=10
            )
            
            if response.status_code in [200, 201, 202]:
                print(f"✅ Lead sent to Zoho CRM: {first_name} {last_name}")
                return True
            else:
                print(f"❌ Zoho CRM error: {response.status_code} - {response.text}")
                return False
        except Exception as e:
            print(f"❌ Zoho CRM error: {str(e)}")
            return False


# Initialize default webhook handler
webhook_handler = WebhookHandler()
