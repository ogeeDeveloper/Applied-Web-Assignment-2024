<?php

namespace App\Models;

use PDO;

class Order
{
    private PDO $db;
    private $logger;
    private OrderStatusManager $statusManager;

    public function __construct(PDO $db, $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->statusManager = new OrderStatusManager($db, $logger);
    }

    /**
     * Creates a new order in the database.
     *
     * @param array $data The order data to be inserted.
     * @return array An array containing the success status, order ID, and message.
     * @throws Exception If the order creation fails.
     *
     * @throws \Exception If the order creation fails.
     */
    public function create(array $data): array
    {
        try {
            // Start transaction explicitly
            $this->db->beginTransaction();

            // Create the order with initial status
            $sql = "INSERT INTO orders (
                customer_id, total_amount, delivery_address,
                delivery_notes, order_status, payment_status,
                ordered_date
            ) VALUES (
                :customer_id, :total_amount, :delivery_address,
                :delivery_notes, 'pending', 'pending',
                NOW()
            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'customer_id' => $data['customer_id'],
                'total_amount' => $data['total_amount'],
                'delivery_address' => $data['delivery_address'],
                'delivery_notes' => !empty($data['delivery_notes']) ? $data['delivery_notes'] : null
            ]);

            $orderId = (int) $this->db->lastInsertId();

            // Create order items
            foreach ($data['items'] as $item) {
                $this->createOrderItem($orderId, $item);
                // Update product stock
                $this->updateProductStock($item['product_id'], $item['quantity']);
            }

