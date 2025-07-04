<?php

declare(strict_types=1);

define('PACKAGE_ROOT', dirname(__DIR__));

if (!file_exists(PACKAGE_ROOT . '/bin/bootstrap.php')) {
    fwrite(STDERR, "Bootstrap file missing. Reinstall package.\n");
    exit(1);
}

require_once PACKAGE_ROOT . '/bin/bootstrap.php';

$autoload = find_composer_autoload(PACKAGE_ROOT);
if ($autoload === null) {
    fwrite(STDERR, "ERROR: Composer autoloader not found.\n");
    fwrite(STDERR, "Run 'composer install' in the project root.\n");
    exit(1);
}

require_once $autoload;

use Vix\Syntra\Web\WebApplication;

try {
    $webApp = new WebApplication();
    $webApp->run();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => $e->getMessage(),
    ]);
}
