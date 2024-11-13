<?php
// Start session at the very beginning with secure settings
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }

    session_start();
}

// Define the application root directory
define('APP_ROOT', dirname(__DIR__));

// Initialize autoloading and required components
try {
    require_once APP_ROOT . '/vendor/autoload.php';
} catch (Exception $e) {
    echo "Autoload error: " . $e->getMessage();
    exit;
}

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->load();

// Import required classes
use App\Config\Database;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\CustomerController;
use App\Controllers\FarmerController;
use App\Controllers\AdminController;
use App\Controllers\AdminAuthController;
use App\Controllers\ProductController;
use App\Controllers\OrderController;
use App\Controllers\HomeController;
use App\Controllers\ErrorController;
use App\Controllers\Api\AuthController as ApiAuthController;
use App\Middleware\RoleMiddleware;
use App\Models\Logger;

// Error handling for development/production
if ($_ENV['APP_ENV'] === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    error_reporting(0);
}

// Initialize core components
try {
    // Initialize logger first for error tracking
    $logger = new Logger('app');

    // Check if required environment variables are set
    $requiredEnvVars = ['MYSQL_HOST', 'MYSQL_DATABASE', 'MYSQL_USER', 'MYSQL_PASSWORD'];
    foreach ($requiredEnvVars as $var) {
        if (!isset($_ENV[$var])) {
            throw new Exception("Missing required environment variable: {$var}");
        }
    }

    // Initialize database connection
    try {
        $database = new Database();
        $db = $database->getConnection();
    } catch (Exception $e) {
        $logger->error("Database connection error: " . $e->getMessage());
        throw $e;
    }

    // Initialize middleware
    $roleMiddleware = new RoleMiddleware($logger);

    // Define public admin routes that don't require authentication
    $publicAdminRoutes = [
        'GET /admin/login' => ['AdminAuthController', 'showLoginForm'],
        'POST /admin/login' => ['AdminAuthController', 'login'],
        'GET /admin/forgot-password' => ['AdminAuthController', 'showForgotPasswordForm'],
        'POST /admin/forgot-password' => ['AdminAuthController', 'forgotPassword']
    ];

    // Define protected admin routes
    $protectedAdminRoutes = [
        'GET /admin' => ['AdminController', 'dashboard'],
        'GET /admin/dashboard' => ['AdminController', 'dashboard'],
        'POST /admin/logout' => ['AdminAuthController', 'logout'],
        'GET /admin/farmers' => ['AdminController', 'manageFarmers'],
        'GET /admin/farmers/{id}' => ['AdminController', 'viewFarmer'],
        'POST /admin/farmers/approve' => ['AdminController', 'approveFarmer'],
        'POST /admin/farmers/reject' => ['AdminController', 'rejectFarmer'],
        'POST /admin/farmers/suspend' => ['AdminController', 'suspendFarmer'],
        'GET /admin/products' => ['AdminController', 'manageProducts'],
        'POST /admin/products/approve' => ['AdminController', 'approveProduct'],
        'POST /admin/products/reject' => ['AdminController', 'rejectProduct'],
        'GET /admin/orders' => ['AdminController', 'manageOrders'],
        'GET /admin/orders/{id}' => ['AdminController', 'viewOrder'],
        'POST /admin/orders/update-status' => ['AdminController', 'updateOrderStatus'],
        'GET /admin/system/health' => ['AdminController', 'systemHealth'],
        'GET /admin/system/logs' => ['AdminController', 'systemLogs'],
        'GET /admin/system/metrics' => ['AdminController', 'systemMetrics']
    ];

    // Define protected route groups for other roles
    $protectedRoutes = [
        'customer' => [
            // ... your existing customer routes
        ],
        'farmer' => [
            // ... your existing farmer routes
        ]
    ];

    // Define public routes
    $publicRoutes = [
        'GET /' => ['HomeController', 'index'],
        'GET /login' => ['AuthController', 'loginForm'],
        'POST /login' => ['AuthController', 'login'],
        'GET /register' => ['AuthController', 'customerRegistrationForm'],
        'POST /register' => ['AuthController', 'register'],
        'GET /register/farmer' => ['AuthController', 'farmerRegistrationForm'],
        'POST /register/farmer' => ['AuthController', 'register'],
        'GET /logout' => ['AuthController', 'logout'],
        'GET /products' => ['ProductController', 'listProducts'],
        'GET /farmers' => ['HomeController', 'listFarmers'],
        'GET /about' => ['HomeController', 'about'],
        'GET /contact' => ['HomeController', 'contact'],
        'GET /unauthorized' => ['ErrorController', 'unauthorized']
    ];

    // Get request method and URI
    $request_method = $_SERVER['REQUEST_METHOD'];
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $route = $request_method . ' ' . $request_uri;

    // Route handling
    $routeHandled = false;

    // Check if it's a public admin route
    if (isset($publicAdminRoutes[$route])) {
        $controllerName = "App\\Controllers\\" . $publicAdminRoutes[$route][0];
        $methodName = $publicAdminRoutes[$route][1];

        $controller = new $controllerName($db, $logger);
        $controller->$methodName();
        $routeHandled = true;
    }
    // Check if it's a protected admin route
    elseif (isset($protectedAdminRoutes[$route])) {
        try {
            // Apply admin role middleware
            if ($roleMiddleware->handle('admin')()) {
                $controllerName = "App\\Controllers\\" . $protectedAdminRoutes[$route][0];
                $methodName = $protectedAdminRoutes[$route][1];

                $controller = new $controllerName($db, $logger);
                $controller->$methodName();
                $routeHandled = true;
            }
        } catch (Exception $e) {
            if (!headers_sent()) {
                header('Location: /admin/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
                exit();
            }
            echo '<script>window.location.href="/admin/login?redirect=' . urlencode($_SERVER['REQUEST_URI']) . '";</script>';
            exit();
        }
    }
    // Check other protected routes
    elseif (!$routeHandled) {
        foreach ($protectedRoutes as $role => $routes) {
            if (isset($routes[$route])) {
                try {
                    if ($roleMiddleware->handle($role)()) {
                        $controllerName = "App\\Controllers\\" . $routes[$route][0];
                        $methodName = $routes[$route][1];

                        $controller = new $controllerName($db, $logger);
                        $controller->$methodName();
                        $routeHandled = true;
                        break;
                    }
                } catch (Exception $e) {
                    if (!headers_sent()) {
                        header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
                        exit();
                    }
                    echo '<script>window.location.href="/login?redirect=' . urlencode($_SERVER['REQUEST_URI']) . '";</script>';
                    exit();
                }
            }
        }
    }

    // Check public routes if not handled
    if (!$routeHandled && isset($publicRoutes[$route])) {
        $controllerName = "App\\Controllers\\" . $publicRoutes[$route][0];
        $methodName = $publicRoutes[$route][1];

        $controller = new $controllerName($db, $logger);
        $controller->$methodName();
        $routeHandled = true;
    }

    // If no route matched, show 404
    if (!$routeHandled) {
        $logger->warning("404 Not Found: {$request_uri}");
        if (!headers_sent()) {
            http_response_code(404);
        }
        $controller = new ErrorController($db, $logger);
        $controller->notFound();
    }
} catch (Exception $e) {
    $logger->error("Application error: " . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);

    if (!headers_sent()) {
        http_response_code(500);
    }
    require APP_ROOT . '/src/Views/errors/500.php';
}

// Clean up any unused session messages
if (isset($_SESSION['error'])) unset($_SESSION['error']);
if (isset($_SESSION['success'])) unset($_SESSION['success']);
