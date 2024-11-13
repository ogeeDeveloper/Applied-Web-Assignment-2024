<?php

namespace App\Utils\Admin;

class NavigationHelper
{
    public static function isCurrentPage(string $path): bool
    {
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return $currentPath === $path;
    }

    public static function getActiveClass(string $path): string
    {
        return self::isCurrentPage($path) ? 'bg-gray-100' : '';
    }
}
