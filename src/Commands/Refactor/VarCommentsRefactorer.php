<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Enums\ProgressIndicatorType;
use Vix\Syntra\Facades\File;
use Vix\Syntra\Traits\ProcessesFilesTrait;

/**
 * Refactors @var comments by standardizing the order of type and variable name.
 *
 * Converts comments like `/* @var $varName Type *\/` to the standardized format `/** @var Type $varName *\/`
 * across all PHP files in the specified directory and its subdirectories.
 */
class VarCommentsRefactorer extends SyntraRefactorCommand
{
    use ProcessesFilesTrait;
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
        return $this->processFiles(function (string $content): string {
            return $this->convertVarComments($content);
        });
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
