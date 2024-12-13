<?php
namespace App\Controllers\Api;

use PDO;
use Respect\Validation\Validator as v;

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
        header('Content-Type: application/json');
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $password = $data['password'] ?? '';

            if (!$email || !$password) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid input data'
                ]);
                exit();
            }

            $result = $this->authController->login($email, $password);

            if ($result['success']) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'redirect' => $this->authController->getRedirectUrl(),
                    'user' => $result['user']
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error("API Login error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred during login'
            ]);
        }
    }

    public function signup(): void {
        header('Content-Type: application/json');
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            // Validate input
            if (empty($data['email']) || empty($data['password']) || empty($data['name'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Required fields missing'
                ]);
                exit();
            }

            // Additional validation
            if (!v::email()->validate($data['email']) || strlen($data['password']) < 8) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid email format or password too short'
                ]);
                exit();
            }

            $result = $this->authController->register($data);

            if ($result['success']) {
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Registration successful',
                    'user' => $result['user']
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error("API Signup error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred during registration'
            ]);
        }
    }
}
