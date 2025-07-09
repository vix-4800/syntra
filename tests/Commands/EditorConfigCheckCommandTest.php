<?php

declare(strict_types=1);

namespace Vix\Syntra\Tests\Commands;

use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Commands\Health\EditorConfigCheckCommand;
use Vix\Syntra\DI\Container;
use Vix\Syntra\Enums\CommandStatus;
use Vix\Syntra\Facades\Facade;
use Vix\Syntra\Facades\Project;
use Vix\Syntra\Tests\CommandTestCase;
use Vix\Syntra\Utils\ConfigLoader;

class EditorConfigCheckCommandTest extends CommandTestCase
{
    private function makeCommand(string $root): EditorConfigCheckCommand
    {
        $container = new Container();
        $container->instance(ConfigLoader::class, new ConfigLoader());
        Facade::setContainer($container);
        Project::setRootPath($root);

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
        $command = $this->app->find('health:editorconfig');
        $tester = new CommandTester($command);
        $tester->execute(['path' => $this->dir, '--generate' => true]);

        $this->assertFileExists("$this->dir/.editorconfig");
        $expected = trim((string) file_get_contents(PACKAGE_ROOT . '/stubs/editorconfig.stub'));
        $actual = trim((string) file_get_contents("$this->dir/.editorconfig"));
        $this->assertSame($expected, $actual);
    }
}
