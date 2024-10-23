<?php

namespace App\Models;

use PDO;

class Admin {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // Find an admin by their email address
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Register a new admin
    public function createAdmin($name, $email, $password, $role) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO admins (name, email, password, role) VALUES (:name, :email, :password, :role)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $role);
        return $stmt->execute();
    }

    // Get all admins
    public function getAllAdmins() {
        $stmt = $this->db->prepare("SELECT * FROM admins");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Other admin-related methods as necessary (update admin, delete admin, etc.)
}
