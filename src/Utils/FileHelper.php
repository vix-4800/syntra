<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class FileHelper
{
    /**
     * @param string[] $extensions
     * @param string[] $excludeDirs
     *
     * @return string[]
     */
    public function collectFiles(string $dir, array $extensions = ['php'], array $excludeDirs = ['vendor', 'tests']): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            foreach ($excludeDirs as $dir) {
                if (strpos($file->getPathname(), DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR) !== false) {
                    continue 2;
                }
            }

            if (in_array($file->getExtension(), $extensions, true)) {
                $files[] = $file->getPathname();
            }
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
        }
    }
}
