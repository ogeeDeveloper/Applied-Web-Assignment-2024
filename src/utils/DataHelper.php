<?php

namespace App\Utils;

class DataHelper
{
    /**
     * Safely get a value from a nested array using dot notation
     *
     * @param array|null $array The array to search in
     * @param string $key The key to search for (supports dot notation)
     * @param mixed $default The default value to return if key not found
     * @return mixed
     */
    public static function get(?array $array, string $key, $default = null)
    {
        if ($array === null) {
            return $default;
        }

        if (strpos($key, '.') === false) {
            return $array[$key] ?? $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Format a number with proper thousands separator
     *
     * @param mixed $number The number to format
     * @param int $decimals Number of decimal points
     * @return string
     */
    public static function formatNumber($number, int $decimals = 0): string
    {
        if (!is_numeric($number)) {
            return '0';
        }
        return number_format((float)$number, $decimals);
    }

    /**
     * Format currency amount
     *
     * @param mixed $amount The amount to format
     * @param string $currency Currency symbol
     * @return string
     */
    public static function formatCurrency($amount, string $currency = '₱'): string
    {
        if (!is_numeric($amount)) {
            return $currency . '0.00';
        }
        return $currency . number_format((float)$amount, 2);
    }

    /**
     * Get the CSS classes for an order status
     *
     * @param string $status
     * @return string
     */
    public static function getOrderStatusClass(string $status): string
    {
        return match ($status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
