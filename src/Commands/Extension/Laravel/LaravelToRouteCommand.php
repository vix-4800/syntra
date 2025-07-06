<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Laravel;

use Vix\Syntra\Commands\Rector\RedirectToRouteRector;
use Vix\Syntra\Commands\RectorRunnerCommand;

class LaravelToRouteCommand extends RectorRunnerCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('laravel:to-route')
            ->setDescription('Replaces redirect()->route() with to_route()')
            ->setHelp('Usage: vendor/bin/syntra laravel:to-route');
    }

    protected function getRectorRules(): string
    {
        return RedirectToRouteRector::class;
    }
}
