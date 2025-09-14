# n8n Workflow Import Steps

## ✅ Prerequisites Complete
- ✅ n8n running on http://localhost:5678
- ✅ n8n tunnel: https://mh0nnzlcuxfqymhx3crldvqf.hooks.n8n.cloud
- ⏳ XAMPP starting (http://localhost/leadgen)

## 🔄 NEXT: Import Workflows

### Step 1: Access n8n Interface
Open your browser and go to: **http://localhost:5678**

### Step 2: Import Workflow 1 (Main CSV Processing)
1. Click **"Import from file"** button (📁 icon in top toolbar)
2. Navigate to: `C:\Users\Elvis\documents\rdsteam-leadgen\local-dev\n8n-workflows\`
3. Select: **`workflow-1-csv-processing.json`**
4. Click **"Import"**
5. **Save** the workflow (Ctrl+S)
6. **Activate** the workflow (toggle switch at top - should turn green)
7. ✅ Verify webhook URL shows: `http://localhost:5678/webhook/csv-upload`

### Step 3: Import Workflow 2 (Hot Leads Notifications)
1. Click **"New"** to create a new workflow
2. Click **"Import from file"** button
3. Select: **`workflow-2-hot-leads-notification.json`**
4. Click **"Import"**
5. **Save** the workflow (Ctrl+S)
6. **Activate** the workflow (toggle switch at top)
7. ✅ Verify webhook URL shows: `http://localhost:5678/webhook/hot-leads`

### Step 4: Import Workflow 3 (GoHighLevel Integration)
1. Click **"New"** to create a new workflow
2. Click **"Import from file"** button
3. Select: **`workflow-3-gohighlevel-integration.json`**
4. Click **"Import"**
5. **Save** the workflow (Ctrl+S)
6. **Activate** the workflow (toggle switch at top)
7. ✅ Verify webhook URL shows: `http://localhost:5678/webhook/gohighlevel`

## 🧪 Quick Test
Once all workflows are imported and active, test the main webhook:

Open Command Prompt and run:
```bash
curl -X POST http://localhost:5678/webhook/csv-upload -H "Content-Type: application/json" -d "{\"test\": true}"
```

You should see the workflow execute in the n8n interface!

## 📋 Success Checklist
- [ ] All 3 workflows imported
- [ ] All 3 workflows activated (green toggle switches)
- [ ] All 3 webhook URLs are active and waiting
- [ ] Test webhook responds successfully
- [ ] XAMPP running (http://localhost/leadgen accessible)

## 🎯 Next Steps After Import
1. **Test Basic Functionality**: Upload sample CSV via dashboard
2. **Configure API Keys**: Add Slack and GoHighLevel credentials when ready
3. **End-to-End Testing**: Test complete automation pipeline

## 📞 Support
- **n8n Interface**: http://localhost:5678
- **Lead Dashboard**: http://localhost/leadgen (once XAMPP is running)
- **Workflow Files**: Located in `n8n-workflows/` folder