<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Analyze;

use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Enums\ProgressIndicatorType;
use Vix\Syntra\Facades\File;
use Vix\Syntra\Traits\AnalyzesFilesTrait;

class FindDebugCallsCommand extends SyntraCommand
{
    use AnalyzesFilesTrait;

    protected ProgressIndicatorType $progressType = ProgressIndicatorType::PROGRESS_BAR;

    private const DEBUG_FUNCTIONS = [
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
        $matches = [];
        $pattern = '/(?<![\w\$])(' . implode('|', array_map('preg_quote', self::DEBUG_FUNCTIONS)) . ')\s*\(/i';

        $this->analyzeFiles(function (string $filePath) use (&$matches, $pattern): void {
            if (str_contains((string) $filePath, 'FindDebugCallsCommand')) {
                return;
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                return;
            }

            $relativePath = File::makeRelative($filePath, $this->path);

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
        });

        if (!$matches) {
            $this->output->success('No debug calls or comments found! Code is clean 👌');
            return Command::SUCCESS;
        }

        usort($matches, fn ($a, $b): int => [$a[0], $a[1]] <=> [$b[0], $b[1]]);

        $rows = array_map(
            static fn (array $row): array => array_map('strval', $row),
            $matches,
        );

        $this->table(
            ['File', 'Line', 'Type', 'Code'],
            $rows,
        );

        $this->output->warning('Found ' . count($matches) . ' debug calls/comments. Please remove them before production!');
        return Command::FAILURE;
    }
}
