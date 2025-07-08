<?php

declare(strict_types=1);

// Define the project root directory
if (!defined('PACKAGE_ROOT')) {
    define('PACKAGE_ROOT', __DIR__);
}

// Define configuration directory
if (!defined('CONFIG_DIR')) {
    define('CONFIG_DIR', PACKAGE_ROOT . '/config');
}

// Load helper functions
require_once __DIR__ . '/helpers.php';

// Locate Composer's autoloader using shared helper
$autoload = find_in_vendor(PACKAGE_ROOT, 'autoload.php', 'is_file');
if ($autoload === null) {
    fwrite(STDERR, "ERROR: Composer autoloader not found.\n");
    fwrite(STDERR, "Run 'composer install' in the project root.\n");
    exit(1);
}

require_once $autoload;
