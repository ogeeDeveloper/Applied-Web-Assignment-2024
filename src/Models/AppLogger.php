<?php

namespace App\Models;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;

class AppLogger
{
    private $logger;

    public function __construct($channel = 'app')
    {
        $this->logger = new MonologLogger($channel);

        // Daily rotating file handler
        $handler = new RotatingFileHandler(
            __DIR__ . '/../../storage/logs/app.log',
            30, // Keep 30 days of logs
            Level::Debug
        );

        // Custom format for detailed logging
        $dateFormat = "Y-m-d H:i:s";
        $output = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
        $formatter = new LineFormatter($output, $dateFormat);
        $handler->setFormatter($formatter);

        $this->logger->pushHandler($handler);
    }

    public function error($message, array $context = [])
    {
        $this->logger->error($message, $this->addRequestContext($context));
    }

    public function info($message, array $context = [])
    {
        $this->logger->info($message, $this->addRequestContext($context));
    }

    public function warning($message, array $context = [])
    {
        $this->logger->warning($message, $this->addRequestContext($context));
    }

    private function addRequestContext(array $context = [])
    {
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION)) {
            $context['session'] = [
                'user_id' => $_SESSION['user_id'] ?? null,
                'user_role' => $_SESSION['user_role'] ?? 'guest',
            ];
        }

        $context['request'] = [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ];

        return $context;
    }
}
