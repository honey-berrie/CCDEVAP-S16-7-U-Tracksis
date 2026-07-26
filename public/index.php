<?php

// front controller (single entry point for the entire app)

session_start();

// autoloader
spl_autoload_register(function ($class) {

    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0){
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// define the base url so all redirects include the subfolder path
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') ?: '');

// strip the subdirectory base path from REQUEST_URI
$basePath = BASE_URL;
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// remove query string for base path comparison
$uriWithoutQuery = strtok($requestUri, '?');

if ($basePath !== '' && $basePath !== '/' && strpos($uriWithoutQuery, $basePath) === 0) {

    $appUri = substr($uriWithoutQuery, strlen($basePath));

    // re-attach query string if present
    $queryString = $_SERVER['QUERY_STRING'] ?? '';

    if ($queryString !== '') {
        $appUri = strtok($appUri, '?') . '?' . $queryString;
    }

    $_SERVER['APP_REQUEST_URI'] = $appUri ?: '/';

} else {
    $_SERVER['APP_REQUEST_URI'] = $requestUri;
}

// load config
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/routes.php';
