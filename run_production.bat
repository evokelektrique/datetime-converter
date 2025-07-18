@echo off
TITLE Build and Run Production Application

ECHO --- Building and Running Production Application ---
ECHO.

REM 1. Check for prerequisites
ECHO Checking for prerequisites...
where php >nul 2>nul
if %errorlevel% neq 0 (
    ECHO Error: PHP is not installed or not in your PATH.
    pause
    exit
)
where npm >nul 2>nul
if %errorlevel% neq 0 (
    ECHO Error: Node.js/npm is not installed or not in your PATH.
    pause
    exit
)
ECHO Prerequisites check passed.
ECHO.

REM 2. Install dependencies
ECHO Installing dependencies...
call npm install
if %errorlevel% neq 0 (
    ECHO npm install failed.
    pause
    exit
)
ECHO.

REM 3. Build the project
ECHO Building assets with Vite...
call npm run build
if %errorlevel% neq 0 (
    ECHO Vite build failed.
    pause
    exit
)
ECHO Build complete!
ECHO.

REM 4. Check if build was successful
if not exist dist (
    ECHO Error: 'dist' folder not found after build. Something went wrong.
    pause
    exit
)

REM 5. Start the production server
ECHO Starting production server on http://localhost:8000
ECHO Press Ctrl+C in this window to stop.
php -S localhost:8000 server.php