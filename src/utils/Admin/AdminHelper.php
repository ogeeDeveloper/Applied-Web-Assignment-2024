<?php
class AdminHelper
{
    public static function isAdminLoggedIn(): bool
    {
        return isset($_SESSION['admin_id']);
    }

    public static function requireAdmin(): void
    {
        if (!self::isAdminLoggedIn()) {
            header('Location: /admin/login');
            exit;
        }
    }
}
