<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Enums\DangerLevel;
use Vix\Syntra\Exceptions\MissingBinaryException;

class RectorRefactorer extends SyntraRefactorCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->setName('refactor:rector')
            ->setDescription('Runs Rector for automated refactoring')
            ->setHelp('Usage: vendor/bin/syntra refactor:rector [--dry-run] [--force]')
            ->setDangerLevel(DangerLevel::HIGH);
    }

    public function perform(): int
    {
        $binary = find_composer_bin('rector', $this->configLoader->getProjectRoot());

        if (!$binary) {
            throw new MissingBinaryException("rector", "composer require --dev rector/rector");
        }

        $this->startProgress();

        $outputCallback = function (): void {
            $this->advanceProgress();
        };

        $result = $this->processRunner->run($binary, [
            'process',
            $this->configLoader->getProjectRoot(),
            "--config=" . $this->configLoader->getCommandOption('refactor', self::class, 'config'),
            "--clear-cache",
        ]);

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
