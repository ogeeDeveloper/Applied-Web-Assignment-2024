<?php
namespace App\Models;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Logger {
    private $logger;

    public function __construct($channel = 'app') {
        $this->logger = new MonologLogger($channel);
        
        // Daily rotating file handler
        $handler = new RotatingFileHandler(
            __DIR__ . '/../../storage/logs/app.log',
            30, // Keep 30 days of logs
            MonologLogger::DEBUG
        );

        // Custom format for detailed logging
        $dateFormat = "Y-m-d H:i:s";
        $output = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
        $formatter = new LineFormatter($output, $dateFormat);
        $handler->setFormatter($formatter);

        $this->logger->pushHandler($handler);
    }

    public function error($message, array $context = []) {
        $context = $this->addUserContext($context);
        $this->logger->error($message, $context);
    }

    public function info($message, array $context = []) {
        $context = $this->addUserContext($context);
        $this->logger->info($message, $context);
    }

    public function warning($message, array $context = []) {
        $context = $this->addUserContext($context);
        $this->logger->warning($message, $context);
    }

    private function addUserContext(array $context = []) {
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
            $context['user_id'] = $_SESSION['user_id'];
            $context['user_role'] = $_SESSION['user_role'] ?? 'guest';
        }
        $context['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $context['request_uri'] = $_SERVER['REQUEST_URI'] ?? 'unknown';
        return $context;
    }
}