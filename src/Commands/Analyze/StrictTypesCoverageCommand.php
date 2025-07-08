<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Analyze;

use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Enums\ProgressIndicatorType;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\File;

class StrictTypesCoverageCommand extends SyntraCommand
{
    protected ProgressIndicatorType $progressType = ProgressIndicatorType::PROGRESS_BAR;

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('analyze:strict-types')
            ->setDescription('Shows coverage of PHP files using declare(strict_types=1).')
            ->setHelp('Usage: vendor/bin/syntra analyze:strict-types');
    }

    public function perform(): int
    {
        $projectRoot = Config::getProjectRoot();
        $files = File::collectFiles($projectRoot);

        $total = count($files);
        $withStrict = 0;

        $this->setProgressMax($total);
        $this->startProgress();

        foreach ($files as $file) {
            $content = file_get_contents($file);
            if ($content !== false && preg_match('/^\s*<\?php\s+declare\(strict_types=1\);/m', $content)) {
                $withStrict++;
            }
            $this->advanceProgress();
        }

        $this->finishProgress();

        $coverage = $total > 0 ? round($withStrict / $total * 100, 2) : 100.0;
        $this->output->writeln(sprintf('Strict types coverage: %d/%d (%.1f%%)', $withStrict, $total, $coverage));

        if ($withStrict === $total) {
            $this->output->success('All files declare strict_types=1.');
            return Command::SUCCESS;
        }

        $missing = $total - $withStrict;
        $this->output->warning($missing . ' file(s) missing strict_types declaration.');
        return Command::FAILURE;
    }
}
