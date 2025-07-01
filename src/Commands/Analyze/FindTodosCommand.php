<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Analyze;

use Vix\Syntra\Commands\SyntraCommand;
use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Utils\FileHelper;

class FindTodosCommand extends SyntraCommand
{
    protected static array $TAGS = [
        'TODO',
        'FIXME',
        '@todo',
        '@fixme',
        '@deprecated',
        '@note',
        '@see',
        '@hack',
        '@internal'
    ];

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('analyze:find-todos')
            ->setDescription('Scans project files and collects all TODO, FIXME, @todo, @deprecated and other important comments for further review and refactoring.')
            ->setHelp('');
    }

    public function perform(): int
    {
        $projectRoot = $this->configLoader->getProjectRoot();

        $fileHelper = new FileHelper();
        $files = $fileHelper->collectFiles($projectRoot);

        $matches = [];
        $allTags = implode('|', array_map('preg_quote', self::$TAGS));
        $pattern = "/(?:\/\/|#|\*|\s)\s*($allTags)\b(.*)/i";

        foreach ($files as $filePath) {
            if (strpos($filePath, "TodoReportCommand") !== false) {
                continue;
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                continue;
            }

            $lines = explode("\n", $content);

            $relativePath = str_starts_with($filePath, $projectRoot)
                ? substr($filePath, strlen($projectRoot))
                : $filePath;

            foreach ($lines as $lineNumber => $line) {
                if (preg_match($pattern, $line, $m)) {
                    $matches[] = [
                        $relativePath,
                        $lineNumber + 1,
                        $m[1],
                        trim($m[2])
                    ];
                }
            }
        }

        if (!$matches) {
            $this->output->success('No TODO or special tags found! Clean code 👌');
            return Command::SUCCESS;
        }

        $this->table(['File', 'Line', 'Tag', 'Comment'], $matches);

        $this->output->success('Scan complete (' . count($matches) . ' matches). Review your TODO/FIXME/deprecated and other notes!');

        return Command::SUCCESS;
    }
}
