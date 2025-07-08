<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Enums\ProgressIndicatorType;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\Project;
use Vix\Syntra\Facades\File;

/**
 * Refactors @var comments by standardizing the order of type and variable name.
 *
 * Converts comments like `/* @var $varName Type *\/` to the standardized format `/** @var Type $varName *\/`
 * across all PHP files in the specified directory and its subdirectories.
 */
class VarCommentsRefactorer extends SyntraRefactorCommand
{
    protected ProgressIndicatorType $progressType = ProgressIndicatorType::PROGRESS_BAR;

    protected function configure(): void
    {
        parent::configure();

        $this->setName('refactor:var-comments')
            ->setDescription('Standardizes @var comments to /** @var Type $var */')
            ->setHelp('Transforms all single-line @var annotations such as /* @var ... */ to PHPDoc-style /** @var Type $var */. Usage: vendor/bin/syntra refactor:var-comments [--dry-run] [--force]');
    }

    public function perform(): int
    {
        $files = File::collectFiles($this->path);

        $this->setProgressMax(count($files));
        $this->startProgress();

        foreach ($files as $filePath) {
            $content = file_get_contents($filePath);

            $newContent = $this->convertVarComments($content);

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
                $changed
            );
            $this->listing($list);
        } else {
            $this->output->success('No files needed updating.');
        }

        return Command::SUCCESS;
    }

    /**
     * Converts @var comments to PHPDoc-compliant style.
     */
    private function convertVarComments(string $content): string
    {
        $newContent = $content;

        // /* @var $var Type */ → /** @var Type $var */
        $newContent = preg_replace_callback(
            '/\/\*\s*@var\s+(\$[\w\[\]\'"->]+)\s+([^\s\*]+)\s*\*\//',
            fn (array $m): string => "/** @var {$m[2]} {$m[1]} */",
            $newContent
        );

        // /* @var Type $var */ → /** @var Type $var */
        $newContent = preg_replace_callback(
            '/\/\*\s*@var\s+([^\s\$][^\s\*]*)\s+(\$[\w\[\]\'"->]+)\s*\*\//',
            fn (array $m): string => "/** @var {$m[1]} {$m[2]} */",
            (string) $newContent
        );

        // /** @var $var Type */ → /** @var Type $var */
        $newContent = preg_replace_callback(
            '/\/\*\*\s*@var\s+(\$[\w\[\]\'"->]+)\s+([^\s\*]+)\s*\*\//',
            fn (array $m): string => "/** @var {$m[2]} {$m[1]} */",
            (string) $newContent
        );

        return $newContent;
    }
}
