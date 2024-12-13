<?php
// src/Views/errors/404.php
$pageTitle = "404 - Page Not Found";
require_once APP_ROOT . '/src/Views/shared/header.php';
?>

<div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
        <div class="mb-6">
            <h1 class="text-6xl font-bold text-red-500 mb-2">404</h1>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Page Not Found</h2>
            <p class="text-gray-600 mb-6">
                The page you're looking for doesn't exist or may have been moved.
            </p>
        </div>
        
        <div class="space-y-4">
            <a href="/" 
               class="inline-block bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600 transition-colors">
                Go to Homepage
            </a>
            
            <div class="text-sm text-gray-500 mt-4">
                <p>If you believe this is an error, please contact support.</p>
                <p class="mt-2">Error ID: <?= uniqid('404_') ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/src/Views/shared/footer.php'; ?>