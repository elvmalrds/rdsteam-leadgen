@echo off
echo Starting n8n Setup for RDS Lead Generation Automation
echo ======================================================
echo.

REM Check if n8n is installed
echo Checking n8n installation...
n8n --version
if %errorlevel% neq 0 (
    echo ERROR: n8n is not installed or not in PATH
    echo Please install n8n first: npm install n8n -g
    pause
    exit /b 1
)

echo.
echo n8n is installed and ready!
echo.

echo Starting n8n server...
echo.
echo IMPORTANT: Once n8n starts, you can:
echo 1. Access n8n at: http://localhost:5678
echo 2. Import the workflow files from: n8n-workflows/ folder
echo 3. Configure API credentials as needed
echo.
echo Press Ctrl+C to stop n8n when you're done
echo.

REM Start n8n
n8n start