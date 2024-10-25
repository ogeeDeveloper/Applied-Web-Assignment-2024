<?php
namespace App\Models;

use PDO;

class User {
    private PDO $db;
    private $logger;

    public function __construct(PDO $db, $logger) {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function create(array $data): int {
        $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role']
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function getTotalCount(): int {
        try {
            $sql = "SELECT 
                    (SELECT COUNT(*) FROM customers) +
                    (SELECT COUNT(*) FROM farmers) +
                    (SELECT COUNT(*) FROM admins) as total";
            $stmt = $this->db->query($sql);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            $this->logger->error("Error getting total user count: " . $e->getMessage());
            return 0;
        }
    }

    public function getNewUsersCount(int $days = 30): int {
        try {
            $sql = "SELECT 
                    (SELECT COUNT(*) FROM customers WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL :days DAY)) +
                    (SELECT COUNT(*) FROM farmers WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL :days DAY)) +
                    (SELECT COUNT(*) FROM admins WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL :days DAY)) as total";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            $this->logger->error("Error getting new users count: " . $e->getMessage());
            return 0;
        }
    }

    public function getActiveUsersCount(int $days = 30): int {
        try {
            $sql = "SELECT COUNT(DISTINCT user_id) 
                    FROM activities 
                    WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL :days DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            $this->logger->error("Error getting active users count: " . $e->getMessage());
            return 0;
        }
    }

    public function getFarmerStats(): array {
        try {
            return [
                'total' => $this->getTotalFarmers(),
                'active' => $this->getActiveFarmers(),
                'topPerforming' => $this->getTopPerformingFarmers(),
                'recentlyJoined' => $this->getRecentlyJoinedFarmers()
            ];
        } catch (\PDOException $e) {
            $this->logger->error("Error getting farmer stats: " . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'topPerforming' => [],
                'recentlyJoined' => []
            ];
        }
    }

    private function getTotalFarmers(): int {
        $sql = "SELECT COUNT(*) FROM farmers";
        $stmt = $this->db->query($sql);
        return (int)$stmt->fetchColumn();
    }

    private function getActiveFarmers(): int {
        $sql = "SELECT COUNT(*) 
                FROM farmers 
                WHERE farmer_trn IN (
                    SELECT DISTINCT farmer_trn 
                    FROM products 
                    WHERE availability = TRUE
                )";
        $stmt = $this->db->query($sql);
        return (int)$stmt->fetchColumn();
    }

    private function getTopPerformingFarmers(int $limit = 5): array {
        $sql = "SELECT f.*, 
                       COUNT(DISTINCT o.order_id) as total_orders,
                       SUM(oi.total_price) as total_revenue
                FROM farmers f
                JOIN products p ON f.farmer_trn = p.farmer_trn
                JOIN order_items oi ON p.product_id = oi.product_id
                JOIN orders o ON oi.order_id = o.order_id
                WHERE o.ordered_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                GROUP BY f.farmer_trn
                ORDER BY total_revenue DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getRecentlyJoinedFarmers(int $limit = 5): array {
        $sql = "SELECT * 
                FROM farmers 
                ORDER BY created_at DESC 
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}