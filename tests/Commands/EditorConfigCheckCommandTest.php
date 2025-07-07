<?php

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Application;
use Vix\Syntra\Commands\Health\EditorConfigCheckCommand;
use Vix\Syntra\DI\Container;
use Vix\Syntra\Enums\CommandStatus;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\Facade;
use Vix\Syntra\Utils\ConfigLoader;

class EditorConfigCheckCommandTest extends TestCase
{
    private function makeCommand(string $root): EditorConfigCheckCommand
    {
        $container = new Container();
        $container->instance(ConfigLoader::class, new ConfigLoader($root));
        Facade::setContainer($container);

        return new EditorConfigCheckCommand();
    }

    public function testOkWhenFileExists(): void
    {
        $dir = sys_get_temp_dir() . '/syntra_test_' . uniqid();
        mkdir($dir);
        file_put_contents($dir . '/.editorconfig', "root = true\n");

        $cmd = $this->makeCommand($dir);
        $result = $cmd->runCheck();

        $this->assertSame(CommandStatus::OK, $result->status);

        unlink($dir . '/.editorconfig');
        rmdir($dir);
    }

    public function testWarningWhenFileMissing(): void
    {
        $dir = sys_get_temp_dir() . '/syntra_test_' . uniqid();
        mkdir($dir);

        $cmd = $this->makeCommand($dir);
        $result = $cmd->runCheck();

        $this->assertSame(CommandStatus::WARNING, $result->status);
        $this->assertStringContainsString('file not found', implode('\n', $result->messages));

        rmdir($dir);
    }

    public function testGenerateOptionCreatesFile(): void
    {
        $dir = sys_get_temp_dir() . '/syntra_test_' . uniqid();
        mkdir($dir);

        $app = new Application();
        $app->getContainer();
        Config::setProjectRoot($dir);

        $command = $app->find('health:editorconfig');
        $tester = new CommandTester($command);
        $tester->execute(['path' => $dir, '--generate' => true]);

        $this->assertFileExists("$dir/.editorconfig");
        $ref = new ReflectionClass(EditorConfigCheckCommand::class);
        $expected = trim((string) $ref->getConstant('DEFAULT_CONFIG'));
        $actual = trim((string) file_get_contents("$dir/.editorconfig"));
        $this->assertSame($expected, $actual);

        unlink("$dir/.editorconfig");
        rmdir($dir);
    }
}
