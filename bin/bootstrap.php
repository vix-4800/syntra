<?php

declare(strict_types=1);

define('PACKAGE_ROOT', dirname(__DIR__));

/**
 * Find composer autoloader
 */
function find_composer_autoload(string $startDir): ?string
{
    $dir = $startDir;

    do {
        $autoload = "$dir/vendor/autoload.php";
        if (is_file($autoload)) {
            return $autoload;
        }

        $parent = dirname($dir);
        if ($dir === $parent) {
            break;
        }

        $dir = $parent;
    } while (true);

    return null;
}

/**
 * Find composer binary
 */
function find_composer_bin(string $binary, string $startDir): ?string
{
    $dir = $startDir;

    do {
        $path = "$dir/vendor/bin/$binary";
        if (is_executable($path)) {
            return $path;
        }

        $parent = dirname($dir);
        if ($dir === $parent) {
            break;
        }

        $dir = $parent;
    } while (true);

    return null;
}
