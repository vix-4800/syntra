<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DocblockRefactorer extends SyntraRefactorCommand
{
    public function isAvailable(): bool
    {
        return $this->configLoader->get('refactor.add_missing_docblocks.enabled', false);
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('refactor:docblocks')
            ->setDescription('')
            ->addForceOption();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return 1;
    }
}
