<?php

namespace App\Utils\Admin;

class AlertHelper
{
    public static function getAlertClass(string $type): string
    {
        return match ($type) {
            'success' => 'bg-green-100 text-green-800',
            'error' => 'bg-red-100 text-red-800',
            'warning' => 'bg-yellow-100 text-yellow-800',
            'info' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
}
