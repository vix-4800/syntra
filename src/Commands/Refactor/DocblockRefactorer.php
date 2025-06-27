<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Vix\Syntra\Enums\DangerLevel;

class DocblockRefactorer extends SyntraRefactorCommand
{
    public function isAvailable(): bool
    {
        return $this->configLoader->get('refactor.add_missing_docblocks.enabled', false);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName('refactor:docblocks')
            ->setDescription('')
            ->setDescription('Adds a file-level PHPDoc block to the beginning of the file and a PHPDoc block to each class if it is missing')
            ->setDangerLevel(DangerLevel::MEDIUM)
            ->addForceOption();
    }

    public function perform(): int
    {
        return 1;
    }
}
