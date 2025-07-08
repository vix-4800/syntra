<?php

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use Vix\Syntra\Commands\Health\SecurityCheckCommand;
use Vix\Syntra\DI\Container;
use Vix\Syntra\DTO\ProcessResult;
use Vix\Syntra\Enums\CommandStatus;
use Vix\Syntra\Facades\Facade;
use Vix\Syntra\Facades\Project;
use Vix\Syntra\Utils\ConfigLoader;
use Vix\Syntra\Utils\ProcessRunner;

class SecurityCheckerTest extends TestCase
{
    private static ConfigLoader $config;

    public static function setUpBeforeClass(): void
    {
        self::$config = new ConfigLoader();
    }

    private function makeCommand(ProcessResult $result): SecurityCheckCommand
    {
        $runner = new class ($result) extends ProcessRunner {
            public function __construct(private readonly ProcessResult $result)
            {
                //
            }

            public function run(string $command, array $args = [], array $options = [], ?callable $callback = null): ProcessResult
            {
                return $this->result;
            }
        };

        $container = new Container();
        $container->instance(ConfigLoader::class, self::$config);
        $container->instance(ProcessRunner::class, $runner);
        Facade::setContainer($container);
        Project::setRootPath(sys_get_temp_dir());
        return new SecurityCheckCommand();
    }

    public function testOkWhenNoAdvisories(): void
    {
        $json = json_encode(['advisories' => [], 'abandoned' => []]);
        $command = $this->makeCommand(new ProcessResult(0, $json, ''));
        $result = $command->runCheck();

        $this->assertSame(CommandStatus::OK, $result->status);
    }

    public function testWarningsWhenAdvisoriesPresent(): void
    {
        $json = json_encode([
            'advisories' => [
                'pkg/a' => [
                    ['link' => 'https://example.com/a'],
                ],
                'pkg/b' => [
                    ['link' => 'https://example.com/b'],
                ],
            ],
        ]);

        $command = $this->makeCommand(new ProcessResult(0, $json, ''));
        $result = $command->runCheck();

        $this->assertSame(CommandStatus::WARNING, $result->status);
        $this->assertContains('pkg/a: https://example.com/a', $result->messages);
        $this->assertContains('pkg/b: https://example.com/b', $result->messages);
    }
}
