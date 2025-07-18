#!/bin/bash

# A script to install dependencies and run the date converter application.

# --- Functions ---
cleanup() {
    echo ""
    echo "Stopping servers..."
    # Kill the processes using their stored PIDs
    if [ -n "$PHP_PID" ]; then
        kill "$PHP_PID"
    fi
    if [ -n "$VITE_PID" ]; then
        kill "$VITE_PID"
    fi
    echo "Application stopped."
    exit 0
}

# Trap Ctrl+C (interrupt signal) and call the cleanup function
trap cleanup INT

# --- Main Script ---
echo "--- Advanced Date Converter Setup & Run ---"

# 1. Check for prerequisites
command -v php >/dev/null 2>&1 || { echo >&2 "PHP is not installed. Please install it and try again."; exit 1; }
command -v npm >/dev/null 2>&1 || { echo >&2 "Node.js/npm is not installed. Please install it and try again."; exit 1; }

echo "Prerequisites check passed (PHP & npm found)."
echo ""

# 2. Install frontend dependencies
echo "Installing frontend dependencies with npm..."
npm install
if [ $? -ne 0 ]; then
    echo "npm install failed. Please check for errors."
    exit 1
fi
echo "Dependencies installed successfully."
echo ""

# 3. Start the PHP API server in the background
echo "Starting PHP API server on http://localhost:8000..."
php -S localhost:8000 &
PHP_PID=$! # Store the Process ID (PID) of the PHP server

# 4. Start the Vite dev server in the background
echo "Starting Vite dev server..."
npm run dev &
VITE_PID=$! # Store the Process ID of the Vite server

echo ""
echo "----------------------------------------------------"
echo "✅ Application is running!"
echo ""
echo "   - Backend API is on: http://localhost:8000"
echo "   - Frontend is on:  http://localhost:5173 (or as indicated above by Vite)"
echo ""
echo "   Press Ctrl+C in this terminal to stop both servers."
echo "----------------------------------------------------"

# Wait for background processes to finish (which they won't until killed)
wait