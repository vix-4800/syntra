<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Enums\CommandGroup;
use Vix\Syntra\Enums\DangerLevel;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Tools\RectorTool;
use Vix\Syntra\Traits\RunsExternalTool;

class RectorRefactorer extends SyntraRefactorCommand
{
    use RunsExternalTool;

    protected DangerLevel $dangerLevel = DangerLevel::HIGH;

    protected function configure(): void
    {
        parent::configure();

        $this->setName('refactor:rector')
            ->setDescription('Runs Rector for automated refactoring')
            ->setHelp('Usage: vendor/bin/syntra refactor:rector [--dry-run] [--force]');
    }

    public function perform(): int
    {
        return $this->runTool(
            new RectorTool(),
            [
                'process',
                $this->path,
                "--config=" . Config::getCommandOption(CommandGroup::REFACTOR->value, self::class, 'config'),
                '--clear-cache',
            ],
            'Rector refactoring completed.',
            'Rector refactoring crashed.'
        );
    }
}
