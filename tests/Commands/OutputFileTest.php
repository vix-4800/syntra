<?php

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Application;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Facades\Config;

class OutputFileTest extends TestCase
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
        $app = new Application();
        $app->getContainer();
        Config::setProjectRoot(sys_get_temp_dir());

        $command = $this->makeCommand();
        $app->add($command);
        $tester = new CommandTester($command);

        $file = tempnam(sys_get_temp_dir(), 'syntra_log');
        $tester->execute(['--output-file' => $file]);

        $this->assertFileExists($file);
        $this->assertStringContainsString('hello', file_get_contents($file));

        unlink($file);
    }
}
