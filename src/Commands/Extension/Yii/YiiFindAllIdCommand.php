<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\Rector\FindAllIdShortcutRector;
use Vix\Syntra\Commands\RectorRunnerCommand;

class YiiFindAllIdCommand extends RectorRunnerCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:find-all-id')
            ->setDescription('Replaces Model::findAll([\'id\' => $id]) with Model::findAll($id)')
            ->setHelp('Usage: vendor/bin/syntra yii:find-all-id');
    }

    protected function getRectorRules(): array
    {
        return [FindAllIdShortcutRector::class];
    }
}
