<?php
/**
 * Main layout file
 * Variables available:
 * - $pageTitle: Title of the page
 * - $content: Main content to render
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="icon" type="image/png" href="images/favicon/favicon-16x16.png" />
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/nouislider.min.css">
    <link rel="stylesheet" href="/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="/css/venobox.css">
    <link rel="stylesheet" href="lib/css/swiper-bundle.min.css" />
    <link rel="stylesheet" href="/lib/css/bvselect.css" />
</head>
<body>
    <?php require_once APP_ROOT . '/src/Views/shared/header.php'; ?>
    
    <main>
        <?php echo $content; ?>
    </main>

    <?php require_once APP_ROOT . '/src/Views/shared/footer.php'; ?>

    <!-- Scripts -->
    <script src="/lib/js/jquery.min.js"></script>
    <script src="/lib/js/bootstrap.bundle.min.js"></script>
    <script src="/lib/js/swiper-bundle.min.js"></script>
    <script src="/lib/js/main.js"></script>
</body>
</html>