<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\Rector\CanHelpersRector;
use Vix\Syntra\Commands\RectorRunnerCommand;

class YiiCanHelpersCommand extends RectorRunnerCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:can-helpers')
            ->setDescription('Replaces can()/!can() chains with canAny(), canAll(), cannotAny(), or cannotAll()')
            ->setHelp('Usage: vendor/bin/syntra yii:can-helpers');
    }

    protected function getRectorRules(): array
    {
        return [CanHelpersRector::class];
    }
}
