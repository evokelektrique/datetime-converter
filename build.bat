@echo off
REM A script to build the frontend for production.

TITLE Build Production Assets

ECHO --- Building Production Assets ---

REM 1. Check for npm
where npm >nul 2>nul
if %errorlevel% neq 0 (
    ECHO Error: Node.js/npm is not installed or not in your PATH.
    pause
    exit
)

REM 2. Install/update dependencies
ECHO Installing dependencies...
call npm install
if %errorlevel% neq 0 (
    ECHO npm install failed.
    pause
    exit
)

REM 3. Run the Vite build process
ECHO Building assets with Vite...
call npm run build
if %errorlevel% neq 0 (
    ECHO Vite build failed.
    pause
    exit
)

ECHO.
ECHO ========================================================
ECHO  Build complete! Production files are in the 'dist' folder.
ECHO ========================================================
ECHO.
pause