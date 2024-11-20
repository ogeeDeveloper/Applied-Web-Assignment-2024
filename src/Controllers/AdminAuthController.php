<?php

namespace App\Controllers;

use App\Models\User;
use PDO;
use PDOException;
use App\Constants\Roles;
use App\Utils\SessionManager;
use Exception;

class AdminAuthController extends BaseController
{
    private User $userModel;
    private string $adminLayout = 'admin/layouts/admin';

    public function __construct(PDO $db, $logger)
    {
        parent::__construct($db, $logger);
        $this->userModel = new User($db, $logger);
        SessionManager::initialize();
    }

    /**
     * Show admin login form
     */
    public function showLoginForm(): void
    {
        // Clear any existing flash messages
        if (isset($_SESSION['flash'])) {
            unset($_SESSION['flash']);
        }

        // If already authenticated as admin and trying to access login page, redirect to dashboard
        if ($this->isAdminAuthenticated()) {
            $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if ($currentPath === '/admin/login') {
                $this->redirect('/admin/dashboard');
                return;
            }
        }

        // Show login form
        $this->render('admin/login', [
            'pageTitle' => 'Admin Login - AgriKonnect'
        ], 'layouts/auth'); // Use a simple layout for auth pages
    }

    /**
     * Handle admin login
     */
    public function login(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->redirect('/admin/login');
                return;
            }

            // Validate input
            $input = $this->validateInput([
                'email' => 'email',
                'password' => 'string'
            ]);

            $user = $this->userModel->findByEmail($input['email']);

            // Log authentication attempt
            $this->logger->info('Admin login attempt', [
                'email' => $input['email'],
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);

            if (!$user || !password_verify($input['password'], $user['password'])) {
                $this->setFlashMessage('Invalid credentials', 'error');
                $this->redirect('/admin/login');
                return;
            }

            if ($user['role'] !== Roles::ADMIN) {
                $this->setFlashMessage('Unauthorized access', 'error');
                $this->redirect('/admin/login');
                return;
            }

            // Clear any existing session
            SessionManager::destroy();
            SessionManager::initialize();

            // Start new session with regenerated ID
            SessionManager::regenerate();

            // Set admin session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = Roles::ADMIN;
            $_SESSION['is_authenticated'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['last_activity'] = time();

            // Update last login timestamp
            $this->userModel->updateLastLogin($user['id']);

            // Log successful login
            $this->logger->info('Admin login successful', [
                'user_id' => $user['id'],
                'email' => $user['email']
            ]);

            $this->redirect('/admin/dashboard', 'Welcome back!', 'success');
        } catch (Exception $e) {
            $this->logger->error("Login error: " . $e->getMessage());
            $this->setFlashMessage('An error occurred during login', 'error');
            $this->redirect('/admin/login');
        }
    }


    /**
     * Handle admin logout
     */
    public function logout(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->logger->info('Admin logout', ['user_id' => $_SESSION['user_id']]);
        }

        SessionManager::destroy();
        $this->redirect('/admin/login', 'You have been logged out successfully', 'success');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm(): void
    {
        $this->render('admin/forgot-password', [
            'pageTitle' => 'Reset Admin Password - AgriKonnect'
        ]);
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(): void
    {
        try {
            $input = $this->validateInput([
                'email' => 'email'
            ]);

            $user = $this->userModel->findByEmail($input['email']);

            if (!$user || $user['role'] !== 'admin') {
                $this->setFlashMessage('If an admin account exists for this email, you will receive password reset instructions.', 'info');
                $this->redirect('/admin/forgot-password');
                return;
            }

            // Generate and store reset token
            $token = bin2hex(random_bytes(32));
            $this->userModel->storePasswordResetToken($user['id'], $token);

            // Send reset email
            // TODO: Implement email sending

            $this->setFlashMessage('Password reset instructions have been sent to your email.', 'success');
            $this->redirect('/admin/login');
        } catch (Exception $e) {
            $this->logger->error("Admin password reset error: " . $e->getMessage());
            $this->setFlashMessage('An error occurred. Please try again later.', 'error');
            $this->redirect('/admin/forgot-password');
        }
    }

    /**
     * Start admin session
     */
    private function startAdminSession(array $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear any existing session data
        $_SESSION = array();

        // Regenerate session ID
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_name'] = $user['name'];
        $_SESSION['user_role'] = 'admin';
        $_SESSION['is_authenticated'] = true;
        $_SESSION['last_activity'] = time();
    }

    /**
     * Clear admin session
     */
    private function clearAdminSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Unset all admin-related session variables
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_email']);
        unset($_SESSION['admin_name']);
        unset($_SESSION['user_role']);

        // Destroy session
        session_destroy();
    }

    /**
     * Check if user is authenticated as admin
     */
    private function isAdminAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) &&
            isset($_SESSION['user_role']) &&
            $_SESSION['user_role'] === Roles::ADMIN &&
            isset($_SESSION['is_authenticated']) &&
            $_SESSION['is_authenticated'] === true &&
            isset($_SESSION['admin_id']);
    }
}
