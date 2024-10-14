<?php
require_once '../../Controllers/AuthController.php';
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $authController = new AuthController();
    $authController->login();
}
 
$pageTitle = "Login";
require_once __DIR__ . '/shared/header.php';

?>
 
<div class="container mt-5">
<h2>Login</h2>
<form method="POST" action="login.php">
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
 
<?php require_once __DIR__ . '/shared/footer.php';?>
