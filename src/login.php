<?php
use App\Controllers\AuthController;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $auth = new AuthController();
    $auth->login($email, $password);
}

include 'views/login.view.php';