            // Add initial status history without using status manager
            $sql = "INSERT INTO order_status_history (
                order_id, status, changed_by, notes
            ) VALUES (
                :order_id, 'pending', :changed_by, :notes
            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'order_id' => $orderId,
                'changed_by' => $data['customer_id'],
                'notes' => 'Order created'
            ]);

            // If everything is successful, commit the transaction
            $this->db->commit();

            return [
                'success' => true,
                'order_id' => $orderId,
                'message' => 'Order created successfully'
            ];
        } catch (\PDOException $e) {
            // Roll back the transaction on any error
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->logger->error("Error creating order: " . $e->getMessage());
            throw new \Exception("Failed to create order");
        } catch (\Exception $e) {
            // Roll back the transaction on any error
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->logger->error("Error creating order: " . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Updates the status of an order.
     *
     * @param int $orderId The ID of the order to update.
     * @param string $newStatus The new status to set for the order.
     * @param int $userId The ID of the user performing the status update.
     * @param string|null $notes Additional notes related to the status update.
     *
     * @return array An array containing the success status and message.
     *
     * @throws Exception If the status update fails.
     */
    public function updateStatus(int $orderId, string $newStatus, int $userId, ?string $notes = null): array
    {
        return $this->statusManager->updateOrderStatus($orderId, $newStatus, $userId, $notes);
    }

    /**
     * Retrieves the order timeline for a given order ID.
     *
     * @param int $orderId The ID of the order for which the timeline is to be retrieved.
     *
     * @return array An array containing the order timeline. Each element in the array represents a status update,
     *               with the following keys: 'status', 'updated_by', 'updated_at', and 'notes'.
     *
     * @throws Exception If the order timeline retrieval fails.
     */
    public function getOrderTimeline(int $orderId): array
    {
        return $this->statusManager->getOrderTimeline($orderId);
    }

    /**
     * Retrieves delivery metrics for all orders.
     *
     * @return array An associative array containing delivery metrics.
     *               The array will have the following keys:
     *               - 'total_orders': The total number of orders.
     *               - 'total_delivered': The total number of delivered orders.
     *               - 'total_pending': The total number of pending orders.
     *               - 'total_delayed': The total number of delayed orders.
     *               - 'average_delivery_time': The average delivery time for all orders.
     *               - 'most_frequent_delivery_option': The most frequent delivery option used.
     *               - 'least_frequent_delivery_option': The least frequent delivery option used.
     *
     * @throws Exception If there is an error retrieving the delivery metrics.
     */
    public function getDeliveryMetrics(): array
    {
        return $this->statusManager->getDeliveryMetrics();
    }

    private function updateProductStock(int $productId, int $quantity): void
    {
        $sql = "UPDATE products 
                SET stock_quantity = stock_quantity - :quantity 
                WHERE product_id = :product_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'product_id' => $productId,
            'quantity' => $quantity
        ]);

        // Check if stock is now low
        $sql = "SELECT stock_quantity, low_stock_alert_threshold 
                FROM products 
                WHERE product_id = :product_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['product_id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product && $product['stock_quantity'] <= ($product['low_stock_alert_threshold'] ?? 10)) {
            $sql = "UPDATE products 
                    SET status = 'out_of_stock' 
                    WHERE product_id = :product_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['product_id' => $productId]);
        }
    }

    /**
     * Creates a new order item in the database.
     *
     * @param int $orderId The ID of the order to which the item belongs.
     * @param array $item An associative array containing the details of the order item.
     *                     The array should have the following keys: 'product_id', 'quantity', 'unit_price'.
     *
     * @return void
     *
     * @throws PDOException If there is an error executing the database query.
     */
    private function createOrderItem(int $orderId, array $item): void
    {
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


    /**
     * Retrieves recent orders placed by a specific customer.
     *
     * @param int $customerId The unique identifier of the customer.
     * @param int $limit The maximum number of orders to retrieve. Default is 5.
     *
     * @return array An array of order details, each containing the following keys:
     *               - 'order_id': The unique identifier of the order.
     *               - 'ordered_date': The date and time when the order was placed.
     *               - 'product_name': The name of the product ordered.
     *               - 'farm_name': The name of the farm where the product is sourced.
     *
     * @throws PDOException If there is an error executing the database query.
     */
    public function getRecentOrdersByCustomer(int $customerId, int $limit = 5): array
    {
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


    /**
     * Retrieves pending orders placed by a specific farmer.
     *
     * @param int $farmerId The unique identifier of the farmer.
     *
     * @return array An array of order details, each containing the following keys:
     *               - 'order_id': The unique identifier of the order.
     *               - 'ordered_date': The date and time when the order was placed.
     *               - 'product_name': The name of the product ordered.
     *               - 'customer_name': The name of the customer who placed the order.
     *
     * @throws PDOException If there is an error executing the database query.
     */
    public function getPendingOrdersByFarmer(int $farmerId): array
    {
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


    /**
     * Retrieves active orders placed by a specific customer.
     *
     * @param int $customerId The unique identifier of the customer.
     *
     * @return array An array of order details, each containing the following keys:
     *               - 'order_id': The unique identifier of the order.
     *               - 'ordered_date': The date and time when the order was placed.
     *               - 'ordered_status': The current status of the order.
     *               - 'total_price': The total price of the order.
     *               - 'delivery_option': The chosen delivery option.
     *               - 'items': An array of order items, each containing the following keys:
     *                         - 'product_name': The name of the product ordered.
     *                         - 'quantity': The quantity of the product ordered.
     *                         - 'unit_price': The price per unit of the product.
     *                         - 'farm_name': The name of the farm where the product is sourced.
     *                         - 'farmer_name': The name of the farmer who produces the product.
     *
     * @throws PDOException If there is an error executing the database query.
     */
    public function getActiveOrdersByCustomer(int $customerId): array
    {
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


    /**
     * Retrieves the total price of orders placed by a specific farmer in the current month.
     * If no farmer ID is provided, the function will return the total price of all orders in the current month.
     *
     * @param int|null $farmerId The unique identifier of the farmer. If null, the total for all farmers will be returned.
     * @return float The total price of orders placed by the farmer in the current month.
     * @throws PDOException If there is an error executing the database query.
     */
    public function getMonthlyTotal($farmerId = null): float
    {
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


    /**
     * Retrieves the total number of orders placed by a specific farmer in the current month.
     * If no farmer ID is provided, the function will return the total for all farmers.
     *
     * @param int|null $farmerId The unique identifier of the farmer. If null, the total for all farmers will be returned.
     * @return int The total number of orders placed by the farmer in the current month.
     * @throws PDOException If there is an error executing the database query.
     */
    public function getMonthlyOrderCount($farmerId = null): int
    {
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


    public function getOrderStats(): array
    {
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

    /**
     * Retrieves recent orders placed by customers.
     *
     * @param int $limit The maximum number of orders to retrieve. Default is 10.
     * @return array An array of order details, each containing the following keys:
     *               - 'order_id': The unique identifier of the order.
     *               - 'ordered_date': The date and time when the order was placed.
     *               - 'customer_name': The name of the customer who placed the order.
     *               - 'products': A comma-separated list of product names in the order.
     *
     * @throws PDOException If there is an error executing the database query.
     */
    public function getRecentOrders(int $limit = 10): array
    {
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


    /**
     * Retrieves recent orders placed by farmers.
     *
     * @param int $farmerId The unique identifier of the farmer.
     * @param int $limit The maximum number of orders to retrieve. Default is 5.
     *
     * @return array An array of order details, each containing the following keys:
     *               - 'order_id': The unique identifier of the order.
     *               - 'ordered_date': The date and time when the order was placed.
     *               - 'customer_name': The name of the customer who placed the order.
     *               - 'products': A comma-separated list of product names in the order.
     *
     * @throws PDOException If there is an error executing the database query.
     */
    public function getRecentOrdersByFarmer(int $farmerId, int $limit = 5): array
    {
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


    private function getDailyStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as count,
                    SUM(total_amount) as total,
                    AVG(total_amount) as average
                FROM orders
                WHERE DATE(ordered_date) = CURRENT_DATE";

        return $this->executeStatsQuery($sql);
    }

    private function getWeeklyStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as count,
                    SUM(total_amount) as total,
                    AVG(total_amount) as average
                FROM orders
                WHERE ordered_date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)";

        return $this->executeStatsQuery($sql);
    }

    private function getMonthlyStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as count,
                    SUM(total_amount) as total,
                    AVG(total_amount) as average
                FROM orders
                WHERE MONTH(ordered_date) = MONTH(CURRENT_DATE)
                AND YEAR(ordered_date) = YEAR(CURRENT_DATE)";

        return $this->executeStatsQuery($sql);
    }

    private function getYearlyStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as count,
                    SUM(total_amount) as total,
                    AVG(total_amount) as average
                FROM orders
                WHERE YEAR(ordered_date) = YEAR(CURRENT_DATE)";

        return $this->executeStatsQuery($sql);
    }

    private function executeStatsQuery(string $sql): array
    {
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

    public function getCustomerOrderCount(int $customerId): int
    {
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

    public function getActiveOrdersCount(int $customerId): int
    {
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

    public function getAllOrders(array $filters = [], int $limit = null, int $offset = null): array
    {
        try {
            $conditions = [];
            $params = [];

            $sql = "SELECT o.*, 
                    c.name as customer_name,
                    fp.farm_name,
                    COUNT(DISTINCT oi.product_id) as total_items,
                    GROUP_CONCAT(DISTINCT p.name) as product_names
                    FROM orders o
                    JOIN customer_profiles cp ON o.customer_id = cp.customer_id
                    JOIN users c ON cp.user_id = c.id
                    JOIN order_items oi ON o.order_id = oi.order_id
                    JOIN products p ON oi.product_id = p.product_id
                    JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id";

            // Apply filters
            if (!empty($filters['status'])) {
                $conditions[] = "o.order_status = :status";
                $params['status'] = $filters['status'];
            }

            if (!empty($filters['date_from'])) {
                $conditions[] = "o.ordered_date >= :date_from";
                $params['date_from'] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $conditions[] = "o.ordered_date <= :date_to";
                $params['date_to'] = $filters['date_to'];
            }

            if (!empty($filters['customer_id'])) {
                $conditions[] = "o.customer_id = :customer_id";
                $params['customer_id'] = $filters['customer_id'];
            }

            if (!empty($filters['farmer_id'])) {
                $conditions[] = "fp.farmer_id = :farmer_id";
                $params['farmer_id'] = $filters['farmer_id'];
            }

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $sql .= " GROUP BY o.order_id ORDER BY o.ordered_date DESC";

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
                $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
                if ($offset !== null) {
                    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
                }
            }

            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting all orders: " . $e->getMessage());
            return [];
        }
    }

    public function getCustomerOrders(int $customerId, int $page = 1, int $limit = 10): array
    {
        try {
            // Calculate offset for pagination
            $offset = ($page - 1) * $limit;

            // Get total count for pagination
            $countSql = "SELECT COUNT(*) 
                        FROM orders 
                        WHERE customer_id = :customer_id";

            $stmt = $this->db->prepare($countSql);
            $stmt->execute(['customer_id' => $customerId]);
            $total = $stmt->fetchColumn();

            // Get orders with details
            $sql = "SELECT o.*,
                       GROUP_CONCAT(DISTINCT p.name) as products,
                       GROUP_CONCAT(DISTINCT f.farm_name) as farms,
                       COUNT(DISTINCT oi.product_id) as total_items,
                       (SELECT status 
                        FROM order_status_history 
                        WHERE order_id = o.order_id 
                        ORDER BY changed_at DESC 
                        LIMIT 1) as latest_status
                    FROM orders o
                    LEFT JOIN order_items oi ON o.order_id = oi.order_id
                    LEFT JOIN products p ON oi.product_id = p.product_id
                    LEFT JOIN farmer_profiles f ON p.farmer_id = f.farmer_id
                    WHERE o.customer_id = :customer_id
                    GROUP BY o.order_id
                    ORDER BY o.ordered_date DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':customer_id', $customerId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get detailed items for each order
            foreach ($orders as &$order) {
                $itemsSql = "SELECT oi.*, 
                               p.name as product_name,
                               p.unit_type,
                               f.farm_name,
                               f.farmer_id
                            FROM order_items oi
                            JOIN products p ON oi.product_id = p.product_id
                            JOIN farmer_profiles f ON p.farmer_id = f.farmer_id
                            WHERE oi.order_id = :order_id";

                $stmt = $this->db->prepare($itemsSql);
                $stmt->execute(['order_id' => $order['order_id']]);
                $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Get status history
                $historySql = "SELECT h.*,
                                u.name as changed_by_name
                             FROM order_status_history h
                             LEFT JOIN users u ON h.changed_by = u.id
                             WHERE h.order_id = :order_id
                             ORDER BY h.changed_at DESC";

                $stmt = $this->db->prepare($historySql);
                $stmt->execute(['order_id' => $order['order_id']]);
                $order['status_history'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Get estimated and actual delivery times if applicable
                if (in_array($order['order_status'], ['out_for_delivery', 'delivered', 'completed'])) {
                    $order['delivery_info'] = [
                        'estimated_delivery' => $order['estimated_delivery'],
                        'actual_delivery' => $order['actual_delivery'],
                        'delivery_notes' => $order['delivery_notes']
                    ];
                }
            }

            return [
                'success' => true,
                'data' => [
                    'orders' => $orders,
                    'pagination' => [
                        'total' => $total,
                        'page' => $page,
                        'limit' => $limit,
                        'total_pages' => ceil($total / $limit)
                    ]
                ]
            ];
        } catch (\PDOException $e) {
            $this->logger->error("Error getting customer orders: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'data' => [
                    'orders' => [],
                    'pagination' => [
                        'total' => 0,
                        'page' => $page,
                        'limit' => $limit,
                        'total_pages' => 0
                    ]
                ]
            ];
        }
    }

    public function getCustomerOrderDetails(int $orderId, int $customerId): ?array
    {
        try {
            // First verify this order belongs to the customer
            $sql = "SELECT o.*,
                       (SELECT status 
                        FROM order_status_history 
                        WHERE order_id = o.order_id 
                        ORDER BY changed_at DESC 
                        LIMIT 1) as latest_status
                    FROM orders o
                    WHERE o.order_id = :order_id 
                    AND o.customer_id = :customer_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'order_id' => $orderId,
                'customer_id' => $customerId
            ]);

            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                return null;
            }

            // Get order items
            $itemsSql = "SELECT oi.*, 
                           p.name as product_name,
                           p.unit_type,
                           p.description,
                           f.farm_name,
                           f.farmer_id,
                           f.location as farm_location
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.product_id
                        JOIN farmer_profiles f ON p.farmer_id = f.farmer_id
                        WHERE oi.order_id = :order_id";

            $stmt = $this->db->prepare($itemsSql);
            $stmt->execute(['order_id' => $orderId]);
            $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get status history
            $historySql = "SELECT h.*,
                            u.name as changed_by_name,
                            u.role as changed_by_role
                         FROM order_status_history h
                         LEFT JOIN users u ON h.changed_by = u.id
                         WHERE h.order_id = :order_id
                         ORDER BY h.changed_at DESC";

            $stmt = $this->db->prepare($historySql);
            $stmt->execute(['order_id' => $orderId]);
            $order['status_history'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group items by farmer
            $order['items_by_farmer'] = [];
            foreach ($order['items'] as $item) {
                $farmerId = $item['farmer_id'];
                if (!isset($order['items_by_farmer'][$farmerId])) {
                    $order['items_by_farmer'][$farmerId] = [
                        'farm_name' => $item['farm_name'],
                        'farm_location' => $item['farm_location'],
                        'items' => []
                    ];
                }
                $order['items_by_farmer'][$farmerId]['items'][] = $item;
            }

            return $order;
        } catch (\PDOException $e) {
            $this->logger->error("Error getting order details: " . $e->getMessage());
            return null;
        }
    }
}
