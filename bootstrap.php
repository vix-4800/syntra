<?php

declare(strict_types=1);

// Define the project root directory
if (!defined('PACKAGE_ROOT')) {
    define('PACKAGE_ROOT', __DIR__);
}

// Load helper functions
require_once __DIR__ . '/helpers.php';

// Locate Composer's autoloader
$autoload = find_composer_autoload(PACKAGE_ROOT);
if ($autoload === null) {
    fwrite(STDERR, "ERROR: Composer autoloader not found.\n");
    fwrite(STDERR, "Run 'composer install' in the project root.\n");
    exit(1);
}

require_once $autoload;
