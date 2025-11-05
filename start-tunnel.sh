#!/bin/bash

# Quick Setup Script for LocalTunnel
# This makes your local backend accessible from WordPress

echo "🚀 Koolboks Backend - LocalTunnel Setup"
echo "========================================"
echo ""

# Check if backend is running
echo "1️⃣  Checking if backend is running..."
if curl -s http://localhost:8000/health > /dev/null 2>&1; then
    echo "   ✅ Backend is running on port 8000"
else
    echo "   ❌ Backend is NOT running!"
    echo "   👉 Start it with: uvicorn main:app --reload"
    echo ""
    exit 1
fi

# Check if LocalTunnel is installed
echo ""
echo "2️⃣  Checking LocalTunnel installation..."
if command -v lt &> /dev/null; then
    echo "   ✅ LocalTunnel is installed"
else
    echo "   ❌ LocalTunnel is NOT installed"
    echo "   👉 Install it with: sudo npm install -g localtunnel"
    echo ""
    exit 1
fi

# Start LocalTunnel
echo ""
echo "3️⃣  Starting LocalTunnel..."
echo "   Press Ctrl+C to stop the tunnel"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Start tunnel and capture URL
lt --port 8000 &
LT_PID=$!

# Wait for tunnel to start
sleep 3

# Get the URL from the tunnel process
TUNNEL_URL=$(lt --port 8000 2>&1 | grep -o 'https://[^[:space:]]*' | head -1)

if [ -n "$TUNNEL_URL" ]; then
    echo "   ✅ Tunnel is running!"
    echo ""
    echo "   🌐 Your public URL: $TUNNEL_URL"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "📋 Next Steps:"
    echo ""
    echo "1. Copy this URL: $TUNNEL_URL"
    echo ""
    echo "2. Go to WordPress Admin → Koolboks Chat"
    echo ""
    echo "3. Update API URL to: $TUNNEL_URL"
    echo ""
    echo "4. Click Save Changes"
    echo ""
    echo "5. Test your chatbot!"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "⚠️  IMPORTANT:"
    echo "   • Keep this terminal open while testing"
    echo "   • LocalTunnel must stay running"
    echo "   • Press Ctrl+C to stop"
    echo ""
else
    echo "   ❌ Failed to get tunnel URL"
    echo "   Try running manually: lt --port 8000"
fi

# Keep running
wait $LT_PID
