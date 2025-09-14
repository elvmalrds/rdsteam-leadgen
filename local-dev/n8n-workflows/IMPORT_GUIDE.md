# n8n Workflow Import Guide

## Step 1: Start n8n

Run the setup script:
```bash
cd C:\Users\Elvis\documents\rdsteam-leadgen\local-dev
n8n-setup.bat
```

Or start n8n manually:
```bash
n8n start
```

Access n8n at: **http://localhost:5678**

## Step 2: Initial n8n Setup

1. **First Time Setup**: Create your n8n account (local only)
2. **Login**: Access the n8n interface
3. **Workspace**: You'll see an empty workflow canvas

## Step 3: Import Workflows (Do in This Order)

### Import Workflow 1: CSV Processing (Main)
1. Click the **"Import from file"** button (📁 icon in top toolbar)
2. Select: `workflow-1-csv-processing.json`
3. Click **"Import"**
4. **Save** the workflow (Ctrl+S)
5. **Activate** the workflow (toggle switch at top)

### Import Workflow 2: Hot Leads Notification
1. Click **"New"** to create a new workflow
2. Click **"Import from file"** button
3. Select: `workflow-2-hot-leads-notification.json`
4. Click **"Import"**
5. **Save** the workflow (Ctrl+S)
6. **Activate** the workflow (toggle switch at top)

### Import Workflow 3: GoHighLevel Integration
1. Click **"New"** to create a new workflow
2. Click **"Import from file"** button
3. Select: `workflow-3-gohighlevel-integration.json`
4. Click **"Import"**
5. **Save** the workflow (Ctrl+S)
6. **Activate** the workflow (toggle switch at top)

## Step 4: Verify Webhook URLs

After importing, check that these webhook URLs are active:

### Workflow 1: CSV Processing
- **URL**: `http://localhost:5678/webhook/csv-upload`
- **Method**: POST
- **Status**: Should show "Waiting for webhook call"

### Workflow 2: Hot Leads
- **URL**: `http://localhost:5678/webhook/hot-leads`
- **Method**: POST
- **Status**: Should show "Waiting for webhook call"

### Workflow 3: GoHighLevel
- **URL**: `http://localhost:5678/webhook/gohighlevel`
- **Method**: POST
- **Status**: Should show "Waiting for webhook call"

## Step 5: Test Basic Connectivity

### Test CSV Processing Webhook
Open Command Prompt and test:
```bash
curl -X POST http://localhost:5678/webhook/csv-upload ^
  -H "Content-Type: application/json" ^
  -d "{\"test\": true}"
```

You should see the workflow execute in n8n interface!

## Step 6: Configure API Credentials (When Ready)

### For Slack Notifications (Workflow 2):
1. Open **Workflow 2** in n8n
2. Click on **"Send Slack Alert"** node
3. Update the webhook URL to your actual Slack webhook
4. Get Slack webhook from: https://api.slack.com/messaging/webhooks

### For GoHighLevel Integration (Workflow 3):
1. Open **Workflow 3** in n8n
2. Click on **"Create GHL Contact"** node
3. Update the Authorization header with your GHL API key
4. Update the campaign IDs to match your actual campaigns

## Step 7: Integration with Local Dashboard

Once n8n workflows are active:
1. Go to: **http://localhost/leadgen** (your local dashboard)
2. Upload the sample CSV file: `test-data/sample_leads.csv`
3. Check the **"Use n8n Workflows"** option
4. Click **"Process to VTiger"**
5. Watch the automation run in n8n interface!

## Troubleshooting

### Common Issues:

**1. n8n won't start**
- Make sure no other process is using port 5678
- Try: `netstat -ano | findstr :5678` to check

**2. Workflows won't import**
- Make sure JSON files are valid
- Check file permissions
- Try importing one at a time

**3. Webhooks not working**
- Verify workflows are **ACTIVE** (toggle switch on)
- Check webhook URLs match exactly
- Look at n8n execution logs for errors

**4. VTiger API calls failing**
- Check your local PHP server is running (XAMPP)
- Verify VTiger credentials in `api/vtiger.php`
- Test VTiger connection independently first

### Debug Mode:
1. In n8n, click **Settings** (gear icon)
2. Enable **"Save execution data"**
3. Enable **"Save execution data on error"**
4. This will show detailed logs for troubleshooting

## Success Indicators

✅ **n8n Running**: Interface accessible at localhost:5678
✅ **Workflows Imported**: All 3 workflows show up in workflow list
✅ **Workflows Active**: Toggle switches are ON (green)
✅ **Webhooks Ready**: All webhook nodes show "Waiting for webhook call"
✅ **Test Successful**: Webhook test returns proper response

## Next Steps After Setup

1. **Test with Sample Data**: Use the 5-lead sample CSV
2. **Configure Real API Keys**: Add Slack and GoHighLevel credentials
3. **Production Testing**: Test with real Lead411 data
4. **Monitor Performance**: Watch execution logs and success rates

**Once n8n is set up, you'll have the complete automation pipeline running!**