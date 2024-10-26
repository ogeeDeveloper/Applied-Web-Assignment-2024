<?php
namespace App\Controllers;

use PDO;
use Exception;

class CustomerController extends BaseController {
    private $db;
    private $logger;

    public function __construct(PDO $db, $logger) {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function updateCustomerProfile(array $data): array {
        try {
            $userId = $_SESSION['user_id']; // Assuming the user is logged in
    
            // Handle file upload
            if (!empty($_FILES['profile_picture']['name'])) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);
                $uploadDir = __DIR__ . '/../../../storage/uploads/profile_pictures/';
                $fileName = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
                $filePath = $uploadDir . $fileName;
    
                if (!in_array($fileType, $allowedTypes)) {
                    throw new \Exception("Invalid file type. Allowed types are JPEG, PNG, and GIF.");
                }
    
                // Move the uploaded file
                if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $filePath)) {
                    throw new \Exception("Failed to upload profile picture.");
                }
    
                $data['profile_picture'] = '/storage/uploads/profile_pictures/' . $fileName;
            }
    
            // Update the customer's profile
            $stmt = $this->db->prepare("UPDATE customer_profiles SET profile_picture = :profile_picture WHERE user_id = :user_id");
            $stmt->execute([
                ':profile_picture' => $data['profile_picture'],
                ':user_id' => $userId
            ]);
    
            return [
                'success' => true,
                'message' => 'Profile updated successfully'
            ];
        } catch (Exception $e) {
            $this->logger->error("Profile update error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during profile update'
            ];
        }
    }    
}
