# Lead411 Manual Export Standard Operating Procedure

## Daily Export Process (15 minutes)

### Prerequisites
- Active Lead411 account with Bombora data access
- Access to Google Drive folder: `/rdsteam-leadgen/imports/`
- VTiger admin permissions issue resolved

### Step 1: Access Lead411 Dashboard (2 minutes)
1. Log into Lead411 at https://lead411.com
2. Navigate to "Bombora Intent Data" section
3. Verify data is current (within 24 hours)

### Step 2: Configure Export Filters (5 minutes)

#### Intent Score Filters
- **Minimum Score**: 70+ (high intent only)
- **Time Period**: Last 7 days
- **Company Size**: 50+ employees (adjust based on ICP)

#### Industry Filters (Select All That Apply)
- [ ] Professional Services
- [ ] Technology
- [ ] Healthcare
- [ ] Manufacturing  
- [ ] Financial Services
- [ ] [Add other relevant industries]

#### Geographic Filters
- **Primary Markets**: [Define based on RDSTeam territories]
- **Secondary Markets**: [Optional expansion areas]

### Step 3: Generate Export (3 minutes)
1. Click "Export to CSV"
2. Select fields to include:
   - ✅ Company Name
   - ✅ Website
   - ✅ Industry
   - ✅ Employee Count
   - ✅ Revenue (if available)
   - ✅ Intent Topics
   - ✅ Intent Score
   - ✅ Contact Information (if available)
   - ✅ Address
   - ✅ Phone

3. Confirm export settings
4. Generate file

### Step 4: File Management (3 minutes)
1. Download CSV file
2. Rename file using format: `leads_YYYYMMDD_HHMM.csv`
   - Example: `leads_20240904_0900.csv`
3. Upload to Google Drive folder: `/rdsteam-leadgen/imports/`
4. Verify file uploaded successfully

### Step 5: Quality Check (2 minutes)
1. Open CSV file to verify:
   - ✅ Data populated in all key columns
   - ✅ Intent scores are 70+
   - ✅ Company names are not obviously duplicated
   - ✅ File contains 10-100 records (reasonable daily volume)

2. If issues found:
   - Note in daily log
   - Re-export if necessary
   - Contact Lead411 support if systemic issues

## File Naming Convention
- **Format**: `leads_YYYYMMDD_HHMM.csv`
- **Examples**:
  - `leads_20240904_0900.csv` (Morning export)
  - `leads_20240904_1400.csv` (Afternoon export)

## Quality Standards
- **Minimum Records**: 5 per export (if less, investigate)
- **Maximum Records**: 200 per export (if more, consider filtering)
- **Intent Score**: All records must be 70+
- **Company Data**: Company name and industry required
- **Contact Data**: At least company phone or website required

## Backup Process
- Keep previous 7 days of files in `/processed/` folder
- Archive monthly files to long-term storage
- Maintain export log with record counts and issues

## Troubleshooting

### Common Issues
1. **No records found**: Adjust filters, check data freshness
2. **Duplicate companies**: Normal - automation will handle deduplication
3. **Missing contact info**: Expected - Make.com will enrich via Lead411 API
4. **File upload fails**: Check internet connection, retry upload

### Contact Information
- **Lead411 Support**: [To be added after account setup]
- **Technical Support**: Elvis
- **Process Owner**: [To be assigned]

## Success Metrics
- **Time per export**: <15 minutes
- **Daily consistency**: 90%+ days with successful export
- **Data quality**: <5% records rejected by automation
- **Volume consistency**: 10-50 records per day average

## Automation Goal
This manual process is Phase 1. Target is to eliminate manual steps by:
1. **Phase 2**: Automated filtering and download
2. **Phase 3**: Real-time API integration (if Lead411 provides API access)
3. **Phase 4**: Predictive lead scoring to reduce manual filtering

---
*This SOP should be reviewed weekly and updated based on results and feedback.*