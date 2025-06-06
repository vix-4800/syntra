<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PhpCsFixerRefactorer extends SyntraRefactorCommand
{
    public function isAvailable(): bool
    {
        return $this->configLoader->get('refactor.php_cs_fixer.enabled', false);
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('refactor:cs-fixer')
            ->setDescription('')
            ->addForceOption();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return 1;
    }
}
