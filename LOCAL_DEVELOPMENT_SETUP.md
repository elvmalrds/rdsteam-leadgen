# Local Development Setup Guide

## Prerequisites ✅ (You already have these!)
- ✅ Node.js v22.11.0 
- ✅ Git configured
- ✅ VTiger API working

## Required Software Installation

### 1. XAMPP (Local Web Server)
```bash
# Download from: https://www.apachefriends.org/download.html
# Install to default location: C:\xampp
# Start Apache and MySQL from XAMPP Control Panel
```

### 2. n8n (Local Automation)
```bash
# Install n8n globally
npm install -g n8n

# Start n8n
n8n start

# Access at: http://localhost:5678
```

## Project Directory Structure

```
C:\Users\Elvis\documents\rdsteam-leadgen\
├── local-dev/                    # Local development files
│   ├── html/                     # XAMPP htdocs content
│   │   ├── index.php              # Main dashboard
│   │   ├── api/                   # API endpoints
│   │   │   ├── process_csv.php    # CSV processing
│   │   │   ├── vtiger_proxy.php   # VTiger API wrapper
│   │   │   └── webhook_handler.php# n8n webhook receiver
│   │   ├── uploads/               # CSV file uploads
│   │   ├── processed/             # Processed files
│   │   └── assets/               # CSS, JS, images
│   ├── n8n-workflows/            # n8n workflow exports
│   └── test-data/                # Sample CSV files for testing
├── docs/                         # Documentation
└── config/                       # Configuration files
```

## Setup Steps

### Step 1: Install XAMPP
1. Download XAMPP from official website
2. Install to C:\xampp (default)
3. Start XAMPP Control Panel
4. Start Apache and MySQL services
5. Test: http://localhost should show XAMPP dashboard

### Step 2: Setup Project Files
```bash
# Create development directory
mkdir C:\Users\Elvis\documents\rdsteam-leadgen\local-dev
mkdir C:\Users\Elvis\documents\rdsteam-leadgen\local-dev\html
mkdir C:\Users\Elvis\documents\rdsteam-leadgen\local-dev\html\api
mkdir C:\Users\Elvis\documents\rdsteam-leadgen\local-dev\html\uploads
mkdir C:\Users\Elvis\documents\rdsteam-leadgen\local-dev\html\processed
mkdir C:\Users\Elvis\documents\rdsteam-leadgen\local-dev\html\assets
mkdir C:\Users\Elvis\documents\rdsteam-leadgen\local-dev\n8n-workflows
mkdir C:\Users\Elvis\documents\rdsteam-leadgen\local-dev\test-data

# Copy to XAMPP directory
# You'll copy local-dev/html/* to C:\xampp\htdocs\leadgen\
```

### Step 3: Install n8n
```bash
# Install n8n globally
npm install -g n8n

# Create n8n data directory
mkdir C:\Users\Elvis\.n8n

# Start n8n
n8n start
```

### Step 4: Configure Local Environment
Create `.env` file in local-dev/:
```env
# VTiger Configuration (your existing working config)
VTIGER_URL=https://rdsteamglobalpresenceorg.od2.vtiger.com
VTIGER_USERNAME=elvis@rdsteam.com
VTIGER_ACCESS_KEY=dN9NniLcjQq5tg72

# Local Development URLs
LOCAL_WEB_URL=http://localhost/leadgen
N8N_URL=http://localhost:5678
WEBHOOK_URL=http://localhost/leadgen/api/webhook_handler.php

# API Keys (add as you get them)
LEAD411_API_KEY=your_lead411_key_here
GOHIGHLEVEL_API_KEY=your_ghl_key_here

# Database (local MySQL from XAMPP)
DB_HOST=localhost
DB_NAME=leadgen_local
DB_USER=root
DB_PASSWORD=
```

## Development Workflow

### Daily Development Process:
1. **Start XAMPP**: Apache + MySQL
2. **Start n8n**: `n8n start`
3. **Access Dashboard**: http://localhost/leadgen
4. **Access n8n**: http://localhost:5678
5. **Test with sample data**: Upload CSV files
6. **Monitor workflows**: Watch n8n executions

### Testing Process:
1. **Upload CSV**: Use test data files
2. **Verify Processing**: Check n8n workflow execution
3. **Confirm VTiger**: Verify leads created (you know this works!)
4. **Test Integration**: End-to-end workflow validation

## Local URLs
- **Main Dashboard**: http://localhost/leadgen
- **n8n Interface**: http://localhost:5678
- **XAMPP Admin**: http://localhost/phpmyadmin
- **API Endpoints**: http://localhost/leadgen/api/

## Advantages of Local Development

### ✅ Benefits:
- **No hosting costs** during development
- **Fast iteration** - immediate testing
- **Full control** - no external dependencies  
- **Safe testing** - won't affect production systems
- **Offline development** - work anywhere
- **Easy debugging** - direct file access

### 🔄 When Ready to Deploy:
1. **Zip local-dev/html/** → Upload to production server
2. **Export n8n workflows** → Import to production n8n
3. **Update .env** → Production URLs and credentials
4. **Test production** → Verify everything works

## Next Steps After Setup:
1. **Create sample data** for testing
2. **Build basic dashboard** (index.php)
3. **Create API endpoints** for n8n integration
4. **Test VTiger integration** locally
5. **Build n8n workflows** step by step

## Troubleshooting

### XAMPP Issues:
- **Port conflicts**: Change Apache to port 8080 if needed
- **Permissions**: Run XAMPP as administrator
- **MySQL**: Default user=root, password=blank

### n8n Issues:
- **Port conflicts**: Use `n8n start --port=5679` if needed
- **Data location**: ~/.n8n directory stores workflows
- **Webhooks**: Use http://localhost URLs for testing

Ready to start building your local development environment?