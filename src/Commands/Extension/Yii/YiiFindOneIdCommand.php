<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\Rector\FindOneIdShortcutRector;
use Vix\Syntra\Commands\RectorRunnerCommand;

class YiiFindOneIdCommand extends RectorRunnerCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:find-one-id')
            ->setDescription('Replaces Model::findOne([\'id\' => $id]) with Model::findOne($id)')
            ->setHelp('Usage: vendor/bin/syntra yii:find-one-id');
    }

    protected function getRectorRules(): array
    {
        return [FindOneIdShortcutRector::class];
    }
}
