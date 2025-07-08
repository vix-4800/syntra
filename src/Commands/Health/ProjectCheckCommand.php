<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Enums\CommandGroup;
use Vix\Syntra\Traits\CommandRunnerTrait;
use Vix\Syntra\Traits\RunsSubCommandsTrait;

class ProjectCheckCommand extends SyntraCommand
{
    use CommandRunnerTrait;
    use RunsSubCommandsTrait;

    protected function configure(): void
    {
        parent::configure();

        $this->setName('health:all')
            ->setDescription('Run all health checks: composer, phpstan, phpunit, etc.')
            ->setHelp('Usage: vendor/bin/syntra health:all');
    }

    public function perform(): int
    {
        $this->output->section('Starting full health check...');
        $hasErrors = $this->runSubCommands('', CommandGroup::HEALTH->value);

        if ($hasErrors) {
            return self::FAILURE;
        }

        $this->output->success('All health checks completed without critical errors.');
        return self::SUCCESS;
    }
}
