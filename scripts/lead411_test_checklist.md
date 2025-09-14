# Lead411 Account Testing Checklist

## Step 1: Login and Dashboard Access
- [ ] Log into Lead411 at https://lead411.com
- [ ] Verify dashboard loads correctly
- [ ] Check account status and subscription details
- [ ] Note available features and data access

## Step 2: Bombora Intent Data Access
- [ ] Navigate to "Bombora Intent Data" or "Intent Data" section
- [ ] Verify you can see intent data (this is critical for the automation)
- [ ] Check date range of available data
- [ ] Note any limitations on search volume

## Step 3: Test Search Filters
Try these filters to understand your data:

### Basic Search
- [ ] Company size: 50+ employees
- [ ] Intent score: 70+ (high intent)
- [ ] Time period: Last 7 days
- [ ] Geographic location: [Your target markets]

### Industry Filters (Test 2-3 relevant to RDSTeam)
- [ ] Professional Services
- [ ] Technology
- [ ] Healthcare
- [ ] Manufacturing
- [ ] Financial Services

## Step 4: Test Export Function
- [ ] Can you find "Export" or "Download CSV" option?
- [ ] What fields are available for export?
- [ ] Test with small sample (10-20 records)
- [ ] Check export file format and quality

## Step 5: API Access Investigation
- [ ] Look for "API" or "Developer" section
- [ ] Check if API access is included in your plan
- [ ] Note any API documentation or credentials

## Step 6: Contact Enrichment Testing
- [ ] Can you get individual contact details?
- [ ] What contact fields are available? (Name, Email, Phone, Title)
- [ ] Is this manual or can it be automated?

## Key Questions to Answer

1. **Data Volume**: How many records do typical searches return?
2. **Export Limits**: Any daily/monthly export restrictions?
3. **Data Freshness**: How recent is the intent data?
4. **Field Quality**: Are email addresses and phone numbers included?
5. **API Availability**: Can we automate the export process?

## Report Back
After testing, provide:
- Screenshot of main dashboard
- Sample CSV export (5-10 records, anonymized if needed)
- List of available export fields
- Any limitations or issues encountered

---

**Next Steps After Testing**:
- Analyze sample data structure
- Configure optimal filters for RDSTeam
- Set up regular export schedule
- Plan Make.com integration approach