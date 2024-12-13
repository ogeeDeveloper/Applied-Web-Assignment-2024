<?php
namespace App\Controllers;

use App\Models\Order;
use Exception;
use PDO;

class OrderController extends BaseController {
    private Order $orderModel;

    public function __construct(PDO $db, $logger) {
        parent::__construct($db, $logger);
        $this->orderModel = new Order($db, $logger);
    }

    /**
     * Creates a new order.
     *
     * @throws Exception If the order items are not provided or invalid.
     * @return void
     */
    public function create(): void {
        try {
            $this->validateAuthenticatedRequest();

            $input = $this->validateInput([
                'customer_id' => 'int',
                'total_amount' => 'float',
                'delivery_address' => 'string',
                'delivery_notes' => 'string'
            ]);

            // Validate items array
            if (empty($_POST['items']) || !is_array($_POST['items'])) {
                throw new Exception('Order items are required');
            }

            $result = $this->orderModel->create([
                'customer_id' => $input['customer_id'],
                'total_amount' => $input['total_amount'],
                'delivery_address' => $input['delivery_address'],
                'delivery_notes' => $input['delivery_notes'] ?? null,
                'items' => $_POST['items']
            ]);

            $this->jsonResponse($result);
        } catch (Exception $e) {
            $this->logger->error("Error creating order: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to create order'
            ], 500);
        }
    }


    /**
     * Updates the status of an order.
     *
     * @throws Exception If the required parameters are not provided or invalid.
     *
     * @param void
     *
     * @return void
     */
    public function updateOrderStatus(): void {
        try {
            $this->validateAuthenticatedRequest();

            $input = $this->validateInput([
                'order_id' => 'int',
                'status' => 'string',
                'notes' => 'string'
            ]);

            $result = $this->orderModel->updateStatus(
                $input['order_id'],
                $input['status'],
                $_SESSION['user_id'],
                $input['notes'] ?? null
            );

            $this->jsonResponse($result);
        } catch (Exception $e) {
            $this->logger->error("Error updating order status: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update order status'
            ], 500);
        }
    }


    /**
     * Retrieves the order timeline for a given order ID.
     *
     * @param int $orderId The unique identifier of the order.
     *
     * @throws Exception If the user is not authenticated.
     *
     * @return void
     */
    public function getOrderTimeline(int $orderId): void {
        try {
            $this->validateAuthenticatedRequest();

            $timeline = $this->orderModel->getOrderTimeline($orderId);
            $this->jsonResponse([
                'success' => true,
                'timeline' => $timeline
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error getting order timeline: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get order timeline'
            ], 500);
        }
    }


    public function getCustomerOrders(): void {
        try {
            $this->validateAuthenticatedRequest();
            $customerId = $_SESSION['user_id'];

            $activeOrders = $this->orderModel->getActiveOrdersByCustomer($customerId);
            $recentOrders = $this->orderModel->getRecentOrdersByCustomer($customerId);

            $this->jsonResponse([
                'success' => true,
                'active_orders' => $activeOrders,
                'recent_orders' => $recentOrders
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error getting customer orders: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get orders'
            ], 500);
        }
    }

    public function getFarmerOrders(): void {
        try {
            $this->validateAuthenticatedRequest();
            $farmerId = $_SESSION['user_id'];
            
            $pendingOrders = $this->orderModel->getPendingOrdersByFarmer($farmerId);
            $recentOrders = $this->orderModel->getRecentOrdersByFarmer($farmerId, 5);
            
            $this->jsonResponse([
                'success' => true,
                'pending_orders' => $pendingOrders,
                'recent_orders' => $recentOrders
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error getting farmer orders: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get orders'
            ], 500);
        }
    }

    public function getOrderStats(): void {
        try {
            $this->validateAuthenticatedRequest();
            
            $stats = $this->orderModel->getOrderStats();
            $metrics = $this->orderModel->getDeliveryMetrics();
            
            $this->jsonResponse([
                'success' => true,
                'stats' => $stats,
                'delivery_metrics' => $metrics
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error getting order stats: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get order statistics'
            ], 500);
        }
    }
}