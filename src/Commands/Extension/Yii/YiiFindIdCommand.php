<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\RectorRunnerCommand;
use Vix\Syntra\Commands\Rector\FindOneIdShortcutRector;

class YiiFindIdCommand extends RectorRunnerCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:find-id')
            ->setDescription('Replaces Model::findOne([\'id\' => $id]) with Model::findOne($id)')
            ->setHelp('');
    }

    protected function getRectorRules(): string
    {
        return FindOneIdShortcutRector::class;
    }
}
