<?php

namespace App\Controllers;

use App\Models\User;
use PDO;
use PDOException;

class AdminAuthController extends BaseController
{
    private User $userModel;
    private string $adminLayout = 'admin/layouts/admin';

    public function __construct(PDO $db, $logger)
    {
        parent::__construct($db, $logger);
        $this->userModel = new User($db, $logger);
    }

    /**
     * Show admin login form
     */
    public function showLoginForm(): void
    {
        if ($this->isAdminAuthenticated()) {
            $this->redirect('/admin/dashboard');
            return;
        }

        $this->render('admin/login', [
            'pageTitle' => 'Admin Login - AgriKonnect'
        ], $this->adminLayout);
    }

    /**
     * Handle admin login
     */
    public function login(): void
    {
        try {
            $input = $this->validateInput([
                'email' => 'email',
                'password' => 'string'
            ]);

            $user = $this->userModel->findByEmail($input['email']);

            if (!$user || !password_verify($input['password'], $user['password']) || $user['role'] !== 'admin') {
                $this->setFlashMessage('Invalid credentials', 'error');
                $this->redirect('/admin/login');
                return;
            }

            // Start admin session
            $this->startAdminSession($user);

            // Log successful login
            $this->logger->info("Admin login successful: {$input['email']}");

            // Redirect to intended page or dashboard
            $redirectUrl = $_GET['redirect'] ?? '/admin/dashboard';
            $this->redirect($redirectUrl);
        } catch (Exception $e) {
            $this->logger->error("Admin login error: " . $e->getMessage());
            $this->setFlashMessage('An error occurred during login', 'error');
            $this->redirect('/admin/login');
        }
    }

    /**
     * Handle admin logout
     */
    public function logout(): void
    {
        // Clean up admin session
        $this->clearAdminSession();

        $this->setFlashMessage('You have been logged out successfully', 'success');
        $this->redirect('/admin/login');
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

        // Regenerate session ID for security
        session_regenerate_id(true);

        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_name'] = $user['name'];
        $_SESSION['user_role'] = 'admin'; // Keep this for compatibility with existing code

        // Set last login time
        $this->userModel->updateLastLogin($user['id']);
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['admin_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}
