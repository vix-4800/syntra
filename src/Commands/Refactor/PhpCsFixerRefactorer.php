<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Enums\CommandGroup;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\Process;
use Vix\Syntra\Tools\PhpCsFixerTool;
use Vix\Syntra\Traits\HasBinaryTool;

class PhpCsFixerRefactorer extends SyntraRefactorCommand
{
    use HasBinaryTool;

    protected function configure(): void
    {
        parent::configure();

        $this->setName('refactor:cs-fixer')
            ->setDescription('Fixes code style using php-cs-fixer for the selected files')
            ->setHelp('Usage: vendor/bin/syntra refactor:cs-fixer [--dry-run] [--force]');
    }

    public function perform(): int
    {
        $this->findBinaryTool(new PhpCsFixerTool());

        $this->startProgress();

        $outputCallback = function (): void {
            $this->advanceProgress();
        };

        $config = Config::getCommandOption(CommandGroup::REFACTOR->value, self::class, 'config');

        $result = Process::run($this->binary, [
            'fix',
            $this->path,
            "--config={$config}",
        ], callback: $outputCallback);

        $this->progressIndicator->setMessage(
            $result->exitCode === 0 ? 'Success!' : 'Error!'
        );

        $this->finishProgress();

        if ($result->exitCode === 0) {
            $this->output->success('CS Fixer refactoring completed.');
        } else {
            $this->output->error('CS Fixer refactoring crashed.');
        }

        return $result->exitCode;
    }
}
