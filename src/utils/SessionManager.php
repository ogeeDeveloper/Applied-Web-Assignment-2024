<?php

namespace App\Utils;

class SessionManager
{
    public static function initialize(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
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

        if (time() - ($_SESSION['last_activity'] ?? 0) > 7200) { // 2-hour timeout
            session_destroy();
            header('Location: /login');
            exit;
        }

        $_SESSION['last_activity'] = time(); // Update activity timestamp
    }

    public static function regenerate(): void
    {
        self::initialize();
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        self::initialize();
        session_unset();
        session_destroy();
    }
}
