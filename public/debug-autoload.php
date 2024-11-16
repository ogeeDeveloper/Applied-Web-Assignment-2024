<?php

// Show current include path
echo "Include path: " . get_include_path() . "\n";

// Show current directory
echo "Current directory: " . __DIR__ . "\n";

// Try to locate the file
$helperPaths = [
    __DIR__ . '/../src/utils/helpers.php',
    __DIR__ . '/../src/utils/functions.php',
    '/var/www/src/utils/helpers.php',
    '/var/www/src/utils/functions.php'
];

foreach ($helperPaths as $path) {
    echo "Checking path: $path\n";
    echo "File exists: " . (file_exists($path) ? "Yes" : "No") . "\n";
}

// Show composer autoload files
$autoloadFile = __DIR__ . '/../vendor/composer/autoload_files.php';
if (file_exists($autoloadFile)) {
    echo "\nAutoloaded files:\n";
    print_r(require $autoloadFile);
}
