<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\RectorRunnerCommand;
use Vix\Syntra\Commands\Rector\CanHelpersRector;
use Vix\Syntra\Commands\Rector\ConvertAccessChainRector;
use Vix\Syntra\Commands\Rector\DeleteAllShortcutRector;
use Vix\Syntra\Commands\Rector\FindAllIdShortcutRector;
use Vix\Syntra\Commands\Rector\FindOneFindAllShortcutRector;
use Vix\Syntra\Commands\Rector\FindOneIdShortcutRector;
use Vix\Syntra\Commands\Rector\UpdateAllShortcutRector;
use Vix\Syntra\Commands\Rector\UserFindOneToIdentityRector;

class YiiAllCommand extends RectorRunnerCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:all')
            ->setDescription('Runs all Yii-specific Rector refactorings in sequence')
            ->setHelp('Usage: vendor/bin/syntra yii:all');
    }

    protected function getRectorRules(): array
    {
        return [
            FindOneFindAllShortcutRector::class,
            FindOneIdShortcutRector::class,
            FindAllIdShortcutRector::class,
            UpdateAllShortcutRector::class,
            DeleteAllShortcutRector::class,
            CanHelpersRector::class,
            ConvertAccessChainRector::class,
            UserFindOneToIdentityRector::class,
        ];
    }

    protected function getSuccessMessage(): string
    {
        return 'All Yii Rector refactorings completed.';
    }

    protected function getErrorMessage(): string
    {
        return 'Yii Rector refactoring crashed.';
    }
}
