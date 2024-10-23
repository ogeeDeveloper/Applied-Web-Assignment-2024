<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo isset($pageTitle) ? $pageTitle . ' - AgriKonnect' : 'AgriKonnect'; ?></title>
        <link rel="stylesheet" href="/css/bootstrap.min.css">
        <link rel="stylesheet" href="/css/style.css">
    </head>
    <body>
        <header class="p-3 bg-dark text-white">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="/index.php" class="navbar-brand text-white">AgriKonnect</a>
                <nav>
                    <a href="/signup" class="text-white me-3">Create Account</a>
                    <a href="/login" class="text-white">Login</a>
                </nav>
            </div>
        </div>
</header>