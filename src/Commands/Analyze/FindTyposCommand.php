<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Analyze;

use Peck\Kernel;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Enums\ProgressIndicatorType;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\File;

class FindTyposCommand extends SyntraCommand
{
    protected ProgressIndicatorType $progressType = ProgressIndicatorType::SPINNER;

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('analyze:find-typos')
            ->setDescription('Detects misspellings in filenames and source code using Peck.')
            ->setHelp('Usage: vendor/bin/syntra analyze:find-typos');
    }

    public function perform(): int
    {
        $projectRoot = Config::getProjectRoot();
        $finder = new ExecutableFinder();
        if ($finder->find('aspell') === null) {
            throw new \Vix\Syntra\Exceptions\MissingBinaryException('aspell');
        }

        $kernel = Kernel::default();

        $this->setProgressMax(0); // spinner
        $this->startProgress();
        $this->progressIndicator->setMessage('Scanning for typos...');

        $issues = $kernel->handle([
            'directory' => $projectRoot,
            'onSuccess' => fn () => $this->advanceProgress(),
            'onFailure' => fn () => $this->advanceProgress(),
        ]);

        $this->finishProgress();

        if ($issues === []) {
            $this->output->success('No misspellings found.');
            return Command::SUCCESS;
        }

        $rows = array_map(
            static function (\Peck\ValueObjects\Issue $issue) use ($projectRoot): array {
                $file = File::makeRelative($issue->file, $projectRoot);
                $line = $issue->line > 0 ? (string) $issue->line : '-';
                $suggestions = implode(', ', $issue->misspelling->suggestions);

                return [$file, $line, $issue->misspelling->word, $suggestions];
            },
            $issues,
        );

        $this->table(['File', 'Line', 'Word', 'Suggestions'], $rows);
        $this->output->warning(count($rows) . ' misspelling(s) found.');

        return Command::FAILURE;
    }
}
