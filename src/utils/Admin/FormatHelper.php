<?php

namespace App\Utils\Admin;

class FormatHelper
{
    public static function currency(float $amount): string
    {
        // Use the existing format function for consistency
        return \App\Utils\Functions::formatPrice($amount);
    }

    public static function number(int $number): string
    {
        if ($number >= 1000000000) return round($number / 1000000000, 1) . 'B';
        if ($number >= 1000000) return round($number / 1000000, 1) . 'M';
        if ($number >= 1000) return round($number / 1000, 1) . 'K';
        return (string) $number;
    }

    public static function date(string $date): string
    {
        return date('M j, Y', strtotime($date));
    }
}
