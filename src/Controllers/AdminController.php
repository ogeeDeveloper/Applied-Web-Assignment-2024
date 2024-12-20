<?php

namespace App\Controllers;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use PDO;
use Exception;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\SystemHealth;
use App\Utils\SessionManager;
use App\Constants\Roles;
use App\Models\Farmer;

class AdminController extends BaseController
{
    private User $userModel;
    private Order $orderModel;
    private Product $productModel;
    private SystemHealth $systemHealth;
    private string $adminLayout = 'admin/layouts/admin';
    private Farmer $farmerModel;

    public function __construct(PDO $db, $logger)
    {
        parent::__construct($db, $logger);
        $this->userModel = new User($db, $logger);
        $this->orderModel = new Order($db, $logger);
        $this->productModel = new Product($db, $logger);
        $this->systemHealth = new SystemHealth($db, $logger);
        $this->farmerModel = new Farmer($db, $logger);

        // Initialize session
        SessionManager::initialize();
        SessionManager::validateActivity();

        // Only validate admin access for non-login routes
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (!in_array($currentPath, ['/admin/login', '/admin/forgot-password'])) {
            $this->validateAdminAccess();
        }
    }

    private function validateAdminAccess(): void
    {
        if (
            !isset($_SESSION['user_role']) ||
            $_SESSION['user_role'] !== Roles::ADMIN ||
            !isset($_SESSION['is_authenticated']) ||
            $_SESSION['is_authenticated'] !== true ||
            !isset($_SESSION['admin_id'])
        ) {

            // Log the failed access attempt
            $this->logger->warning('Unauthorized admin access attempt', [
                'session' => $_SESSION,
                'uri' => $_SERVER['REQUEST_URI']
            ]);

            // Clear the invalid session
            SessionManager::destroy();

            // Redirect to login
            $this->redirect('/admin/login');
            exit;
        }

        // Update last activity
        $_SESSION['last_activity'] = time();
    }

