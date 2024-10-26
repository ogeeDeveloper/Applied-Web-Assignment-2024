<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Database\Migrations\MigrationManager;

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();

    // Initialize migration manager
    $migrationsPath = __DIR__ . '/../mysql/migrations';
    $migrationManager = new MigrationManager($db, $migrationsPath);

    // Run migrations
    $migrationManager->migrate();

    echo "Migrations completed successfully!\n";
} catch (Exception $e) {
    echo "Error running migrations: " . $e->getMessage() . "\n";
    exit(1);
}
