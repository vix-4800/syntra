<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Facades\File;

/**
 * Helper trait for refactorer commands that process files.
 */
trait ProcessesFilesTrait
{
    use IteratesFilesTrait;
    /**
     * Collect files, process them with the provided callback and list changed files.
     *
     * @param callable(string,string):string $processor
     */
    protected function processFiles(callable $processor): int
    {
        $files = File::collectFiles($this->path);

        $this->iterateFiles($files, function (string $filePath) use ($processor): void {
            $content = file_get_contents($filePath);

            $newContent = $processor($content, $filePath);

            if (!$this->dryRun) {
                File::writeChanges($filePath, $content, $newContent);
            }
        });

        $changed = File::getChangedFiles();
        File::clearChangedFiles();

        if ($changed) {
            $this->output->section('Changed files');
            $root = is_file($this->path) ? dirname($this->path) : $this->path;
            $list = array_map(
                fn (string $f): string => File::makeRelative($f, $root),
                $changed,
            );
            $this->listing($list);
        } else {
            $this->output->success('No files needed updating.');
        }

        return Command::SUCCESS;
    }
}
