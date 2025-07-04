<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Exceptions\MissingBinaryException;

class PhpCsFixerRefactorer extends SyntraRefactorCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->setName('refactor:cs-fixer')
            ->setDescription('Fixes code style using php-cs-fixer for the selected files')
            ->setHelp('Usage: vendor/bin/syntra refactor:cs-fixer [--dry-run] [--force]');
    }

    public function perform(): int
    {
        $binary = find_composer_bin('php-cs-fixer', $this->configLoader->getProjectRoot());

        if (!$binary) {
            throw new MissingBinaryException("php-cs-fixer", "composer require --dev friendsofphp/php-cs-fixer");
        }

        $config = $this->configLoader->getCommandOption('refactor', self::class, 'config');

        $result = $this->processRunner->run($binary, [
            'fix',
            $this->configLoader->getProjectRoot(),
            "--config={$config}",
        ]);

        if ($result->exitCode === 0) {
            $this->output->success('CS Fixer refactoring completed.');
        } else {
            $this->output->error('CS Fixer refactoring crashed.');
        }

        return $result->exitCode;
    }
}
