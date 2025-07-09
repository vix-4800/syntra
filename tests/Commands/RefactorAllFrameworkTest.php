<?php

declare(strict_types=1);

namespace Vix\Syntra\Tests\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Commands\Extension\Yii\YiiAllCommand;
use Vix\Syntra\Commands\Refactor\RefactorAllCommand;
use Vix\Syntra\Tests\CommandTestCase;

class RefactorAllFrameworkTest extends CommandTestCase
{
    public function testRunsFrameworkCommandWhenOptionEnabled(): void
    {
        file_put_contents("$this->dir/composer.json", json_encode([
            'require' => [
                'yiisoft/yii2' => '*',
            ],
        ]));

        $command = new class () extends RefactorAllCommand {
            public array $executed = [];
            protected function runCommand(string $class, array $input = []): int
            {
                $this->executed[] = $class;
                return Command::SUCCESS;
            }
        };

        $this->app->add($command);
        $tester = new CommandTester($command);
        $tester->execute(['--framework' => true, '--force' => true]);

        $this->assertContains(YiiAllCommand::class, $command->executed);
    }
}
