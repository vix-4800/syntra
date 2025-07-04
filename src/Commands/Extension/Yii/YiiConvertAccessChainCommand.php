<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\RectorRunnerCommand;
use Vix\Syntra\Commands\Rector\ConvertAccessChainRector;

class YiiConvertAccessChainCommand extends RectorRunnerCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:convert-access-chain')
            ->setDescription('Replaces user->identity->hasAccessChain/hasNoAccessChain with user->canAny/cannotAny')
            ->setHelp('');
    }

    protected function getRectorRules(): string
    {
        return ConvertAccessChainRector::class;
    }
}
