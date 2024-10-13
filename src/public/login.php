<?php
require_once '../Controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $auth = new AuthController();
    $auth->login($email, $password);
}

// Include the view for the login form
include 'views/login.view.php';
