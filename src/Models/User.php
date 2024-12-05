<?php

namespace App\Models;

use PDO;

class User
{
    private PDO $db;
    private $logger;

    public function __construct(PDO $db, $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function create(array $data): int
    {
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


    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function getTotalCount(): int
    {
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

    public function getNewUsersCount(int $days = 30): int
    {
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

    public function getActiveUsersCount(int $days = 30): int
    {
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

    public function getFarmerStats(): array
    {
        try {
            return [
                'total' => $this->getTotalFarmers(),
                'active' => $this->getActiveFarmers(),
                'pending_approval' => $this->getPendingFarmersCount(),
                'topPerforming' => $this->getTopPerformingFarmers(),
                'recentlyJoined' => $this->getRecentlyJoinedFarmers()
            ];
        } catch (\PDOException $e) {
            $this->logger->error("Error getting farmer stats: " . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'pending_approval' => 0,
                'topPerforming' => [],
                'recentlyJoined' => []
            ];
        }
    }

    private function getActiveFarmers(): int
    {
        try {
            $sql = "SELECT COUNT(*) 
                FROM farmer_profiles 
                WHERE status = 'active'";

            $stmt = $this->db->query($sql);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            $this->logger->error("Error getting active farmers: " . $e->getMessage());
            return 0;
        }
    }

    private function getRecentlyJoinedFarmers(int $limit = 5): array
    {
        try {
            $sql = "SELECT 
                    fp.*,
                    u.name,
                    u.email
                FROM farmer_profiles fp
                JOIN users u ON fp.user_id = u.id
                ORDER BY fp.created_at DESC
                LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting recently joined farmers: " . $e->getMessage());
            return [];
        }
    }

    public function updateFarmerStatus(int $farmerId, string $status, int $adminId, ?string $reason = null, ?string $duration = null): bool
    {
        try {
            $this->db->beginTransaction();

            // Debug log before update
            $this->logger->info("Attempting to update farmer status", [
                'farmer_id' => $farmerId,
                'status' => $status,
                'admin_id' => $adminId
            ]);

            // Update farmer status - Using farmer_id column
            $sql = "UPDATE farmer_profiles 
               SET status = :status,
                   updated_at = CURRENT_TIMESTAMP 
               WHERE farmer_id = :farmer_id";  // Changed from id to farmer_id

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'status' => $status,
                'farmer_id' => $farmerId
            ]);

            // Log affected rows
            $rowCount = $stmt->rowCount();
            $this->logger->info("Update query affected rows", ['count' => $rowCount]);

            if ($rowCount === 0) {
                throw new \Exception("No farmer found with ID: {$farmerId}");
            }

            // Handle specific status actions
            switch ($status) {
                case 'suspended':
                    $this->storeSuspensionDetails($farmerId, $duration ?? 'permanent', $reason ?? '', $adminId);
                    break;
                case 'rejected':
                    $this->createRejectionRecord($farmerId, $adminId, $reason ?? 'No reason provided');
                    break;
            }

            // Log the status change
            $this->logFarmerStatusChange($farmerId, $status, $adminId, $reason);

            $this->db->commit();

            $this->logger->info("Farmer status updated successfully", [
                'farmer_id' => $farmerId,
                'status' => $status,
                'admin_id' => $adminId,
                'duration' => $duration ?? 'N/A'
            ]);

            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            $this->logger->error("Error updating farmer status: " . $e->getMessage(), [
                'query' => $sql,
                'params' => [
                    'status' => $status,
                    'farmer_id' => $farmerId
                ]
            ]);
            return false;
        }
    }

    public function getCurrentSuspension(int $farmerId): ?array
    {
        try {
            $sql = "SELECT fs.*, u.name as suspended_by_name 
                    FROM farmer_suspensions fs
                    JOIN users u ON fs.suspended_by = u.id
                    WHERE fs.farmer_id = :farmer_id
                    AND (fs.expires_at IS NULL OR fs.expires_at > CURRENT_TIMESTAMP)
                    ORDER BY fs.created_at DESC
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['farmer_id' => $farmerId]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            $this->logger->error("Error getting current suspension: " . $e->getMessage());
            return null;
        }
    }

    public function getSuspensionHistory(int $farmerId): array
    {
        try {
            $sql = "SELECT fs.*, 
                          u.name as suspended_by_name
                   FROM farmer_suspensions fs
                   JOIN users u ON fs.suspended_by = u.id
                   WHERE fs.farmer_id = :farmer_id
                   ORDER BY fs.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['farmer_id' => $farmerId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting suspension history: " . $e->getMessage());
            return [];
        }
    }

    private function createRejectionRecord(int $farmerId, int $adminId, string $reason): void
    {
        $sql = "INSERT INTO farmer_rejections (
                farmer_id,
                reason,
                rejected_by
            ) VALUES (
                :farmer_id,
                :reason,
                :rejected_by
            )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'farmer_id' => $farmerId,
            'reason' => $reason,
            'rejected_by' => $adminId
        ]);
    }

    public function getRejectionDetails(int $farmerId): ?array
    {
        try {
            $sql = "SELECT fr.*, u.name as rejected_by_name
                FROM farmer_rejections fr
                JOIN users u ON fr.rejected_by = u.id
                WHERE fr.farmer_id = :farmer_id
                ORDER BY fr.created_at DESC
                LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['farmer_id' => $farmerId]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            $this->logger->error("Error getting rejection details: " . $e->getMessage());
            return null;
        }
    }

    private function logFarmerStatusChange(int $farmerId, string $status, int $adminId, ?string $reason = null): void
    {
        try {
            $sql = "INSERT INTO farmer_status_logs (
                    farmer_id, 
                    status, 
                    changed_by, 
                    reason, 
                    created_at
                ) VALUES (
                    :farmer_id, 
                    :status, 
                    :changed_by, 
                    :reason, 
                    CURRENT_TIMESTAMP
                )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'farmer_id' => $farmerId,
                'status' => $status,
                'changed_by' => $adminId,
                'reason' => $reason
            ]);

