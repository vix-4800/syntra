<?php

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Application;
use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Enums\DangerLevel;
use Vix\Syntra\Facades\Project;

class DangerLevelWarningTest extends TestCase
{
    public function testWarningShownForHighDanger(): void
    {
        $app = new Application();
        Project::setRootPath(sys_get_temp_dir());

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

        $app->add($command);
        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertStringContainsString('Warning: the danger level of command "dummy:danger" is marked as', $tester->getDisplay());
    }
}
