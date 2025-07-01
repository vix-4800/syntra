<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Enums\DangerLevel;
use Vix\Syntra\Utils\FileHelper;

class DocblockRefactorer extends SyntraRefactorCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->setName('refactor:docblocks')
            ->setDescription('Adds a file-level PHPDoc block to the beginning of the file and a PHPDoc block to each class if it is missing')
            ->setDangerLevel(DangerLevel::MEDIUM)
            ->addForceOption();
    }

    public function perform(): int
    {
        $fileHelper = new FileHelper();
        $files = $fileHelper->collectFiles($this->configLoader->getProjectRoot());

        foreach ($files as $filePath) {
            $content = file_get_contents($filePath);

            $newContent = $this->addDocBlocksToClasses($content, $filePath);
            $newContent = $this->addFileDocBlock($newContent, $filePath);

            if (!$this->dryRun) {
                $fileHelper->writeChanges($filePath, $content, $newContent);
            }
            // $this->progressBar->advance();
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
            $className = $nextToken[1];

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
                $insertions[$i] = $this->generateClassDocblock($className, $filePath);
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
            $newContent .= $docBlock . "\n";
            $currentIndex = $index;
        }

        $newContent .= $this->concatTokens(array_slice($tokens, $currentIndex));
        return $newContent;
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
     * Generates a standard PHPDoc block for a class.
     *
     * @param  string $className
     * @param  string $filePath
     * @return string
     */
    private function generateClassDocblock(string $className, string $filePath): string
    {
        $date = date('Y');
        return <<<EOT
        /**
         * Class $className
         *
         * @category  Class
         * @package   Package
         * @author    author <author@gmail.com>
         * @copyright $date
         * @license   MIT License
         * @link      http://example.com/
         */
        EOT;
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
        if (count($tokens) === 0) {
            return $content;
        }

        $firstToken = $tokens[0];
        $hasOpenTag = is_array($firstToken) &&
            ($firstToken[0] === T_OPEN_TAG || $firstToken[0] === T_OPEN_TAG_WITH_ECHO);

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

        $docBlock = $this->generateFileDocBlock($filePath);

        return $firstToken[1] . $docBlock . $this->concatTokens(array_slice($tokens, 1));
    }

    /**
     * Generates a standard file-level PHPDoc block.
     *
     * @param  string $filePath
     * @return string
     */
    private function generateFileDocBlock(string $filePath): string
    {
        $shortPath = str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $filePath);
        $year = date('Y');
        $phpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;

        return <<<EOT

        /**
         * $shortPath
         *
         * PHP Version $phpVersion
         *
         * @category  File
         * @package   Package
         * @author    author <author@gmail.com>
         * @copyright $year
         * @license   MIT License
         * @link      http://example.com/
         */

        EOT;
    }
}
