<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\Rector\ConvertAccessChainRector;
use Vix\Syntra\Commands\RectorRunnerCommand;

class YiiConvertAccessChainCommand extends RectorRunnerCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:convert-access-chain')
            ->setDescription('Replaces user->identity->hasAccessChain/hasNoAccessChain with user->canAny/cannotAny')
            ->setHelp('Usage: vendor/bin/syntra yii:convert-access-chain');
    }

    protected function getRectorRules(): array
    {
        return [ConvertAccessChainRector::class];
    }
}
