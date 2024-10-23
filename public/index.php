<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Get the request URI
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Define basic routing logic
switch ($request_uri) {
    case '/':
    case '/index.php':
        require __DIR__ . '/../src/Views/home.view.php';
        break;

    case '/login':
    case '/login.php':
        require __DIR__ . '/../src/Views/auth/login.php';
        break;

    case '/signup':
    case '/signup.php':
        require __DIR__ . '/../src/Views/auth/signup.php';
        break;

    case '/logout':
    case '/logout.php':
        require __DIR__ . '/../src/Views/logout.php';
        break;

    default:
        http_response_code(404);
        echo '404 Not Found';
        break;
}
