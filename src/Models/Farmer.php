<?php

namespace App\Models;

use PDO;

class Farmer {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // Find a farmer by their email address
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM farmers WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Register a new farmer
    public function createFarmer($name, $email, $password, $address, $farmName, $location, $typeOfFarmer, $description, $phoneNumber) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO farmers (name, email, password, address, farm_name, location, type_of_farmer, description, phone_number) VALUES (:name, :email, :password, :address, :farm_name, :location, :type_of_farmer, :description, :phone_number)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':farm_name', $farmName);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':type_of_farmer', $typeOfFarmer);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':phone_number', $phoneNumber);
        return $stmt->execute();
    }

    // Get all farmers
    public function getAllFarmers() {
        $stmt = $this->db->prepare("SELECT * FROM farmers");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Other farmer-related methods as necessary (update farmer, delete farmer, etc.)
}
