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
    /**
     * Collect files, process them with the provided callback and list changed files.
     *
     * @param callable(string,string):string $processor
     */
    protected function processFiles(callable $processor): int
    {
        $files = File::collectFiles($this->path);

        $this->setProgressMax(count($files));
        $this->startProgress();

        foreach ($files as $filePath) {
            $content = file_get_contents($filePath);

            $newContent = $processor($content, $filePath);

            if (!$this->dryRun) {
                File::writeChanges($filePath, $content, $newContent);
            }

            $this->advanceProgress();
        }

        $this->finishProgress();

        $changed = File::getChangedFiles();
        File::clearChangedFiles();

        if ($changed) {
            $this->output->section('Changed files');
            $list = array_map(
                fn (string $f): string => File::makeRelative($f, $this->path),
                $changed,
            );
            $this->listing($list);
        } else {
            $this->output->success('No files needed updating.');
        }

        return Command::SUCCESS;
    }
}
