<?php

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Application;
use Vix\Syntra\Commands\Refactor\RefactorAllCommand;
use Vix\Syntra\Utils\ConfigLoader;
use Vix\Syntra\Utils\FileHelper;

class RefactorAllFrameworkTest extends TestCase
{
    public function testRunsFrameworkCommandWhenOptionEnabled(): void
    {
        $dir = sys_get_temp_dir() . '/syntra_test_' . uniqid();
        mkdir($dir);
        file_put_contents("$dir/composer.json", json_encode([
            'require' => [
                'yiisoft/yii2' => '*',
            ],
        ]));

        FileHelper::clearCache();

        $app = new Application();
        $container = $app->getContainer();
        $container->get(ConfigLoader::class)->setProjectRoot($dir);

        $command = new class extends RefactorAllCommand {
            public array $executed = [];
            protected function runCommand(string $class, array $input = []): int
            {
                $this->executed[] = $class;
                return Command::SUCCESS;
            }
        };

        $app->add($command);
        $tester = new CommandTester($command);
        $tester->execute(['--framework' => true, '--force' => true]);

        $this->assertContains('Vix\\Syntra\\Commands\\Extension\\Yii\\YiiAllCommand', $command->executed);

        unlink("$dir/composer.json");
        rmdir($dir);
    }
}
