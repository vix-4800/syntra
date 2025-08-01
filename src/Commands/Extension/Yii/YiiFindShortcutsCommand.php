<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\Rector\FindOneFindAllShortcutRector;
use Vix\Syntra\Commands\RectorRunnerCommand;

class YiiFindShortcutsCommand extends RectorRunnerCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:find-shortcuts')
            ->setDescription('Converts Model::find()->where([...])->one()/all() into Model::findOne([...]) or findAll([...])')
            ->setHelp('Usage: vendor/bin/syntra yii:find-shortcuts');
    }

    protected function getRectorRules(): array
    {
        return [FindOneFindAllShortcutRector::class];
    }
}
