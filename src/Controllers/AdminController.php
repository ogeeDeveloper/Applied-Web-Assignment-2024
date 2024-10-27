<?php
namespace App\Controllers;

use PDO;
use Exception;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\SystemHealth;

class AdminController extends BaseController {
    private User $userModel;
    private Order $orderModel;
    private Product $productModel;
    private SystemHealth $systemHealth;


    public function __construct(PDO $db, $logger) {
        parent::__construct($db, $logger);
        $this->userModel = new User($db, $logger);
        $this->orderModel = new Order($db, $logger);
        $this->productModel = new Product($db, $logger);
        $this->systemHealth = new SystemHealth($db, $logger);
    }

    public function dashboard(): void {
        try {
            $this->validateAuthenticatedRequest();
            $this->validateAdminRole();

            $stats = [
                'users' => $this->userModel->getUserStats(),
                'orders' => $this->orderModel->getOrderStats(),
                'products' => [
                    'total' => $this->productModel->getTotalProducts(),
                    'top' => $this->productModel->getTopProducts()
                ],
                'farmers' => $this->userModel->getFarmerStats()
            ];

            $this->render('admin/dashboard', ['stats' => $stats]);
        } catch (Exception $e) {
            $this->logger->error("Admin dashboard error: " . $e->getMessage());
            $this->redirect('/error', 'Failed to load admin dashboard');
        }
    }

    public function manageFarmers(): void {
        try {
            $this->validateAuthenticatedRequest();
            $this->validateAdminRole();

            $farmers = $this->userModel->getAllFarmers();
            $this->render('admin/farmers', ['farmers' => $farmers]);
        } catch (Exception $e) {
            $this->logger->error("Farmer management error: " . $e->getMessage());
            $this->redirect('/error', 'Failed to load farmer management');
        }
    }

    public function manageCustomers(): void {
        try {
            $this->validateAuthenticatedRequest();
            $this->validateAdminRole();

            $customers = $this->userModel->getAllCustomers();
            $this->render('admin/customers', ['customers' => $customers]);
        } catch (Exception $e) {
            $this->logger->error("Customer management error: " . $e->getMessage());
            $this->redirect('/error', 'Failed to load customer management');
        }
    }

    public function orderManagement(): void {
        try {
            $this->validateAuthenticatedRequest();
            $this->validateAdminRole();

            $orders = $this->orderModel->getAllOrders();
            $this->render('admin/orders', ['orders' => $orders]);
        } catch (Exception $e) {
            $this->logger->error("Order management error: " . $e->getMessage());
            $this->redirect('/error', 'Failed to load order management');
        }
    }

    public function productManagement(): void {
        try {
            $this->validateAuthenticatedRequest();
            $this->validateAdminRole();

            $products = $this->productModel->getAllProducts();
            $this->render('admin/products', ['products' => $products]);
        } catch (Exception $e) {
            $this->logger->error("Product management error: " . $e->getMessage());
            $this->redirect('/error', 'Failed to load product management');
        }
    }

    public function systemHealth(): void {
        try {
            $this->validateAuthenticatedRequest();
            $this->validateAdminRole();

            $health = [
                'database' => $this->systemHealth->checkDatabaseHealth(),
                'storage' => $this->systemHealth->checkStorageHealth(),
                'services' => $this->systemHealth->checkServicesHealth(),
                'queue' => $this->systemHealth->checkQueueHealth()
            ];

            $this->jsonResponse([
                'success' => true,
                'health' => $health
            ]);
        } catch (Exception $e) {
            $this->logger->error("System health check error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to check system health'
            ], 500);
        }
    }

    public function approveNewFarmer(): void {
        try {
            $this->validateAuthenticatedRequest();
            $this->validateAdminRole();

            $input = $this->validateInput([
                'farmer_id' => 'int'
            ]);

            $result = $this->userModel->updateFarmerStatus(
                $input['farmer_id'],
                'active',
                $_SESSION['user_id']
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Farmer approved successfully'
            ]);
        } catch (Exception $e) {
            $this->logger->error("Farmer approval error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to approve farmer'
            ], 500);
        }
    }

    public function suspendUser(): void {
        try {
            $this->validateAuthenticatedRequest();
            $this->validateAdminRole();

            $input = $this->validateInput([
                'user_id' => 'int',
                'reason' => 'string'
            ]);

            $result = $this->userModel->updateUserStatus(
                $input['user_id'],
                'suspended',
                $input['reason'],
                $_SESSION['user_id']
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'User suspended successfully'
            ]);
        } catch (Exception $e) {
            $this->logger->error("User suspension error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to suspend user'
            ], 500);
        }
    }

    public function getSystemMetrics(): void {
        try {
            $this->validateAuthenticatedRequest();
            $this->validateAdminRole();

            $metrics = [
                'users' => [
                    'total' => $this->userModel->getTotalCount(),
                    'active' => $this->userModel->getActiveUsersCount(),
                    'new' => $this->userModel->getNewUsersCount()
                ],
                'orders' => $this->orderModel->getOrderStats(),
                'products' => [
                    'total' => $this->productModel->getTotalProducts(),
                    'out_of_stock' => $this->productModel->getTotalProducts(null, 'out_of_stock'),
                    'top_selling' => $this->productModel->getTopProducts(5)
                ],
                'farmers' => [
                    'total' => $this->userModel->getTotalFarmers(),
                    'pending_approval' => $this->userModel->getPendingFarmersCount(),
                    'top_performing' => $this->userModel->getTopPerformingFarmers(5)
                ]
            ];

            $this->jsonResponse([
                'success' => true,
                'metrics' => $metrics
            ]);
        } catch (Exception $e) {
            $this->logger->error("System metrics error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get system metrics'
            ], 500);
        }
    }

    public function getUserAuditLog(): void {
        try {
            $this->validateAuthenticatedRequest();
            $this->validateAdminRole();

            $input = $this->validateInput([
                'user_id' => 'int',
                'start_date' => 'string',
                'end_date' => 'string'
            ]);

            $logs = $this->userModel->getUserAuditLog(
                $input['user_id'],
                $input['start_date'],
                $input['end_date']
            );

            $this->jsonResponse([
                'success' => true,
                'audit_logs' => $logs
            ]);
        } catch (Exception $e) {
            $this->logger->error("Audit log error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get audit logs'
            ], 500);
        }
    }

    public function getSystemLogs(): void {
        try {
            $this->validateAuthenticatedRequest();
            $this->validateAdminRole();

            $input = $this->validateInput([
                'log_type' => 'string',
                'start_date' => 'string',
                'end_date' => 'string',
                'level' => 'string'
            ]);

            $logs = $this->systemHealth->getSystemLogs(
                $input['log_type'],
                $input['start_date'],
                $input['end_date'],
                $input['level'] ?? 'ERROR'
            );

            $this->jsonResponse([
                'success' => true,
                'logs' => $logs
            ]);
        } catch (Exception $e) {
            $this->logger->error("System logs error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get system logs'
            ], 500);
        }
    }

    protected function validateAdminRole(): void {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->logger->warning('Unauthorized access attempt to admin area');
            throw new Exception('Unauthorized access to admin area', 403);
        }
    }

}