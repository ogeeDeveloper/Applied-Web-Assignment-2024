<?php
namespace App\Models;

use PDO;

class OrderStatusManager {
    private PDO $db;
    private $logger;
    private $validStatusTransitions = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['processing', 'cancelled'],
        'processing' => ['ready_for_pickup', 'out_for_delivery', 'cancelled'],
        'ready_for_pickup' => ['completed', 'cancelled'],
        'out_for_delivery' => ['delivered', 'cancelled'],
        'delivered' => ['completed', 'refunded'],
        'completed' => ['refunded'],
        'cancelled' => ['refunded'],
        'refunded' => []
    ];

    public function __construct(PDO $db, $logger) {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Updates the status of an order in the database.
     *
     * @param int $orderId The ID of the order to update.
     * @param string $newStatus The new status to set for the order.
     * @param int $userId The ID of the user making the status change.
     * @param string|null $notes Additional notes for the status change.
     *
     * @return array An associative array containing the success status, message, and new status.
     *               If the update is successful, the 'success' key will be true, and the 'new_status' key will contain the new status.
     *               If the update fails, the 'success' key will be false, and the 'message' key will contain the error message.
     *
     * @throws \Exception If an invalid status transition is attempted.
     */
    public function updateOrderStatus(
        int $orderId, 
        string $newStatus, 
        int $userId, 
        ?string $notes = null
    ): array {
        try {
            // Get current status
            $currentStatus = $this->getCurrentStatus($orderId);

            // Validate status transition
            if (!$this->isValidTransition($currentStatus, $newStatus)) {
                throw new \Exception("Invalid status transition from {$currentStatus} to {$newStatus}");
            }

            $this->db->beginTransaction();

            // Update order status
            $sql = "UPDATE orders 
                    SET order_status = :status,
                        status_history = JSON_ARRAY_APPEND(
                            COALESCE(status_history, JSON_ARRAY()),
                            '$',
                            JSON_OBJECT(
                                'status', :status,
                                'timestamp', NOW(),
                                'changed_by', :user_id,
                                'notes', :notes
                            )
                        )
                    WHERE order_id = :order_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'status' => $newStatus,
                'user_id' => $userId,
                'notes' => $notes,
                'order_id' => $orderId
            ]);

            // Record in status history
            $sql = "INSERT INTO order_status_history (
                order_id, status, changed_by, notes
            ) VALUES (
                :order_id, :status, :changed_by, :notes
            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'order_id' => $orderId,
                'status' => $newStatus,
                'changed_by' => $userId,
                'notes' => $notes
            ]);

            // Handle status-specific actions
            $this->handleStatusSpecificActions($orderId, $newStatus);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Order status updated successfully',
                'new_status' => $newStatus
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error("Error updating order status: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }


    /**
     * Retrieves the current status of an order based on the order ID.
     *
     * @param int $orderId The ID of the order for which the current status needs to be retrieved.
     *
     * @return string Returns the current status of the order.
     *
     * @throws PDOException If an error occurs while executing the SQL query.
     */
    private function getCurrentStatus(int $orderId): string {
        $sql = "SELECT order_status FROM orders WHERE order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchColumn();
    }


    /**
     * Validates if a transition from the current status to the new status is valid.
     *
     * @param string $currentStatus The current order status.
     * @param string $newStatus The new order status to validate.
     *
     * @return bool Returns true if the transition is valid, false otherwise.
     *              A transition is considered valid if the new status is found in the list of possible next statuses
     *              for the current status.
     *
     * @throws \Exception If the current status is not found in the valid status transitions array.
     */
    private function isValidTransition(string $currentStatus, string $newStatus): bool {
        return isset($this->validStatusTransitions[$currentStatus]) &&
               in_array($newStatus, $this->validStatusTransitions[$currentStatus]);
    }


    private function handleStatusSpecificActions(int $orderId, string $newStatus): void {
        switch ($newStatus) {
            case 'out_for_delivery':
                $this->updateEstimatedDelivery($orderId);
                break;
            case 'delivered':
                $this->recordDeliveryCompletion($orderId);
                break;
            case 'cancelled':
                $this->handleOrderCancellation($orderId);
                break;
        }
    }

    /**
     * Retrieves the order timeline for a given order ID.
     *
     * The order timeline includes all status changes, timestamps, and the user who made the change.
     *
     * @param int $orderId The ID of the order for which the timeline needs to be retrieved.
     *
     * @return array Returns an array of order status history records. Each record is an associative array
     *               containing the following keys:
     *               - h.order_id: The ID of the order.
     *               - h.status: The new order status.
     *               - h.changed_at: The timestamp when the status was changed.
     *               - h.changed_by: The ID of the user who made the change.
     *               - u.name: The name of the user who made the change.
     *               - u.role: The role of the user who made the change.
     *               If no order status history is found for the given order ID, an empty array is returned.
     *
     * @throws \PDOException If an error occurs while executing the SQL query.
     */
    public function getOrderTimeline(int $orderId): array {
        $sql = "SELECT 
                    h.*,
                    u.name as changed_by_name,
                    u.role as changed_by_role
                FROM order_status_history h
                JOIN users u ON h.changed_by = u.id
                WHERE h.order_id = :order_id
                ORDER BY h.changed_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Updates the estimated delivery date for a given order by adding 2 hours to the current time.
     *
     * @param int $orderId The ID of the order for which the estimated delivery date needs to be updated.
     *
     * @return void
     *
     * @throws \PDOException If an error occurs while executing the SQL query.
     */
    private function updateEstimatedDelivery(int $orderId): void {
        $sql = "UPDATE orders 
                SET estimated_delivery = DATE_ADD(NOW(), INTERVAL 2 HOUR)
                WHERE order_id = :order_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);
    }


    /**
     * Records the completion of a delivery by updating the 'actual_delivery' timestamp in the 'orders' table.
     *
     * @param int $orderId The ID of the order for which the delivery is being completed.
     *
     * @return void
     *
     * @throws \PDOException If an error occurs while executing the SQL query.
     */
    private function recordDeliveryCompletion(int $orderId): void {
        $sql = "UPDATE orders 
                SET actual_delivery = NOW()
                WHERE order_id = :order_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);
    }


    /**
     * Handles the cancellation of an order by restoring the stock quantity for each product
     * and updating the order cancellation details.
     *
     * @param int $orderId The ID of the order to be cancelled.
     *
     * @return void
     *
     * @throws \Exception If an error occurs while executing the SQL queries or restoring stock.
     */
    private function handleOrderCancellation(int $orderId): void {
        try {
            // Get order items to restore stock
            $sql = "SELECT oi.product_id, oi.quantity
                    FROM order_items oi
                    WHERE oi.order_id = :order_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['order_id' => $orderId]);
            $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Restore stock for each product
            foreach ($orderItems as $item) {
                $this->restoreProductStock($item['product_id'], $item['quantity']);
            }

            // Update order cancellation details
            $sql = "UPDATE orders 
                    SET cancellation_reason = :reason,
                        status_updated_at = NOW()
                    WHERE order_id = :order_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'order_id' => $orderId,
                'reason' => $_POST['cancellation_reason'] ?? 'Order cancelled'
            ]);

            // Log the cancellation
            $this->logger->info("Order {$orderId} cancelled and stock restored");
        } catch (\Exception $e) {
            $this->logger->error("Error handling order cancellation: " . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Restores the stock quantity for a specific product after an order cancellation.
     *
     * @param int $productId The ID of the product whose stock needs to be restored.
     * @param int $quantity The quantity of the product to be restored.
     *
     * @return void
     *
     * @throws \Exception If an error occurs while executing the SQL query.
     */
    private function restoreProductStock(int $productId, int $quantity): void {
        $sql = "UPDATE products 
                SET stock_quantity = stock_quantity + :quantity,
                    updated_at = NOW()
                WHERE product_id = :product_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'product_id' => $productId,
            'quantity' => $quantity
        ]);
    }


    /**
     * Retrieves delivery metrics for the past 30 days.
     *
     * @return array Returns an associative array containing the following delivery metrics:
     *               - total_deliveries: The total number of delivered orders in the past 30 days.
     *               - avg_delivery_time: The average delivery time in minutes for the delivered orders.
     *               - on_time_deliveries: The number of delivered orders that were delivered on time.
     *               - delayed_deliveries: The number of delivered orders that were delayed.
     *               - on_time_percentage: The percentage of delivered orders that were delivered on time.
     *               If an error occurs during the retrieval, an array with default values is returned.
     *
     * @throws \Exception If an error occurs while executing the SQL query.
     */
    public function getDeliveryMetrics(): array {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_deliveries,
                        AVG(TIMESTAMPDIFF(MINUTE, ordered_date, actual_delivery)) as avg_delivery_time,
                        COUNT(CASE WHEN actual_delivery <= estimated_delivery THEN 1 END) as on_time_deliveries,
                        COUNT(CASE WHEN actual_delivery > estimated_delivery THEN 1 END) as delayed_deliveries
                    FROM orders 
                    WHERE order_status = 'delivered'
                    AND actual_delivery IS NOT NULL
                    AND ordered_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)";

            $stmt = $this->db->query($sql);
            $metrics = $stmt->fetch(PDO::FETCH_ASSOC);

            // Calculate on-time delivery percentage
            $metrics['on_time_percentage'] = $metrics['total_deliveries'] > 0 
                ? ($metrics['on_time_deliveries'] / $metrics['total_deliveries']) * 100 
                : 0;

            return $metrics;
        } catch (\Exception $e) {
            $this->logger->error("Error getting delivery metrics: " . $e->getMessage());
            return [
                'total_deliveries' => 0,
                'avg_delivery_time' => 0,
                'on_time_deliveries' => 0,
                'delayed_deliveries' => 0,
                'on_time_percentage' => 0
            ];
        }
    }


    /**
     * Retrieves the count of orders for each order status.
     *
     * @return array Returns an associative array where the keys are the order statuses
     *               and the values are the corresponding order counts.
     *               If an error occurs during the retrieval, an empty array is returned.
     *
     * @throws \Exception If an error occurs while executing the SQL query.
     */
    public function getOrderStatusCount(): array {
        try {
            $sql = "SELECT 
                        order_status,
                        COUNT(*) as count
                    FROM orders
                    GROUP BY order_status";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (\Exception $e) {
            $this->logger->error("Error getting order status counts: " . $e->getMessage());
            return [];
        }
    }


    /**
     * Searches for orders based on the provided filters.
     *
     * @param array $filters An associative array containing the search filters.
     *                       The keys can be 'status', 'date_from', 'date_to', and 'customer_id'.
     *                       The values are the corresponding filter values.
     *
     * @return array Returns an array of order records that match the provided filters.
     *               Each order record is an associative array containing the order details.
     *               If an error occurs during the search, an empty array is returned.
     *
     * @throws \Exception If an error occurs while executing the SQL query.
     */
    public function searchOrders(array $filters): array {
        try {
            $conditions = [];
            $params = [];

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

            $whereClause = !empty($conditions) 
                ? "WHERE " . implode(" AND ", $conditions) 
                : "";

            $sql = "SELECT 
                        o.*,
                        c.name as customer_name,
                        COUNT(oi.order_item_id) as total_items
                    FROM orders o
                    JOIN customer_profiles c ON o.customer_id = c.customer_id
                    LEFT JOIN order_items oi ON o.order_id = oi.order_id
                    {$whereClause}
                    GROUP BY o.order_id
                    ORDER BY o.ordered_date DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $this->logger->error("Error searching orders: " . $e->getMessage());
            return [];
        }
    }


    // Helper method to validate status exists
    /**
     * Checks if a given order status is valid.
     *
     * @param string $status The order status to validate.
     *
     * @return bool Returns true if the status is valid, false otherwise.
     *
     * @throws \Exception If the status is not found in the valid status transitions array.
     */
    public function isValidStatus(string $status): bool {
        return array_key_exists($status, $this->validStatusTransitions);
    }


    // Helper method to get possible next statuses
    /**
     * Retrieves the possible next statuses for a given order status.
     *
     * @param string $currentStatus The current order status.
     *
     * @return array Returns an array of possible next statuses for the given current status.
     *               If the current status is not found in the valid status transitions array,
     *               an empty array is returned.
     */
    public function getPossibleNextStatuses(string $currentStatus): array {
        return $this->validStatusTransitions[$currentStatus] ?? [];
    }

}