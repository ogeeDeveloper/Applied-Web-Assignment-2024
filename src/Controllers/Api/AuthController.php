<?php
namespace App\Controllers\Api;

use PDO;

class AuthController {
    private $db;
    private $logger;
    private $authController;

    public function __construct(PDO $db, $logger) {
        $this->db = $db;
        $this->logger = $logger;
        $this->authController = new \App\Controllers\AuthController($db, $logger);
    }

    public function login(): void {
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $password = $data['password'] ?? '';

            if (!$email || !$password) {
                throw new \Exception("Invalid input data");
            }

            $result = $this->authController->login($email, $password);

            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'redirect' => $this->authController->getRedirectUrl(),
                    'user' => $result['user']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error("API Login error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred during login'
            ]);
        }
    }

    public function signup(): void {
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            
            // Validate input
            if (empty($data['email']) || empty($data['password']) || empty($data['name'])) {
                throw new \Exception("Required fields missing");
            }

            $result = $this->authController->register($data);

            echo json_encode($result);
        } catch (\Exception $e) {
            $this->logger->error("API Signup error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred during registration'
            ]);
        }
    }
}