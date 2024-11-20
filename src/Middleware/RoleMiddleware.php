<?php

namespace App\Middleware;

use App\Constants\Roles;
use App\Utils\SessionManager;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Utils\Functions;

class RoleMiddleware
{
    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle role-based authorization
     * 
     * @param string $role Required role for access
     * @return callable Middleware function
     */
    public function handle(string $requiredRole): callable
    {
        return function () use ($requiredRole) {
            SessionManager::validateActivity();

            if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $requiredRole) {
                $this->logger->warning("Unauthorized access attempt", [
                    'required_role' => $requiredRole,
                    'current_role' => $_SESSION['user_role'] ?? 'none',
                    'uri' => $_SERVER['REQUEST_URI']
                ]);

                // If not logged in at all, show 401
                if (!isset($_SESSION['user_role'])) {
                    showErrorPage(401, $this->logger);
                }
                // If logged in but wrong role, show 403
                else {
                    showErrorPage(403, $this->logger);
                }
            }

            return true;
        };
    }

    /**
     * Redirect to unauthorized page
     */
    private function redirectToUnauthorized(): void
    {
        $currentUri = $_SERVER['REQUEST_URI'];

        // Avoid redirecting to the same unauthorized page repeatedly
        if ($currentUri === '/unauthorized') {
            echo 'You do not have access to this page. If you think this is a mistake, contact the administrator.';
            exit();
        }

        if (!headers_sent()) {
            header('Location: /unauthorized');
            exit();
        }
        // Fallback if headers already sent
        echo '<script>window.location.href="/unauthorized";</script>';
        echo 'If you are not redirected, <a href="/unauthorized">click here</a>.';
        exit();
    }


    /**
     * Redirect to login page with current URL as redirect parameter
     */
    private function redirectToLogin(): void
    {
        $currentUri = $_SERVER['REQUEST_URI'];
        $redirectUri = '/admin/login?redirect=' . urlencode($currentUri);

        // Avoid redirecting to the same URI repeatedly
        if ($currentUri === '/admin/login' || $currentUri === $redirectUri) {
            echo 'You are being redirected to the login page. If this persists, <a href="/admin/login">click here</a>.';
            exit();
        }

        if (!headers_sent()) {
            header("Location: {$redirectUri}");
            exit();
        }
        // Fallback if headers already sent
        echo '<script>window.location.href="' . $redirectUri . '";</script>';
        exit();
    }

    protected function getLoginUrlForRole(string $role): string
    {
        return match ($role) {
            Roles::ADMIN => '/admin/login',
            Roles::FARMER => '/farmer/login',
            Roles::CUSTOMER => '/login',
            default => '/login',
        };
    }
}
