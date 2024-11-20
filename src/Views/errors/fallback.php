<?php
// Ensure required variables are set
$code = $code ?? 500;
$errorMessages = $errorMessages ?? [
    400 => 'Bad Request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Page Not Found',
    500 => 'Internal Server Error'
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $code ?> - <?= $errorMessages[$code] ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50">
    <div class="flex min-h-screen flex-col items-center justify-center">
        <div class="text-center">
            <h1 class="text-6xl font-bold text-red-600"><?= $code ?></h1>
            <h2 class="mt-4 text-2xl font-semibold"><?= $errorMessages[$code] ?></h2>
            <p class="mt-2 text-gray-600">
                <?php if ($code === 404): ?>
                    The page you're looking for doesn't exist or has been moved.
                <?php elseif ($code === 403): ?>
                    You don't have permission to access this resource.
                <?php else: ?>
                    Something went wrong. Please try again later.
                <?php endif; ?>
            </p>
            <a href="/" class="mt-6 inline-block rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">
                Return Home
            </a>
        </div>
    </div>
</body>

</html>