<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\Rector\FindAllIdShortcutRector;
use Vix\Syntra\Commands\RectorRunnerCommand;

class YiiFindAllIdCommand extends RectorRunnerCommand
{
/**
 * Configure the command options.
 */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:find-all-id')
            ->setDescription('Replaces Model::findAll([\'id\' => $id]) with Model::findAll($id)')
            ->setHelp('Usage: vendor/bin/syntra yii:find-all-id');
    }

/**
 * Get a service from the container.rector rules.
 */
    protected function getRectorRules(): string
    {
        return FindAllIdShortcutRector::class;
    }
}
