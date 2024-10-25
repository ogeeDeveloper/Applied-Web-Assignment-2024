<?php
namespace App\Controllers;

use App\Models\User;
use PDO;
use PDOException;

class AuthController {
    private PDO $db;
    private User $userModel;
    private $logger;

    public function __construct(PDO $db, $logger) {
        $this->db = $db;
        $this->userModel = new User($db, $logger);
        $this->logger = $logger;
    }

    public function register(array $data): array {
        try {
            $this->logger->info("Attempting to register user with email: {$data['email']}");

            if ($this->userModel->findByEmail($data['email'])) {
                return [
                    'success' => false,
                    'message' => 'Email already in use'
                ];
            }

            // Validate role
            if (!in_array($data['role'], ['customer', 'farmer', 'admin'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid role specified'
                ];
            }

            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'role' => $data['role']
            ];

            // Add role-specific data
            if ($data['role'] === 'farmer') {
                $userData['farm_name'] = $data['farm_name'] ?? null;
                $userData['location'] = $data['location'] ?? null;
                $userData['type_of_farmer'] = $data['type_of_farmer'] ?? null;
            }

            $userId = $this->userModel->create($userData);

            if ($userId) {
                $this->logger->info("Registration successful for user: {$data['email']}");
                return [
                    'success' => true,
                    'message' => 'Registration successful',
                    'user_id' => $userId
                ];
            }

            return [
                'success' => false,
                'message' => 'Registration failed'
            ];

        } catch (PDOException $e) {
            $this->logger->error("Registration error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during registration'
            ];
        }
    }

    public function login(string $email, string $password): array {
        try {
            $user = $this->userModel->findByEmail($email);

            if (!$user || !password_verify($password, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials'
                ];
            }

            $this->startSession($user);
            
            $this->logger->info("Login successful for user: {$email}");
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ];

        } catch (PDOException $e) {
            $this->logger->error("Login error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during login'
            ];
        }
    }

    private function startSession(array $user): void {
        session_start();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
    }

    public function logout(): array {
        session_start();
        session_destroy();
        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }

    public function getCurrentUser(): ?array {
        session_start();
        if (isset($_SESSION['user_id'])) {
            return $this->userModel->findById($_SESSION['user_id']);
        }
        return null;
    }

    public function isAuthenticated(): bool {
        session_start();
        return isset($_SESSION['user_id']);
    }

    public function hasRole(string $role): bool {
        session_start();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    public function getRedirectUrl(): string {
        if (!isset($_SESSION['user_role'])) {
            return '/login';
        }

        return match($_SESSION['user_role']) {
            'admin' => '/admin/dashboard',
            'farmer' => '/farmer/dashboard',
            'customer' => '/customer/dashboard',
            default => '/login'
        };
    }
}