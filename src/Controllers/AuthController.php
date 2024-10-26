<?php
namespace App\Controllers;

use App\Models\User;
use PDO;
use PDOException;

class AuthController extends BaseController {
    private User $userModel;

    public function __construct(PDO $db, $logger) {
        $this->db = $db;
        $this->userModel = new User($db, $logger);
        $this->logger = $logger;
    }

    public function loginForm() {
        if ($this->isAuthenticated()) {
            header('Location: ' . $this->getRedirectUrl());
            exit;
        }
        $this->render('auth/login', [], 'Login - AgriKonnect');
    }

    public function farmerRegistrationForm() {
        if ($this->isAuthenticated()) {
            header('Location: ' . $this->getRedirectUrl());
            exit;
        }
         // Set the content type to text/html
        header('Content-Type: text/html');
        $this->render('auth/farmer-register', [], 'Register - AgriKonnect');
    }

    public function customerRegistrationForm() {
        if ($this->isAuthenticated()) {
            header('Location: ' . $this->getRedirectUrl());
            exit;
        }
        $this->render('auth/signup', [], 'Sign Up - AgriKonnect');
    }

    // Register a new user (customer/farmer)
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
            if (!in_array($data['role'], ['customer', 'farmer'])) {
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

            $this->db->beginTransaction();

            try {
                // Create user account
                $userId = $this->userModel->create($userData);

                if ($data['role'] === 'farmer') {
                    // Register farmer-specific data
                    $this->registerFarmerProfile($userId, $data);
                } elseif ($data['role'] === 'customer') {
                    // Register customer-specific data
                    $this->registerCustomerProfile($userId, $data);
                }

                $this->db->commit();

                $this->logger->info("Registration successful for user: {$data['email']}");
                return [
                    'success' => true,
                    'message' => 'Registration successful',
                    'user_id' => $userId
                ];
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (PDOException $e) {
            $this->logger->error("Registration error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during registration'
            ];
        }
    }

    public function registerFarmerProfile(int $userId, array $data): void {
        $farmerData = [
            'user_id' => $userId,
            'farm_name' => $data['farm_name'],
            'location' => $data['location'],
            'farm_type' => $data['farm_type'],
            'farm_size' => $data['farm_size'],
            'primary_products' => $data['primary_products'],
            'farming_experience' => $data['farming_experience'],
            'organic_certified' => isset($data['organic_certified']) ? 1 : 0,
            'phone_number' => $data['phone_number'],
            'additional_info' => $data['additional_info'] ?? null,
            'status' => 'pending'
        ];

        $stmt = $this->db->prepare("
            INSERT INTO farmer_profiles (
                user_id, farm_name, location, farm_type, farm_size,
                primary_products, farming_experience, organic_certified,
                phone_number, additional_info, status, created_at
            ) VALUES (
                :user_id, :farm_name, :location, :farm_type, :farm_size,
                :primary_products, :farming_experience, :organic_certified,
                :phone_number, :additional_info, :status, NOW()
            )
        ");

        $stmt->execute($farmerData);
    }

    public function registerCustomerProfile(int $userId, array $data): void {
        $customerData = [
            'user_id' => $userId,
            'address' => $data['address'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'preferences' => json_encode($data['preferences'] ?? [])
        ];

        $stmt = $this->db->prepare("
            INSERT INTO customer_profiles (
                user_id, address, phone_number, preferences, created_at
            ) VALUES (
                :user_id, :address, :phone_number, :preferences, NOW()
            )
        ");

        $stmt->execute($customerData);
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
