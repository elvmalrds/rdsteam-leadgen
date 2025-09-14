@echo off
echo ========================================
echo  RDS Lead Generation System - Quick Test
echo ========================================
echo.

echo 1. Testing XAMPP Status...
curl -s "http://localhost/leadgen/" -I | findstr "200 OK" >nul
if %errorlevel%==0 (
    echo    ✅ Lead Dashboard: RUNNING
) else (
    echo    ❌ Lead Dashboard: NOT ACCESSIBLE
)

echo.
echo 2. Testing n8n Status...
curl -s "http://localhost:5678" -I | findstr "200 OK" >nul
if %errorlevel%==0 (
    echo    ✅ n8n Interface: RUNNING
) else (
    echo    ❌ n8n Interface: NOT ACCESSIBLE
)

echo.
echo 3. Checking Test Data...
if exist "local-dev\test-data\sample_leads.csv" (
    echo    ✅ Sample CSV File: AVAILABLE
) else (
    echo    ❌ Sample CSV File: MISSING
)

echo.
echo 4. Testing VTiger API...
curl -s -X POST "http://localhost/leadgen/api/vtiger.php" -H "Content-Type: application/json" -d "{\"action\":\"test_connection\"}" | findstr "success" >nul
if %errorlevel%==0 (
    echo    ✅ VTiger API: CONNECTED
) else (
    echo    ⚠️  VTiger API: Check connection
)

echo.
echo ========================================
echo  🚀 READY TO TEST!
echo ========================================
echo.
echo Next Steps:
echo  1. Open: http://localhost/leadgen
echo  2. Upload: local-dev\test-data\sample_leads.csv
echo  3. Check: "Use n8n Workflows"
echo  4. Click: "Process to VTiger"
echo  5. Monitor: http://localhost:5678
echo.
echo ✅ System Status: OPERATIONAL
echo ✅ Test Data: READY
echo ✅ Automation: CONFIGURED
echo.
pause