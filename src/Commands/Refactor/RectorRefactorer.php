<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Vix\Syntra\Exceptions\CommandException;

class RectorRefactorer extends SyntraRefactorCommand
{
    public function isAvailable(): bool
    {
        return $this->configLoader->get('tools.rector.enabled', false);
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('refactor:rector')
            ->setDescription('')
            ->addForceOption();
    }

    public function perform(): int
    {
        $binary = find_composer_bin('rector', $this->configLoader->getProjectRoot());

        if (!$binary) {
            throw new CommandException("rector not installed.");
        }

        $result = $this->processRunner->run(
            $binary,
            [
                'process',
                $this->configLoader->getProjectRoot(),
                "--config=" . $this->configLoader->get('tools.rector.config'),
            ],
        );

        $this->output->success('Rector refactoring completed.');

        return $result->exitCode;
    }
}