            $this->logger->info("Status change logged successfully", [
                'farmer_id' => $farmerId,
                'status' => $status,
                'admin_id' => $adminId
            ]);
        } catch (\PDOException $e) {
            $this->logger->error("Error logging farmer status change: " . $e->getMessage());
        }
    }

    public function updateUserStatus(int $userId, string $status, string $reason, int $adminId): bool
    {
        try {
            $this->db->beginTransaction();

            // Update user status
            $sql = "UPDATE users 
                    SET status = :status 
                    WHERE id = :user_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'status' => $status,
                'user_id' => $userId
            ]);

            // Log the status change with reason
            $this->logStatusChange('user', $userId, $status, $adminId, $reason);

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            $this->logger->error("Error updating user status: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalFarmers(): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM farmer_profiles";
            $stmt = $this->db->query($sql);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            $this->logger->error("Error getting total farmers: " . $e->getMessage());
            return 0;
        }
    }

    public function getPendingFarmersCount(): int
    {
        try {
            $sql = "SELECT COUNT(*) 
                FROM farmer_profiles 
                WHERE status = 'pending'";

            $stmt = $this->db->query($sql);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            $this->logger->error("Error getting pending farmers count: " . $e->getMessage());
            return 0;
        }
    }

    public function getTopPerformingFarmers(int $limit = 5): array
    {
        try {
            $sql = "SELECT 
                    fp.*,
                    u.name, 
                    u.email,
                    COUNT(DISTINCT o.order_id) as total_orders,
                    COALESCE(SUM(oi.total_price), 0) as total_revenue,
                    COALESCE(AVG(p.stock_quantity), 0) as avg_stock_level
                FROM farmer_profiles fp
                JOIN users u ON fp.user_id = u.id
                LEFT JOIN products p ON fp.farmer_id = p.farmer_id
                LEFT JOIN order_items oi ON p.product_id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.order_id
                    AND o.ordered_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                WHERE fp.status = 'active'
                GROUP BY fp.farmer_id, u.id
                ORDER BY total_revenue DESC
                LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting top performing farmers: " . $e->getMessage());
            return [];
        }
    }

    public function getUserAuditLog(int $userId, string $startDate, string $endDate): array
    {
        try {
            $sql = "SELECT 
                        al.*,
                        u.name as actor_name,
                        u.role as actor_role
                    FROM audit_logs al
                    JOIN users u ON al.actor_id = u.id
                    WHERE al.user_id = :user_id
                    AND al.created_at BETWEEN :start_date AND :end_date
                    ORDER BY al.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting user audit log: " . $e->getMessage());
            return [];
        }
    }

    private function logStatusChange(string $entityType, int $entityId, string $status, int $adminId, string $reason = null): void
    {
        $sql = "INSERT INTO status_change_logs (
                    entity_type, entity_id, status, changed_by, reason, created_at
                ) VALUES (
                    :entity_type, :entity_id, :status, :changed_by, :reason, NOW()
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'status' => $status,
            'changed_by' => $adminId,
            'reason' => $reason
        ]);
    }

    public function getAllCustomers(int $limit = null, int $offset = null): array
    {
        try {
            $sql = "SELECT u.*, cp.*
                    FROM users u
                    JOIN customer_profiles cp ON u.id = cp.user_id
                    WHERE u.role = 'customer'
                    ORDER BY u.created_at DESC";

            if ($limit !== null) {
                $sql .= " LIMIT :limit";
                if ($offset !== null) {
                    $sql .= " OFFSET :offset";
                }
            }

            $stmt = $this->db->prepare($sql);

            if ($limit !== null) {
                $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
                if ($offset !== null) {
                    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
                }
            }

            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting all customers: " . $e->getMessage());
            return [];
        }
    }

    public function getAllFarmers(?array $filters = null, ?int $limit = null, ?int $offset = null): array
    {
        try {
            $sql = "SELECT u.*, fp.*, 
                COUNT(DISTINCT p.product_id) as total_products,
                COUNT(DISTINCT o.order_id) as total_orders
                FROM users u
                JOIN farmer_profiles fp ON u.id = fp.user_id
                LEFT JOIN products p ON fp.farmer_id = p.farmer_id
                LEFT JOIN order_items oi ON p.product_id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.order_id
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

            $sql .= " GROUP BY u.id, fp.farmer_id ORDER BY u.created_at DESC";

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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting all farmers: " . $e->getMessage());
            return [];
        }
    }

    public function getUserStats(): array
    {
        try {
            $stats = [
                'total_users' => 0,
                'total_customers' => 0,
                'total_farmers' => 0,
                'active_users' => 0,
                'new_users_today' => 0,
                'new_users_this_week' => 0,
                'new_users_this_month' => 0
            ];

            // Get total counts
            $sql = "SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN role = 'customer' THEN 1 ELSE 0 END) as total_customers,
                    SUM(CASE WHEN role = 'farmer' THEN 1 ELSE 0 END) as total_farmers,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users
                    FROM users";
            $stmt = $this->db->query($sql);
            $totals = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stats = array_merge($stats, $totals);

            // Get new users counts
            $sql = "SELECT 
                    SUM(CASE WHEN created_at >= CURRENT_DATE THEN 1 ELSE 0 END) as new_today,
                    SUM(CASE WHEN created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY) THEN 1 ELSE 0 END) as new_this_week,
                    SUM(CASE WHEN created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_this_month
                    FROM users";
            $stmt = $this->db->query($sql);
            $newUsers = $stmt->fetch(\PDO::FETCH_ASSOC);

            $stats['new_users_today'] = $newUsers['new_today'];
            $stats['new_users_this_week'] = $newUsers['new_this_week'];
            $stats['new_users_this_month'] = $newUsers['new_this_month'];

            return $stats;
        } catch (\PDOException $e) {
            $this->logger->error("Error getting user stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Updates the last login timestamp for a user
     * 
     * @param int $userId The ID of the user
     * @return bool True if update was successful, false otherwise
     */
    public function updateLastLogin(int $userId): bool
    {
        try {
            $sql = "UPDATE users SET 
                    last_login = CURRENT_TIMESTAMP,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :user_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);

            $this->logger->info("Updated last login for user ID: {$userId}");
            return true;
        } catch (\PDOException $e) {
            $this->logger->error("Error updating last login: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Stores a password reset token for a user
     * 
     * @param int $userId The ID of the user
     * @param string $token The reset token
     * @return bool True if successful, false otherwise
     */
    public function storePasswordResetToken(int $userId, string $token): bool
    {
        try {
            $sql = "UPDATE users SET 
                    password_reset_token = :token,
                    token_expiry = DATE_ADD(NOW(), INTERVAL 24 HOUR),
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :user_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'token' => $token,
                'user_id' => $userId
            ]);

            $this->logger->info("Stored password reset token for user ID: {$userId}");
            return true;
        } catch (\PDOException $e) {
            $this->logger->error("Error storing password reset token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validates a password reset token
     * 
     * @param string $token The reset token to validate
     * @return int|null The user ID if valid, null otherwise
     */
    public function validatePasswordResetToken(string $token): ?int
    {
        try {
            $sql = "SELECT id FROM users 
                    WHERE password_reset_token = :token 
                    AND token_expiry > NOW()";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['token' => $token]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['id'] : null;
        } catch (\PDOException $e) {
            $this->logger->error("Error validating reset token: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Clears the password reset token for a user
     * 
     * @param int $userId The ID of the user
     * @return bool True if successful, false otherwise
     */
    public function clearPasswordResetToken(int $userId): bool
    {
        try {
            $sql = "UPDATE users SET 
                    password_reset_token = NULL,
                    token_expiry = NULL,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :user_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);

            return true;
        } catch (\PDOException $e) {
            $this->logger->error("Error clearing reset token: " . $e->getMessage());
            return false;
        }
    }

    public function getPendingFarmers(int $limit = 5): array
    {
        try {
            $sql = "SELECT fp.farmer_id as id, u.name, fp.farm_name, fp.farm_type, fp.location, fp.created_at
                FROM users u
                JOIN farmer_profiles fp ON u.id = fp.user_id
                WHERE fp.status = 'pending'
                ORDER BY fp.created_at DESC
                LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting pending farmers: " . $e->getMessage());
            return [];
        }
    }

    public function getFarmerDetails(int $farmerId): ?array
    {
        try {
            $sql = "SELECT 
                    fp.*,
                    u.name,
                    u.email,
                    u.created_at as joined_date,
                    COUNT(DISTINCT p.product_id) as total_products,
                    COUNT(DISTINCT o.order_id) as total_orders,
                    COALESCE(SUM(oi.total_price), 0) as total_revenue
                FROM farmer_profiles fp
                JOIN users u ON fp.user_id = u.id
                LEFT JOIN products p ON fp.farmer_id = p.farmer_id
                LEFT JOIN order_items oi ON p.product_id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.order_id
                WHERE fp.farmer_id = :farmer_id
                GROUP BY fp.farmer_id, u.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['farmer_id' => $farmerId]);

            $farmer = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($farmer) {
                // Get current suspension if any
                $farmer['current_suspension'] = $this->getCurrentSuspension($farmerId);
            }

            return $farmer ?: null;
        } catch (\PDOException $e) {
            $this->logger->error("Error getting farmer details: " . $e->getMessage());
            return null;
        }
    }

    public function storeSuspensionDetails(int $farmerId, string $duration, string $reason, int $adminId): bool
    {
        try {
            $sql = "INSERT INTO farmer_suspensions (
                    farmer_id, 
                    duration, 
                    reason, 
                    suspended_by, 
                    suspended_at,
                    expires_at
                ) VALUES (
                    :farmer_id,
                    :duration,
                    :reason,
                    :suspended_by,
                    CURRENT_TIMESTAMP,
                    CASE 
                        WHEN :duration = 'permanent' THEN NULL
                        ELSE DATE_ADD(CURRENT_TIMESTAMP, INTERVAL :duration_days DAY)
                    END
                )";

            $stmt = $this->db->prepare($sql);

            $durationDays = $duration === 'permanent' ? null : (int)$duration;

            $params = [
                'farmer_id' => $farmerId,
                'duration' => $duration,
                'reason' => $reason,
                'suspended_by' => $adminId,
                'duration_days' => $durationDays
            ];

            $stmt->execute($params);

            $this->logger->info("Suspension details stored successfully", [
                'farmer_id' => $farmerId,
                'duration' => $duration,
                'admin_id' => $adminId
            ]);

            return true;
        } catch (\PDOException $e) {
            $this->logger->error("Error storing suspension details: " . $e->getMessage(), [
                'query' => $sql,
                'params' => $params ?? []
            ]);
            return false;
        }
    }

    public function getTotalFarmersCount(?array $filters = null): int
    {
        try {
            $sql = "SELECT COUNT(DISTINCT u.id)
                FROM users u
                JOIN farmer_profiles fp ON u.id = fp.user_id
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

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            $this->logger->error("Error getting total farmers count: " . $e->getMessage());
            return 0;
        }
    }

    public function getFarmerStatusHistory(int $farmerId): array
    {
        try {
            $sql = "SELECT fsl.*, u.name as changed_by_name
                FROM farmer_status_logs fsl
                JOIN users u ON fsl.changed_by = u.id
                WHERE fsl.farmer_id = :farmer_id
                ORDER BY fsl.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['farmer_id' => $farmerId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting farmer status history: " . $e->getMessage());
            return [];
        }
    }
}
