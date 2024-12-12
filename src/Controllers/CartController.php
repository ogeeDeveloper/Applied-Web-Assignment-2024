<?php

namespace App\Controllers;

use App\Models\CartManager;
use Exception;

class CartController extends BaseController
{
    private CartManager $cartManager;

    public function __construct($db, $logger)
    {
        parent::__construct($db, $logger);
        $this->cartManager = new CartManager($db, $logger);
    }

    /**
     * Display shopping cart page
     */
    public function index(): void
    {
        $cartItems = [];
        $isLoggedIn = isset($_SESSION['user_id']);

        if ($isLoggedIn) {
            // Get cart items from database for logged in users
            $cartItems = $this->cartManager->getCartItems($_SESSION['user_id']);
        } else {
            // Get cart items from session for guests
            $cartItems = $_SESSION['cart'] ?? [];
        }

        $this->render('shopping-cart', [
            'cartItems' => $cartItems,
            'isLoggedIn' => $isLoggedIn
        ]);
    }

    /**
     * Add item to cart
     */
    public function addToCart(): void
    {
        try {
            // Get JSON input
            $jsonData = json_decode(file_get_contents('php://input'), true);

            if (!$jsonData || !isset($jsonData['product_id']) || !isset($jsonData['quantity'])) {
                throw new Exception('Invalid input data');
            }

            // Validate the input
            if (!is_numeric($jsonData['product_id']) || !is_numeric($jsonData['quantity'])) {
                throw new Exception('Invalid product ID or quantity');
            }

            $input = [
                'product_id' => (int)$jsonData['product_id'],
                'quantity' => (int)$jsonData['quantity']
            ];

            if (isset($_SESSION['user_id'])) {
                // Add to database cart for logged in users
                $result = $this->cartManager->addToCart(
                    $_SESSION['user_id'],
                    $input['product_id'],
                    $input['quantity']
                );
            } else {
                // Add to session cart for guests
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }

                // Get product details
                $stmt = $this->db->prepare("
                    SELECT product_id, name, price_per_unit, media_files
                    FROM products 
                    WHERE product_id = ? AND availability = TRUE
                ");
                $stmt->execute([$input['product_id']]);
                $product = $stmt->fetch(\PDO::FETCH_ASSOC);

                if (!$product) {
                    throw new Exception('Product not available');
                }

                // Add or update cart item
                $found = false;
                foreach ($_SESSION['cart'] as &$item) {
                    if ($item['product_id'] === $input['product_id']) {
                        $item['quantity'] += $input['quantity'];
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $_SESSION['cart'][] = [
                        'product_id' => $product['product_id'],
                        'name' => $product['name'],
                        'price' => $product['price_per_unit'],
                        'quantity' => $input['quantity'],
                        'media_files' => $product['media_files']
                    ];
                }

                $result = [
                    'success' => true,
                    'message' => 'Product added to cart'
                ];
            }

            $this->jsonResponse($result);
        } catch (Exception $e) {
            $this->logger->error("Error adding to cart: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateCart(): void
    {
        try {
            $input = $this->validateInput([
                'cart_id' => 'int',
                'quantity' => 'int'
            ]);

            if (isset($_SESSION['user_id'])) {
                $result = $this->cartManager->updateCartItemQuantity(
                    $_SESSION['user_id'],
                    $input['cart_id'],
                    $input['quantity']
                );
            } else {
                // Update session cart
                if (isset($_SESSION['cart'][$input['cart_id']])) {
                    if ($input['quantity'] <= 0) {
                        unset($_SESSION['cart'][$input['cart_id']]);
                    } else {
                        $_SESSION['cart'][$input['cart_id']]['quantity'] = $input['quantity'];
                    }
                }

                $result = [
                    'success' => true,
                    'message' => 'Cart updated successfully'
                ];
            }

            $this->jsonResponse($result);
        } catch (Exception $e) {
            $this->logger->error("Error updating cart: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error updating cart'
            ], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(): void
    {
        try {
            $input = $this->validateInput([
                'cart_id' => 'int'
            ]);

            if (isset($_SESSION['user_id'])) {
                $result = $this->cartManager->removeCartItem(
                    $_SESSION['user_id'],
                    $input['cart_id']
                );
            } else {
                // Remove from session cart
                if (isset($_SESSION['cart'][$input['cart_id']])) {
                    unset($_SESSION['cart'][$input['cart_id']]);
                }

                $result = [
                    'success' => true,
                    'message' => 'Item removed from cart'
                ];
            }

            $this->jsonResponse($result);
        } catch (Exception $e) {
            $this->logger->error("Error removing from cart: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error removing item from cart'
            ], 500);
        }
    }

    public function getCartItems(): void
    {
        try {
            if (isset($_SESSION['user_id'])) {
                $items = $this->cartManager->getCartItems($_SESSION['user_id']);
            } else {
                $items = $_SESSION['cart'] ?? [];
            }

            $this->jsonResponse([
                'success' => true,
                'items' => $items
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error getting cart items: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error getting cart items'
            ], 500);
        }
    }
}
