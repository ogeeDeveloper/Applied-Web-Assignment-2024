<?php

class AuthController {

    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
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
        // Check if the email exists in the database
        $query = "SELECT * FROM customers WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Password is correct

            // Start the session
            session_start();

            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);

            // Store the user's ID in the session
            $_SESSION['user_id'] = $user['customer_id'];

            // Set a session cookie
            setcookie("session_cookie", session_id(), [
                'expires' => time() + 3600,  // 1 hour expiry
                'path' => '/',
                'secure' => true,   // Use secure flag for HTTPS only
                'httponly' => true, // Prevent JavaScript from accessing the cookie
                'samesite' => 'Strict' // Helps prevent CSRF
            ]);

            echo "Login successful!";
        } else {
            // Invalid credentials
            echo "Invalid email or password.";
        }
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
