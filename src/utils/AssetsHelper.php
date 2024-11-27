<?php

namespace App\Utils;

class AssetsHelper
{
    private static string $publicPath;
    private static string $cssPath = 'css';
    private static string $jsPath = 'js';
    private static string $imagesPath = 'images';
    private static string $libPath = 'lib';
    private static array $manifestCache = [];
    private static bool $useManifest = false;
    private static ?string $manifestPath = null;
    private static string $cdnUrl = '';

    /**
     * Initialize the AssetsHelper
     */
    public static function initialize(): void
    {
        if (!defined('APP_ROOT')) {
            define('APP_ROOT', dirname(dirname(__DIR__)));
        }

        self::$publicPath = APP_ROOT . '/public';
        self::$manifestPath = self::$publicPath . '/mix-manifest.json';

        // Load manifest if it exists
        if (file_exists(self::$manifestPath)) {
            self::$useManifest = true;
            self::$manifestCache = json_decode(file_get_contents(self::$manifestPath), true) ?? [];
        }
    }

    /**
     * Get CSS file URL
     */
    public static function css(string $filename): string
    {
        return self::asset(self::$cssPath . '/' . $filename);
    }

    /**
     * Get JavaScript file URL
     */
    public static function js(string $filename): string
    {
        return self::asset(self::$jsPath . '/' . $filename);
    }

    /**
     * Get library file URL
     */
    public static function lib(string $path): string
    {
        return self::asset(self::$libPath . '/' . $path);
    }

    /**
     * Get image URL
     */
    public static function image(string $path, ?string $fallback = null): string
    {
        $imagePath = self::$imagesPath . '/' . $path;
        if (self::exists($imagePath)) {
            return self::asset($imagePath);
        }
        return $fallback ?? '/placeholder.png';
    }

    /**
     * Get favicon URL
     */
    public static function favicon(string $size = '16x16'): string
    {
        return self::image("favicon/favicon-{$size}.png");
    }

    /**
     * Get logo URL
     */
    public static function logo(string $variant = 'default'): string
    {
        return match ($variant) {
            'white' => self::image('logo-white.png'),
            default => self::image('logo.png'),
        };
    }

    /**
     * Get the URL for any asset
     */
    public static function asset(string $path): string
    {
        $path = ltrim($path, '/');

        if (self::$useManifest) {
            $manifestPath = self::getManifestPath($path);
            if ($manifestPath) {
                return self::$cdnUrl . $manifestPath;
            }
        }

        return '/' . $path;
    }

    /**
     * Check if an asset exists
     */
    public static function exists(string $path): bool
    {
        return file_exists(self::$publicPath . '/' . ltrim($path, '/'));
    }

    /**
     * Load multiple CSS files
     */
    public static function loadCSS(array $files): string
    {
        $html = '';
        foreach ($files as $file) {
            $html .= sprintf(
                '<link rel="stylesheet" href="%s">' . PHP_EOL,
                self::css($file)
            );
        }
        return $html;
    }

    /**
     * Load multiple JS files
     */
    public static function loadJS(array $files, bool $defer = false): string
    {
        $html = '';
        foreach ($files as $file) {
            $html .= sprintf(
                '<script src="%s"%s></script>' . PHP_EOL,
                self::js($file),
                $defer ? ' defer' : ''
            );
        }
        return $html;
    }

    /**
     * Get asset from manifest
     */
    private static function getManifestPath(string $path): ?string
    {
        return self::$manifestCache["/{$path}"] ?? null;
    }
}
