<?php

namespace App\Controllers;

use Exception;
use App\Constants\Roles;

class BaseController
{
    protected $db;
    protected $logger;

    public function __construct($db = null, $logger = null)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    protected function render($view, $data = [], $pageTitle = null, $layout = 'layouts/main')
    {
        // Extract data to make it available in view
        if (!empty($data)) {
            extract($data);
        }

        // Start output buffering
        ob_start();

        // Include the view file
        $viewPath = APP_ROOT . "/src/Views/{$view}.php";
        if (!file_exists($viewPath)) {
            throw new Exception("View file not found: {$viewPath}");
        }
        require_once $viewPath;

        // Get the buffered content
        $content = ob_get_clean();

        // Set page title
        $pageTitle = $pageTitle ?? 'AgriKonnect';

        // Get current page for sidebar active state
        $currentPage = basename($view);

        // Include the layout with the content
        $layoutPath = APP_ROOT . "/src/Views/{$layout}.php";
        if (!file_exists($layoutPath)) {
            throw new Exception("Layout file not found: {$layoutPath}");
        }
        require_once $layoutPath;
    }


    protected function requireAdmin()
    {
        if (!isset($_SESSION['admin_id'])) {
            $this->logger->warning('Unauthorized access attempt to admin area');
            $this->redirect('/admin/login', 'Please login to access the admin area', 'error');
        }
    }

