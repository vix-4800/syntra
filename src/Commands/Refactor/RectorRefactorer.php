<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Enums\CommandGroup;
use Vix\Syntra\Enums\DangerLevel;
use Vix\Syntra\Exceptions\MissingBinaryException;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\Process;
use Vix\Syntra\Facades\Project;

class RectorRefactorer extends SyntraRefactorCommand
{
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
        $binary = find_composer_bin('rector', Project::getRootPath());

        if (!$binary) {
            throw new MissingBinaryException("rector", "composer require --dev rector/rector");
        }

        $this->startProgress();

        $outputCallback = function (): void {
            $this->advanceProgress();
        };

        $result = Process::run($binary, [
            'process',
            $this->path,
            "--config=" . Config::getCommandOption(CommandGroup::REFACTOR->value, self::class, 'config'),
            "--clear-cache",
        ], callback: $outputCallback);

        $this->progressIndicator->setMessage(
            $result->exitCode === 0 ? 'Success!' : 'Error!'
        );

        $this->finishProgress();

        if ($result->exitCode === 0) {
            $this->output->success('Rector refactoring completed.');
        } else {
            $this->output->error('Rector refactoring crashed.');
        }

        return $result->exitCode;
    }
}
