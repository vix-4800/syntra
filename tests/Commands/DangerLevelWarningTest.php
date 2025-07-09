<?php

namespace Vix\Syntra\Tests\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Enums\DangerLevel;
use Vix\Syntra\Tests\CommandTestCase;

class DangerLevelWarningTest extends CommandTestCase
{
    public function testWarningShownForHighDanger(): void
    {
        $command = new class () extends SyntraRefactorCommand {
            protected DangerLevel $dangerLevel = DangerLevel::HIGH;

            protected function configure(): void
            {
                parent::configure();
                $this->setName('dummy:danger')
                    ->setDescription('desc');
            }

            public function perform(): int
            {
                return Command::SUCCESS;
            }
        };

        $this->app->add($command);
        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertStringContainsString('Warning: the danger level of command "dummy:danger" is marked as', $tester->getDisplay());
    }
}
