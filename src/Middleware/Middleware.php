<?php

namespace App\Middleware;

function applyMiddleware(array $middleware): void
{
    foreach ($middleware as $rule) {
        if ($rule === 'auth') {
            if (!isset($_SESSION['user_id'])) {
                header('Location: /login');
                exit;
            }
        } elseif (str_starts_with($rule, 'role:')) {
            $requiredRole = explode(':', $rule)[1];
            if ($_SESSION['user_role'] !== $requiredRole) {
                header('Location: /unauthorized');
                exit;
            }
        }
    }
}
