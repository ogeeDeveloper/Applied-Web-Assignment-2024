<?php
namespace App\Controllers;

use App\Models\Customer;    
use App\Models\Farmer;    
use App\Models\Admin;    
use PDO;
use PDOException;
use App\Config\Database;

class AuthController {

    private $db;
    private $customerModel;
    private $farmerModel;
    private $adminModel; 

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->customerModel = new Customer($this->db);
        $this->farmerModel = new Farmer($this->db);
        $this->adminModel = new Admin($this->db);
    }  

    public function register($name, $email, $password) {
        // Check if the email already exists
        $query = "SELECT * FROM customers WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
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
            echo "Registration successful!";
            return true;
        } else {
            echo "Registration failed.";
            return false;
        }
    }

    public function login($email, $password) {
        // Check for customer
        $user = $this->customerModel->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            // Check role and start a session
            $this->startSession($user, 'customer');
            echo "Customer login successful!";
            return;
        }

        // Check for farmer
        $farmer = $this->farmerModel->findByEmail($email);
        if ($farmer && password_verify($password, $farmer['password'])) {
            $this->startSession($farmer, 'farmer');
            echo "Farmer login successful!";
            return;
        }

        // Check for admin
        $admin = $this->adminModel->findByEmail($email);
        if ($admin && password_verify($password, $admin['password'])) {
            $this->startSession($admin, 'admin');
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
