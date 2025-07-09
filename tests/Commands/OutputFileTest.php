<?php

declare(strict_types=1);

namespace Vix\Syntra\Tests\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Tests\CommandTestCase;

class OutputFileTest extends CommandTestCase
{
    private function makeCommand(): SyntraCommand
    {
        return new class () extends SyntraCommand {
            protected function configure(): void
            {
                parent::configure();
                $this->setName('dummy:output');
            }

            public function perform(): int
            {
                $this->output->writeln('hello');
                return Command::SUCCESS;
            }
        };
    }

    public function testWritesToOutputFile(): void
    {
        $command = $this->makeCommand();
        $this->app->add($command);
        $tester = new CommandTester($command);

        $file = tempnam(sys_get_temp_dir(), 'syntra_log');
        $tester->execute(['--output-file' => $file]);

        $this->assertFileExists($file);
        $this->assertStringContainsString('hello', file_get_contents($file));

        unlink($file);
    }
}
