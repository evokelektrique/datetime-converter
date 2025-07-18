#!/bin/bash

# A single script to build the frontend AND run the production server.

echo "--- Building and Running Production Application ---"

# 1. Check for prerequisites
command -v php >/dev/null 2>&1 || { echo >&2 "PHP is not installed. Aborting."; exit 1; }
command -v npm >/dev/null 2>&1 || { echo >&2 "Node.js/npm is not installed. Aborting."; exit 1; }
echo "Prerequisites check passed."
echo ""

# 2. Install/update dependencies
echo "Installing dependencies..."
npm install
if [ $? -ne 0 ]; then
    echo "npm install failed."
    exit 1
fi
echo ""

# 3. Run the Vite build process
echo "Building assets with Vite..."
npm run build
if [ $? -ne 0 ]; then
    echo "Vite build failed."
    exit 1
fi
echo "Build complete!"
echo ""

# 4. Check if build was successful before running
if [ ! -d "dist" ]; then
    echo "Error: 'dist' folder not found after build. Something went wrong."
    exit 1
fi

# 5. Start the PHP production server
echo "Starting production server on http://localhost:8000"
echo "Press Ctrl+C to stop."
php -S localhost:8000 server.php