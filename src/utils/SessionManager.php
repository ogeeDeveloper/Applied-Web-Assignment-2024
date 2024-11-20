<?php

namespace App\Utils;

class SessionManager
{
    public static function initialize(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.gc_maxlifetime', 7200); // 2 hours
            ini_set('session.cookie_lifetime', 7200);

            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                ini_set('session.cookie_secure', 1);
            }

            session_start();
        }
    }

    public static function validateActivity(): void
    {
        self::initialize();

        if (
            !isset($_SESSION['last_activity']) ||
            (time() - $_SESSION['last_activity'] > 7200)
        ) { // 2 hours timeout
            self::destroy();

            // Get current URL path
            $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            // Determine correct login page based on path
            $loginUrl = str_starts_with($currentPath, '/admin') ? '/admin/login' : '/login';

            header("Location: $loginUrl");
            exit;
        }

        $_SESSION['last_activity'] = time();
    }

    public static function regenerate(): void
    {
        self::initialize();
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Clear all session variables
            $_SESSION = array();

            // Destroy the session cookie
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }

            // Destroy the session
            session_destroy();
        }
    }

    /**
     * Check if user is authenticated
     * @return bool
     */
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) &&
            isset($_SESSION['user_role']) &&
            isset($_SESSION['is_authenticated']) &&
            $_SESSION['is_authenticated'] === true;
    }

    public static function hasRole(string $role): bool
    {
        return self::isAuthenticated() && $_SESSION['user_role'] === $role;
    }
}
