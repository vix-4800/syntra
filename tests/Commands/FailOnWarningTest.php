<?php

declare(strict_types=1);

namespace Vix\Syntra\Tests\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Tests\CommandTestCase;
use Vix\Syntra\Traits\HandlesResultTrait;

class FailOnWarningTest extends CommandTestCase
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
        $command = $this->makeCommand();
        $this->app->add($command);
        $tester = new CommandTester($command);

        $tester->execute(['--fail-on-warning' => true]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
    }

    public function testCiModeFailsOnWarning(): void
    {
        $command = $this->makeCommand();
        $this->app->add($command);
        $tester = new CommandTester($command);

        $tester->execute(['--ci' => true]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
    }
}
