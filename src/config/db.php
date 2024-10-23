<?php
namespace App\Config;

use PDO;
use PDOException;

// Database configuration class
class Database {
    private $host = "mysql";
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $this->db_name = getenv('MYSQL_DATABASE');
        $this->username = getenv('MYSQL_USER');
        $this->password = getenv('MYSQL_PASSWORD');
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            error_log($exception->getMessage());
            die('Database connection could not be established.');
            // echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}