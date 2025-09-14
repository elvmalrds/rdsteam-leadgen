# Make.com Workflow Design

## Overview
Three interconnected workflows to automate lead processing from Lead411 CSV export to VTiger CRM and GoHighLevel campaigns.

## Workflow 1: CSV Processing & Contact Enrichment

**Trigger**: New file uploaded to Google Drive `/imports/` folder

### Steps:
1. **File Detection**
   - Monitor Google Drive folder
   - Trigger on `.csv` files only
   - Parse filename for date/timestamp

2. **CSV Parsing**
   - Read CSV content
   - Map columns to standard fields:
     - Company Name → `company_name`
     - Website → `website`  
     - Industry → `industry`
     - Employee Count → `employee_count`
     - Intent Topics → `intent_topics`
     - Intent Score → `intent_score`

3. **Data Validation**
   - Check intent score ≥ 70
   - Validate required fields present
   - Skip incomplete records
   - Log validation failures

4. **Contact Enrichment (Lead411 API)**
   - For each company, call Lead411 API
   - Retrieve key contacts:
     - CEO/President
     - CMO/Marketing Director
     - CTO/Technology Director
   - Fields to retrieve:
     - First Name, Last Name
     - Email Address
     - Direct Phone
     - LinkedIn Profile
     - Job Title

5. **Data Formatting**
   - Format for VTiger lead creation
   - Calculate lead priority based on:
     - Intent score (70-79=Medium, 80-89=High, 90+=Hot)
     - Company size
     - Industry match
   - Generate unique lead reference ID

6. **Output**
   - Pass enriched data to Workflow 2
   - Log processing results
   - Move CSV to `/processed/` folder

**Error Handling**:
- Skip records with enrichment failures
- Log all errors for manual review
- Continue processing remaining records
- Send daily error summary email

---

## Workflow 2: VTiger CRM Integration

**Trigger**: Enriched lead data from Workflow 1

### Steps:
1. **Duplicate Check**
   - Search VTiger for existing leads
   - Check by: email, company name, phone
   - Skip if duplicate found
   - Log duplicate prevention

2. **Lead Creation**
   - Map enriched data to VTiger fields:
     ```
     lastname → Contact Last Name
     firstname → Contact First Name  
     company → Company Name
     email → Primary Email
     phone → Office Phone
     website → Website
     industry → Industry
     leadstatus → "Not Contacted"
     leadsource → "Lead411 Intent"
     assigned_user_id → Based on territory rules
     ```

3. **Custom Fields Population**
   - `bombora_score` → Intent Score
   - `intent_topics` → Intent Topics (comma-separated)
   - `lead_source` → "Lead411 Bombora"
   - `lead_priority` → Calculated priority
   - `import_date` → Current date
   - `api_reference` → Unique reference ID

4. **Territory Assignment**
   - Rules based on company location/size:
     ```
     IF company_state IN ["CA", "NV", "AZ"] THEN assign_to = "West_Coast_Rep"
     IF company_state IN ["NY", "NJ", "CT"] THEN assign_to = "East_Coast_Rep"  
     IF employee_count > 500 THEN assign_to = "Enterprise_Rep"
     ELSE assign_to = "SMB_Rep"
     ```

5. **Lead Creation**
   - Create lead record in VTiger
   - Capture VTiger lead ID
   - Log success/failure

6. **Output**
   - Pass VTiger lead data to Workflow 3
   - Update processing log
   - Trigger notification for high-priority leads

**Error Handling**:
- Retry failed creations once
- Queue failed records for manual review
- Alert admin if >10% failure rate
- Continue processing other records

---

## Workflow 3: GoHighLevel Campaign Launch

**Trigger**: New lead created in VTiger (from Workflow 2)

### Steps:
1. **Campaign Selection**
   - Select campaign based on lead characteristics:
     ```
     IF intent_score >= 90 THEN "Hot_Lead_Sequence"
     IF intent_score >= 80 THEN "High_Intent_Sequence"  
     IF industry = "Technology" THEN "Tech_Industry_Sequence"
     ELSE "General_Prospecting_Sequence"
     ```

2. **Contact Creation in GoHighLevel**
   - Create contact with lead data
   - Map custom fields:
     - Intent Score
     - Intent Topics
     - VTiger Lead ID
     - Company Industry
     - Employee Count

3. **Campaign Assignment**
   - Add contact to selected campaign
   - Set campaign variables for personalization:
     - `{{company_name}}`
     - `{{intent_topics}}`
     - `{{first_name}}`

4. **Task Creation**
   - Create follow-up task for assigned sales rep
   - Task details:
     - Subject: "Follow up on high-intent lead: {{company_name}}"
     - Due date: +1 business day for hot leads, +3 days for others
     - Notes: Include intent topics and score

5. **Notification**
   - Send Slack notification for hot leads (score ≥ 90)
   - Include lead summary and VTiger link
   - @ mention assigned sales rep

**Error Handling**:
- Retry campaign assignment failures
- Default to general campaign if selection fails
- Log all GoHighLevel API errors
- Create manual task if automation fails

---

## Workflow Configuration

### Make.com Account Setup
- **Plan Required**: Professional ($29/month) - 10,000 operations
- **API Connections Needed**:
  - Google Drive
  - Lead411 API
  - VTiger REST API  
  - GoHighLevel API
  - Slack (optional)

### Estimated Monthly Operations
- 50 leads/day × 30 days = 1,500 leads
- 3 workflows × 5 operations each = 15 operations per lead
- Total: ~2,250 operations/month (well under 10,000 limit)

### Monitoring & Alerts
- **Daily Summary Email**: Processing statistics
- **Error Threshold Alerts**: >5% failure rate
- **Hot Lead Notifications**: Slack for score ≥ 90
- **Weekly Report**: Lead volume and conversion metrics

### Testing Strategy
1. **Phase 1**: Test with 5-10 sample records
2. **Phase 2**: Run with single day's export
3. **Phase 3**: Full automation with monitoring
4. **Phase 4**: Performance optimization

---

## Success Metrics

### Processing Efficiency
- **Target**: <5 minutes from CSV upload to VTiger creation
- **Error Rate**: <3% of processed records
- **Duplicate Prevention**: >95% accuracy

### Lead Quality
- **Contact Enrichment**: >80% success rate
- **Territory Assignment**: 100% accuracy
- **Campaign Assignment**: 100% success rate

### Business Impact
- **Time Savings**: 3+ hours daily → <15 minutes
- **Lead Response Time**: <24 hours for hot leads
- **Data Consistency**: 100% standardized format

---

*This design assumes VTiger API permissions are resolved. If blocked, Workflow 2 will export CSV for manual import instead.*