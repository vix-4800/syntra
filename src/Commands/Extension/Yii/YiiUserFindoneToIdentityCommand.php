<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\Rector\UserFindOneToIdentityRector;
use Vix\Syntra\Commands\RectorRunnerCommand;

class YiiUserFindoneToIdentityCommand extends RectorRunnerCommand
{
/**
 * Configure the command options.
 */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:user-findone-to-identity')
            ->setDescription('Replaces redundant User::findOne(...) lookups for current user with Yii::$app->user->identity')
            ->setHelp('Usage: vendor/bin/syntra yii:user-findone-to-identity');
    }

/**
 * Get a service from the container.rector rules.
 */
    protected function getRectorRules(): string
    {
        return UserFindOneToIdentityRector::class;
    }
}
