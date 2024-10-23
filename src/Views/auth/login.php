<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
use App\Controllers\AuthController;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $authController = new AuthController();

    // Retrieve the form data
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!$email || !$password) {
        die("Invalid input data.");
    }


    // Pass the data to the login method
    $authController->login($email, $password);
}
 
$pageTitle = "Login";
require_once __DIR__ . '/../shared/header.php';


?>
 
<div class="container mt-5">
<h2>Login</h2>
<form method="POST" action="/login">
<div class="mb-3">
<label for="email" class="form-label">Email address</label>
<input type="email" class="form-control" id="email" name="email" required>
</div>
<div class="mb-3">
<label for="password" class="form-label">Password</label>
<input type="password" class="form-control" id="password" name="password" required>
</div>
<button type="submit" class="btn btn-primary">Login</button>
</form>
</div>
 
<?php require_once __DIR__ . '/../shared/footer.php'; ?>
