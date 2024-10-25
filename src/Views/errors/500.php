<?php
// src/Views/errors/500.php
$pageTitle = "500 - Server Error";
require_once APP_ROOT . '/src/Views/shared/header.php';
?>

<div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
        <div class="mb-6">
            <div class="text-6xl text-red-500 mb-4">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h1 class="text-6xl font-bold text-red-500 mb-2">500</h1>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Server Error</h2>
            <p class="text-gray-600 mb-6">
                Something went wrong on our end. We're working to fix it.
            </p>
        </div>

        <div class="space-y-4">
            <div class="space-y-2">
                <button onclick="window.location.reload()" 
                        class="inline-block bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 transition-colors">
                    Try Again
                </button>
                <p class="text-sm text-gray-500">or</p>
                <a href="/" 
                   class="inline-block bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600 transition-colors">
                    Go to Homepage
                </a>
            </div>

            <div class="mt-6 p-4 bg-gray-50 rounded-md">
                <p class="text-sm text-gray-600 mb-2">Technical Details:</p>
                <?php if (isset($error)): ?>
                    <div class="text-left text-xs text-gray-500">
                        <p>Error ID: <?= uniqid('500_') ?></p>
                        <?php if ($_ENV['APP_ENV'] === 'development'): ?>
                            <p class="mt-2">Message: <?= htmlspecialchars($error['message']) ?></p>
                            <p>File: <?= htmlspecialchars($error['file']) ?></p>
                            <p>Line: <?= htmlspecialchars($error['line']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="text-sm text-gray-500 mt-4">
                <p>If this problem persists, please contact support.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/src/Views/shared/footer.php'; ?>