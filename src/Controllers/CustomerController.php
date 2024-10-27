<?php
namespace App\Controllers;

use PDO;
use Exception;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;

class CustomerController extends BaseController {
    private $db;
    private $logger;
    private $customerModel;
    private $orderModel;
    private $productModel;

    public function __construct(PDO $db, $logger) {
        $this->db = $db;
        $this->logger = $logger;
        $this->customerModel = new Customer($db, $logger);
        $this->orderModel = new Order($db, $logger);
        $this->productModel = new Product($db, $logger);
    }

    public function index(): void {
        try {
            $this->validateAuthenticatedRequest();
            $this->validateRole('customer');

            $dashboardController = new DashboardController($this->db, $this->logger);
            $dashboardData = $dashboardController->customerDashboard();

            if ($dashboardData['success']) {
                $this->render('customer/dashboard', $dashboardData['data']);
            } else {
                throw new Exception($dashboardData['message']);
            }
        } catch (Exception $e) {
            $this->logger->error("Error loading customer dashboard: " . $e->getMessage());
            $this->render('error', ['message' => 'Failed to load dashboard']);
        }
    }

    public function updateCustomerProfile(array $data): array {
        try {
            $userId = $_SESSION['user_id']; // Assuming the user is logged in
    
            // Handle file upload
            if (!empty($_FILES['profile_picture']['name'])) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);
                $uploadDir = __DIR__ . '/../../../storage/uploads/profile_pictures/';
                $fileName = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
                $filePath = $uploadDir . $fileName;
    
                if (!in_array($fileType, $allowedTypes)) {
                    throw new \Exception("Invalid file type. Allowed types are JPEG, PNG, and GIF.");
                }
    
                // Move the uploaded file
                if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $filePath)) {
                    throw new \Exception("Failed to upload profile picture.");
                }
    
                $data['profile_picture'] = '/storage/uploads/profile_pictures/' . $fileName;
            }
    
            // Update the customer's profile
            $stmt = $this->db->prepare("UPDATE customer_profiles SET profile_picture = :profile_picture WHERE user_id = :user_id");
            $stmt->execute([
                ':profile_picture' => $data['profile_picture'],
                ':user_id' => $userId
            ]);
    
            return [
                'success' => true,
                'message' => 'Profile updated successfully'
            ];
        } catch (Exception $e) {
            $this->logger->error("Profile update error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during profile update'
            ];
        }
    }    

    public function updatePreferences(): void {
        try {
            $this->validateAuthenticatedRequest();
            $this->validateRole('customer');
            
            $input = $this->validateInput([
                'preferences' => 'json',
                'address' => 'string',
                'phone_number' => 'string'
            ]);

            $result = $this->customerModel->updatePreferences(
                $_SESSION['user_id'],
                $input
            );

            $this->jsonResponse($result);
        } catch (Exception $e) {
            $this->logger->error("Error updating preferences: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update preferences'
            ], 500);
        }
    }

    public function saveProduct(): void {
        try {
            $this->validateAuthenticatedRequest();
            
            $input = $this->validateInput([
                'product_id' => 'int'
            ]);

            $result = $this->productModel->saveProduct(
                $_SESSION['user_id'],
                $input['product_id']
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Product saved successfully'
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error saving product: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to save product'
            ], 500);
        }
    }

    public function removeSavedProduct(): void {
        try {
            $this->validateAuthenticatedRequest();
            
            $input = $this->validateInput([
                'product_id' => 'int'
            ]);

            $result = $this->productModel->removeSavedProduct(
                $_SESSION['user_id'],
                $input['product_id']
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Product removed from saved items'
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error removing saved product: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to remove saved product'
            ], 500);
        }
    }

    public function getOrderHistory(): void {
        try {
            $this->validateAuthenticatedRequest();
            
            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1;
            $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?? 10;

            $orders = $this->orderModel->getCustomerOrders(
                $_SESSION['user_id'],
                $page,
                $limit
            );

            $this->jsonResponse([
                'success' => true,
                'orders' => $orders
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error getting order history: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get order history'
            ], 500);
        }
    }

    public function getCustomerStats(): void {
        try {
            $this->validateAuthenticatedRequest();
            
            $stats = [
                'total_orders' => $this->orderModel->getCustomerOrderCount($_SESSION['user_id']),
                'active_orders' => $this->orderModel->getActiveOrdersCount($_SESSION['user_id']),
                'saved_products' => $this->productModel->getSavedProductsCount($_SESSION['user_id'])
            ];

            $this->jsonResponse([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error getting customer stats: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get customer statistics'
            ], 500);
        }
    }
}
