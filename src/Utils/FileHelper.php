<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class FileHelper
{
    /**
     * @var array<string, string[]>
     */
    private static array $filesCache = [];

    private static bool $cacheEnabled = true;

    /**
     * @var string[]
     */
    private static array $changedFiles = [];

    /**
     * Clears the internal files cache.
     */
    public static function clearCache(): void
    {
        self::$filesCache = [];
    }

/**
 * Set cache enabled.
 */
    public static function setCacheEnabled(bool $enabled): void
    {
        self::$cacheEnabled = $enabled;

        if (!$enabled) {
            self::$filesCache = [];
        }
    }

    /**
     * Returns the list of files changed by writeChanges().
     *
     * @return string[]
     */
    public static function getChangedFiles(): array
    {
        return self::$changedFiles;
    }

    /**
     * Clears the list of changed files.
     */
    public static function clearChangedFiles(): void
    {
        self::$changedFiles = [];
    }

    /**
     * @param string[] $extensions
     * @param string[] $excludeDirs
     *
     * @return string[]
     */
    public function collectFiles(string $dir, array $extensions = ['php'], array $excludeDirs = ['vendor', 'tests']): array
    {
        $cacheKey = md5($dir . '|' . implode(',', $extensions) . '|' . implode(',', $excludeDirs));
        if (self::$cacheEnabled && isset(self::$filesCache[$cacheKey])) {
            return self::$filesCache[$cacheKey];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            foreach ($excludeDirs as $exclude) {
                if (str_contains($file->getPathname(), DIRECTORY_SEPARATOR . $exclude . DIRECTORY_SEPARATOR)) {
                    continue 2;
                }
            }

            if (in_array($file->getExtension(), $extensions, true)) {
                $files[] = $file->getPathname();
            }
        }

        if (self::$cacheEnabled) {
            self::$filesCache[$cacheKey] = $files;
        }

        return $files;
    }

    /**
     * Writes changes to a file if they differ from the original.
     */
    public function writeChanges(string $filePath, string $oldContent, string $newContent): void
    {
        if ($newContent !== $oldContent) {
            file_put_contents($filePath, $newContent);
            self::$changedFiles[] = $filePath;
        }
    }

/**
 * Resolve a class instance.relative.
 */
    public function makeRelative(string $path, string $root): string
    {
        $root = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return str_starts_with($path, $root)
            ? substr($path, strlen($root))
            : $path;
    }
}
