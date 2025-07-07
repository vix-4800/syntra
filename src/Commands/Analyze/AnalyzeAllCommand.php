<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Analyze;

use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Traits\CommandRunnerTrait;

class AnalyzeAllCommand extends SyntraCommand
{
    use CommandRunnerTrait;

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
        $enabled = Config::getEnabledCommands();
        $commands = array_filter(
            $enabled,
            static fn(string $class): bool => str_contains($class, '\\Commands\\Analyze\\') && $class !== self::class
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
