<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Laravel;

use Vix\Syntra\Commands\RectorRunnerCommand;

class LaravelAllCommand extends RectorRunnerCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('laravel:all')
            ->setDescription('Runs all Laravel-specific Rector refactorings in sequence')
            ->setHelp('Usage: vendor/bin/syntra laravel:all');
    }

    protected function getRectorRules(): array
    {
        return [];
    }

    protected function getSuccessMessage(): string
    {
        return 'All Laravel Rector refactorings completed.';
    }

    protected function getErrorMessage(): string
    {
        return 'Laravel Rector refactoring crashed.';
    }
}
