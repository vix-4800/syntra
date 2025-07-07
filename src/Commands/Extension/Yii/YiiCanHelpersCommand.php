<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\Rector\CanHelpersRector;
use Vix\Syntra\Commands\RectorRunnerCommand;

class YiiCanHelpersCommand extends RectorRunnerCommand
{
/**
 * Configure the command options.
 */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:can-helpers')
            ->setDescription('Replaces can()/!can() chains with canAny(), canAll(), cannotAny(), or cannotAll()')
            ->setHelp('Usage: vendor/bin/syntra yii:can-helpers');
    }

/**
 * Get a service from the container.rector rules.
 */
    protected function getRectorRules(): string
    {
        return CanHelpersRector::class;
    }
}
