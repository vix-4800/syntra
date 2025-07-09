<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Enums\CommandGroup;
use Vix\Syntra\Enums\Tool;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Traits\RunsExternalTool;

class PhpCsFixerRefactorer extends SyntraRefactorCommand
{
    use RunsExternalTool;

    protected function configure(): void
    {
        parent::configure();

        $this->setName('refactor:cs-fixer')
            ->setDescription('Fixes code style using php-cs-fixer for the selected files')
            ->setHelp('Usage: vendor/bin/syntra refactor:cs-fixer [--dry-run] [--force]');
    }

    public function perform(): int
    {
        $config = Config::getCommandOption(CommandGroup::REFACTOR->value, self::class, 'config');

        $tool = Tool::PHP_CS_FIXER;

        return $this->runTool(
            $tool,
            [
                'fix',
                $this->path,
                "--config={$config}",
            ],
            $tool->name() . ' refactoring completed.',
            $tool->name() . ' refactoring crashed.'
        );
    }
}
