<?php
namespace App\Utils;

class Functions {
    /**
     * Sanitize output to prevent XSS
     * @param string $string
     * @return string
     */
    public static function h($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get cart item count
     */
    public static function getCartCount() {
        if (!isset($_SESSION)) {
            session_start();
        }
        return isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
    }

    /**
     * Format price in Jamaican dollars
     */
    public static function formatPrice($price) {
        return '$' . number_format($price, 2);
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get cart total
     */
    public static function getCartTotal() {
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
    public static function getCategories() {
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
    public static function getCurrentPath() {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }
}