#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Simple development server for Syntra Web Interface
 *
 * This script starts PHP's built-in web server for development purposes.
 * For production, use a proper web server like Apache or Nginx.
 */

$host = $argv[1] ?? 'localhost';
$port = (int)($argv[2] ?? 8000);
$webRoot = __DIR__;

echo "Starting Syntra Web Interface development server...\n";
echo "Host: http://$host:$port\n";
echo "Web root: $webRoot\n";
echo "Press Ctrl+C to stop the server.\n\n";

// Start the built-in PHP server
$command = sprintf(
    'php -S %s:%d -t %s',
    escapeshellarg($host),
    $port,
    escapeshellarg($webRoot)
);

passthru($command);
