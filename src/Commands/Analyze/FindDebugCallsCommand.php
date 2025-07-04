<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Analyze;

use Vix\Syntra\Commands\SyntraCommand;
use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Traits\ContainerAwareTrait;
use Vix\Syntra\Utils\FileHelper;

class FindDebugCallsCommand extends SyntraCommand
{
    use ContainerAwareTrait;

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

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('analyze:find-debug-calls')
            ->setDescription('Checks that var_dump, dd, print_r, eval, and other calls prohibited in production are not used.')
            ->setHelp('Usage: vendor/bin/syntra analyze:find-debug-calls');
    }

    public function perform(): int
    {
        $projectRoot = $this->configLoader->getProjectRoot();

        $fileHelper = $this->getService(FileHelper::class, fn(): FileHelper => new FileHelper());

        $files = $fileHelper->collectFiles($projectRoot);

        $matches = [];
        $pattern = '/(?<![\w\$])(' . implode('|', array_map('preg_quote', self::$DEBUG_FUNCTIONS)) . ')\s*\(/i';

        $this->startProgress(count($files));

        foreach ($files as $filePath) {
            if (str_contains($filePath, "FindDebugCallsCommand")) {
                continue;
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                continue;
            }

            $relativePath = $fileHelper->makeRelative($filePath, $projectRoot);

            $lines = explode("\n", $content);
            foreach ($lines as $lineNumber => $line) {
                if (preg_match($pattern, $line, $fnMatch)) {
                    $matches[] = [
                        $relativePath,
                        $lineNumber + 1,
                        "$fnMatch[1]()",
                        trim($line)
                    ];
                }
            }

            $this->advance();
        }

        $this->finishProgress();

        if (!$matches) {
            $this->output->success('No debug calls or comments found! Code is clean 👌');
            return Command::SUCCESS;
        }

        usort($matches, fn($a, $b) => [$a[0], $a[1]] <=> [$b[0], $b[1]]);

        $this->table(
            ['File', 'Line', 'Type', 'Code'],
            $matches
        );

        $this->output->warning('Found ' . count($matches) . ' debug calls/comments. Please remove them before production!');
        return Command::FAILURE;
    }
}
