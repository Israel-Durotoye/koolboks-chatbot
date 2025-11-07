# Azure Web App Configuration

## Required Application Settings (Environment Variables)

Set these in Azure Portal → Your App Service → Configuration → Application settings:

### Required Settings:
- `SCM_DO_BUILD_DURING_DEPLOYMENT` = `true`
- `WEBSITE_HTTPLOGGING_RETENTION_DAYS` = `3`
- `WEBSITES_ENABLE_APP_SERVICE_STORAGE` = `false`

### Python Settings:
- Python version: 3.10 or 3.11

### Your App Settings:
- `OPENAI_API_KEY` = your_openai_api_key
- Any other environment variables from your .env file

## Startup Command

In Azure Portal → Your App Service → Configuration → General settings → Startup Command:
```
gunicorn -w 4 -k uvicorn.workers.UvicornWorker main:app --bind=0.0.0.0:8000 --timeout 600
```

Or simply use the startup.txt file.

## Port Configuration

Azure expects the app to listen on port 8000 by default.
Your FastAPI app should bind to: `0.0.0.0:8000`

## Deployment Steps

1. **Push code to GitHub** (already done)

2. **Configure Azure App Service:**
   - Go to Azure Portal
   - Select your App Service
   - Go to Deployment Center
   - Connect to your GitHub repository
   - Enable automatic deployments

3. **Set Environment Variables:**
   - Go to Configuration → Application settings
   - Add `OPENAI_API_KEY` and any other required variables
   - Click Save

4. **Configure Startup:**
   - Go to Configuration → General settings
   - Set Startup Command to the gunicorn command above
   - Click Save

5. **Restart the App:**
   - Click Restart in the Overview page

## Logs

View logs in:
- Azure Portal → Your App Service → Log stream
- Or via Azure CLI: `az webapp log tail --name koolboks-chatbot-h7cmczfye2aqhafu --resource-group <your-resource-group>`

## Common Issues

### 503 Error:
- Check if all dependencies installed correctly
- Verify startup command is correct
- Check Application Insights/Logs for errors

### Missing Dependencies:
- Ensure `SCM_DO_BUILD_DURING_DEPLOYMENT=true`
- Check that requirements.txt includes all packages

### Port Issues:
- Azure uses port 8000 by default
- Your app must bind to 0.0.0.0:8000
