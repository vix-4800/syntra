<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

use Vix\Syntra\Facades\File;

/**
 * Provides helper methods for analyze commands that work with project files.
 */
trait AnalyzesFilesTrait
{
    use IteratesFilesTrait;

    /**
     * Collect all PHP files within the current path.
     *
     * @return string[]
     */
    protected function collectFiles(): array
    {
        return File::collectFiles($this->path);
    }

    /**
     * Collect files and iterate over them with progress feedback.
     *
     * @param callable(string):void $callback
     */
    protected function analyzeFiles(callable $callback): void
    {
        $files = $this->collectFiles();
        $this->iterateFiles($files, $callback);
    }
}
