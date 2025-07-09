<?php

declare(strict_types=1);

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Application;
use Vix\Syntra\Commands\General\InitCommand;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\Facade;
use Vix\Syntra\Facades\Cache;
use Vix\Syntra\Facades\Project;
use Vix\Syntra\Tests\Fixtures\DummyInstaller;
use Vix\Syntra\Utils\PackageInstaller;

class InitCommandTest extends TestCase
{
    private function createApp(string $dir, DummyInstaller $installer): Application
    {
        $app = new Application();
        Cache::clearAll();
        Config::setContainer($app->getContainer());
        Project::setRootPath($dir);

        $container = $app->getContainer();
        $container->instance(PackageInstaller::class, $installer);
        Facade::setContainer($container);

        $cmd = new InitCommand();
        $app->add($cmd);

        return $app;
    }

    public function testCopiesConfigFiles(): void
    {
        $dir = sys_get_temp_dir() . '/syntra_' . uniqid();
        mkdir($dir);

        $installer = new DummyInstaller();
        $app = $this->createApp($dir, $installer);

        $command = $app->find('general:init');
        $tester = new CommandTester($command);
        $tester->setInputs(['no', 'no', 'no', 'no']);
        $tester->execute(['path' => $dir]);

        $this->assertFileExists("$dir/syntra.php");
        $this->assertFileExists("$dir/config/php_cs_fixer.php");
        $this->assertFileExists("$dir/config/phpstan.neon");
        $this->assertFileExists("$dir/config/rector.php");
        $this->assertFileExists("$dir/config/rector_only_custom.php");

        // cleanup
        unlink("$dir/syntra.php");
        unlink("$dir/config/php_cs_fixer.php");
        unlink("$dir/config/phpstan.neon");
        unlink("$dir/config/rector.php");
        unlink("$dir/config/rector_only_custom.php");
        rmdir("$dir/config");
        rmdir($dir);
    }

    public function testInstallsSelectedPackages(): void
    {
        $dir = sys_get_temp_dir() . '/syntra_' . uniqid();
        mkdir($dir);

        $installer = new DummyInstaller();
        $app = $this->createApp($dir, $installer);

        $command = $app->find('general:init');
        $tester = new CommandTester($command);
        $tester->setInputs(['yes', 'no', 'yes', 'no']);
        $tester->execute(['path' => $dir]);

        $this->assertSame([
            'composer require --dev rector/rector',
            'composer require --dev phpstan/phpstan',
        ], $installer->commands);

        // cleanup
        unlink("$dir/syntra.php");
        unlink("$dir/config/php_cs_fixer.php");
        unlink("$dir/config/phpstan.neon");
        unlink("$dir/config/rector.php");
        unlink("$dir/config/rector_only_custom.php");
        rmdir("$dir/config");
        rmdir($dir);
    }
}
