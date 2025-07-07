<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Enums\DangerLevel;
use Vix\Syntra\Enums\ProgressIndicatorType;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\File;
use Vix\Syntra\Utils\StubHelper;

class DocblockRefactorer extends SyntraRefactorCommand
{
    protected ProgressIndicatorType $progressType = ProgressIndicatorType::PROGRESS_BAR;
    protected DangerLevel $dangerLevel = DangerLevel::MEDIUM;

/**
 * Configure the command options.
 */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('refactor:docblocks')
            ->setDescription('Adds a file-level PHPDoc block to the beginning of the file and a PHPDoc block to each class if it is missing')
            ->setHelp('Usage: vendor/bin/syntra refactor:docblocks [--dry-run] [--force] [--author=NAME] [--link=URL] [--category=CATEGORY]')
            ->addOption('author', null, InputOption::VALUE_OPTIONAL, 'Value for the @author tag')
            ->addOption('link', null, InputOption::VALUE_OPTIONAL, 'URL used for the @link tag')
            ->addOption('category', null, InputOption::VALUE_OPTIONAL, 'Value for the @category tag');
    }

/**
 * Perform the command actions.
 */
    public function perform(): int
    {
        $files = File::collectFiles(Config::getProjectRoot());

        $this->setProgressMax(count($files));
        $this->startProgress();

        foreach ($files as $filePath) {
            $content = file_get_contents($filePath);

            $newContent = $this->addDocBlocksToClasses($content, $filePath);
            $newContent = $this->addFileDocBlock($newContent, $filePath);

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
                fn (string $f): string => File::makeRelative($f, Config::getProjectRoot()),
                $changed
            );
            $this->listing($list);
        } else {
            $this->output->success('No files needed updating.');
        }

        return Command::SUCCESS;
    }

    /**
     * Adds PHPDoc blocks to all class declarations that don't already have them.
     *
     * @param  string $content
     * @param  string $filePath
     * @return string
     */
    private function addDocBlocksToClasses(string $content, string $filePath): string
    {
        $tokens = token_get_all($content);
        $insertions = [];
        $numTokens = count($tokens);

        for ($i = 0; $i < $numTokens; $i++) {
            if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_CLASS) {
                continue;
            }

            // Skip anonymous classes
            $nextToken = $this->getNextSignificantToken($tokens, $i);
            if (!$nextToken || $nextToken[0] !== T_STRING) {
                continue;
            }

            // Check for existing docblock
            $prevTokenIndex = $this->getPreviousTokenIndex($tokens, $i);
            $hasDocBlock = false;
            $currentIndex = $prevTokenIndex;

            while ($currentIndex !== null) {
                if (is_array($tokens[$currentIndex]) && $tokens[$currentIndex][0] === T_DOC_COMMENT) {
                    $hasDocBlock = true;
                    break;
                }
                $currentIndex = $this->getPreviousTokenIndex($tokens, $currentIndex);
            }

            if (!$hasDocBlock) {
                $insertIndex = $i;

                // Place docblock before class modifiers like final, abstract, or readonly
                $checkIndex = $prevTokenIndex;
                $modifiers = $this->getClassModifierTokens();
                while (
                    $checkIndex !== null &&
                    is_array($tokens[$checkIndex]) &&
                    in_array($tokens[$checkIndex][0], $modifiers, true)
                ) {
                    $insertIndex = $checkIndex;
                    $checkIndex = $this->getPreviousTokenIndex($tokens, $insertIndex);
                }

                $insertions[$insertIndex] = (new StubHelper("class-docblock"))->render([
                    'description' => str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $filePath),
                    'category' => (string) ($this->input->getOption('category') ?: 'Class'),
                    'author' => (string) ($this->input->getOption('author') ?: 'author <author@gmail.com>'),
                    'copyright' => date('Y'),
                    'link' => (string) ($this->input->getOption('link') ?: 'http://example.com/'),
                ]);
            }
        }

        if (empty($insertions)) {
            return $content;
        }

        krsort($insertions);
        $newContent = '';
        $currentIndex = 0;

        foreach ($insertions as $index => $docBlock) {
            $newContent .= $this->concatTokens(array_slice($tokens, $currentIndex, $index - $currentIndex));
            $newContent .= $docBlock;
            $currentIndex = $index;
        }
        return $newContent . $this->concatTokens(array_slice($tokens, $currentIndex));
    }

    /**
     * Returns the next non-whitespace token.
     *
     * @param  array      $tokens
     * @param  int        $index
     * @return array|null
     */
    private function getNextSignificantToken(array $tokens, int $index): ?array
    {
        for ($i = $index + 1; $i < count($tokens); $i++) {
            if (!is_array($tokens[$i])) {
                if (trim((string) $tokens[$i]) !== '') {
                    return [null, $tokens[$i]];
                }
                continue;
            }

            if ($tokens[$i][0] === T_WHITESPACE) {
                continue;
            }

            return $tokens[$i];
        }
        return null;
    }

    /**
     * Returns the index of the previous non-whitespace token.
     *
     * @param  array    $tokens
     * @param  int      $index
     * @return int|null
     */
    private function getPreviousTokenIndex(array $tokens, int $index): ?int
    {
        for ($i = $index - 1; $i >= 0; $i--) {
            if (!is_array($tokens[$i])) {
                if (trim((string) $tokens[$i]) !== '') {
                    return $i;
                }
                continue;
            }

            if ($tokens[$i][0] === T_WHITESPACE) {
                continue;
            }

            return $i;
        }
        return null;
    }

    /**
     * Returns an array of tokens that represent class modifiers.
     *
     * @return array<int>
     */
    private function getClassModifierTokens(): array
    {
        return [T_FINAL, T_ABSTRACT, T_READONLY];
    }

    /**
     * Converts an array of PHP tokens back into a string.
     *
     * @param  array  $tokens
     * @return string
     */
    private function concatTokens(array $tokens): string
    {
        $content = '';
        foreach ($tokens as $token) {
            $content .= is_array($token) ? $token[1] : $token;
        }
        return $content;
    }

    /**
     * Adds a file-level docblock if it doesn't already exist.
     *
     * @param  string $content
     * @param  string $filePath
     * @return string
     */
    private function addFileDocBlock(string $content, string $filePath): string
    {
        $tokens = token_get_all($content);
        if (empty($tokens)) {
            return $content;
        }

        $firstToken = $tokens[0];
        $hasOpenTag = is_array($firstToken) && ($firstToken[0] === T_OPEN_TAG || $firstToken[0] === T_OPEN_TAG_WITH_ECHO);

        // Skip if file does not start with an opening PHP tag
        if (!$hasOpenTag) {
            return $content;
        }

        // Check for existing file-level docblock
        $index = 1;
        $hasExistingDoc = false;
        $n = count($tokens);

        while ($index < $n) {
            $token = $tokens[$index];

            if (is_array($token)) {
                if ($token[0] === T_DOC_COMMENT) {
                    $hasExistingDoc = true;
                    break;
                }

                if (!in_array($token[0], [T_WHITESPACE, T_COMMENT], true)) {
                    break;
                }
            }

            $index++;
        }

        if ($hasExistingDoc) {
            return $content;
        }

        $docBlock = (new StubHelper("file-docblock"))->render([
            'description' => str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $filePath),
            'phpVersion' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
            'category' => (string) ($this->input->getOption('category') ?: 'File'),
            'author' => (string) ($this->input->getOption('author') ?: 'author <author@gmail.com>'),
            'copyright' => date('Y'),
            'link' => (string) ($this->input->getOption('link') ?: 'http://example.com/'),
        ]);

        return $firstToken[1] . "\n$docBlock" . $this->concatTokens(array_slice($tokens, 1));
    }
}
