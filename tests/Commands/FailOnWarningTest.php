<?php

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Application;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Traits\HandlesResultTrait;
use Vix\Syntra\Facades\Config;

class FailOnWarningTest extends TestCase
{
    private function makeCommand(): SyntraCommand
    {
        return new class () extends SyntraCommand {
            use HandlesResultTrait;

            protected function configure(): void
            {
                parent::configure();
                $this->setName('dummy:warn');
            }

            public function perform(): int
            {
                $result = CommandResult::warning(['warn']);
                return $this->handleResult($result, 'done', $this->failOnWarning);
            }
        };
    }

    public function testFailOnWarningOption(): void
    {
        $app = new Application();
        $app->getContainer();
        Config::setProjectRoot(sys_get_temp_dir());

        $command = $this->makeCommand();
        $app->add($command);
        $tester = new CommandTester($command);

        $tester->execute(['--fail-on-warning' => true]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
    }

    public function testCiModeFailsOnWarning(): void
    {
        $app = new Application();
        $app->getContainer();
        Config::setProjectRoot(sys_get_temp_dir());

        $command = $this->makeCommand();
        $app->add($command);
        $tester = new CommandTester($command);

        $tester->execute(['--ci' => true]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
    }
}
