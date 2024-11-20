<?php

namespace App\Models;

use PDO;

class Farmer
{
    private $db;
    private $logger;
    private $mediaManager;

    public function __construct(PDO $db, $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->mediaManager = new MediaManager($db, $logger);
    }

    // Find a farmer by their email address
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM farmers WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Register a new farmer
    public function createFarmerProfile(int $userId, array $data): void
    {
        try {
            $this->logger->info("Creating farmer profile for user ID: {$userId}, data: " . json_encode($data));
            $stmt = $this->db->prepare("
            INSERT INTO farmer_profiles (
                user_id, farm_name, location, farm_type, farm_size,
                primary_products, farming_experience, organic_certified,
                phone_number, additional_info, status, created_at
            ) VALUES (
                :user_id, :farm_name, :location, :farm_type, :farm_size,
                :primary_products, :farming_experience, :organic_certified,
                :phone_number, :additional_info, :status, :created_at
            )
        ");

            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':farm_name', $data['farm_name'], PDO::PARAM_STR);
            $stmt->bindValue(':location', $data['location'], PDO::PARAM_STR);
            $stmt->bindValue(':farm_type', $data['farm_type'], PDO::PARAM_STR);
            $stmt->bindValue(':farm_size', $data['farm_size'], PDO::PARAM_STR);
            $stmt->bindValue(':primary_products', $data['primary_products'], PDO::PARAM_STR);
            $stmt->bindValue(':farming_experience', $data['farming_experience'], PDO::PARAM_STR);
            $stmt->bindValue(':organic_certified', $data['organic_certified'] ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':phone_number', $data['phone_number'], PDO::PARAM_STR);
            $stmt->bindValue(':additional_info', $data['additional_info'], PDO::PARAM_STR);
            $stmt->bindValue(':status', 'pending', PDO::PARAM_STR);
            $stmt->bindValue(':
            $this->logger->info("Creating farmer profile for user ID: {$userId}, data: " . json_encode($data));', date('Y-m-d H:i:s'), PDO::PARAM_STR);

            $stmt->execute();
            $this->logger->info("Farmer profile created successfully.");
        } catch (PDOException $e) {
            // Log error and rethrow
            $this->logger->error("Database error during farmer profile creation: " . $e->getMessage());
            $this->logger->error("Failed to create farmer profile for user ID {$userId}: " . $e->getMessage());
            throw new Exception("Failed to create farmer profile: " . $e->getMessage());
        }
    }

    // public function createFarmer($name, $email, $password, $address, $farmName, $location, $typeOfFarmer, $description, $phoneNumber) {
    //     $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    //     $stmt = $this->db->prepare("INSERT INTO farmers (name, email, password, address, farm_name, location, type_of_farmer, description, phone_number) VALUES (:name, :email, :password, :address, :farm_name, :location, :type_of_farmer, :description, :phone_number)");
    //     $stmt->bindParam(':name', $name);
    //     $stmt->bindParam(':email', $email);
    //     $stmt->bindParam(':password', $hashed_password);
    //     $stmt->bindParam(':address', $address);
    //     $stmt->bindParam(':farm_name', $farmName);
    //     $stmt->bindParam(':location', $location);
    //     $stmt->bindParam(':type_of_farmer', $typeOfFarmer);
    //     $stmt->bindParam(':description', $description);
    //     $stmt->bindParam(':phone_number', $phoneNumber);
    //     return $stmt->execute();
    // }

    // Get all farmers
    public function getAllFarmers()
    {
        $stmt = $this->db->prepare("SELECT * FROM farmers");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProfile(int $userId, array $data, ?array $files = null): array
    {
        try {
            $this->db->beginTransaction();

            // Update farmer profile
            $sql = "UPDATE farmer_profiles SET 
                    farm_name = :farm_name,
                    location = :location,
                    farm_type = :farm_type,
                    farm_size = :farm_size,
                    farming_experience = :farming_experience,
                    primary_products = :primary_products,
                    organic_certified = :organic_certified,
                    phone_number = :phone_number,
                    additional_info = :additional_info,
                    updated_at = NOW()
                    WHERE user_id = :user_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'farm_name' => $data['farm_name'],
                'location' => $data['location'],
                'farm_type' => $data['farm_type'],
                'farm_size' => $data['farm_size'],
                'farming_experience' => $data['farming_experience'],
                'primary_products' => $data['primary_products'],
                'organic_certified' => $data['organic_certified'] ?? false,
                'phone_number' => $data['phone_number'],
                'additional_info' => $data['additional_info'] ?? null,
                'user_id' => $userId
            ]);

            // Handle certification documents if provided
            if ($files && !empty($files['certification_docs'])) {
                foreach ($files['certification_docs'] as $file) {
                    $uploadResult = $this->mediaManager->uploadFile(
                        $file,
                        'farmer_profile',
                        $userId
                    );

                    if (!$uploadResult['success']) {
                        throw new \Exception("Failed to upload certification document");
                    }
                }
            }

            // Update user basic info if provided
            if (!empty($data['name']) || !empty($data['email'])) {
                $updateFields = [];
                $params = ['user_id' => $userId];

                if (!empty($data['name'])) {
                    $updateFields[] = "name = :name";
                    $params['name'] = $data['name'];
                }

                if (!empty($data['email'])) {
                    // Check if email is already in use by another user
                    $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email AND id != :user_id");
                    $stmt->execute(['email' => $data['email'], 'user_id' => $userId]);
                    if ($stmt->fetch()) {
                        throw new \Exception("Email already in use");
                    }

                    $updateFields[] = "email = :email";
                    $params['email'] = $data['email'];
                }

                if (!empty($updateFields)) {
                    $sql = "UPDATE users SET " . implode(', ', $updateFields) .
                        ", updated_at = NOW() WHERE id = :user_id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute($params);
                }
            }

            // Log the profile update
            $sql = "INSERT INTO audit_logs (
                user_id, action_type, entity_type, entity_id
            ) VALUES (
                :user_id, 'profile_update', 'farmer', :farmer_id
            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $userId,
                'farmer_id' => $userId
            ]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Profile updated successfully'
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error("Error updating farmer profile: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getFarmerProfile(int $userId): ?array
    {
        try {
            $sql = "SELECT fp.*, u.name, u.email
                    FROM farmer_profiles fp
                    JOIN users u ON fp.user_id = u.id
                    WHERE fp.user_id = :user_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);

            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($profile) {
                // Get farmer's certifications and documents
                $profile['documents'] = $this->mediaManager->getEntityFiles('farmer_profile', $userId);
            }

            return $profile ?: null;
        } catch (\PDOException $e) {
            $this->logger->error("Error getting farmer profile: " . $e->getMessage());
            return null;
        }
    }
}
