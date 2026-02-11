<?php
/**
 * PHP Built-in Server Router
 * For clean URL support in local development
 */

$uri = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '/';
$path = __DIR__ . $uri;

// If file exists and is not index.php, serve it directly
if ($uri !== '/' && file_exists($path) && is_file($path) && basename($path) !== 'index.php') {
    return false;
}

// Handle pretty URLs - route to appropriate PHP files
$routes = [
    '/' => 'index.php',
    '/login' => 'login.php',
    '/logout' => 'logout.php',
    '/redirect' => 'redirect.php',
    '/setup' => 'setup/index.php',
    '/portal' => 'portal/index.php',
    '/portal/' => 'portal/index.php',
];

if (isset($routes[$uri])) {
    require __DIR__ . '/' . $routes[$uri];
} else {
    // Default to index
    require __DIR__ . '/index.php';
}
