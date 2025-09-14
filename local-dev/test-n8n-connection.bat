@echo off
echo Testing n8n Connection and Webhooks
echo =====================================
echo.

echo Testing if n8n is running...
curl -s http://localhost:5678 >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: n8n is not running on localhost:5678
    echo Please start n8n first with: n8n start
    pause
    exit /b 1
)

echo ✅ n8n is running!
echo.

echo Testing CSV Processing Webhook...
curl -X POST http://localhost:5678/webhook/csv-upload ^
  -H "Content-Type: application/json" ^
  -d "{\"test\": true, \"message\": \"Test from batch script\"}"

echo.
echo.
echo Testing Hot Leads Webhook...
curl -X POST http://localhost:5678/webhook/hot-leads ^
  -H "Content-Type: application/json" ^
  -d "{\"test\": true, \"hot_leads\": []}"

echo.
echo.
echo Testing GoHighLevel Webhook...  
curl -X POST http://localhost:5678/webhook/gohighlevel ^
  -H "Content-Type: application/json" ^
  -d "{\"test\": true, \"leads\": []}"

echo.
echo.
echo Test completed! Check n8n interface at http://localhost:5678
echo Look for executions in the workflows to see if they triggered properly.
echo.
pause