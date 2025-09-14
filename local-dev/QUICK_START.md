# Quick Start Guide - Local Development

## ✅ What's Already Done:
- ✅ n8n v1.110.1 installed globally
- ✅ VTiger API working (tested successfully)
- ✅ Node.js v22.11.0 ready
- ✅ Local development structure created

## 🚀 Next Steps (30 minutes to get running):

### 1. Install XAMPP (5 minutes)
```bash
# Download: https://www.apachefriends.org/download.html
# Install to: C:\xampp (default)
# Start: Apache + MySQL from XAMPP Control Panel
```

### 2. Copy Files to XAMPP (2 minutes)
```bash
# Copy everything from local-dev/html/ to:
C:\xampp\htdocs\leadgen\

# Or create a symbolic link:
mklink /D C:\xampp\htdocs\leadgen C:\Users\Elvis\documents\rdsteam-leadgen\local-dev\html
```

### 3. Start n8n and Import Workflows (5 minutes)
```bash
# Option 1: Use our setup script
n8n-setup.bat

# Option 2: Start manually
n8n start

# Access n8n at: http://localhost:5678
```

**Import Workflows:**
1. Open http://localhost:5678 in your browser
2. Import these 3 files (in order):
   - `n8n-workflows/workflow-1-csv-processing.json`
   - `n8n-workflows/workflow-2-hot-leads-notification.json`
   - `n8n-workflows/workflow-3-gohighlevel-integration.json`
3. **Activate each workflow** (toggle switch must be ON)
4. **Test connection**: Run `test-n8n-connection.bat`

### 4. Test Your Setup (2 minutes)
- **Main Dashboard**: http://localhost/leadgen
- **n8n Interface**: http://localhost:5678
- **Test CSV**: Use sample_leads.csv from test-data folder

## 🎯 Your Local Environment URLs:
- **Lead Gen Dashboard**: http://localhost/leadgen
- **n8n Workflows**: http://localhost:5678
- **XAMPP Admin**: http://localhost/phpmyadmin
- **Test Data**: Available in local-dev/test-data/

## 📋 Development Workflow:
1. **Start XAMPP** (Apache + MySQL)
2. **Start n8n**: `n8n start`
3. **Code in**: local-dev/html/ directory
4. **Test at**: http://localhost/leadgen
5. **Build workflows**: http://localhost:5678

## 🔧 What You Have:
- ✅ Bootstrap 5 dashboard ready
- ✅ Sample CSV data for testing
- ✅ VTiger API integration ready
- ✅ n8n platform installed
- ✅ Local file structure organized

## 🎯 Next Development Steps:
1. Create basic PHP API endpoints
2. Build n8n workflows (CSV → VTiger → GoHighLevel)
3. Test with sample data
4. Add real Lead411 integration
5. Deploy to production when ready

**Ready to start coding? Your environment is set up!**