<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Farmer;
use App\Models\Customer;
use PDO;
use PDOException;
use App\Utils\SessionManager;
use Exception;

class AuthController extends BaseController
{
    private User $userModel;
    private $farmerModel;
    private $customerModel;

    public function __construct(PDO $db, $logger)
    {
        parent::__construct($db, $logger);
        $this->db = $db;
        $this->userModel = new User($db, $logger);
        $this->logger = $logger;
        $this->farmerModel = new Farmer($db, $logger);
        $this->customerModel = new Customer($db, $logger);
    }

    public function loginForm()
    {
        if ($this->isAuthenticated()) {
            header('Location: ' . $this->getRedirectUrl());
            exit;
        }
        $this->render('auth/login', [], 'Login - AgriKonnect');
    }

    public function farmerRegistrationForm()
    {
        if ($this->isAuthenticated()) {
            header('Location: ' . $this->getRedirectUrl());
            exit;
        }
        // Set the content type to text/html
        header('Content-Type: text/html');
        $this->render('auth/farmer-register', [], 'Register - AgriKonnect');
    }

    public function customerRegistrationForm()
    {
        if ($this->isAuthenticated()) {
            header('Location: ' . $this->getRedirectUrl());
            exit;
        }
        $this->render('auth/signup', [], 'Sign Up - AgriKonnect');
    }

    // Register a new user (customer/farmer)
    public function register()
    {
        try {
            // Determine registration type (farmer or customer)
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $isFarmerRegistration = strpos($path, 'farmer') !== false;

            // Check if it's an AJAX request
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

            // Collect user data from the request
            $data = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'role' => $isFarmerRegistration ? 'farmer' : 'customer'
            ];

            // Add farmer-specific fields if farmer registration
            if ($isFarmerRegistration) {
                $data = array_merge($data, [
                    'farm_name' => $_POST['farm_name'] ?? '',
                    'location' => $_POST['location'] ?? '',
                    'farm_type' => $_POST['farm_type'] ?? '',
                    'farm_size' => $_POST['farm_size'] ?? '',
                    'primary_products' => $_POST['primary_products'] ?? '',
                    'farming_experience' => $_POST['farming_experience'] ?? '',
                    'organic_certified' => isset($_POST['organic_certified']),
                    'phone_number' => $_POST['phone_number'] ?? '',
                    'additional_info' => $_POST['additional_info'] ?? null
                ]);
            } else {
                // Add customer-specific fields if customer registration
                $data = array_merge($data, [
                    'address' => $_POST['address'] ?? null,
                    'phone_number' => $_POST['phone_number'] ?? null,
                    'preferences' => $_POST['preferences'] ?? []
                ]);
            }

            // Handle registration logic
            $result = $this->registerUser($data);

            if ($isAjax) {
                $this->jsonResponse($result);
            } else {
                if ($result['success']) {
                    // Set success flash message and redirect to login
                    $this->setFlashMessage('Registration successful! Please login to continue.', 'success');
                    $this->redirect('/login');
                } else {
                    // Set error flash message and redirect back with input data
                    $this->setFlashMessage($result['message'], 'error');
                    $this->redirectWithInput($isFarmerRegistration ? '/farmer/register' : '/register', $_POST);
                }
            }
        } catch (Exception $e) {
            $this->logger->error("Registration error: " . $e->getMessage());

            if ($isAjax) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Registration failed. Please try again.'
                ], 500);
            } else {
                $this->setFlashMessage('Registration failed. Please try again.', 'error');
                $redirectUrl = $isFarmerRegistration ? '/farmer/register' : '/register';
                $this->redirectWithInput($redirectUrl, $_POST);
            }
        }
    }

    /**
     * Internal method to handle user registration logic
     */
    private function registerUser(array $data): array
    {
        try {
            $this->logger->info("Attempting to register user with email: {$data['email']}");
            $this->logger->info("Registering user with data: " . json_encode($data));

            // Validate required fields
            $requiredFields = ['name', 'email', 'password', 'role'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'message' => ucfirst($field) . ' is required'
                    ];
                }
            }

            // Check for duplicate email
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

            // Hash the password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Prepare user data
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'role' => $data['role']
            ];

            // Start transaction
            $this->db->beginTransaction();

            // Create user account
            $userId = $this->userModel->create($userData);

            // Register role-specific profile
            if ($data['role'] === 'farmer') {
                $this->farmerModel->createFarmerProfile($userId, $data);
            } elseif ($data['role'] === 'customer') {
                $this->customerModel->createCustomerProfile($userId, $data);
            }

            // Commit transaction
            $this->db->commit();

            $this->logger->info("Registration successful for user: {$data['email']}");
            return [
                'success' => true,
                'message' => 'Registration successful',
                'user_id' => $userId
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->logger->error("Registration error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during registration'
            ];
        }
    }

    public function farmerLoginForm()
    {
        if ($this->isAuthenticated()) {
            header('Location: ' . $this->getRedirectUrl());
            exit;
        }
        $this->render('auth/farmer-login', [], 'Farmer Login - AgriKonnect');
    }

    public function registerFarmerProfile(int $userId, array $data): void
    {
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

    public function registerCustomerProfile(int $userId, array $data): void
    {
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

    public function login(string $email, string $password): array
    {
        try {
            $user = $this->userModel->findByEmail($email);

            if (!$user || !password_verify($password, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials'
                ];
            }

            SessionManager::initialize();
            SessionManager::regenerate();

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['is_authenticated'] = true;
            $_SESSION['last_activity'] = time();

            return [
                'success' => true,
                'message' => 'Login successful',
                'redirect' => $this->getRedirectUrlForRole($user['role']),
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

    private function startSession(array $user): void
    {
        session_start();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
    }

    public function logout(): array
    {
        session_start();
        session_destroy();
        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }

    public function getCurrentUser(): ?array
    {
        session_start();
        if (isset($_SESSION['user_id'])) {
            return $this->userModel->findById($_SESSION['user_id']);
        }
        return null;
    }

    public function isAuthenticated(): bool
    {
        session_start();
        return isset($_SESSION['user_id']);
    }

    public function hasRole(string $role): bool
    {
        session_start();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    public function getRedirectUrl(): string
    {
        if (!isset($_SESSION['user_role'])) {
            return '/login';
        }

        return match ($_SESSION['user_role']) {
            'admin' => '/admin/dashboard',
            'farmer' => '/farmer/dashboard',
            'customer' => '/customer/dashboard',
            default => '/login'
        };
    }
}
