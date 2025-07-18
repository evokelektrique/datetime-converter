<?php

// A simple PHP server to run the production build of the application.

$public_dir = __DIR__ . '/dist';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1. Route API requests to api.php
if ($uri === '/api.php') {
    require_once __DIR__ . '/api.php';
    return;
}

// 2. Serve static files from the 'dist' directory if they exist
$static_file = $public_dir . $uri;
if (file_exists($static_file) && !is_dir($static_file)) {
    // Determine MIME type
    $mime_types = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'svg' => 'image/svg+xml',
        'ico' => 'image/vnd.microsoft.icon',
    ];
    $extension = strtolower(pathinfo($static_file, PATHINFO_EXTENSION));
    $mime_type = $mime_types[$extension] ?? 'application/octet-stream';
    
    header('Content-Type: ' . $mime_type);
    readfile($static_file);
    return;
}

// 3. For any other request, serve the main index.html file
// This allows the React app to handle its own routing.
$index_file = $public_dir . '/index.html';
if (file_exists($index_file)) {
    readfile($index_file);
} else {
    http_response_code(404);
    echo "404 Not Found: The 'dist/index.html' file does not exist. Please run the build script first.";
}