<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use App\Config\Database;
use App\Database\Migrations\MigrationManager;

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();

    // Initialize migration manager with correct path
    $migrationsPath = __DIR__ . '/../docker/mysql/migrations';
    $migrationManager = new MigrationManager($db, $migrationsPath);

    // Run migrations
    echo "Starting migrations...\n";
    $migrationManager->migrate();
    echo "Migrations completed successfully!\n";

} catch (Exception $e) {
    echo "Error running migrations: " . $e->getMessage() . "\n";
    exit(1);
}