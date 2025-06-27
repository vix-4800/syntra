<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Vix\Syntra\Exceptions\CommandException;

class PhpCsFixerRefactorer extends SyntraRefactorCommand
{
    public function isAvailable(): bool
    {
        return $this->configLoader->get('tools.php_cs_fixer_refactorer.enabled', false);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName('refactor:cs-fixer')
            ->setDescription('Fixes code style using php-cs-fixer for the selected files')
            ->addForceOption();
    }

    public function perform(): int
    {
        $binary = find_composer_bin('php-cs-fixer', $this->configLoader->getProjectRoot());

        if (!$binary) {
            throw new CommandException("php-cs-fixer not installed.");
        }

        $config = $this->configLoader->get('tools.php_cs_fixer_refactorer.config', 'php_cs_fixer.php');

        $result = $this->processRunner->run($binary, [
            'fix',
            $this->configLoader->getProjectRoot(),
            "--config={$config}",
        ]);

        $this->output->success('CS Fixer refactoring completed.');

        return $result->exitCode;
    }
}
