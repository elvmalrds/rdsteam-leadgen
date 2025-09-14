@echo off
echo ========================================
echo  RDS Lead Generation - Setup Script
echo ========================================
echo.

REM Check if XAMPP directory exists
if not exist "C:\xampp\htdocs" (
    echo ERROR: XAMPP not found at C:\xampp\htdocs
    echo Please install XAMPP first!
    pause
    exit /b 1
)

REM Create leadgen directory
echo Creating leadgen directory in XAMPP...
if not exist "C:\xampp\htdocs\leadgen" mkdir "C:\xampp\htdocs\leadgen"

REM Copy files
echo Copying project files...
xcopy "html\*" "C:\xampp\htdocs\leadgen\" /E /Y /I

REM Copy environment file
echo Copying environment configuration...
copy ".env" "C:\xampp\htdocs\leadgen\.env"

echo.
echo ========================================
echo  Setup Complete!
echo ========================================
echo.
echo Your dashboard will be available at:
echo http://localhost/leadgen
echo.
echo Next steps:
echo 1. Make sure XAMPP Apache is running
echo 2. Start n8n by running: n8n start
echo 3. Open http://localhost/leadgen in your browser
echo.
pause