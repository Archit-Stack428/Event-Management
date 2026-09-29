<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

// Ensure /tmp is used for sessions in serverless lambdas
@ini_set('session.save_path', '/tmp');

chdir(__DIR__ . '/..');

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && ($err['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR))) {
        http_response_code(500);
        header('Content-Type: text/plain');
        echo "PHP FATAL ERROR in Lambda:\n";
        print_r($err);
    }
});


// Initialize database connection & validate persistent login token on every serverless invocation
require_once __DIR__ . '/../dbconnect.php';

$rawUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = '/' . trim($rawUri, '/');

// Handle root path
if ($uri === '/' || $uri === '/index.php') {
    require __DIR__ . '/../index.php';
    exit;
}

// Admin panel alias: /admin or /admin.php -> dashboard.php
if ($uri === '/admin' || $uri === '/admin.php') {
    require __DIR__ . '/../dashboard.php';
    exit;
}

$target = __DIR__ . '/..' . $uri;

// If a PHP file exists matching the exact request path, require it
if (file_exists($target) && !is_dir($target)) {
    $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
    if ($ext === 'php') {
        require $target;
        exit;
    }
}

// If $uri . '.php' exists, require it (supports clean URLs like /dashboard, /events, /login)
if (file_exists($target . '.php') && !is_dir($target . '.php')) {
    require $target . '.php';
    exit;
}

// Fallback to index.php
require __DIR__ . '/../index.php';
