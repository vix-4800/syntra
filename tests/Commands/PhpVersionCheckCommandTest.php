<?php

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Vix\Syntra\Commands\Health\PhpVersionCheckCommand;
use Vix\Syntra\DI\Container;
use Vix\Syntra\Enums\CommandStatus;
use Vix\Syntra\Facades\Facade;
use Vix\Syntra\Utils\ConfigLoader;

class PhpVersionCheckCommandTest extends TestCase
{
    private static ConfigLoader $config;
    private string $dir;

    public static function setUpBeforeClass(): void
    {
        $ref = new ReflectionClass(ConfigLoader::class);
        /** @var ConfigLoader $cfg */
        $cfg = $ref->newInstanceWithoutConstructor();

        $propRoot = $ref->getProperty('projectRoot');
        $propRoot->setAccessible(true);
        $propRoot->setValue($cfg, sys_get_temp_dir());

        $propCmd = $ref->getProperty('commands');
        $propCmd->setAccessible(true);
        $propCmd->setValue($cfg, require PACKAGE_ROOT . '/config.php');

        self::$config = $cfg;
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
        self::$config->setProjectRoot($this->dir);
        Facade::setContainer($container);
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
