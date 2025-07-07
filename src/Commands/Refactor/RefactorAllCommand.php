<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Enums\DangerLevel;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Traits\CommandRunnerTrait;

class RefactorAllCommand extends SyntraRefactorCommand
{
    use CommandRunnerTrait;

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('refactor:all')
            ->setDescription('Runs all enabled refactor commands in sequence')
            ->setHelp('Usage: vendor/bin/syntra refactor:all')
            ->setDangerLevel(DangerLevel::HIGH);
    }

    public function perform(): int
    {
        $enabled = Config::getEnabledCommands();
        $commands = array_filter(
            $enabled,
            static fn(string $class): bool => str_contains($class, '\\Commands\\Refactor\\') && $class !== self::class
        );

        $hasErrors = false;
        foreach ($commands as $class) {
            $exitCode = $this->runCommand($class);
            if ($exitCode !== self::SUCCESS) {
                $hasErrors = true;
            }
        }

        return $hasErrors ? self::FAILURE : self::SUCCESS;
    }
}
