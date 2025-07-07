<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Laravel;

use Vix\Syntra\Commands\RectorRunnerCommand;

class LaravelAllCommand extends RectorRunnerCommand
{
/**
 * Configure the command options.
 */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('laravel:all')
            ->setDescription('Runs all Laravel-specific Rector refactorings in sequence')
            ->setHelp('Usage: vendor/bin/syntra laravel:all');
    }

/**
 * Get a service from the container.rector rules.
 */
    protected function getRectorRules(): array
    {
        return [];
    }

/**
 * Get a service from the container.success message.
 */
    protected function getSuccessMessage(): string
    {
        return 'All Laravel Rector refactorings completed.';
    }

/**
 * Get a service from the container.error message.
 */
    protected function getErrorMessage(): string
    {
        return 'Laravel Rector refactoring crashed.';
    }
}
