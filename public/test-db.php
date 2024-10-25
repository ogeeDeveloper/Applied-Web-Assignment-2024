<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the application root directory
define('APP_ROOT', dirname(__DIR__));

try {
    // Load autoloader
    require_once APP_ROOT . '/vendor/autoload.php';
    echo "Autoloader loaded successfully<br>";

    // Load environment variables
    $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
    $dotenv->load();
    echo "Environment variables loaded successfully<br>";

    // Print MySQL connection details (don't do this in production!)
    echo "Attempting to connect to MySQL with following details:<br>";
    echo "Host: " . ($_ENV['MYSQL_HOST'] ?? 'not set') . "<br>";
    echo "Database: " . ($_ENV['MYSQL_DATABASE'] ?? 'not set') . "<br>";
    echo "User: " . ($_ENV['MYSQL_USER'] ?? 'not set') . "<br>";

    // Try to resolve MySQL hostname
    echo "Attempting to resolve MySQL hostname...<br>";
    $ips = gethostbynamel($_ENV['MYSQL_HOST']);
    if ($ips) {
        echo "MySQL hostname resolved to: " . implode(', ', $ips) . "<br>";
    } else {
        echo "Failed to resolve MySQL hostname<br>";
    }

    // Test database connection
    echo "Testing database connection...<br>";
    $database = new \App\config\Database();
    $connection = $database->getConnection();
    echo "Database connection successful!<br>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}