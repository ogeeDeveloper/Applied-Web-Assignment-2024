<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

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

// use App\Config\Database;
use App\config\Database; 
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\Api\AuthController as ApiAuthController;
use App\Controllers\Api\ProductController;
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
        echo "Database connection error: " . $e->getMessage();
        exit;
    }

    // Initialize controllers
    $authController = new AuthController($db, $logger);
    $apiAuthController = new ApiAuthController($db, $logger);
    $dashboardController = new DashboardController($db, $logger);
    $productController = new ProductController($db, $logger);
    $roleMiddleware = new RoleMiddleware($logger);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    
    if (file_exists(APP_ROOT . '/src/Views/errors/500.php')) {
        require APP_ROOT . '/src/Views/errors/500.php';
    } else {
        echo "Internal Server Error. Please try again later.";
    }
    exit;
}

// Get request method and URI
$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = $request_method . ' ' . $request_uri;

// CORS headers for API routes
if (strpos($request_uri, '/api/') === 0) {
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Origin, Content-Type, X-Requested-With, Authorization');
    
    // Handle preflight requests
    if ($request_method === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

try {
    // Main routing switch
    switch ($route) {
        // Public routes
        case 'GET /':
            case 'GET /index.php':
                $controller = new \App\Controllers\HomeController($db, $logger);
                $controller->index();
                break;

        // Authentication routes
        case 'GET /login':
            case 'GET /login.php':
                if ($authController->isAuthenticated()) {
                    header('Location: ' . $authController->getRedirectUrl());
                    exit;
                }
                $controller = new \App\Controllers\AuthController($db, $logger);
                $controller->loginForm();
                break;

        case 'POST /api/auth/login':
            header('Content-Type: application/json');
            $authController = new \App\Controllers\Api\AuthController($db, $logger);
            $authController->login();
            exit();
            break;
        
        // Customer registration
        case 'GET /register':
            $authController->customerRegistrationForm();
            break;

        // case 'POST /register':
        //     $response = $authController->register(array_merge($_POST, ['role' => 'customer']));
        //     if ($response['success']) {
        //         $_SESSION['success'] = $response['message'];
        //         header('Location: /login');
        //     } else {
        //         $_SESSION['error'] = $response['message'];
        //         header('Location: /register');
        //     }
        //     exit;
        //     break;

        case 'POST /api/auth/customers/register':
            header('Content-Type: application/json');
            $authController = new \App\Controllers\AuthController($db, $logger);
            $result = $authController->register(array_merge($_POST, ['role' => 'customer']));
            echo json_encode($result);
            exit();
    
        // Farmer registration
        case 'GET /register/farmer':
            $authController->farmerRegistrationForm();
            break;

        case 'POST /register/farmer':
            $response = $authController->register(array_merge($_POST, ['role' => 'farmer']));
            if ($response['success']) {
                $_SESSION['success'] = $response['message'];
                header('Location: /login');
            } else {
                $_SESSION['error'] = $response['message'];
                header('Location: /register/farmer');
            }
            exit;
            break;

        case 'GET /logout':
            $authController->logout();
            header('Location: /login');
            exit;
            break;
        
         // Dashboard routes
        case 'GET /customer/dashboard':
            if ($authController->hasRole('customer')) {
                $dashboardController->customerDashboard();
            } else {
                header('Location: /unauthorized');
                exit;
            }
            break;

        case 'GET /farmer/dashboard':
            if ($authController->hasRole('farmer')) {
                $dashboardController->farmerDashboard();
            } else {
                header('Location: /unauthorized');
                exit;
            }
            break;

        case 'GET /admin/dashboard':
            if ($authController->hasRole('admin')) {
                $dashboardController->adminDashboard();
            } else {
                header('Location: /unauthorized');
                exit;
            }
            break;

        // Error Routes
        case 'GET /unauthorized':
            http_response_code(403);
            $controller = new \App\Controllers\ErrorController($db, $logger);
            $controller->unauthorized();
            break;

        default:
            $logger->warning("404 Not Found: {$request_uri}");
            http_response_code(404);
            $controller = new \App\Controllers\ErrorController($db, $logger);
            $controller->notFound();
            break;
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