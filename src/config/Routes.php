<?php

return [
    'public' => [
        'GET /' => ['HomeController', 'index'],
        'GET /products' => ['ProductController', 'listProducts'],
        'GET /about' => ['HomeController', 'about'],
        'GET /contact' => ['HomeController', 'contact'],
    ],
    'auth' => [
        'GET /login' => ['AuthController', 'loginForm'],
        'POST /login' => ['AuthController', 'login'],
        'GET /register' => ['AuthController', 'customerRegistrationForm'],
        'POST /register' => ['AuthController', 'register'],
        'POST /logout' => ['AuthController', 'logout'],
    ],
    'admin' => [
        'GET /admin/login' => ['AdminAuthController', 'showLoginForm'],
        'POST /admin/login' => ['AdminAuthController', 'login'],
        'GET /admin/dashboard' => ['AdminController', 'dashboard'],
        'POST /admin/farmers/approve' => ['AdminController', 'approveFarmer'],
    ],
    'protected' => [
        'GET /customer/dashboard' => ['CustomerController', 'dashboard'],
        'GET /farmer/dashboard' => ['FarmerController', 'dashboard'],
    ]
];
