# n8n Workflows for RDS Lead Generation Automation

## Overview
Three interconnected n8n workflows that automate the complete Lead411 → VTiger → GoHighLevel pipeline with intelligent lead routing and notifications.

## Workflows

### 1. CSV Processing Workflow (`workflow-1-csv-processing.json`)
**Purpose**: Main automation workflow that processes Lead411 CSV exports
**Trigger**: Webhook from local PHP application
**Features**:
- CSV file validation and parsing
- VTiger lead creation with custom fields
- Intent score filtering (≥70)
- Automatic routing to hot leads and GoHighLevel workflows

**Webhook URL**: `http://localhost:5678/webhook/csv-upload`

### 2. Hot Leads Notification Workflow (`workflow-2-hot-leads-notification.json`)
**Purpose**: Immediate alerts for high-intent leads (score ≥90)
**Trigger**: Called by CSV Processing Workflow
**Features**:
- Slack notifications with lead details
- Follow-up task creation
- Priority assignment (URGENT for 95+, HIGH for 90+)
- VTiger direct links

**Webhook URL**: `http://localhost:5678/webhook/hot-leads`

### 3. GoHighLevel Integration Workflow (`workflow-3-gohighlevel-integration.json`)
**Purpose**: Marketing automation campaign assignment
**Trigger**: Called by CSV Processing Workflow  
**Features**:
- Contact creation in GoHighLevel
- Campaign assignment based on intent score:
  - Hot (90+): Immediate follow-up sequence
  - Warm (80-89): Nurture sequence
  - Qualified (70-79): General prospecting sequence
- Custom field mapping with VTiger sync

**Webhook URL**: `http://localhost:5678/webhook/gohighlevel`

## Setup Instructions

### 1. Install n8n (if not already done)
```bash
npm install n8n -g
```

### 2. Start n8n
```bash
n8n start
```
Access n8n at: http://localhost:5678

### 3. Import Workflows
1. Open n8n interface (http://localhost:5678)
2. Click "Import from File" for each workflow:
   - `workflow-1-csv-processing.json`
   - `workflow-2-hot-leads-notification.json`  
   - `workflow-3-gohighlevel-integration.json`

### 4. Configuration Required

#### Slack Integration (Hot Leads Workflow)
1. Edit `workflow-2-hot-leads-notification.json`
2. Update Slack webhook URL in "Send Slack Alert" node:
   ```
   https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK
   ```
3. Get webhook URL from: https://api.slack.com/messaging/webhooks

#### GoHighLevel Integration (GHL Workflow)
1. Edit `workflow-3-gohighlevel-integration.json`
2. Update API key in "Create GHL Contact" and "Assign to Campaign" nodes:
   ```
   Authorization: Bearer YOUR_GHL_API_KEY
   ```
3. Update campaign IDs for your specific campaigns:
   - `hot-leads-immediate-followup`
   - `warm-leads-nurture-sequence`
   - `qualified-leads-general-sequence`

### 5. Activate Workflows
1. Open each imported workflow
2. Click the toggle switch to activate
3. Verify webhook URLs are accessible

## Workflow Data Flow

```
CSV Upload (PHP App)
    ↓ (POST webhook)
CSV Processing Workflow
    ↓ (processes CSV, creates VTiger leads)
    ├─→ Hot Leads Workflow (if intent score ≥90)
    │   ├─→ Slack Notification
    │   └─→ Task Creation
    └─→ GoHighLevel Workflow (all leads)
        ├─→ Create Contacts  
        └─→ Assign Campaigns
```

## Testing

### Test CSV Processing
```bash
# Upload test CSV through local dashboard
# http://localhost/leadgen

# Or trigger directly:
curl -X POST http://localhost:5678/webhook/csv-upload \
  -H "Content-Type: application/json" \
  -d '{
    "file_path": "/path/to/test.csv",
    "processing_id": "test123",
    "test_mode": true
  }'
```

### Test Hot Leads Notification
```bash
curl -X POST http://localhost:5678/webhook/hot-leads \
  -H "Content-Type: application/json" \
  -d '{
    "hot_leads": [{
      "company": "Test Corp",
      "intent_score": 95,
      "lead_id": "2x12345"
    }],
    "processing_summary": {"created": 1},
    "timestamp": "2025-09-11T12:00:00.000Z"
  }'
```

## Monitoring

### Workflow Execution
- Monitor executions in n8n interface
- Check logs for each workflow run
- Set up error notifications if needed

### Performance Metrics
- CSV processing time: <5 minutes for 100 leads
- VTiger API calls: ~1 second per lead
- GoHighLevel sync: ~2 seconds per contact
- Hot lead notifications: <30 seconds

## Customization

### Adding New Campaigns
1. Edit GoHighLevel workflow
2. Update `campaignMapping` object in "Process Leads for GHL" node
3. Add new intent score thresholds as needed

### Custom Notifications
1. Edit Hot Leads workflow
2. Add new notification nodes (email, SMS, etc.)
3. Update formatting in "Format Hot Leads Notification" node

### Additional Integrations
1. Create new workflows following the same pattern
2. Add webhook triggers to CSV Processing workflow
3. Update main PHP application to call new webhooks

## Troubleshooting

### Common Issues
1. **Webhook not triggering**: Check n8n is running on port 5678
2. **VTiger API errors**: Verify credentials in PHP application
3. **Slack notifications failing**: Check webhook URL and format
4. **GoHighLevel sync issues**: Verify API key and campaign IDs

### Debug Mode
1. Enable "Save execution data" in workflow settings
2. Check execution logs for detailed error messages  
3. Use "Test workflow" functionality for debugging

## Security Notes
- Keep API keys secure and use environment variables
- Restrict n8n access to local network only
- Monitor webhook endpoints for unauthorized access
- Regular backup of workflow configurations

## Maintenance
- Update API credentials as needed
- Monitor execution success rates
- Archive old execution data periodically
- Update campaign configurations as business needs change