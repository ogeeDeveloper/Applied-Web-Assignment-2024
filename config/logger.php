<?php

require_once __DIR__ . '/../vendor/autoload.php';  // Load Composer autoloader

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

function getLogger($name = 'app'): Logger {
    $logFilePath = __DIR__ . '/../storage/logs/app.log';

    // Create a log channel
    $logger = new Logger($name);
    $logger->pushHandler(new StreamHandler($logFilePath, Logger::DEBUG));

    return $logger;
}
