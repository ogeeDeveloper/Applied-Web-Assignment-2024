<?php
namespace App\Controllers;

require_once __DIR__ . '/../../config/logger.php';

use App\Models\Customer;    
use App\Models\Farmer;    
use App\Models\Admin;    
use PDOException;
use App\Config\Database;



class AuthController {

    private $db;
    private $customerModel;
    private $farmerModel;
    private $adminModel; 
    private $logger;

    public function __construct() {
        $this->logger = getLogger('AuthController');  // Initialize logger
        $database = new Database();
        $this->db = $database->getConnection();
        $this->customerModel = new Customer($this->db);
        $this->farmerModel = new Farmer($this->db);
        $this->adminModel = new Admin($this->db);
    }  

    public function register($name, $email, $password) {
        try {
            // Logging registration attempt
            $this->logger->info("Attempting to register user with email: {$email}");

            // Check if the email already exists
            $query = "SELECT * FROM customers WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $this->logger->warning("Registration failed: Email already in use - {$email}");
                echo "Email already in use.";
                return false;
            }

            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the user into the database
            $query = "INSERT INTO customers (name, email, password) VALUES (:name, :email, :password)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);

            if ($stmt->execute()) {
                $this->logger->info("Registration successful for user with email: {$email}");
                echo "Registration successful!";
                return true;
            } else {
                $this->logger->error("Registration failed for user with email: {$email}");
                echo "Registration failed.";
                return false;
            }
        } catch (PDOException $e) {
            $this->logger->error("Database error during registration: " . $e->getMessage());
            echo "An error occurred.";
        }
    }

    public function login($email, $password) {
        // Logging the login attempt
        $this->logger->info("Attempting to login user with email: {$email}");

        // Check for customer
        $user = $this->customerModel->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            // Check role and start a session
            $this->startSession($user, 'customer');
            $this->logger->info("Login successful for customer: {$email}");
            echo "Customer login successful!";
            return;
        }

        // Check for farmer
        $farmer = $this->farmerModel->findByEmail($email);
        if ($farmer && password_verify($password, $farmer['password'])) {
            $this->startSession($farmer, 'farmer');
            $this->logger->info("Login successful for farmer: {$email}");
            echo "Farmer login successful!";
            return;
        }

        // Check for admin
        $admin = $this->adminModel->findByEmail($email);
        if ($admin && password_verify($password, $admin['password'])) {
            $this->startSession($admin, 'admin');
            $this->logger->info("Login successful for admin: {$email}");
            echo "Admin login successful!";
            return;
        }

        echo "Invalid email or password.";
    }

    private function startSession($user, $role) {
        session_start();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $role;
    }

    public function isAuthenticated() {
        session_start();

        // Check if the user is logged in by checking the session
        if (isset($_SESSION['user_id'])) {
            // User is authenticated
            return true;
        } else {
            // User is not authenticated
            return false;
        }
    }

    public function logout() {
        // Start session and clear all session data
        session_start();
        session_unset();
        session_destroy();

        // Clear the session cookie
        setcookie("session_cookie", "", time() - 3600, '/');

        echo "Logged out successfully.";
    }
}
