<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\Facades\Config;
use Vix\Syntra\Enums\CommandGroup;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Traits\CommandRunnerTrait;

class ProjectCheckCommand extends SyntraCommand
{
    use CommandRunnerTrait;

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

        $commands = Config::getEnabledCommandsByGroup(CommandGroup::HEALTH->value);
        $commands = array_filter(
            $commands,
            static fn (string $class): bool => $class !== self::class
        );

        $hasErrors = false;
        foreach ($commands as $class) {
            $exitCode = $this->runCommand($class);

            if ($exitCode !== self::SUCCESS) {
                $hasErrors = true;
            }
        }

        if ($hasErrors) {
            return self::FAILURE;
        }

        $this->output->success('All health checks completed without critical errors.');
        return self::SUCCESS;
    }
}
