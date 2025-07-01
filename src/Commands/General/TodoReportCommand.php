<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\General;

use Vix\Syntra\SyntraCommand;
use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Utils\FileHelper;

class TodoReportCommand extends SyntraCommand
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
            ->setName('general:todos')
            ->setDescription('Scans project files and collects all TODO, FIXME, @todo, @deprecated and other important comments for further review and refactoring.')
            ->setHelp('');
    }

    public function perform(): int
    {
        $fileHelper = new FileHelper();
        $files = $fileHelper->collectFiles($this->configLoader->getProjectRoot());

        $matches = [];

        foreach ($files as $filePath) {
            $content = file_get_contents($filePath);
            $lines = explode("\n", $content);

            foreach ($lines as $lineNumber => $line) {
                foreach (self::$TAGS as $tag) {
                    if (preg_match('/(?:\/\/|#|\*|\s)\s*(' . preg_quote($tag, '/') . ')\b(.*)/i', $line, $m)) {
                        $matches[] = [
                            $filePath,
                            $lineNumber + 1,
                            strtoupper($tag),
                            trim($m[2])
                        ];
                    }
                }
            }
        }

        if (!$matches) {
            $this->output->success('No TODO or special tags found! Clean code 👌');
            return Command::SUCCESS;
        }

        $this->table(
            ['File', 'Line', 'Tag', 'Comment'],
            $matches
        );

        $this->output->success('Scan complete. Review your TODO/FIXME/deprecated and other notes!');

        return Command::SUCCESS;
    }
}
