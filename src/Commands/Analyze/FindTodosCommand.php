<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Analyze;

use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\ProgressIndicators\ProgressIndicatorFactory;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\File;

class FindTodosCommand extends SyntraCommand
{

    protected string $progressType = ProgressIndicatorFactory::TYPE_PROGRESS_BAR;

    protected static array $TAGS = [
        'TODO',
        'FIXME',
        '@todo',
        '@fixme',
        '@deprecated',
        '@note',
        // '@see',
        '@hack',
        '@internal',
    ];

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
        $projectRoot = Config::getProjectRoot();

        $files = File::collectFiles($projectRoot);

        $matches = [];
        $allTags = implode('|', array_map('preg_quote', self::$TAGS));
        $pattern = "/(?:\/\/|#|\*|\s)\s*($allTags)\b(.*)/i";

        $this->setProgressMax(count($files));
        $this->startProgress();

        foreach ($files as $filePath) {
            if (str_contains($filePath, "FindTodosCommand")) {
                continue;
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                continue;
            }

            $relativePath = File::makeRelative($filePath, $projectRoot);

            $lines = explode("\n", $content);
            foreach ($lines as $lineNumber => $line) {
                if (preg_match($pattern, $line, $m)) {
                    $matches[] = [
                        $relativePath,
                        $lineNumber + 1,
                        $m[1],
                        trim($m[2]),
                    ];
                }
            }

            $this->advanceProgress();
        }

        $this->finishProgress();

        if (!$matches) {
            $this->output->success('No TODO or special tags found! Clean code 👌');
            return Command::SUCCESS;
        }

        $this->table(['File', 'Line', 'Tag', 'Comment'], $matches);

        $this->output->success('Scan complete (' . count($matches) . ' matches). Review your TODO/FIXME/deprecated and other notes!');

        return Command::SUCCESS;
    }
}
