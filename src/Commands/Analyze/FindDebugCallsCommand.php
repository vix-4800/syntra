<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Analyze;

use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Enums\ProgressIndicatorType;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\File;

class FindDebugCallsCommand extends SyntraCommand
{
    protected ProgressIndicatorType $progressType = ProgressIndicatorType::PROGRESS_BAR;

    protected static array $DEBUG_FUNCTIONS = [
        'var_dump',
        'print_r',
        'dd',
        'dump',
        'ray',
        'die',
        'exit',
        'logger(',
        'eval',
        'xdebug_break',
    ];

/**
 * Configure the command options.
 */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('analyze:find-debug-calls')
            ->setDescription('Checks that var_dump, dd, print_r, eval, and other calls prohibited in production are not used.')
            ->setHelp('Usage: vendor/bin/syntra analyze:find-debug-calls');
    }

/**
 * Perform the command actions.
 */
    public function perform(): int
    {
        $projectRoot = Config::getProjectRoot();

        $files = File::collectFiles($projectRoot);

        $matches = [];
        $pattern = '/(?<![\w\$])(' . implode('|', array_map('preg_quote', self::$DEBUG_FUNCTIONS)) . ')\s*\(/i';

        $this->setProgressMax(count($files));
        $this->startProgress();

        foreach ($files as $filePath) {
            if (str_contains((string) $filePath, "FindDebugCallsCommand")) {
                continue;
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                continue;
            }

            $relativePath = File::makeRelative($filePath, $projectRoot);

            $lines = explode("\n", $content);
            foreach ($lines as $lineNumber => $line) {
                if (preg_match($pattern, $line, $fnMatch)) {
                    $matches[] = [
                        $relativePath,
                        $lineNumber + 1,
                        "$fnMatch[1]()",
                        trim($line),
                    ];
                }
            }

            $this->advanceProgress();
        }

        $this->finishProgress();

        if (!$matches) {
            $this->output->success('No debug calls or comments found! Code is clean 👌');
            return Command::SUCCESS;
        }

        usort($matches, fn ($a, $b): int => [$a[0], $a[1]] <=> [$b[0], $b[1]]);

        $this->table(
            ['File', 'Line', 'Type', 'Code'],
            $matches
        );

        $this->output->warning('Found ' . count($matches) . ' debug calls/comments. Please remove them before production!');
        return Command::FAILURE;
    }
}
