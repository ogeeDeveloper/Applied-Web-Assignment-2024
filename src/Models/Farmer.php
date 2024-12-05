<?php

namespace App\Models;

use PDO;

class Farmer
{
    private $db;
    private $logger;
    private $mediaManager;
    private $userModel;

    public function __construct(PDO $db, $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->mediaManager = new MediaManager($db, $logger);
        $this->userModel = new User($db, $logger);
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
            $this->logger->info("Creating farmer profile for user ID: {$userId}");

            // Convert farming experience range to integer (take the lower bound)
            $farmingExperience = (int) explode('-', $data['farming_experience'])[0];

            $sql = "INSERT INTO farmer_profiles (
            user_id, 
            farm_name, 
            location, 
            farm_type, 
            farm_size,
            primary_products, 
            farming_experience, 
            organic_certified,
            phone_number, 
            additional_info, 
            status
        ) VALUES (
            :user_id, 
            :farm_name, 
            :location, 
            :farm_type, 
            :farm_size,
            :primary_products, 
            :farming_experience, 
            :organic_certified,
            :phone_number, 
            :additional_info, 
            :status
        )";

            $stmt = $this->db->prepare($sql);

            $params = [
                ':user_id' => $userId,
                ':farm_name' => $data['farm_name'],
                ':location' => $data['location'],
                ':farm_type' => $data['farm_type'],
                ':farm_size' => $data['farm_size'],
                ':primary_products' => $data['primary_products'],
                ':farming_experience' => $farmingExperience,
                ':organic_certified' => $data['organic_certified'] ? 1 : 0,
                ':phone_number' => $data['phone_number'],
                ':additional_info' => $data['additional_info'] ?? null,
                ':status' => 'pending'
            ];

            // Log the actual parameters being used
            $this->logger->info("Executing farmer profile creation with params: " . json_encode($params));

            $stmt->execute($params);

            $this->logger->info("Farmer profile created successfully for user ID: {$userId}");
        } catch (\PDOException $e) {
            $this->logger->error("Database error during farmer profile creation: " . $e->getMessage());
            $this->logger->error("SQL: " . $sql);
            $this->logger->error("Params: " . json_encode($params));
            throw new \Exception("Failed to create farmer profile: " . $e->getMessage());
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
    public function getAllFarmers(?array $filters = null, ?int $limit = null, ?int $offset = null): array
    {
        try {
            $sql = "SELECT u.*, fp.*, 
                COUNT(DISTINCT p.id) as product_count
                FROM users u
                JOIN farmer_profiles fp ON u.id = fp.user_id
                LEFT JOIN products p ON fp.farmer_id = p.farmer_id
                WHERE u.role = 'farmer'";

            $params = [];

            if ($filters) {
                if (!empty($filters['status'])) {
                    $sql .= " AND fp.status = :status";
                    $params['status'] = $filters['status'];
                }
                if (!empty($filters['farm_type'])) {
                    $sql .= " AND fp.farm_type = :farm_type";
                    $params['farm_type'] = $filters['farm_type'];
                }
                if (!empty($filters['search'])) {
                    $sql .= " AND (u.name LIKE :search OR fp.farm_name LIKE :search OR fp.location LIKE :search)";
                    $params['search'] = "%{$filters['search']}%";
                }
            }

            $sql .= " GROUP BY u.id, fp.farmer_id, fp.status";
            $sql .= " ORDER BY fp.created_at DESC";

            if ($limit !== null) {
                $sql .= " LIMIT :limit";
                if ($offset !== null) {
                    $sql .= " OFFSET :offset";
                }
            }

            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($limit !== null) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                if ($offset !== null) {
                    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                }
            }

            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Ensure product_count is set for each row
            foreach ($result as &$row) {
                if (!isset($row['product_count'])) {
                    $row['product_count'] = 0;
                }
            }

            return $result;
        } catch (\PDOException $e) {
            $this->logger->error("Database error in getAllFarmers: " . $e->getMessage(), [
                'sql' => $sql ?? 'No SQL available',
                'params' => $params ?? 'No params available',
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
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

    public function viewFarmer(int $id): void
    {
        try {
            $farmer = $this->userModel->getFarmerDetails($id);

            if (!$farmer) {
                $this->setFlashMessage('Farmer not found', 'error');
                $this->redirect('/admin/farmers');
                return;
            }

            // Get status history
            $statusHistory = $this->userModel->getFarmerStatusHistory($id);

            $this->render('admin/farmer-details', [
                'farmer' => $farmer,
                'statusHistory' => $statusHistory,
                'pageTitle' => 'Farmer Details - ' . $farmer['name']
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Error viewing farmer: " . $e->getMessage());
            $this->setFlashMessage('Error loading farmer details', 'error');
            $this->redirect('/admin/farmers');
        }
    }

    public function getAllFarmersWithProductCounts(?array $filters = null, ?int $limit = null, ?int $offset = null): array
    {
        try {
            $sql = "SELECT 
                u.*,
                fp.*,
                COALESCE(COUNT(DISTINCT p.id), 0) as product_count,
                COALESCE(MAX(fp.status), 'pending') as current_status
            FROM users u
            JOIN farmer_profiles fp ON u.id = fp.user_id
            LEFT JOIN products p ON fp.farmer_id = p.farmer_id
            WHERE u.role = 'farmer'";

            $params = [];

            // Add filters
            if ($filters) {
                if (!empty($filters['status'])) {
                    $sql .= " AND fp.status = :status";
                    $params['status'] = $filters['status'];
                }
                if (!empty($filters['farm_type'])) {
                    $sql .= " AND fp.farm_type = :farm_type";
                    $params['farm_type'] = $filters['farm_type'];
                }
                if (!empty($filters['search'])) {
                    $sql .= " AND (u.name LIKE :search OR fp.farm_name LIKE :search OR fp.location LIKE :search)";
                    $params['search'] = "%{$filters['search']}%";
                }
            }

            $sql .= " GROUP BY u.id, fp.farmer_id";
            $sql .= " ORDER BY u.created_at DESC";

            if ($limit !== null) {
                $sql .= " LIMIT :limit";
                if ($offset !== null) {
                    $sql .= " OFFSET :offset";
                }
            }

            $stmt = $this->db->prepare($sql);

            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($limit !== null) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                if ($offset !== null) {
                    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                }
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting farmers with product counts: " . $e->getMessage());
            throw $e;
        }
    }
}
