<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Laravel;

use Vix\Syntra\Commands\Rector\Laravel\DispatchShortcutRector;
use Vix\Syntra\Commands\RectorRunnerCommand;

class LaravelDispatchShortcutCommand extends RectorRunnerCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('laravel:dispatch-shortcut')
            ->setDescription('Rewrites dispatch(new SomeJob($args)) to SomeJob::dispatch($args)')
            ->setHelp('Usage: vendor/bin/syntra laravel:dispatch-shortcut');
    }

    protected function getRectorRules(): string
    {
        return DispatchShortcutRector::class;
    }
}
