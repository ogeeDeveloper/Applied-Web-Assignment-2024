<?php

// Secure session initialization
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');
session_start();

// Define application root
define('APP_ROOT', dirname(__DIR__));

// Load composer autoload
require_once APP_ROOT . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->load();

// Initialize logger and database
use App\Config\Database;
use App\Models\AppLogger;

$logger = new AppLogger('app');
$database = new Database();
$db = $database->getConnection();

// Load routes
$routes = include APP_ROOT . '/src/config/Routes.php';

// Get current route
$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = $request_method . ' ' . $request_uri;

$routeHandled = false;

// Match and handle routes
foreach ($routes as $group => $groupRoutes) {
    if (isset($groupRoutes[$route])) {
        [$controllerName, $methodName] = $groupRoutes[$route];
        $controllerClass = "App\\Controllers\\$controllerName";

        $controller = new $controllerClass($db, $logger);
        $controller->$methodName();
        $routeHandled = true;
        break;
    }
}

// Handle unmatched routes
if (!$routeHandled) {
    http_response_code(404);
    echo "404 - Page Not Found";
    exit;
}
