<?php
// src/Views/errors/403.php
$pageTitle = "403 - Unauthorized Access";
require_once APP_ROOT . '/src/Views/shared/header.php';
?>

<div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
        <div class="mb-6">
            <div class="text-6xl text-yellow-500 mb-4">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1 class="text-6xl font-bold text-yellow-500 mb-2">403</h1>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Access Denied</h2>
            <p class="text-gray-600 mb-6">
                You don't have permission to access this resource.
            </p>
        </div>

        <div class="space-y-4">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="space-y-2">
                    <a href="/login" 
                       class="inline-block bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 transition-colors">
                        Login
                    </a>
                    <p class="text-sm text-gray-500">or</p>
                    <a href="/signup" 
                       class="inline-block bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600 transition-colors">
                        Sign Up
                    </a>
                </div>
            <?php else: ?>
                <a href="/dashboard" 
                   class="inline-block bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600 transition-colors">
                    Go to Dashboard
                </a>
            <?php endif; ?>

            <div class="text-sm text-gray-500 mt-4">
                <p>If you believe this is an error, please contact support.</p>
                <p class="mt-2">Error ID: <?= uniqid('403_') ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/src/Views/shared/footer.php'; ?>