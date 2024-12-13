<?php

namespace App\Utils\Admin;

class PaginationHelper
{
    public static function generate(int $currentPage, int $totalPages, string $baseUrl): string
    {
        $links = '';
        $range = 2;

        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == 1 || $i == $totalPages || ($i >= $currentPage - $range && $i <= $currentPage + $range)) {
                $active = $i === $currentPage;
                $links .= sprintf(
                    '<a href="%s?page=%d" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold %s">%d</a>',
                    $baseUrl,
                    $i,
                    $active ? 'bg-green-600 text-white' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50',
                    $i
                );
            } elseif ($i == $currentPage - $range - 1 || $i == $currentPage + $range + 1) {
                $links .= '<span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700">...</span>';
            }
        }

        return $links;
    }
}
