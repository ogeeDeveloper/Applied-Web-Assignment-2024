<?php

namespace App\Controllers;

use App\Models\CartManager;
use App\Models\Order;
use Exception;

class CheckoutController extends BaseController
{
    private CartManager $cartManager;
    private Order $orderModel;

    public function __construct($db, $logger)
    {
        parent::__construct($db, $logger);
        $this->cartManager = new CartManager($db, $logger);
        $this->orderModel = new Order($db, $logger);
    }

    public function index(): void
    {
        try {
            // Ensure user is logged in
            if (!isset($_SESSION['user_id'])) {
                $this->redirect('/login?redirect=/checkout');
                return;
            }

            // Get cart items
            $cartItems = $this->cartManager->getCartItems($_SESSION['user_id']);

            if (empty($cartItems)) {
                $this->setFlashMessage('Your cart is empty', 'error');
                $this->redirect('/cart');
                return;
            }

            // Calculate totals
            $total = 0;
            foreach ($cartItems as $item) {
                $total += $item['price_at_time'] * $item['quantity'];
            }

            // Get customer profile
            $stmt = $this->db->prepare("
                SELECT cp.* 
                FROM customer_profiles cp 
                WHERE cp.user_id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $customer = $stmt->fetch(\PDO::FETCH_ASSOC);

            $this->render('checkout', [
                'cartItems' => $cartItems,
                'total' => $total,
                'customer' => $customer
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error loading checkout page: " . $e->getMessage());
            $this->setFlashMessage('Error loading checkout page', 'error');
            $this->redirect('/cart');
        }
    }

    public function placeOrder(): void
    {
        try {
            // Ensure user is logged in
            if (!isset($_SESSION['user_id'])) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Please log in to place an order'
                ], 401);
                return;
            }

            // Get customer profile ID
            $stmt = $this->db->prepare("SELECT customer_id FROM customer_profiles WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $customerId = $stmt->fetchColumn();

            if (!$customerId) {
                throw new Exception('Customer profile not found');
            }

            // Get JSON input data
            $jsonData = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            if (empty($jsonData['delivery_address'])) {
                throw new Exception('Delivery address is required');
            }

            // Get cart items
            $cartItems = $this->cartManager->getCartItems($_SESSION['user_id']);

            if (empty($cartItems)) {
                throw new Exception('Cart is empty');
            }

            // Calculate total amount
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['price_at_time'] * $item['quantity'];
            }

            // Prepare order data
            $orderData = [
                'customer_id' => $customerId,
                'total_amount' => $totalAmount,
                'delivery_address' => $jsonData['delivery_address'],
                'delivery_notes' => $jsonData['delivery_notes'] ?? null,
                'items' => array_map(function ($item) {
                    return [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price_at_time']
                    ];
                }, $cartItems)
            ];

            // Log the order data for debugging
            $this->logger->info("Creating order with data:", $orderData);

            // Create order
            $result = $this->orderModel->create($orderData);

            if ($result['success']) {
                // Clear cart after successful order
                $this->cartManager->clearCart($_SESSION['user_id']);

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Order placed successfully',
                    'order_id' => $result['order_id'],
                    'redirect' => "/order/confirmation/{$result['order_id']}"
                ]);
            } else {
                throw new Exception($result['message'] ?? 'Failed to create order');
            }
        } catch (Exception $e) {
            $this->logger->error("Error placing order: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function showConfirmation(int $orderId): void
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                $this->redirect('/login');
                return;
            }

            // Get customer profile ID
            $stmt = $this->db->prepare("SELECT customer_id FROM customer_profiles WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $customerId = $stmt->fetchColumn();

            // Get order details with customer verification
            $order = $this->orderModel->getCustomerOrderDetails($orderId, $customerId);

            if (!$order) {
                $this->setFlashMessage('Order not found', 'error');
                $this->redirect('/account/orders');
                return;
            }

            $this->render('order-confirmation', [
                'order' => $order
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error showing order confirmation: " . $e->getMessage());
            $this->setFlashMessage('Error loading order confirmation', 'error');
            $this->redirect('/account/orders');
        }
    }
}
