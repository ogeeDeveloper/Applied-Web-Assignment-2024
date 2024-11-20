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
        // Add admin auth routes here
        'GET /admin/login' => ['AdminAuthController', 'showLoginForm'],
        'POST /admin/login' => ['AdminAuthController', 'login'],
        'GET /admin/forgot-password' => ['AdminAuthController', 'showForgotPasswordForm'],
        'POST /admin/forgot-password' => ['AdminAuthController', 'forgotPassword'],
        'POST /admin/logout' => ['AdminAuthController', 'logout'],
    ],
    'admin' => [
        // Only protected admin routes
        'GET /admin/dashboard' => ['AdminController', 'dashboard'],
        'POST /admin/farmers/approve' => ['AdminController', 'approveFarmer'],
    ],
    'protected' => [
        'GET /customer/dashboard' => ['CustomerController', 'dashboard'],
        'GET /farmer/dashboard' => ['FarmerController', 'dashboard'],
    ]
];
