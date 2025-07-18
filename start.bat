@echo off
REM A script to install dependencies and run the date converter application.

TITLE Advanced Date Converter Launcher

ECHO --- Advanced Date Converter Setup & Run ---

REM 1. Check for prerequisites
ECHO Checking for prerequisites...
where php >nul 2>nul
if %errorlevel% neq 0 (
    ECHO Error: PHP is not installed or not in your PATH.
    ECHO Please install it and try again.
    pause
    exit
)

where npm >nul 2>nul
if %errorlevel% neq 0 (
    ECHO Error: Node.js/npm is not installed or not in your PATH.
    ECHO Please install it and try again.
    pause
    exit
)

ECHO Prerequisites check passed (PHP & npm found).
ECHO.

REM 2. Install frontend dependencies
ECHO Installing frontend dependencies with npm...
call npm install
if %errorlevel% neq 0 (
    ECHO npm install failed. Please check for errors.
    pause
    exit
)
ECHO Dependencies installed successfully.
ECHO.

REM 3. Start servers in new windows
ECHO Starting servers in new windows...

start "PHP API Server" cmd /c "php -S localhost:8000"
start "Vite Frontend Server" cmd /c "npm run dev"

ECHO.
ECHO ----------------------------------------------------
ECHO  Application is running in two new windows.
ECHO.
ECHO    - The Vite frontend will be available at the URL it displays.
ECHO    - The PHP API is running on http://localhost:8000
ECHO.
ECHO    To stop the application, simply close both of the new command prompt windows.
ECHO ----------------------------------------------------
ECHO.
pause