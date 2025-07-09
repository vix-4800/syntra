<?php

declare(strict_types=1);

use Symfony\Component\Process\ExecutableFinder;
use Vix\Syntra\Utils\ProjectInfo;

if (!function_exists('find_in_vendor')) {
    /**
     * Recursively search for a path inside the vendor directory up the
     * directory tree.
     *
     * @param callable $check Callback that validates the found path
     */
    function find_in_vendor(string $startDir, string $relativePath, callable $check): ?string
    {
        $dir = $startDir;

        do {
            $path = $dir . '/vendor/' . ltrim($relativePath, '/');
            if ($check($path)) {
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
}

if (!function_exists('config_path')) {
    /**
     * Get path inside config directory
     */
    function config_path(string $file = ''): string
    {
        static $root = null;
        if ($root === null) {
            $root = (new ProjectInfo())->getRootPath();
        }

        $candidate = rtrim($root, '/').'/config';
        if ($file !== '') {
            $candidate .= '/' . ltrim($file, '/');
        }

        if ($file !== '' && is_readable($candidate)) {
            return $candidate;
        }

        if ($file === '' && is_dir($candidate)) {
            return $candidate;
        }

        return $file === '' ? CONFIG_DIR : CONFIG_DIR . '/' . ltrim($file, '/');
    }
}

if (!function_exists('find_composer_bin')) {
    /**
     * Find composer binary. Searches the vendor directories upwards first and
     * falls back to the system PATH using Symfony's ExecutableFinder.
     */
    function find_composer_bin(string $binary, string $startDir): ?string
    {
        $path = find_in_vendor($startDir, "bin/$binary", 'is_executable');

        if ($path !== null) {
            return $path;
        }

        if (class_exists(ExecutableFinder::class)) {
            return (new ExecutableFinder())->find($binary);
        }

        return null;
    }
}
