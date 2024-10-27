<?php

namespace App\Models;

use PDO;

class Customer {
    private $db;
    private $logger;

    public function __construct(PDO $db, $logger) {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Updates customer preferences in the database.
     *
     * @param int $userId The unique identifier of the customer.
     * @param array $data An associative array containing the customer's preferences, address, and phone number.
     *                    The array should have the following keys: 'preferences', 'address', 'phone_number'.
     *
     * @return array An associative array with two keys: 'success' (boolean) and 'message' (string).
     *               If the update is successful, 'success' will be true and 'message' will be 'Preferences updated successfully'.
     *               If an error occurs during the update, 'success' will be false and 'message' will be 'Failed to update preferences'.
     */
    public function updatePreferences(int $userId, array $data): array {
        try {
            $sql = "UPDATE customer_profiles 
                    SET 
                        preferences = :preferences,
                        address = :address,
                        phone_number = :phone_number,
                        updated_at = NOW()
                    WHERE user_id = :user_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'preferences' => json_encode($data['preferences'] ?? []),
                'address' => $data['address'],
                'phone_number' => $data['phone_number'],
                'user_id' => $userId
            ]);

            return [
                'success' => true,
                'message' => 'Preferences updated successfully'
            ];
        } catch (\PDOException $e) {
            $this->logger->error("Error updating preferences: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update preferences'
            ];
        }
    }


    /**
     * Retrieves a list of customer orders with pagination and sorting.
     *
     * @param int $customerId The unique identifier of the customer.
     * @param int $page The page number for pagination. Default is 1.
     * @param int $limit The number of orders per page. Default is 10.
     *
     * @return array An array of customer orders, each containing order details, product name, farm name, and farmer name.
     *               If an error occurs during the database operation, an empty array is returned.
     */
    public function getCustomerOrders(int $customerId, int $page = 1, int $limit = 10): array {
        try {
            $offset = ($page - 1) * $limit;

            $sql = "SELECT o.*, 
                        p.name as product_name,
                        f.farm_name,
                        f.name as farmer_name
                    FROM orders o
                    JOIN order_items oi ON o.order_id = oi.order_id
                    JOIN products p ON oi.product_id = p.product_id
                    JOIN farmer_profiles f ON p.farmer_id = f.farmer_id
                    WHERE o.customer_id = :customer_id
                    ORDER BY o.ordered_date DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('customer_id', $customerId, PDO::PARAM_INT);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting customer orders: " . $e->getMessage());
            return [];
        }
    }

}