    /**
     * Get the appropriate redirect URL based on user role
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_role'])) {
            return '/login';
        }

        return match ($_SESSION['user_role']) {
            'admin' => '/admin/dashboard',
            'farmer' => '/farmer/dashboard',
            'customer' => '/customer/dashboard',
            default => '/login'
        };
    }

    /**
     * Validate that the request is from an authenticated user
     * @throws Exception if user is not authenticated
     */
    protected function validateAuthenticatedRequest(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            $this->logger->warning('Unauthorized access attempt');
            $this->redirect('/login', null, 'error');
            exit;
        }
    }

    /**
     * Validate user role
     * @param string $requiredRole Required role for access
     * @throws Exception if role validation fails
     */
    protected function validateRole(string $requiredRole): void
    {
        $currentRole = strtolower($_SESSION['user_role'] ?? 'none');
        $requiredRole = strtolower($requiredRole);

        if ($currentRole !== $requiredRole) {
            $this->logger->warning("Role mismatch", [
                'required_role' => $requiredRole,
                'current_role' => $currentRole,
                'session' => $_SESSION
            ]);
            throw new Exception('Unauthorized role access', 403);
        }
    }

    protected function getLoginUrlForRole(string $role): string
    {
        return match ($role) {
            Roles::ADMIN => '/admin/login',
            Roles::FARMER => '/farmer/login',
            Roles::CUSTOMER => '/login',
            default => '/login',
        };
    }

    /**
     * Validate multiple roles
     * @param array $allowedRoles Array of allowed roles
     * @throws Exception if role validation fails
     */
    protected function validateRoles(array $allowedRoles): void
    {
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowedRoles)) {
            $this->logger->warning("Unauthorized roles access attempt: {$_SESSION['user_role']}");
            throw new Exception('Unauthorized role access', 403);
        }
    }

    /**
     * Check if user has specific role
     * @param string $role Role to check
     * @return bool
     */
    protected function hasRole(string $role): bool
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    /**
     * Send a JSON response
     * @param array $data The data to send
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Handle file upload
     * @param array $file $_FILES array element
     * @param array $allowedTypes Allowed MIME types
     * @param string $uploadDir Upload directory
     * @return array Upload result
     * @throws Exception on upload error
     */
    protected function handleFileUpload(array $file, array $allowedTypes, string $uploadDir): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Upload error: " . $file['error']);
        }

        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("Invalid file type");
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = uniqid() . '_' . basename($file['name']);
        $filePath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception("Failed to move uploaded file");
        }

        return [
            'fileName' => $fileName,
            'filePath' => $filePath,
            'fileType' => $fileType
        ];
    }

    /**
     * Sanitize string input
     * @param string|null $input
     * @return string|null
     */
    protected function sanitizeString(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }
        return strip_tags(trim($input));
    }

    /**
     * Validate and sanitize request input
     * @param array $rules Validation rules
     * @return array Sanitized data
     * @throws Exception on validation failure
     */
    protected function validateInput(array $rules): array
    {
        $sanitizedData = [];
        foreach ($rules as $field => $rule) {
            switch ($rule) {
                case 'int':
                    $value = filter_input(INPUT_POST, $field, FILTER_VALIDATE_INT);
                    if ($value === false || $value === null) {
                        throw new Exception("Invalid value for field: {$field}");
                    }
                    $sanitizedData[$field] = $value;
                    break;

                case 'float':
                    $value = filter_input(INPUT_POST, $field, FILTER_VALIDATE_FLOAT);
                    if ($value === false || $value === null) {
                        throw new Exception("Invalid float value for field: {$field}");
                    }
                    $sanitizedData[$field] = $value;
                    break;

                case 'string':
                    $value = filter_input(INPUT_POST, $field, FILTER_UNSAFE_RAW);
                    $sanitizedData[$field] = $this->sanitizeString($value);
                    break;

                case 'email':
                    $value = filter_input(INPUT_POST, $field, FILTER_VALIDATE_EMAIL);
                    if ($value === false || $value === null) {
                        throw new Exception("Invalid email for field: {$field}");
                    }
                    $sanitizedData[$field] = $value;
                    break;

                case 'boolean':
                    $value = filter_input(INPUT_POST, $field, FILTER_VALIDATE_BOOLEAN);
                    $sanitizedData[$field] = $value;
                    break;

                case 'date':
                    $value = filter_input(INPUT_POST, $field, FILTER_UNSAFE_RAW);
                    if (!strtotime($value)) {
                        throw new Exception("Invalid date for field: {$field}");
                    }
                    $sanitizedData[$field] = date('Y-m-d', strtotime($value));
                    break;

                case 'json':
                    $value = filter_input(INPUT_POST, $field, FILTER_UNSAFE_RAW);
                    $decoded = json_decode($value, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception("Invalid JSON for field: {$field}");
                    }
                    $sanitizedData[$field] = $decoded;
                    break;
            }
        }
        return $sanitizedData;
    }

    /**
     * Get pagination data
     * @param int $total Total number of items
     * @param int $page Current page
     * @param int $limit Items per page
     * @return array Pagination data
     */
    protected function getPaginationData(int $total, int $page = 1, int $limit = 10): array
    {
        $totalPages = ceil($total / $limit);
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $limit;

        return [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'limit' => $limit,
            'offset' => $offset,
            'total' => $total,
            'hasNextPage' => $page < $totalPages,
            'hasPrevPage' => $page > 1
        ];
    }

    /**
     * Sets a flash message to be displayed on the next request
     *
     * @param string $message The message to display
     * @param string $type Message type ('success', 'error', 'warning', 'info')
     * @param array $extraData Optional additional data to store
     * @return void
     */
    protected function setFlashMessage(string $message, string $type = 'success', array $extraData = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
            'data' => $extraData,
            'timestamp' => time()
        ];
    }

    /**
     * Gets and clears flash messages
     *
     * @return array|null The flash message data or null if none exists
     */
    protected function getFlashMessages(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    /**
     * Redirects to a URL with optional flash message
     *
     * @param string $url URL to redirect to
     * @param string|null $message Optional flash message
     * @param string $type Message type ('success', 'error', 'warning', 'info')
     * @param array $extraData Optional additional data to store
     * @return void
     */
    protected function redirect(string $url, ?string $message = null, string $type = 'success'): void
    {
        error_log("[REDIRECT]: Redirecting to {$url}");
        if (!headers_sent()) {
            header("Location: {$url}");
            exit;
        }

        if ($message) {
            $this->setFlashMessage($message, $type);
        }

        // Prevent redirect loops
        static $redirectCount = 0;
        $redirectCount++;

        if ($redirectCount > 3) {
            // Log potential infinite redirect
            $this->logger->error("Potential redirect loop detected", [
                'url' => $url,
                'session' => $_SESSION ?? [],
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
            ]);
            http_response_code(500);
            die('Error: Too many redirects');
        }

        if (!headers_sent()) {
            header("Location: $url");
        } else {
            echo '<script>window.location.href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '";</script>';
        }
        exit;
    }

    /**
     * Redirects back to the previous page
     *
     * @param string|null $message Optional flash message
     * @param string $type Message type ('success', 'error', 'warning', 'info')
     * @param array $extraData Optional additional data
     * @param string $defaultUrl Default URL if no referrer is found
     * @return void
     */
    protected function redirectBack(
        ?string $message = null,
        string $type = 'success',
        array $extraData = [],
        string $defaultUrl = '/'
    ): void {
        $previousUrl = $_SERVER['HTTP_REFERER'] ?? $defaultUrl;
        $this->redirect($previousUrl, $message, $type, $extraData);
    }

    /**
     * Redirects with input data preserved
     *
     * @param string $url URL to redirect to
     * @param array $input Input data to preserve
     * @param string|null $message Optional flash message
     * @param string $type Message type
     * @return void
     */
    protected function redirectWithInput(
        string $url,
        array $input,
        ?string $message = null,
        string $type = 'error'
    ): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['old_input'] = $input;
        $this->redirect($url, $message, $type);
    }

    /**
     * Gets old input data
     *
     * @param string|null $key Specific input key to retrieve
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Old input data or default value
     */
    protected function old(?string $key = null, $default = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $oldInput = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);

        if ($key === null) {
            return $oldInput;
        }

        return $oldInput[$key] ?? $default;
    }

    /**
     * Validate admin role
     * @throws Exception if role validation fails
     */
    protected function validateAdminRole(): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->logger->warning('Unauthorized access attempt to admin area');
            // Instead of throwing exception, redirect to unauthorized page
            header('Location: /unauthorized');
            exit;
        }
    }

    protected function getRedirectUrlForRole(string $role): string
    {
        return match ($role) {
            Roles::ADMIN => '/admin/dashboard',
            Roles::FARMER => '/farmer/dashboard',
            Roles::CUSTOMER => '/customer/dashboard',
            default => '/login',
        };
    }
}
