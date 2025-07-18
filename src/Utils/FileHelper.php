<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Vix\Syntra\Exceptions\DirectoryNotFoundException;
use Vix\Syntra\Facades\Cache;

class FileHelper
{
    /**
     * @var string[]
     */
    private array $changedFiles = [];

    /**
     * Returns the list of files changed by writeChanges().
     *
     * @return string[]
     */
    public function getChangedFiles(): array
    {
        return $this->changedFiles;
    }

    /**
     * Clears the list of changed files.
     */
    public function clearChangedFiles(): void
    {
        $this->changedFiles = [];
    }

    /**
     * @param string[] $extensions
     * @param string[] $excludeDirs
     *
     * @return string[]
     */
    public function collectFiles(string $path, array $extensions = ['php'], array $excludeDirs = ['vendor', 'tests']): array
    {
        if (is_file($path)) {
            $ext = pathinfo($path, PATHINFO_EXTENSION);

            return in_array($ext, $extensions, true) ? [$path] : [];
        }

        if (!is_dir($path)) {
            throw new DirectoryNotFoundException($path);
        }

        $cacheKey = md5($path . '|' . implode(',', $extensions) . '|' . implode(',', $excludeDirs));
        if (Cache::has($cacheKey)) {
            /** @var string[] */
            return Cache::get($cacheKey, []);
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

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

        Cache::set($cacheKey, $files);

        return $files;
    }

    /**
     * Writes changes to a file if they differ from the original.
     */
    public function writeChanges(string $filePath, string $oldContent, string $newContent): void
    {
        if ($newContent !== $oldContent) {
            file_put_contents($filePath, $newContent);
            $this->changedFiles[] = $filePath;
        }
    }

    public function makeRelative(string $path, string $root): string
    {
        if (!is_dir($root)) {
            $root = dirname($root);
        }

        $root = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return str_starts_with($path, $root)
            ? substr($path, strlen($root))
            : $path;
    }
}
