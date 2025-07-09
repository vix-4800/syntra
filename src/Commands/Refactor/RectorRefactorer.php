<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Enums\CommandGroup;
use Vix\Syntra\Enums\DangerLevel;
use Vix\Syntra\Enums\Tool;
use Vix\Syntra\Facades\Config;
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
        $tool = Tool::RECTOR;

        return $this->runTool(
            $tool,
            [
                'process',
                $this->path,
                "--config=" . Config::getCommandOption(CommandGroup::REFACTOR->value, self::class, 'config'),
                '--clear-cache',
            ],
            $tool->name() . ' refactoring completed.',
            $tool->name() . ' refactoring crashed.'
        );
    }
}
