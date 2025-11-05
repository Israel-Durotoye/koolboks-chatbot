#!/bin/bash

echo "🚀 Koolboks Backend - Railway Deployment Helper"
echo "================================================"
echo ""

# Check if git is initialized
if [ ! -d ".git" ]; then
    echo "📦 Initializing git repository..."
    git init
    echo "✅ Git initialized"
else
    echo "✅ Git repository already exists"
fi

# Add all files
echo ""
echo "📝 Adding files to git..."
git add .

# Show status
echo ""
echo "📊 Git status:"
git status --short

# Commit
echo ""
read -p "Enter commit message (or press Enter for default): " COMMIT_MSG
if [ -z "$COMMIT_MSG" ]; then
    COMMIT_MSG="Prepare for Railway deployment"
fi

git commit -m "$COMMIT_MSG"
echo "✅ Changes committed"

echo ""
echo "================================================"
echo "✅ Your code is ready for deployment!"
echo "================================================"
echo ""
echo "📋 Next Steps:"
echo ""
echo "1. Create a GitHub repository:"
echo "   - Go to https://github.com/new"
echo "   - Name it: koolboks-chatbot"
echo "   - Don't initialize with README"
echo "   - Click 'Create repository'"
echo ""
echo "2. Push your code to GitHub:"
echo "   Run these commands (replace YOUR_USERNAME):"
echo ""
echo "   git remote add origin https://github.com/YOUR_USERNAME/koolboks-chatbot.git"
echo "   git branch -M main"
echo "   git push -u origin main"
echo ""
echo "3. Deploy to Railway:"
echo "   - Go to https://railway.app/new"
echo "   - Click 'Deploy from GitHub repo'"
echo "   - Select your koolboks-chatbot repository"
echo "   - Railway will auto-deploy!"
echo ""
echo "4. Add environment variables in Railway:"
echo "   - OPENAI_API_KEY"
echo "   - CRM_WEBHOOK_URL"
echo ""
echo "5. Get your public URL:"
echo "   - Railway Settings → Domains → Generate Domain"
echo ""
echo "6. Update WordPress plugin with your Railway URL"
echo ""
echo "================================================"
echo "Need detailed guide? Check DEPLOY_PRODUCTION.md"
echo "================================================"
