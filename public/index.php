<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the application root directory
define('APP_ROOT', dirname(__DIR__));

// Initialize autoloading and required components
// require_once APP_ROOT . '/vendor/autoload.php';

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
    // $database = new Database();
    // $db = $database->getConnection();

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

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
            require APP_ROOT . '/src/Views/home.view.php';
            break;

        // Authentication routes
        case 'GET /login':
        case 'GET /login.php':
            if ($authController->isAuthenticated()) {
                header('Location: ' . $authController->getRedirectUrl());
                exit;
            }
            require APP_ROOT . '/src/Views/auth/login.php';
            break;

        case 'POST /login':
        case 'POST /login.php':
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            if (!$email || !$password) {
                $_SESSION['error'] = 'Invalid input data';
                header('Location: /login');
                exit;
            }

            $result = $authController->login($email, $password);
            if ($result['success']) {
                header('Location: ' . $authController->getRedirectUrl());
            } else {
                $_SESSION['error'] = $result['message'];
                header('Location: /login');
            }
            exit;
            break;

        // ... rest of your routes ...

        // Error Routes
        case 'GET /unauthorized':
            http_response_code(403);
            require APP_ROOT . '/src/Views/errors/403.php';
            break;

        default:
            $logger->warning("404 Not Found: {$request_uri}");
            http_response_code(404);
            require APP_ROOT . '/src/Views/errors/404.php';
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