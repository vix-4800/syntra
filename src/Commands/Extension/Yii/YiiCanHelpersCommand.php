<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\Rector\CanHelpersRector;

class YiiCanHelpersCommand extends YiiRectorCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:can-helpers')
            ->setDescription('Replaces can()/!can() chains with canAny(), canAll(), cannotAny(), or cannotAll()')
            ->setHelp('');
    }

    protected function getRectorRules(): string
    {
        return CanHelpersRector::class;
    }
}
