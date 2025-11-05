#!/usr/bin/env python3
"""
Test script to verify both chat and lead capture endpoints
"""

import requests
import json

BASE_URL = "http://localhost:8000"

def test_health():
    """Test health endpoint"""
    print("🏥 Testing health endpoint...")
    try:
        response = requests.get(f"{BASE_URL}/health")
        print(f"   Status: {response.status_code}")
        print(f"   Response: {response.json()}")
        return response.status_code == 200
    except Exception as e:
        print(f"   ❌ Error: {e}")
        return False

def test_chat():
    """Test chat endpoint"""
    print("\n💬 Testing chat endpoint...")
    try:
        payload = {
            "query": "What is Koolboks?",
            "session_id": "test_session_123",
            "chat_history": [],
            "settings": {
                "temperature": 0.7,
                "max_tokens": 500
            }
        }
        
        response = requests.post(
            f"{BASE_URL}/chat/",
            json=payload,
            headers={"Content-Type": "application/json"},
            timeout=60
        )
        
        print(f"   Status: {response.status_code}")
        
        if response.status_code == 200:
            data = response.json()
            print(f"   Response preview: {data.get('response', '')[:100]}...")
            print(f"   Processing time: {data.get('processing_time', 0):.2f}s")
            return True
        else:
            print(f"   ❌ Error: {response.text}")
            return False
            
    except Exception as e:
        print(f"   ❌ Error: {e}")
        return False

def test_lead_capture():
    """Test lead capture endpoint"""
    print("\n📋 Testing lead capture endpoint...")
    try:
        payload = {
            "name": "John Doe",
            "email": "john.doe@example.com",
            "phone": "+1234567890",
            "message": "Interested in solar solutions",
            "session_id": "test_session_123",
            "interested_products": ["Solar Kit"],
            "chat_history": []
        }
        
        response = requests.post(
            f"{BASE_URL}/capture-lead/",
            json=payload,
            headers={"Content-Type": "application/json"},
            timeout=30
        )
        
        print(f"   Status: {response.status_code}")
        
        if response.status_code == 200:
            data = response.json()
            print(f"   Response: {json.dumps(data, indent=2)}")
            return True
        else:
            print(f"   ❌ Error: {response.text}")
            return False
            
    except Exception as e:
        print(f"   ❌ Error: {e}")
        return False

def main():
    print("🚀 Koolboks Backend Test Suite\n")
    print(f"Testing backend at: {BASE_URL}\n")
    print("=" * 60)
    
    results = {
        "Health Check": test_health(),
        "Chat Endpoint": test_chat(),
        "Lead Capture": test_lead_capture()
    }
    
    print("\n" + "=" * 60)
    print("\n📊 Test Results:\n")
    
    for test_name, passed in results.items():
        status = "✅ PASSED" if passed else "❌ FAILED"
        print(f"   {test_name}: {status}")
    
    all_passed = all(results.values())
    
    print("\n" + "=" * 60)
    if all_passed:
        print("\n🎉 All tests passed! Your backend is working correctly.")
    else:
        print("\n⚠️  Some tests failed. Check the output above for details.")
        print("\nTroubleshooting:")
        print("  1. Make sure backend is running: python main.py")
        print("  2. Check .env file has OPENAI_API_KEY")
        print("  3. Verify no firewall blocking localhost:8000")
    
    print()
    return 0 if all_passed else 1

if __name__ == "__main__":
    exit(main())