    private function clearAdminSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = array();
        session_destroy();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
    }

    public function dashboard(): void
    {
        try {
            // Initialize stats array with default values
            $stats = [
                'users' => [
                    'total' => 0,
                    'new' => 0,
                    'active' => 0
                ],
                'orders' => [
                    'total' => 0,
                    'pending' => 0,
                    'completed' => 0
                ],
                'products' => [
                    'total' => 0,
                    'out_of_stock' => 0
                ],
                'farmers' => [
                    'total' => 0,
                    'pending_approval' => 0,
                    'active' => 0
                ]
            ];

            // Get stats from models with error handling
            try {
                $stats['users'] = $this->userModel->getUserStats() ?: $stats['users'];
            } catch (Exception $e) {
                $this->logger->error("Error getting user stats: " . $e->getMessage());
            }

            try {
                $stats['orders'] = $this->orderModel->getOrderStats() ?: $stats['orders'];
            } catch (Exception $e) {
                $this->logger->error("Error getting order stats: " . $e->getMessage());
            }

            try {
                $stats['products'] = [
                    'total' => $this->productModel->getTotalProducts() ?: 0,
                    'out_of_stock' => $this->productModel->getTotalProducts(null, 'out_of_stock') ?: 0
                ];
            } catch (Exception $e) {
                $this->logger->error("Error getting product stats: " . $e->getMessage());
            }

            try {
                $stats['farmers'] = $this->userModel->getFarmerStats() ?: $stats['farmers'];
            } catch (Exception $e) {
                $this->logger->error("Error getting farmer stats: " . $e->getMessage());
            }

            // Get recent data
            $recentOrders = [];
            $pendingFarmers = [];
            try {
                $recentOrders = $this->orderModel->getRecentOrders(5);
                $pendingFarmers = $this->userModel->getPendingFarmers(5);
            } catch (Exception $e) {
                $this->logger->error("Error getting recent data: " . $e->getMessage());
            }

            // Render dashboard with all data
            $this->render('admin/dashboard', [
                'stats' => $stats,
                'recentOrders' => $recentOrders,
                'pendingFarmers' => $pendingFarmers,
                'pageTitle' => 'Admin Dashboard - AgriKonnect'
            ], $this->adminLayout);
        } catch (Exception $e) {
            $this->logger->error("Dashboard error: " . $e->getMessage());
            $this->setFlashMessage('Error loading dashboard', 'error');
            $this->render('admin/dashboard', [
                'stats' => $stats,
                'error' => true,
                'pageTitle' => 'Admin Dashboard - AgriKonnect'
            ], $this->adminLayout);
        }
    }

    public function manageFarmers(): void
    {
        try {
            // Get pagination parameters
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = 10;
            $offset = ($page - 1) * $limit;

            // Get filter parameters
            $filters = [
                'status' => $_GET['status'] ?? null,
                'farm_type' => $_GET['farm_type'] ?? null,
                'search' => $_GET['search'] ?? null
            ];

            // Get farmers count first
            $totalRecords = $this->userModel->getTotalFarmersCount($filters);

            // Get stats (with error handling for each stat)
            $stats = [];
            try {
                $stats = [
                    'total' => $this->userModel->getTotalFarmersCount(),
                    'active' => $this->userModel->getTotalFarmersCount(['status' => 'active']),
                    'pending' => $this->userModel->getTotalFarmersCount(['status' => 'pending']),
                    'suspended' => $this->userModel->getTotalFarmersCount(['status' => 'suspended'])
                ];
            } catch (Exception $e) {
                $this->logger->error("Error fetching farmer stats: " . $e->getMessage());
                $stats = [
                    'total' => 0,
                    'active' => 0,
                    'pending' => 0,
                    'suspended' => 0
                ];
            }

            // Calculate pagination
            $totalPages = ceil($totalRecords / $limit);
            $startRecord = $offset + 1;
            $endRecord = min($offset + $limit, $totalRecords);

            // Get farmers list
            $farmers = [];
            try {
                $farmers = $this->userModel->getAllFarmers($filters, $limit, $offset);

                // Add product count if not present
                foreach ($farmers as &$farmer) {
                    if (!isset($farmer['product_count'])) {
                        $farmer['product_count'] = 0;
                    }
                }
            } catch (Exception $e) {
                $this->logger->error("Error fetching farmers list: " . $e->getMessage());
                throw $e;
            }

            $this->render('admin/farmers', [
                'pageTitle' => 'Manage Farmers - AgriKonnect Admin',
                'farmers' => $farmers,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalRecords' => $totalRecords,
                'startRecord' => $startRecord,
                'endRecord' => $endRecord,
                'filters' => $filters,
                'stats' => $stats
            ], $this->adminLayout);  // Added adminLayout parameter

        } catch (Exception $e) {
            $this->logger->error("Error in manageFarmers: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Instead of redirecting, render the page with an error message
            $this->render('admin/farmers', [
                'pageTitle' => 'Manage Farmers - AgriKonnect Admin',
                'farmers' => [],
                'currentPage' => 1,
                'totalPages' => 0,
                'totalRecords' => 0,
                'startRecord' => 0,
                'endRecord' => 0,
                'filters' => [],
                'stats' => [
                    'total' => 0,
                    'active' => 0,
                    'pending' => 0,
                    'suspended' => 0
                ],
                'error' => 'Failed to load farmers data. Please try again later.'
            ], $this->adminLayout);
        }
    }

    public function manageCustomers(): void
    {
        try {
            // $this->validateAuthenticatedRequest();
            // $this->validateAdminRole();

            $customers = $this->userModel->getAllCustomers();
            $this->render('admin/customers', ['customers' => $customers]);
        } catch (Exception $e) {
            $this->logger->error("Customer management error: " . $e->getMessage());
            $this->redirect('/error', 'Failed to load customer management');
        }
    }

    public function orderManagement(): void
    {
        try {
            // $this->validateAuthenticatedRequest();
            // $this->validateAdminRole();

            $orders = $this->orderModel->getAllOrders();
            $this->render('admin/orders', ['orders' => $orders]);
        } catch (Exception $e) {
            $this->logger->error("Order management error: " . $e->getMessage());
            $this->redirect('/error', 'Failed to load order management');
        }
    }

    public function productManagement(): void
    {
        try {
            // $this->validateAuthenticatedRequest();
            // $this->validateAdminRole();

            $products = $this->productModel->getAllProducts();
            $this->render('admin/products', ['products' => $products]);
        } catch (Exception $e) {
            $this->logger->error("Product management error: " . $e->getMessage());
            $this->redirect('/error', 'Failed to load product management');
        }
    }

    public function systemHealth(): void
    {
        try {
            $healthMetrics = [
                'database' => $this->systemHealth->checkDatabaseHealth(),
                'storage' => $this->systemHealth->checkStorageHealth(),
                'services' => $this->systemHealth->checkServicesHealth(),
                'queue' => $this->systemHealth->checkQueueHealth()
            ];

            $systemMetrics = [
                'memory' => [
                    'used' => memory_get_usage(true),
                    'total' => ini_get('memory_limit'),
                    'percentage' => (memory_get_usage(true) / ini_get('memory_limit')) * 100
                ],
                'storage' => [
                    'used' => disk_free_space('/'),
                    'total' => disk_total_space('/'),
                    'percentage' => (disk_free_space('/') / disk_total_space('/')) * 100
                ]
            ];

            $recentLogs = $this->systemHealth->getRecentLogs(20);

            $this->render('admin/system', [
                'pageTitle' => 'System Health - AgriKonnect Admin',
                'healthMetrics' => $healthMetrics,
                'systemMetrics' => $systemMetrics,
                'recentLogs' => $recentLogs
            ]);
        } catch (Exception $e) {
            $this->logger->error("System health check error: " . $e->getMessage());
            $this->setFlashMessage('Failed to check system health', 'error');
            $this->redirect('/admin/dashboard');
        }
    }

    public function approveNewFarmer(): void
    {
        try {
            $input = $this->validateInput([
                'farmer_id' => 'int'
            ]);

            $result = $this->userModel->updateFarmerStatus(
                $input['farmer_id'],
                'active',
                $_SESSION['admin_id']
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

    public function suspendUser(): void
    {
        try {
            // $this->validateAuthenticatedRequest();
            // $this->validateAdminRole();

            $input = $this->validateInput([
                'user_id' => 'int',
                'reason' => 'string'
            ]);

            $result = $this->userModel->updateUserStatus(
                $input['user_id'],
                'suspended',
                $input['reason'],
                $_SESSION['admin_id']
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

    public function getSystemMetrics(): void
    {
        try {
            // $this->validateAuthenticatedRequest();
            // $this->validateAdminRole();

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

    public function getUserAuditLog(): void
    {
        try {
            // $this->validateAuthenticatedRequest();
            // $this->validateAdminRole();

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

    public function getSystemLogs(): void
    {
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

    protected function validateAdminRole(): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->logger->warning('Unauthorized access attempt to admin area');
            throw new Exception('Unauthorized access to admin area', 403);
        }
    }

    public function approveFarmer(): void
    {
        try {
            $input = $this->validateInput([
                'farmer_id' => 'int'
            ]);

            if (!isset($_SESSION['user_id'])) {
                $this->setFlashMessage('Authentication required', 'error');
                $this->redirect('/admin/login');
                return;
            }

            $result = $this->userModel->updateFarmerStatus(
                $input['farmer_id'],
                'active',
                $_SESSION['user_id']
            );

            if ($result) {
                $this->setFlashMessage('Farmer approved successfully', 'success');
            } else {
                $this->setFlashMessage('Failed to approve farmer', 'error');
            }

            $this->redirect('/admin/dashboard');
        } catch (Exception $e) {
            $this->logger->error("Error approving farmer: " . $e->getMessage());
            $this->setFlashMessage('An error occurred while approving farmer', 'error');
            $this->redirect('/admin/dashboard');
        }
    }

    public function rejectFarmer(): void
    {
        try {
            $input = $this->validateInput([
                'farmer_id' => 'int',
                'reason' => 'string'
            ]);

            $result = $this->userModel->updateFarmerStatus(
                $input['farmer_id'],
                'rejected',
                $_SESSION['user_id'],
                $input['reason']
            );

            if ($result) {
                $this->setFlashMessage('Farmer application rejected', 'success');
            } else {
                $this->setFlashMessage('Failed to reject farmer application', 'error');
            }

            $this->redirect('/admin/farmers');
        } catch (Exception $e) {
            $this->logger->error("Error rejecting farmer: " . $e->getMessage());
            $this->setFlashMessage('An error occurred while rejecting farmer', 'error');
            $this->redirect('/admin/farmers');
        }
    }

    public function suspendFarmer(): void
    {
        try {
            $input = $this->validateInput([
                'farmer_id' => 'int',
                'reason' => 'string',
                'duration' => 'string'
            ]);

            if (!isset($_SESSION['user_id'])) {
                $this->setFlashMessage('Authentication required', 'error');
                $this->redirect('/admin/login');
                return;
            }

            $result = $this->userModel->updateFarmerStatus(
                $input['farmer_id'],
                'suspended',
                $_SESSION['user_id'],
                $input['reason'],
                $input['duration']
            );

            if ($result) {
                $this->setFlashMessage('Farmer has been suspended', 'success');
            } else {
                $this->setFlashMessage('Failed to suspend farmer', 'error');
            }

            // Redirect back to the previous page or farmers list
            $redirect = $_SERVER['HTTP_REFERER'] ?? '/admin/farmers';
            $this->redirect($redirect);
        } catch (Exception $e) {
            $this->logger->error("Error suspending farmer: " . $e->getMessage());
            $this->setFlashMessage('An error occurred while suspending farmer', 'error');
            $this->redirect('/admin/farmers');
        }
    }

    public function viewFarmer(): void
    {
        try {
            $farmerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

            if (!$farmerId) {
                $this->setFlashMessage('Invalid farmer ID', 'error');
                $this->redirect('/admin/farmers');
                return;
            }

            $farmer = $this->userModel->getFarmerDetails($farmerId);

            if (!$farmer) {
                $this->setFlashMessage('Farmer not found', 'error');
                $this->redirect('/admin/farmers');
                return;
            }

            // Get status history
            $statusHistory = $this->userModel->getFarmerStatusHistory($farmerId);

            // Get current suspension if exists
            $currentSuspension = $this->userModel->getCurrentSuspension($farmerId);

            // Get rejection details if rejected
            $rejectionDetails = null;
            if ($farmer['status'] === 'rejected') {
                $rejectionDetails = $this->userModel->getRejectionDetails($farmerId);
            }

            $this->render('admin/farmer-details', [
                'farmer' => $farmer,
                'statusHistory' => $statusHistory,
                'currentSuspension' => $currentSuspension,
                'rejectionDetails' => $rejectionDetails,
                'pageTitle' => 'Farmer Details - ' . $farmer['name']
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error viewing farmer: " . $e->getMessage());
            $this->setFlashMessage('Error loading farmer details', 'error');
            $this->redirect('/admin/farmers');
        }
    }

    public function getFarmerDetails(): void
    {
        try {
            // Get farmer ID from URL
            $farmerId = $this->getRouteParam('id');

            if (!$farmerId) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Farmer ID is required'
                ], 400);
                return;
            }

            // Validate admin access
            if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
                return;
            }

            // Get farmer details
            $farmer = $this->userModel->getFarmerDetails($farmerId);

            if (!$farmer) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Farmer not found'
                ], 404);
                return;
            }

            // Get additional details
            $stats = [
                'total_products' => $farmer['product_count'] ?? 0,
                'total_orders' => $farmer['order_count'] ?? 0,
                'total_revenue' => $farmer['total_revenue'] ?? 0
            ];

            $statusHistory = $this->userModel->getFarmerStatusHistory($farmerId);
            $currentSuspension = $this->userModel->getCurrentSuspension($farmerId);

            $this->jsonResponse([
                'success' => true,
                'farmer' => $farmer,
                'stats' => $stats,
                'statusHistory' => $statusHistory,
                'currentSuspension' => $currentSuspension
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error getting farmer details: " . $e->getMessage(), [
                'farmer_id' => $farmerId ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to load farmer details'
            ], 500);
        }
    }

    protected function getRouteParam(string $name): ?string
    {
        $uri = $_SERVER['REQUEST_URI'];
        $pattern = "#/admin/api/farmer/(\d+)#";

        if (preg_match($pattern, $uri, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
