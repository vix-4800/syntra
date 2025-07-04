<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;
use Vix\Syntra\Commands\RectorRunnerCommand;

use Vix\Syntra\Commands\Rector\UserFindOneToIdentityRector;

class YiiUserFindoneToIdentityCommand extends RectorRunnerCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:user-findone-to-identity')
            ->setDescription('Replaces redundant User::findOne(...) lookups for current user with Yii::$app->user->identity')
            ->setHelp('');
    }

    protected function getRectorRules(): string
    {
        return UserFindOneToIdentityRector::class;
    }
}
