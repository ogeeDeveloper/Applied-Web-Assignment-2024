<?php

namespace App\Controllers;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use PDO;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Crop;
use App\Models\SystemHealth;
use App\Models\Activity;
use App\Constants\Roles;
use App\Utils\SessionManager;

class DashboardController extends BaseController
{
    private $db;
    private $logger;
    private $productModel;
    private $orderModel;
    private $cropModel;
    private $userModel;
    private $systemHealth;
    private $activityModel;

    public function __construct(PDO $db, $logger)
    {
        parent::__construct($db, $logger);
        SessionManager::validateActivity();
        $this->productModel = new Product($db, $logger);
        $this->orderModel = new Order($db, $logger);
        $this->cropModel = new Crop($db, $logger);
        $this->userModel = new User($db, $logger);
        $this->systemHealth = new SystemHealth($db, $logger);
        $this->activityModel = new Activity($db, $logger);

        // Validate role based on the requested dashboard
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requiredRole = match ($path) {
            '/admin/dashboard' => Roles::ADMIN,
            '/farmer/dashboard' => Roles::FARMER,
            '/customer/dashboard' => Roles::CUSTOMER,
            default => null
        };

        if ($requiredRole) {
            $this->validateRole($requiredRole);
        }
    }

    public function customerDashboard(): array
    {
        try {
            $userId = $_SESSION['user_id'];
            $this->logger->info("Loading customer dashboard for user: {$userId}");

            return [
                'success' => true,
                'data' => [
                    'recentOrders' => $this->orderModel->getRecentOrdersByCustomer($userId, 5),
                    'activeOrders' => $this->orderModel->getActiveOrdersByCustomer($userId),
                    'savedProducts' => $this->productModel->getSavedProducts($userId),
                    'recommendedProducts' => $this->productModel->getRecommendedProducts($userId),
                    'upcomingHarvests' => $this->cropModel->getUpcomingHarvests(null), // Pass null for all harvests
                    'dashboardStats' => [
                        'totalOrders' => $this->orderModel->getCustomerOrderCount($userId),
                        'savedProductsCount' => $this->productModel->getSavedProductsCount($userId),
                        'activeOrdersCount' => $this->orderModel->getActiveOrdersCount($userId)
                    ]
                ]
            ];
        } catch (\Exception $e) {
            $this->logger->error("Customer dashboard error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error loading dashboard'];
        }
    }

    public function farmerDashboard(): array
    {
        try {
            $farmerId = $_SESSION['user_id'];
            $this->logger->info("Loading farmer dashboard for user: {$farmerId}");

            return [
                'success' => true,
                'data' => [
                    'activeProducts' => $this->productModel->getActiveFarmerProducts($farmerId),
                    'pendingOrders' => $this->orderModel->getPendingOrdersByFarmer($farmerId),
                    'currentCrops' => $this->cropModel->getCurrentCrops($farmerId),
                    'upcomingHarvests' => $this->cropModel->getUpcomingHarvests($farmerId),
                    'lowStockProducts' => $this->productModel->getLowStockProducts($farmerId),
                    'recentOrders' => $this->orderModel->getRecentOrdersByFarmer($farmerId, 5),
                    'monthlyStats' => $this->getMonthlyStats($farmerId)
                ]
            ];
        } catch (\Exception $e) {
            $this->logger->error("Farmer dashboard error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error loading dashboard'];
        }
    }

    public function adminDashboard(): array
    {
        try {
            $this->logger->info("Loading admin dashboard");

            return [
                'success' => true,
                'data' => [
                    'userStats' => $this->getUserStats(),
                    'orderStats' => $this->orderModel->getOrderStats(),
                    'recentOrders' => $this->orderModel->getRecentOrders(10),
                    'topProducts' => $this->productModel->getTopProducts(),
                    'topFarmers' => $this->userModel->getFarmerStats(),
                    'systemHealth' => $this->getSystemHealth(),
                    'recentActivities' => $this->activityModel->getRecentActivities()
                ]
            ];
        } catch (\Exception $e) {
            $this->logger->error("Admin dashboard error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error loading dashboard'];
        }
    }

    private function getMonthlyStats($farmerId): array
    {
        // Implementation for monthly statistics
        return [
            'totalSales' => $this->orderModel->getMonthlyTotal($farmerId),
            'orderCount' => $this->orderModel->getMonthlyOrderCount($farmerId),
            'topProducts' => $this->productModel->getMonthlyTopProducts($farmerId)
        ];
    }

    private function getUserStats(): array
    {
        // Implementation for user statistics
        return [
            'totalUsers' => $this->userModel->getTotalCount(),
            'newUsers' => $this->userModel->getNewUsersCount(),
            'activeUsers' => $this->userModel->getActiveUsersCount()
        ];
    }

    private function getSystemHealth(): array
    {
        return [
            'dbStatus' => $this->systemHealth->checkDatabaseHealth(),
            'storageStatus' => $this->systemHealth->checkStorageHealth(),
            'queueStatus' => $this->systemHealth->checkQueueHealth()
        ];
    }
}
