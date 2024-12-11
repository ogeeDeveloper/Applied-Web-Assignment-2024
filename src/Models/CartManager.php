<?php

namespace App\Models;

use PDO;

class CartManager
{
    private PDO $db;
    private $logger;

    public function __construct(PDO $db, $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Add item to cart (database for logged in users)
     */
    public function addToCart(int $userId, int $productId, int $quantity): array
    {
        try {
            // Check if product exists and is available
            $stmt = $this->db->prepare("
                SELECT product_id, stock_quantity, price_per_unit 
                FROM products 
                WHERE product_id = ? AND availability = TRUE
            ");
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Product not available'
                ];
            }

            if ($product['stock_quantity'] < $quantity) {
                return [
                    'success' => false,
                    'message' => 'Not enough stock available'
                ];
            }

            // Check if item already exists in cart
            $stmt = $this->db->prepare("
                SELECT cart_id, quantity 
                FROM cart_items 
                WHERE user_id = ? AND product_id = ?
            ");
            $stmt->execute([$userId, $productId]);
            $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cartItem) {
                // Update existing cart item
                $stmt = $this->db->prepare("
                    UPDATE cart_items 
                    SET quantity = quantity + ?, 
                        updated_at = CURRENT_TIMESTAMP 
                    WHERE cart_id = ?
                ");
                $stmt->execute([$quantity, $cartItem['cart_id']]);
            } else {
                // Add new cart item
                $stmt = $this->db->prepare("
                    INSERT INTO cart_items (
                        user_id, product_id, quantity, price_at_time
                    ) VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    $productId,
                    $quantity,
                    $product['price_per_unit']
                ]);
            }

            return [
                'success' => true,
                'message' => 'Product added to cart'
            ];
        } catch (\PDOException $e) {
            $this->logger->error("Error adding to cart: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error adding product to cart'
            ];
        }
    }

    /**
     * Get cart items for a user
     */
    public function getCartItems(int $userId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    ci.cart_id,
                    ci.product_id,
                    p.name,
                    p.media_files,
                    ci.quantity,
                    ci.price_at_time,
                    p.stock_quantity,
                    (ci.quantity * ci.price_at_time) as subtotal
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.product_id
                WHERE ci.user_id = ?
                ORDER BY ci.created_at DESC
            ");
            $stmt->execute([$userId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting cart items: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateCartItemQuantity(int $userId, int $cartId, int $quantity): array
    {
        try {
            if ($quantity <= 0) {
                return $this->removeCartItem($userId, $cartId);
            }

            $stmt = $this->db->prepare("
                UPDATE cart_items 
                SET quantity = ?, 
                    updated_at = CURRENT_TIMESTAMP 
                WHERE cart_id = ? AND user_id = ?
            ");
            $stmt->execute([$quantity, $cartId, $userId]);

            return [
                'success' => true,
                'message' => 'Cart updated successfully'
            ];
        } catch (\PDOException $e) {
            $this->logger->error("Error updating cart: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error updating cart'
            ];
        }
    }

    /**
     * Remove item from cart
     */
    public function removeCartItem(int $userId, int $cartId): array
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM cart_items 
                WHERE cart_id = ? AND user_id = ?
            ");
            $stmt->execute([$cartId, $userId]);

            return [
                'success' => true,
                'message' => 'Item removed from cart'
            ];
        } catch (\PDOException $e) {
            $this->logger->error("Error removing cart item: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error removing item from cart'
            ];
        }
    }

    /**
     * Get cart summary (total items and total price)
     */
    public function getCartSummary(int $userId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_items,
                    SUM(quantity * price_at_time) as total_price
                FROM cart_items
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting cart summary: " . $e->getMessage());
            return [
                'total_items' => 0,
                'total_price' => 0
            ];
        }
    }
}
