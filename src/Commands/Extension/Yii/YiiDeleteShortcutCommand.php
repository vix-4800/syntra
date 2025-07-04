<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\Rector\DeleteAllShortcutRector;

class YiiDeleteShortcutCommand extends YiiRectorCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:delete-shortcut')
            ->setDescription('Replaces Model::find()->where([...])->delete() with Model::deleteAll([...])')
            ->setHelp('');
    }

    protected function getRectorRules(): string
    {
        return DeleteAllShortcutRector::class;
    }
}
