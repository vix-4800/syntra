<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\File;
use Vix\Syntra\Enums\ProgressIndicatorType;
use Vix\Syntra\ProgressIndicators\ProgressIndicatorFactory;

/**
 * Fixes the order of DocBlock comments and import statements in PHP files.
 *
 * This command finds file-level docblocks (`/** ... *\/`), variable comments like `/** @var Type \$var *\/`,
 * and `use ...;` import statements, and ensures they are placed in the correct order after the <?php tag.
 */
class ImportRefactorer extends SyntraRefactorCommand
{
    protected ProgressIndicatorType $progressType = ProgressIndicatorType::PROGRESS_BAR;

    protected function configure(): void
    {
        parent::configure();

        $this->setName('refactor:imports')
            ->setDescription('Fixes incorrect order of docblocks and import statements in PHP files')
            ->setHelp('Usage: vendor/bin/syntra refactor:imports [--dry-run] [--force]');
    }

    public function perform(): int
    {
        $files = File::collectFiles(Config::getProjectRoot());

        $this->setProgressMax(count($files));
        $this->startProgress();

        foreach ($files as $filePath) {
            $content = file_get_contents($filePath);

            $newContent = $this->reorderHeaderBlocks($content);

            if (!$this->dryRun) {
                File::writeChanges($filePath, $content, $newContent);
            }

            $this->advanceProgress();
        }

        $this->finishProgress();

        return Command::SUCCESS;
    }

    /**
     * Reorders file-level docblock, namespace, use statements, and @var comments in header.
     */
    private function reorderHeaderBlocks(string $content): string
    {
        // 1. Match file-level docblock (not @var)
        $docBlock = '';
        $docStart = null;
        $docLen = 0;
        if (preg_match('/\/\*\*(?!\s*@var)[\s\S]*?\*\//', $content, $mDoc, PREG_OFFSET_CAPTURE)) {
            [$block, $pos] = $mDoc[0];
            $docBlock = $block;
            $docStart = $pos;
            $docLen = strlen($block);
        }

        // 2. Match all use statements
        preg_match_all('/^[ \t]*use\s+[^\r\n;]+;.*$(?:\r?\n)?/m', $content, $mUses, PREG_OFFSET_CAPTURE);
        $useBlocks = $mUses[0];

        // 3. Match all /** @var ... */ comments
        preg_match_all('/^\/\*\*\s*@var[^*]*\*\/\s*$/m', $content, $mVars, PREG_OFFSET_CAPTURE);
        $varBlocks = $mVars[0];

        // 4. Match namespace declaration
        $namespace = '';
        $nsStart = null;
        $nsLen = 0;
        if (preg_match('/^namespace\s+[^;]+;/m', $content, $mNs, PREG_OFFSET_CAPTURE)) {
            [$namespace, $nsStart] = $mNs[0];
            $nsLen = strlen($namespace);
        }

        // Skip if no use or var blocks found
        if (empty($useBlocks) || empty($varBlocks)) {
            return $content;
        }

        // 5. Collect all blocks to remove
        $ranges = [];
        if ($docStart !== null) {
            $ranges[] = ['pos' => $docStart, 'len' => $docLen];
        }
        foreach ($useBlocks as [$str, $p]) {
            $ranges[] = ['pos' => $p, 'len' => strlen($str)];
        }
        foreach ($varBlocks as [$str, $p]) {
            $ranges[] = ['pos' => $p, 'len' => strlen($str)];
        }
        if ($nsStart !== null) {
            $ranges[] = ['pos' => $nsStart, 'len' => $nsLen];
        }

        usort($ranges, fn ($a, $b): int => $b['pos'] <=> $a['pos']);
        $clean = $content;
        foreach ($ranges as $r) {
            $clean = substr_replace($clean, '', $r['pos'], $r['len']);
        }

        // 6. Find <?php
        $phpPos = strpos($clean, '<?php');
        if ($phpPos === false) {
            return $content; // not a PHP file
        }
        $afterPhpPos = $phpPos + strlen('<?php');

        // 7. Trim empty lines after <?php
        $afterPhp = preg_replace('/^(\s*\R)*/', '', substr($clean, $afterPhpPos));

        // 8. Compose the new header
        $parts = [];
        if (!empty($docBlock)) {
            $parts[] = trim($docBlock);
        }
        if ($namespace) {
            $parts[] = trim($namespace);
        }
        if (!empty($useBlocks)) {
            $parts[] = rtrim(implode('', array_column($useBlocks, 0)), "\r\n");
        }
        if (!empty($varBlocks)) {
            $parts[] = rtrim(implode("\n", array_column($varBlocks, 0)), "\r\n");
        }
        $newHeader = implode("\n\n", $parts) . "\n\n";

        // 9. Final assembled content
        $result = substr($clean, 0, $afterPhpPos) . "\n\n" . $newHeader . ltrim((string) $afterPhp, "\r\n");

        return rtrim($result, "\r\n") . "\n";
    }
}
