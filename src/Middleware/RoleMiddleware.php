<?php
namespace App\Middleware;

class RoleMiddleware {
    private $logger;

    public function __construct($logger) {
        $this->logger = $logger;
    }

    public function handle($role) {
        return function() use ($role) {
            session_start();
            
            if (!isset($_SESSION['user_id'])) {
                $this->logger->warning("Unauthorized access attempt to restricted area");
                header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
                exit();
            }

            if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
                $this->logger->warning("Access denied for user {$_SESSION['user_id']} trying to access {$role} area");
                header('Location: /unauthorized');
                exit();
            }

            return true;
        };
    }
}