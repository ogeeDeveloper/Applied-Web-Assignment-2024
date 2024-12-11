<?php

// Secure session initialization
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 7200); // 2 hours
ini_set('session.cookie_lifetime', 7200);

// Import required classes
use App\Config\Database;
use App\Models\AppLogger;
use App\Constants\Roles;
use App\Utils\SessionManager;
use App\Middleware\RoleMiddleware;
use App\Utils\AssetsHelper;

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// Define application root
define('APP_ROOT', dirname(__DIR__));

try {
    // Load composer autoload
    require_once APP_ROOT . '/vendor/autoload.php';

    // Load environment variables
    $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
    $dotenv->load();

    // Error handling based on environment
    // if ($_ENV['APP_ENV'] === 'development') {
    //     ini_set('display_errors', 1);
    //     ini_set('display_startup_errors', 1);
    //     error_reporting(E_ALL);
    // } else {
    //     error_reporting(0);
    // }

    // Initialize logger
    $logger = new AppLogger('app');

    // Initialize AssetsHelper
    AssetsHelper::initialize();

    // Check required environment variables
    $requiredEnvVars = ['MYSQL_HOST', 'MYSQL_DATABASE', 'MYSQL_USER', 'MYSQL_PASSWORD'];
    foreach ($requiredEnvVars as $var) {
        if (!isset($_ENV[$var])) {
            throw new Exception("Missing required environment variable: {$var}");
        }
    }

    // Initialize database
    $database = new Database();
    $db = $database->getConnection();

    // Initialize SessionManager
    SessionManager::initialize();

    // Error page display function
    function showErrorPage($code, $logger = null)
    {
        $validCodes = [400, 401, 403, 404, 500];
        $code = in_array($code, $validCodes) ? $code : 500;

        if ($logger) {
            $logger->error("Showing error page", [
                'code' => $code,
                'uri' => $_SERVER['REQUEST_URI']
            ]);
        }

        if (!headers_sent()) {
            http_response_code($code);
        }

        $errorMessages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Page Not Found',
            500 => 'Internal Server Error'
        ];

        $errorFile = APP_ROOT . "/src/Views/errors/{$code}.php";
        $fallbackFile = APP_ROOT . "/src/Views/errors/fallback.php";

        if (file_exists($errorFile)) {
            require $errorFile;
        } elseif (file_exists($fallbackFile)) {
            require $fallbackFile;
        } else {
            echo "<h1>Error {$code}</h1>";
            echo "<p>{$errorMessages[$code]}</p>";
        }
        exit;
    }

    // Helper functions
    function getLoginUrlForRole(string $role): string
    {
        return match ($role) {
            Roles::ADMIN => '/admin/login',
            Roles::FARMER, Roles::CUSTOMER => '/login',
            default => '/login',
        };
    }

    function getDashboardUrlForRole(string $role): string
    {
        return match ($role) {
            Roles::ADMIN => '/admin/dashboard',
            Roles::FARMER => '/farmer/dashboard',
            Roles::CUSTOMER => '/customer/dashboard',
            default => '/',
        };
    }

    // Load routes
    $routes = include APP_ROOT . '/src/config/Routes.php';

    // Initialize middleware
    $roleMiddleware = new RoleMiddleware($logger);

    // Get current route
    $request_method = $_SERVER['REQUEST_METHOD'];
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $route = $request_method . ' ' . $request_uri;

    $logger->info("Processing request", [
        'method' => $request_method,
        'uri' => $request_uri,
        'route' => $route
    ]);

    $routeHandled = false;

    // Process routes by group
    foreach ($routes as $group => $groupRoutes) {
        if (isset($groupRoutes[$route])) {
            try {
                [$controllerName, $methodName] = $groupRoutes[$route];
                $controllerClass = "App\\Controllers\\$controllerName";

                // Match dynamic route paterns 
                preg_match('/\{(\w+)}}/', $route, $matches);

                if (!empty($matches[1])) {
                    $paramName = $matches[1];
                    $params[$paramName] = $matches[1];
                }

                // Extract dynamic parameters from route pattern
                // if (isset($matches[1])) {
                //     $params['id'] = $matches[1]; // Product ID from the URL
                // }

                // Skip middleware for public and auth routes
                if (
                    $group === 'public' || $group === 'auth' ||
                    ($group === 'admin' && in_array($route, [
                        'GET /admin/login',
                        'POST /admin/login',
                        'GET /admin/forgot-password',
                        'POST /admin/forgot-password'
                    ]))
                ) {
                    $controller = new $controllerClass($db, $logger);
                    // $controller->$methodName($params['id'])
                    $controller->$methodName();
                    $routeHandled = true;
                    break;
                }

                // Handle protected routes
                SessionManager::initialize();

                // Check if user is authenticated
                if (
                    !isset($_SESSION['user_id']) ||
                    !isset($_SESSION['user_role']) ||
                    !isset($_SESSION['is_authenticated']) ||
                    $_SESSION['is_authenticated'] !== true
                ) {

                    $loginUrl = getLoginUrlForRole($group);

                    // Prevent redirect loops for login pages
                    if ($request_uri !== $loginUrl) {
                        $logger->info("Redirecting unauthenticated user", [
                            'from' => $request_uri,
                            'to' => $loginUrl
                        ]);

                        header("Location: $loginUrl?redirect=" . urlencode($request_uri));
                        exit;
                    }
                }

                // Check role authorization
                if (!$roleMiddleware->handle($group)()) {
                    showErrorPage(403, $logger);
                }

                // Execute controller
                $controller = new $controllerClass($db, $logger);
                $controller->$methodName();
                $routeHandled = true;
                break;
            } catch (Exception $e) {
                $logger->error("Route processing error", [
                    'message' => $e->getMessage(),
                    'route' => $route,
                    'controller' => $controllerName,
                    'method' => $methodName
                ]);

                if ($e->getCode() === 401) {
                    showErrorPage(401, $logger);
                } elseif ($e->getCode() === 403) {
                    showErrorPage(403, $logger);
                } else {
                    throw $e;
                }
            }
        }
    }

    // Handle 404 for unmatched routes
    if (!$routeHandled) {
        $logger->warning("Route not found", [
            'method' => $request_method,
            'uri' => $request_uri
        ]);
        showErrorPage(404, $logger);
    }
} catch (Exception $e) {
    // Log the error
    if (isset($logger)) {
        $logger->error("Application error", [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    } else {
        error_log("Critical error: " . $e->getMessage());
    }

    // Show error page
    showErrorPage(500, $logger ?? null);
}

// Clean up
if (isset($_SESSION['flash_messages'])) {
    unset($_SESSION['flash_messages']);
}
