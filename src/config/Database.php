<?php

namespace App\config;

use PDO;
use PDOException;

class Database
{
    private string $host;
    private string $db_name;
    private string $username;
    private string $password;
    private ?PDO $conn = null;

    public function __construct()
    {
        $this->host = $_ENV['MYSQL_HOST'] ?? 'mysql';
        $this->db_name = $_ENV['MYSQL_DATABASE'] ?? 'farmer_db';
        $this->username = $_ENV['MYSQL_USER'] ?? 'farmer_user';
        $this->password = $_ENV['MYSQL_PASSWORD'] ?? '';

        if (!$this->host) {
            throw new \RuntimeException('MYSQL_HOST is not set');
        }
        if (!$this->db_name) {
            throw new \RuntimeException('MYSQL_DATABASE is not set');
        }
        if (!$this->username) {
            throw new \RuntimeException('MYSQL_USER is not set');
        }
        if (!$this->password) {
            throw new \RuntimeException('MYSQL_PASSWORD is not set');
        }
    }

    public function getConnection(): PDO
    {
        if ($this->conn === null) {
            try {
                // Test if we can resolve the hostname
                if (gethostbyname($this->host) === $this->host) {
                    throw new \RuntimeException("Cannot resolve hostname: {$this->host}");
                }

                $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
                
                // Add connection timeout
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 5,
                ];

                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            } catch (PDOException $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                throw new \RuntimeException(
                    sprintf(
                        'Database connection failed: %s. Details: host=%s, database=%s, user=%s',
                        $e->getMessage(),
                        $this->host,
                        $this->db_name,
                        $this->username
                    )
                );
            }
        }

        return $this->conn;
    }
}