<?php

namespace App\Middleware;

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
    public function handle($role)
    {
        return function () use ($role) {
            // Check if session is not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Check if user is authenticated
            if (!isset($_SESSION['user_id'])) {
                $this->logger->warning("Unauthorized access attempt to restricted area");
                $this->redirectToLogin();
                return false;
            }

            // Check if user has required role
            if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
                $this->logger->warning("Access denied for user {$_SESSION['user_id']} trying to access {$role} area");
                $this->redirectToUnauthorized();
                return false;
            }

            return true;
        };
    }

    /**
     * Redirect to login page with current URL as redirect parameter
     */
    private function redirectToLogin(): void
    {
        if (!headers_sent()) {
            header('Location: /admin/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit();
        }
        // Fallback if headers already sent
        echo '<script>window.location.href="/admin/login?redirect=' . urlencode($_SERVER['REQUEST_URI']) . '";</script>';
        echo 'If you are not redirected, <a href="/admin/login">click here</a>.';
        exit();
    }

    /**
     * Redirect to unauthorized page
     */
    private function redirectToUnauthorized(): void
    {
        if (!headers_sent()) {
            header('Location: /unauthorized');
            exit();
        }
        // Fallback if headers already sent
        echo '<script>window.location.href="/unauthorized";</script>';
        echo 'If you are not redirected, <a href="/unauthorized">click here</a>.';
        exit();
    }
}
