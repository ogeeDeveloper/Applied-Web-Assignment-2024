<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Database\Migrations\MigrationManager;

// Wait until MySQL is ready
$maxAttempts = 10;
$attempt = 0;
$connected = false;

while ($attempt < $maxAttempts) {
    try {
        // Initialize database connection
        $database = new Database();
        $db = $database->getConnection();
        $connected = true;
        break;
    } catch (Exception $e) {
        echo "Waiting for MySQL to be ready...\n";
        sleep(5);
        $attempt++;
    }
}

if (!$connected) {
    echo "Failed to connect to MySQL after multiple attempts.\n";
    exit(1);
}

try {
    // Initialize migration manager
    $migrationsPath = __DIR__ . '/../mysql/migrations';
    $migrationManager = new MigrationManager($db, $migrationsPath);

    // Run migrations
    echo "Starting migrations...\n";
    $migrationManager->migrate();
    echo "Migrations completed successfully!\n";
} catch (Exception $e) {
    echo "Error running migrations: " . $e->getMessage() . "\n";
    exit(1);
}
