<?php

declare(strict_types=1);

namespace Vix\Syntra\Tests\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Tests\CommandTestCase;

class DirectoryNotFoundTest extends CommandTestCase
{
    public function testShowsErrorForMissingPath(): void
    {
        $command = new class () extends SyntraCommand {
            protected function configure(): void
            {
                parent::configure();
                $this->setName('dummy:missing');
            }

            public function perform(): int
            {
                return Command::SUCCESS;
            }
        };

        $this->app->add($command);
        $tester = new CommandTester($command);

        $tester->execute(['path' => $this->dir . '/missing']);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('does not exist', $tester->getDisplay());
    }
}
