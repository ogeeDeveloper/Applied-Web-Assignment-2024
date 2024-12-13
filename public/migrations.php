<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\DatabaseInitializer;
use App\Database\Migrations\MigrationManager;

if (PHP_SAPI !== 'cli') {
    exit('This script can only be run from the command line.');
}

try {
    // Get database name from environment
    $dbName = getenv('MYSQL_DATABASE');
    if (!$dbName) {
        throw new RuntimeException('MYSQL_DATABASE environment variable is not set');
    }

    // Initialize database
    $dbInitializer = new DatabaseInitializer($dbName);
    $dbInitializer->initialize();

    // Get migration connection
    $db = $dbInitializer->createMigrationConnection();

    // Get the absolute path to migrations
    $projectRoot = dirname(__DIR__);
    $migrationsPath = $projectRoot . '/mysql/migrations';

    // Ensure migrations directory exists
    if (!is_dir($migrationsPath)) {
        throw new RuntimeException("Migrations directory not found at: " . $migrationsPath);
    }

    // Initialize migration manager
    $migrationManager = new MigrationManager($db, $migrationsPath);

    // Get command from arguments
    $command = $argv[1] ?? 'help';

    switch ($command) {
        case 'cleanup':
            echo "Cleaning up database...\n";
            $migrationManager->cleanup();
            break;

        case 'status':
            $migrationManager->getMigrationStatus();
            break;

        case 'migrate':
            echo "Starting migrations...\n";
            $migrationManager->migrate();
            echo "Migrations completed successfully!\n";
            break;

        case 'help':
        default:
            echo <<<HELP
Migration Command Help
---------------------
Available commands:

migrate status    Show migration status
migrate migrate   Run pending migrations
migrate cleanup   Clean up database (drops all tables)

HELP;
            break;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
