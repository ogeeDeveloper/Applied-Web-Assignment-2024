<?php
namespace App\Models;

use PDO;

class Activity {
    private $db;
    private $logger;

    public function __construct(PDO $db, $logger) {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function getRecentActivities(int $limit = 20): array {
        try {
            $sql = "SELECT a.*, 
                        CASE 
                            WHEN a.user_type = 'customer' THEN c.name
                            WHEN a.user_type = 'farmer' THEN f.name
                            WHEN a.user_type = 'admin' THEN ad.name
                        END as user_name
                    FROM activities a
                    LEFT JOIN customers c ON a.user_id = c.customer_trn AND a.user_type = 'customer'
                    LEFT JOIN farmers f ON a.user_id = f.farmer_trn AND a.user_type = 'farmer'
                    LEFT JOIN admins ad ON a.user_id = ad.admin_id AND a.user_type = 'admin'
                    ORDER BY a.created_at DESC
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting recent activities: " . $e->getMessage());
            return [];
        }
    }

    public function logActivity(string $action, int $userId, string $userType, array $details = []): bool {
        try {
            $sql = "INSERT INTO activities (action, user_id, user_type, details, created_at)
                    VALUES (:action, :user_id, :user_type, :details, CURRENT_TIMESTAMP)";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'action' => $action,
                'user_id' => $userId,
                'user_type' => $userType,
                'details' => json_encode($details)
            ]);
        } catch (\PDOException $e) {
            $this->logger->error("Error logging activity: " . $e->getMessage());
            return false;
        }
    }
}