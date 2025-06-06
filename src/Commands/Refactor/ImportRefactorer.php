<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Fixes the order of DocBlock comments and import statements in PHP files.
 *
 * This command finds file-level docblocks (`/** ... *\/`), variable comments like `/** @var Type \$var *\/`,
 * and `use ...;` import statements, and ensures they are placed in the correct order after the <?php tag.
 */
class ImportRefactorer extends SyntraRefactorCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('refactor:imports')
            ->setDescription('Fixes incorrect order of docblocks and import statements in PHP files')
            ->addForceOption();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return 1;
    }
}
