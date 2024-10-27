<?php
if (session_status() === PHP_SESSION_NONE) {
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

    // Define protected route groups
    $protectedRoutes = [
        'customer' => [
            'GET /customer/dashboard' => ['CustomerController', 'index'],
            'GET /customer/orders' => ['CustomerController', 'getOrderHistory'],
            'POST /customer/profile/update' => ['CustomerController', 'updateCustomerProfile'],
            'POST /customer/preferences' => ['CustomerController', 'updatePreferences'],
            'POST /customer/products/save' => ['CustomerController', 'saveProduct'],
            'POST /customer/products/remove' => ['CustomerController', 'removeSavedProduct'],
            'GET /customer/orders/active' => ['CustomerController', 'getActiveOrders'],
            'GET /customer/products/saved' => ['CustomerController', 'getSavedProducts'],
            'GET /customer/stats' => ['CustomerController', 'getCustomerStats']
        ],
        'farmer' => [
            'GET /farmer/dashboard' => ['FarmerController', 'index'],
            'POST /farmer/profile/update' => ['FarmerController', 'updateProfile'],
            'GET /farmer/products' => ['FarmerController', 'getProducts'],
            'POST /farmer/products' => ['FarmerController', 'addProduct'],
            'PUT /farmer/products/{id}' => ['FarmerController', 'updateProduct'],
            'POST /farmer/plantings' => ['FarmerController', 'addPlanting'],
            'POST /farmer/chemical-usage' => ['FarmerController', 'recordChemicalUsage'],
            'POST /farmer/harvests' => ['FarmerController', 'recordHarvest'],
            'GET /farmer/orders/pending' => ['FarmerController', 'getPendingOrders'],
            'GET /farmer/stats' => ['FarmerController', 'getMonthlyStats']
        ],
        'admin' => [
            'GET /admin/dashboard' => ['AdminController', 'index'],
            'GET /admin/users' => ['AdminController', 'manageUsers'],
            'GET /admin/farmers' => ['AdminController', 'manageFarmers'],
            'POST /admin/farmers/approve' => ['AdminController', 'approveNewFarmer'],
            'POST /admin/users/suspend' => ['AdminController', 'suspendUser'],
            'GET /admin/system/health' => ['AdminController', 'systemHealth'],
            'GET /admin/system/logs' => ['AdminController', 'getSystemLogs'],
            'GET /admin/stats' => ['AdminController', 'getSystemMetrics']
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

    // Define API routes
    $apiRoutes = [
        'POST /api/auth/login' => ['ApiAuthController', 'login'],
        'POST /api/auth/register' => ['ApiAuthController', 'register'],
        'GET /api/products' => ['Api\ProductController', 'index'],
        'POST /api/products' => ['Api\ProductController', 'create'],
        'GET /api/orders' => ['Api\OrderController', 'index'],
        'POST /api/orders' => ['Api\OrderController', 'create'],
        'PUT /api/orders/{id}/status' => ['Api\OrderController', 'updateStatus']
    ];

    // Get request method and URI
    $request_method = $_SERVER['REQUEST_METHOD'];
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $route = $request_method . ' ' . $request_uri;

    // Handle CORS for API routes
    if (strpos($request_uri, '/api/') === 0) {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Requested-With, Authorization');
        
        if ($request_method === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    // Route handling
    $routeHandled = false;

    // Check protected routes first
    foreach ($protectedRoutes as $role => $routes) {
        if (isset($routes[$route])) {
            try {
                // Apply role middleware
                $roleMiddleware->handle($role)();
                
                // Route is authorized, execute controller
                $controllerName = "App\\Controllers\\" . $routes[$route][0];
                $methodName = $routes[$route][1];
                
                $controller = new $controllerName($db, $logger);
                $controller->$methodName();
                $routeHandled = true;
                break;
            } catch (Exception $e) {
                if ($e->getCode() === 401) {
                    header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
                } else {
                    header('Location: /unauthorized');
                }
                exit;
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

    // Check API routes if not handled
    if (!$routeHandled && isset($apiRoutes[$route])) {
        $controllerName = "App\\Controllers\\" . $apiRoutes[$route][0];
        $methodName = $apiRoutes[$route][1];
        
        header('Content-Type: application/json');
        $controller = new $controllerName($db, $logger);
        $controller->$methodName();
        $routeHandled = true;
    }

    // If no route matched, show 404
    if (!$routeHandled) {
        $logger->warning("404 Not Found: {$request_uri}");
        http_response_code(404);
        $controller = new ErrorController($db, $logger);
        $controller->notFound();
    }

} catch (Exception $e) {
    $logger->error("Application error: " . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(500);
    if (strpos($request_uri, '/api/') === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error'
        ]);
    } else {
        require APP_ROOT . '/src/Views/errors/500.php';
    }
}

// Clean up any unused session messages
if (isset($_SESSION['error'])) unset($_SESSION['error']);
if (isset($_SESSION['success'])) unset($_SESSION['success']);