<?php

namespace App\Utils;

use App\Constants\Roles;


class Functions
{
    /**
     * Sanitize output to prevent XSS
     * @param string $string
     * @return string
     */
    public static function h($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get cart item count
     */
    public static function getCartCount()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        return isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
    }

    /**
     * Format price in Jamaican dollars
     */
    public static function formatPrice($price)
    {
        return '$' . number_format($price, 2);
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get cart total
     */
    public static function getCartTotal()
    {
        $total = 0;
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $total += $item['price'] * $item['quantity'];
            }
        }
        return $total;
    }

    /**
     * Get navigation categories
     * @return array
     */
    public static function getCategories()
    {
        // TODO: Replace with database query
        return [
            'vegetables' => [
                'name' => 'Vegetables',
                'url' => '/shop/vegetables'
            ],
            'fruits' => [
                'name' => 'Fruits',
                'url' => '/shop/fruits'
            ],
            'grains' => [
                'name' => 'Rice & Grains',
                'url' => '/shop/grains'
            ],
            'fresh' => [
                'name' => 'Fresh Products',
                'url' => '/shop/fresh'
            ]
        ];
    }

    /**
     * Get current URL path
     * @return string
     */
    public static function getCurrentPath()
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * Get the login URL based on the user's role.
     *
     * @param string $role The user's role. It can be 'admin', 'farmer', 'customer', or any other role.
     * @return string The corresponding login URL for the given role.
     *
     * @throws InvalidArgumentException If an invalid role is provided.
     */
    public static function getLoginUrlForRole($role)
    {
        return match ($role) {
            'admin' => '/admin/login',
            'farmer' => '/farmer/login',
            'customer' => '/login',
            default => '/login',
        };
    }

    public static function initializeSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.gc_maxlifetime', 7200);
            ini_set('session.cookie_lifetime', 7200);

            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                ini_set('session.cookie_secure', 1);
            }

            session_start();
        }
    }

    function getRedirectUrlForRole(string $role): string
    {
        return match ($role) {
            Roles::ADMIN => '/admin/dashboard',
            Roles::FARMER => '/farmer/dashboard',
            Roles::CUSTOMER => '/customer/dashboard',
            default => '/',
        };
    }

    function jsonError(string $message, int $status = 500): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit;
    }

    function validateSessionActivity(): void
    {
        if (time() - ($_SESSION['last_activity'] ?? 0) > 7200) { // 2-hour timeout
            session_destroy();
            header('Location: /login');
            exit;
        }
        $_SESSION['last_activity'] = time(); // Update last activity
    }
}
