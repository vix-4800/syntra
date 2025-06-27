<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

class PhpCsFixerRefactorer extends SyntraRefactorCommand
{
    public function isAvailable(): bool
    {
        return $this->configLoader->get('tools.php_cs_fixer.enabled', false);
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('refactor:cs-fixer')
            ->setDescription('Fixes code style using php-cs-fixer for the selected files')
            ->addForceOption();
    }

    public function perform(): int
    {
        return 1;
    }
}
