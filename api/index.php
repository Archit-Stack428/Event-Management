<?php
// Set working directory to project root so all relative requires (config.php, dbconnect.php) work seamlessly
chdir(__DIR__ . '/..');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Handle root path
if ($uri === '/' || $uri === '' || $uri === '/index.php') {
    require __DIR__ . '/../index.php';
    exit;
}

$target = __DIR__ . '/..' . $uri;

// If a PHP file exists matching the request path, require it
if (file_exists($target) && !is_dir($target)) {
    $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
    if ($ext === 'php') {
        require $target;
        exit;
    }
}

// Fallback to index.php
require __DIR__ . '/../index.php';
