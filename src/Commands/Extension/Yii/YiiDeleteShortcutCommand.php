<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\Rector\DeleteAllShortcutRector;
use Vix\Syntra\Commands\RectorRunnerCommand;

class YiiDeleteShortcutCommand extends RectorRunnerCommand
{
/**
 * Configure the command options.
 */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:delete-shortcut')
            ->setDescription('Replaces Model::find()->where([...])->delete() with Model::deleteAll([...])')
            ->setHelp('Usage: vendor/bin/syntra yii:delete-shortcut');
    }

/**
 * Get a service from the container.rector rules.
 */
    protected function getRectorRules(): string
    {
        return DeleteAllShortcutRector::class;
    }
}
