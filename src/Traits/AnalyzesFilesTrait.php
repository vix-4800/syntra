<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

use Vix\Syntra\Facades\File;

/**
 * Provides helper methods for analyze commands that work with project files.
 */
trait AnalyzesFilesTrait
{
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

    /**
     * Iterate over the given files with progress indicators.
     *
     * @param string[]              $files
     * @param callable(string):void $callback
     */
    protected function iterateFiles(array $files, callable $callback): void
    {
        $this->setProgressMax(count($files));
        $this->startProgress();

        foreach ($files as $file) {
            $callback($file);
            $this->advanceProgress();
        }

        $this->finishProgress();
    }
}
