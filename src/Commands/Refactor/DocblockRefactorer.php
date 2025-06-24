<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

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

    public function perform(): int
    {
        return 1;
    }
}
