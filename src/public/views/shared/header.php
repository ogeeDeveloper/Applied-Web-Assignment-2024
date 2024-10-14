<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? $pageTitle : 'Shopery'; ?></title>
<link rel="stylesheet" href="/public/lib/css/bootstrap.min.css">
<link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
<header class="p-3 bg-dark text-white">
<div class="container">
<div class="d-flex justify-content-between align-items-center">
<a href="/public/views/home.php" class="navbar-brand text-white">Shopery</a>
<nav>
<a href="/public/views/create_account.php" class="text-white me-3">Create Account</a>
<a href="/public/views/login.php" class="text-white">Login</a>
</nav>
</div>
</div>
</header>