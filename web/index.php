<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

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
