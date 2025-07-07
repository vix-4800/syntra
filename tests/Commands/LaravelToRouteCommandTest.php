<?php

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Application;
use Vix\Syntra\Commands\Extension\Laravel\LaravelToRouteCommand;
use Vix\Syntra\Commands\RectorRunnerCommand;
use Vix\Syntra\DTO\ProcessResult;
use Vix\Syntra\Utils\ConfigLoader;
use Vix\Syntra\Utils\RectorCommandExecutor;

class LaravelToRouteCommandTest extends TestCase
{
    public function testRunsWithStubbedExecutor(): void
    {
        $app = new Application();
        $container = $app->getContainer();
        $container->get(ConfigLoader::class)->setProjectRoot(sys_get_temp_dir());

        $command = $app->find('laravel:to-route');

        $stub = new class extends RectorCommandExecutor {
            public string $received = '';
            public function executeRule(string $rectorClass, array $additionalArgs = [], ?callable $callback = null): ProcessResult
            {
                $this->received = $rectorClass;
                return new ProcessResult(0, '', '');
            }
        };

        $ref = new ReflectionClass(RectorRunnerCommand::class);
        $prop = $ref->getProperty('rectorExecutor');
        $prop->setAccessible(true);
        $prop->setValue($command, $stub);

        $tester = new CommandTester($command);
        $tester->execute(['path' => sys_get_temp_dir(), '--dry-run' => true, '--no-progress' => true]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertSame(\Vix\Syntra\Commands\Rector\RedirectToRouteRector::class, $stub->received);
    }
}
