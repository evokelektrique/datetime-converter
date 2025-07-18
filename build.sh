#!/bin/bash

# A script to build the frontend for production.

echo "--- Building Production Assets ---"

# 1. Check for npm
command -v npm >/dev/null 2>&1 || { echo >&2 "Node.js/npm is not installed. Aborting."; exit 1; }

# 2. Install/update dependencies
echo "Installing dependencies..."
npm install
if [ $? -ne 0 ]; then
    echo "npm install failed."
    exit 1
fi

# 3. Run the Vite build process
echo "Building assets with Vite..."
npm run build
if [ $? -ne 0 ]; then
    echo "Vite build failed."
    exit 1
fi

echo ""
echo "✅ Build complete! Production files are in the 'dist' folder."