<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\Database;
use App\Database\Migrations\MigrationManager;

try {
    // Load environment variables from the .env file
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();

    // Set the correct path to migrations
    $migrationsPath = realpath(__DIR__ . '/../mysql/migrations');
    if (!$migrationsPath || !is_dir($migrationsPath)) {
        throw new RuntimeException("Migrations directory not found at: " . $migrationsPath);
    }

    // Initialize the MigrationManager
    $migrationManager = new MigrationManager($db, $migrationsPath);

    // Run migrations
    echo "Starting migrations...\n";
    $migrationManager->migrate();
    echo "Migrations completed successfully!\n";
} catch (Exception $e) {
    echo "Error running migrations: " . $e->getMessage() . "\n";
    exit(1);
}
