<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Utils\FileHelper;

/**
 * Refactors @var comments by standardizing the order of type and variable name.
 *
 * Converts comments like `/* @var $varName Type *\/` to the standardized format `/** @var Type $varName *\/`
 * across all PHP files in the specified directory and its subdirectories.
 */
class VarCommentsRefactorer extends SyntraRefactorCommand
{
    public function isAvailable(): bool
    {
        return $this->configLoader->get('refactor.fix_var_comments.enabled', false);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName('refactor:var-comments')
            ->setDescription('Standardizes @var comments to /** @var Type $var */')
            ->setHelp('Transforms all single-line @var annotations such as /* @var ... */ to PHPDoc-style /** @var Type $var */')
            ->addForceOption();
    }

    public function perform(): int
    {
        $files = (new FileHelper())->collectFiles($this->configLoader->getProjectRoot());

        foreach ($files as $filePath) {
            $content = file_get_contents($filePath);

            $newContent = $this->convertVarComments($content);

            $this->writeChanges($filePath, $content, $newContent);
            // $this->progressBar->advance();
        }

        return Command::SUCCESS;
    }

    /**
     * Converts @var comments to PHPDoc-compliant style.
     */
    public function convertVarComments(string $content): string
    {
        $newContent = $content;

        // /* @var $var Type */ → /** @var Type $var */
        $newContent = preg_replace_callback(
            '/\/\*\s*@var\s+(\$[\w\[\]\'"->]+)\s+([^\s\*]+)\s*\*\//',
            fn(array $m): string => "/** @var {$m[2]} {$m[1]} */",
            $newContent
        );

        // /* @var Type $var */ → /** @var Type $var */
        $newContent = preg_replace_callback(
            '/\/\*\s*@var\s+([^\s\$][^\s\*]*)\s+(\$[\w\[\]\'"->]+)\s*\*\//',
            fn(array $m): string => "/** @var {$m[1]} {$m[2]} */",
            (string) $newContent
        );

        // /** @var $var Type */ → /** @var Type $var */
        $newContent = preg_replace_callback(
            '/\/\*\*\s*@var\s+(\$[\w\[\]\'"->]+)\s+([^\s\*]+)\s*\*\//',
            fn(array $m): string => "/** @var {$m[2]} {$m[1]} */",
            (string) $newContent
        );

        return $newContent;
    }

    /**
     * Writes changes to a file if they differ from the original.
     */
    protected function writeChanges(string $filePath, string $oldContent, string $newContent): void
    {
        if ($newContent !== $oldContent) {
            if (!$this->dryRun) {
                file_put_contents($filePath, $newContent);
                // $this->logChange($filePath, 'modified');
            }
        }
    }
}
