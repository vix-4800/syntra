<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\Rector\FindOneFindAllShortcutRector;

class YiiFindShortcutsCommand extends YiiRectorCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:find-shortcuts')
            ->setDescription('Converts Model::find()->where([...])->one()/all() into Model::findOne([...]) or findAll([...])')
            ->setHelp('');
    }

    protected function getRectorRules(): string
    {
        return FindOneFindAllShortcutRector::class;
    }
}
