<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\RectorRunnerCommand;
use Vix\Syntra\Commands\Rector\UpdateAllShortcutRector;

class YiiUpdateShortcutCommand extends RectorRunnerCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:update-shortcut')
            ->setDescription('Replaces Model::find()->where([...])->update([...]) with Model::updateAll([...], [...])')
            ->setHelp('Usage: vendor/bin/syntra yii:update-shortcut');
    }

    protected function getRectorRules(): string
    {
        return UpdateAllShortcutRector::class;
    }
}
