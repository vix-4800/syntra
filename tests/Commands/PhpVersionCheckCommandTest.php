<?php

declare(strict_types=1);

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use Vix\Syntra\Commands\Health\PhpVersionCheckCommand;
use Vix\Syntra\DI\Container;
use Vix\Syntra\Enums\CommandStatus;
use Vix\Syntra\Facades\Facade;
use Vix\Syntra\Facades\Project;
use Vix\Syntra\Utils\ConfigLoader;

class PhpVersionCheckCommandTest extends TestCase
{
    private static ConfigLoader $config;
    private string $dir;

    public static function setUpBeforeClass(): void
    {
        self::$config = new ConfigLoader();
    }

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/syntra_' . uniqid();
        mkdir($this->dir);
        file_put_contents($this->dir . '/composer.json', json_encode([
            'require' => ['php' => '^8.1'],
        ]));
    }

    protected function tearDown(): void
    {
        unlink($this->dir . '/composer.json');
        rmdir($this->dir);
    }

    private function makeCommand(): PhpVersionCheckCommand
    {
        $container = new Container();
        $container->instance(ConfigLoader::class, self::$config);
        Facade::setContainer($container);

        Project::setRootPath($this->dir);
        return new PhpVersionCheckCommand();
    }

    public function testWarningOnOldVersion(): void
    {
        file_put_contents($this->dir . '/composer.json', json_encode([
            'require' => ['php' => '>=5.6.0'],
        ]));
        $cmd = $this->makeCommand();
        $result = $cmd->runCheck();

        $this->assertSame(CommandStatus::WARNING, $result->status);
    }

    public function testOkOnModernVersion(): void
    {
        $cmd = $this->makeCommand();
        $result = $cmd->runCheck();

        $this->assertSame(CommandStatus::OK, $result->status);
    }
}
