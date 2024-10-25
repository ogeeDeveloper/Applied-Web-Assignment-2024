<?php
namespace App\Models;

use PDO;

class Order {
    private PDO $db;
    private $logger;

    public function __construct(PDO $db, $logger) {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function create(array $data): int {
        try {
            $this->db->beginTransaction();

            // Create the order
            $sql = "INSERT INTO orders (
                customer_trn, total_price, delivery_option,
                ordered_status, ordered_date
            ) VALUES (
                :customer_trn, :total_price, :delivery_option,
                :ordered_status, NOW()
            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'customer_trn' => $data['customer_id'],
                'total_price' => $data['total_price'],
                'delivery_option' => $data['delivery_option'],
                'ordered_status' => 'pending'
            ]);

            $orderId = (int) $this->db->lastInsertId();

            // Create order items
            foreach ($data['items'] as $item) {
                $this->createOrderItem($orderId, $item);
            }

            $this->db->commit();
            return $orderId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error("Error creating order: " . $e->getMessage());
            throw new \Exception("Failed to create order");
        }
    }

    private function createOrderItem(int $orderId, array $item): void {
        $sql = "INSERT INTO order_items (
            order_id, product_id, quantity, unit_price, total_price
        ) VALUES (
            :order_id, :product_id, :quantity, :unit_price, :total_price
        )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'order_id' => $orderId,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'total_price' => $item['quantity'] * $item['unit_price']
        ]);
    }

    public function getRecentOrdersByCustomer(int $customerId, int $limit = 5): array {
        try {
            $sql = "SELECT o.*, oi.*, p.name as product_name, f.farm_name
                    FROM orders o
                    JOIN order_items oi ON o.order_id = oi.order_id
                    JOIN products p ON oi.product_id = p.product_id
                    JOIN farmers f ON p.farmer_trn = f.farmer_trn
                    WHERE o.customer_trn = :customer_id
                    ORDER BY o.ordered_date DESC
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('customer_id', $customerId, PDO::PARAM_INT);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting customer orders: " . $e->getMessage());
            return [];
        }
    }

    public function getPendingOrdersByFarmer(int $farmerId): array {
        try {
            $sql = "SELECT o.*, oi.*, p.name as product_name, c.name as customer_name
                    FROM orders o
                    JOIN order_items oi ON o.order_id = oi.order_id
                    JOIN products p ON oi.product_id = p.product_id
                    JOIN customers c ON o.customer_trn = c.customer_trn
                    WHERE p.farmer_trn = :farmer_id
                    AND o.ordered_status = 'pending'
                    ORDER BY o.ordered_date DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['farmer_id' => $farmerId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting pending farmer orders: " . $e->getMessage());
            return [];
        }
    }

    public function getActiveOrdersByCustomer(int $customerId): array {
        try {
            $sql = "SELECT o.*, oi.*, p.name as product_name, f.farm_name, f.name as farmer_name
                    FROM orders o
                    JOIN order_items oi ON o.order_id = oi.order_id
                    JOIN products p ON oi.product_id = p.product_id
                    JOIN farmers f ON p.farmer_trn = f.farmer_trn
                    WHERE o.customer_trn = :customer_id
                    AND o.ordered_status IN ('pending', 'processing', 'confirmed')
                    ORDER BY o.ordered_date DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['customer_id' => $customerId]);

            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Group order items by order_id
            $groupedOrders = [];
            foreach ($orders as $order) {
                if (!isset($groupedOrders[$order['order_id']])) {
                    $groupedOrders[$order['order_id']] = [
                        'order_id' => $order['order_id'],
                        'ordered_date' => $order['ordered_date'],
                        'ordered_status' => $order['ordered_status'],
                        'total_price' => $order['total_price'],
                        'delivery_option' => $order['delivery_option'],
                        'items' => []
                    ];
                }
                
                $groupedOrders[$order['order_id']]['items'][] = [
                    'product_name' => $order['product_name'],
                    'quantity' => $order['quantity'],
                    'unit_price' => $order['unit_price'],
                    'farm_name' => $order['farm_name'],
                    'farmer_name' => $order['farmer_name']
                ];
            }

            return array_values($groupedOrders);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting active customer orders: " . $e->getMessage());
            return [];
        }
    }

    public function getMonthlyTotal($farmerId = null): float {
        try {
            $sql = "SELECT COALESCE(SUM(total_price), 0) as total 
                    FROM orders 
                    WHERE MONTH(ordered_date) = MONTH(CURRENT_DATE)
                    AND YEAR(ordered_date) = YEAR(CURRENT_DATE)";
            
            if ($farmerId) {
                $sql .= " AND farmer_trn = :farmer_id";
            }

            $stmt = $this->db->prepare($sql);
            if ($farmerId) {
                $stmt->bindValue('farmer_id', $farmerId, PDO::PARAM_INT);
            }
            $stmt->execute();

            return (float)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            $this->logger->error("Error getting monthly total: " . $e->getMessage());
            return 0.0;
        }
    }

    public function getMonthlyOrderCount($farmerId = null): int {
        try {
            $sql = "SELECT COUNT(*) 
                    FROM orders 
                    WHERE MONTH(ordered_date) = MONTH(CURRENT_DATE)
                    AND YEAR(ordered_date) = YEAR(CURRENT_DATE)";
            
            if ($farmerId) {
                $sql .= " AND farmer_trn = :farmer_id";
            }

            $stmt = $this->db->prepare($sql);
            if ($farmerId) {
                $stmt->bindValue('farmer_id', $farmerId, PDO::PARAM_INT);
            }
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            $this->logger->error("Error getting monthly order count: " . $e->getMessage());
            return 0;
        }
    }

    public function getOrderStats(): array {
        try {
            return [
                'daily' => $this->getDailyStats(),
                'weekly' => $this->getWeeklyStats(),
                'monthly' => $this->getMonthlyStats(),
                'yearly' => $this->getYearlyStats()
            ];
        } catch (\PDOException $e) {
            $this->logger->error("Error getting order stats: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentOrders(int $limit = 10): array {
        try {
            $sql = "SELECT 
                        o.*,
                        c.name as customer_name,
                        GROUP_CONCAT(p.name) as products
                    FROM orders o
                    JOIN customers c ON o.customer_trn = c.customer_trn
                    JOIN order_items oi ON o.order_id = oi.order_id
                    JOIN products p ON oi.product_id = p.product_id
                    GROUP BY o.order_id
                    ORDER BY o.ordered_date DESC
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting recent orders: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentOrdersByFarmer(int $farmerId, int $limit = 5): array {
        try {
            $sql = "SELECT 
                        o.*,
                        c.name as customer_name,
                        GROUP_CONCAT(p.name) as products
                    FROM orders o
                    JOIN customers c ON o.customer_trn = c.customer_trn
                    JOIN order_items oi ON o.order_id = oi.order_id
                    JOIN products p ON oi.product_id = p.product_id
                    WHERE p.farmer_trn = :farmer_id
                    GROUP BY o.order_id
                    ORDER BY o.ordered_date DESC
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('farmer_id', $farmerId, PDO::PARAM_INT);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting recent farmer orders: " . $e->getMessage());
            return [];
        }
    }

    private function getDailyStats(): array {
        $sql = "SELECT 
                    COUNT(*) as count,
                    SUM(total_price) as total,
                    AVG(total_price) as average
                FROM orders
                WHERE DATE(ordered_date) = CURRENT_DATE";
        
        return $this->executeStatsQuery($sql);
    }

    private function getWeeklyStats(): array {
        $sql = "SELECT 
                    COUNT(*) as count,
                    SUM(total_price) as total,
                    AVG(total_price) as average
                FROM orders
                WHERE ordered_date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)";
        
        return $this->executeStatsQuery($sql);
    }

    private function getMonthlyStats(): array {
        $sql = "SELECT 
                    COUNT(*) as count,
                    SUM(total_price) as total,
                    AVG(total_price) as average
                FROM orders
                WHERE MONTH(ordered_date) = MONTH(CURRENT_DATE)
                AND YEAR(ordered_date) = YEAR(CURRENT_DATE)";
        
        return $this->executeStatsQuery($sql);
    }

    private function getYearlyStats(): array {
        $sql = "SELECT 
                    COUNT(*) as count,
                    SUM(total_price) as total,
                    AVG(total_price) as average
                FROM orders
                WHERE YEAR(ordered_date) = YEAR(CURRENT_DATE)";
        
        return $this->executeStatsQuery($sql);
    }
    
    private function executeStatsQuery(string $sql): array {
        try {
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'count' => (int)$result['count'],
                'total' => (float)$result['total'],
                'average' => (float)$result['average']
            ];
        } catch (\PDOException $e) {
            $this->logger->error("Error executing stats query: " . $e->getMessage());
            return [
                'count' => 0,
                'total' => 0,
                'average' => 0
            ];
        }
    }

    public function getCustomerOrderCount(int $customerId): int {
        try {
            $sql = "SELECT COUNT(*) FROM orders WHERE customer_trn = :customer_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['customer_id' => $customerId]);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            $this->logger->error("Error getting customer order count: " . $e->getMessage());
            return 0;
        }
    }

    public function getActiveOrdersCount(int $customerId): int {
        try {
            $sql = "SELECT COUNT(*) 
                    FROM orders 
                    WHERE customer_trn = :customer_id 
                    AND ordered_status IN ('pending', 'processing', 'confirmed')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['customer_id' => $customerId]);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            $this->logger->error("Error getting active orders count: " . $e->getMessage());
            return 0;
        }
    }

}