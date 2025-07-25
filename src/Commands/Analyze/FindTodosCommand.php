<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Analyze;

use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Enums\CommandGroup;
use Vix\Syntra\Enums\ProgressIndicatorType;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Commands\Analyze\PatternFinderTrait;

class FindTodosCommand extends SyntraCommand
{
    use PatternFinderTrait;

    protected ProgressIndicatorType $progressType = ProgressIndicatorType::PROGRESS_BAR;

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('analyze:find-todos')
            ->setDescription('Scans project files and collects all TODO, FIXME, @todo, @deprecated and other important comments for further review and refactoring.')
            ->setHelp('Usage: vendor/bin/syntra analyze:find-todos');
    }

    public function perform(): int
    {
        $matches = [];
        $tags = Config::getCommandOption(
            CommandGroup::ANALYZE->value,
            self::class,
            'todo_tags',
            [
                'TODO',
                'FIXME',
                '@todo',
                '@fixme',
                '@deprecated',
                '@note',
                // '@see',
                '@hack',
                '@internal',
            ]
        );
        $patterns = [];
        foreach ($tags as $tag) {
            $patterns[$tag] = '/(?:\\/\\/|#|\\*)\\s*' . preg_quote($tag, '/') . '\\b(.*)/i';
        }

        $this->scanFilesForPatterns(
            $patterns,
            function (string $file, int $line, string $label, string $text, array $m) use (&$matches): void {
                $matches[] = [$file, $line, $label, trim($m[1])];
            },
            'FindTodosCommand'
        );

        if (!$matches) {
            $this->output->success('No TODO or special tags found! Clean code 👌');
            return Command::SUCCESS;
        }

        $rows = array_map(
            static fn (array $row): array => array_map('strval', $row),
            $matches,
        );

        $this->table(['File', 'Line', 'Tag', 'Comment'], $rows);

        $this->output->success('Scan complete (' . count($matches) . ' matches). Review your TODO/FIXME/deprecated and other notes!');

        return Command::SUCCESS;
    }
}
