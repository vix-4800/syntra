<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Analyze;

use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Traits\CommandRunnerTrait;
use Vix\Syntra\Traits\RunsSubCommandsTrait;

class AnalyzeAllCommand extends SyntraCommand
{
    use CommandRunnerTrait;
    use RunsSubCommandsTrait;

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('analyze:all')
            ->setDescription('Runs all enabled analyze commands in sequence')
            ->setHelp('Usage: vendor/bin/syntra analyze:all');
    }

    public function perform(): int
    {
        $hasErrors = $this->runSubCommands('\\Commands\\Analyze\\');

        return $hasErrors ? self::FAILURE : self::SUCCESS;
    }
}
