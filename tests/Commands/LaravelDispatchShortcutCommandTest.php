<?php

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Application;
use Vix\Syntra\Commands\Extension\Laravel\LaravelDispatchShortcutCommand;
use Vix\Syntra\DTO\ProcessResult;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\File;
use Vix\Syntra\Utils\ConfigLoader;
use Vix\Syntra\Utils\RectorCommandExecutor;

class LaravelDispatchShortcutCommandTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/syntra_test_' . uniqid();
        mkdir($this->dir);
        File::clearCache();
    }

    protected function tearDown(): void
    {
        $file = "$this->dir/sample.php";
        if (file_exists($file)) {
            unlink($file);
        }
        if (is_dir($this->dir)) {
            rmdir($this->dir);
        }
    }

    private function createFile(string $content): void
    {
        file_put_contents("$this->dir/sample.php", $content);
    }

    private function runCommand(array $options = []): void
    {
        $app = new Application();
        $container = $app->getContainer();
        $container->get(ConfigLoader::class)->setProjectRoot($this->dir);

        /** @var LaravelDispatchShortcutCommand $command */
        $command = $app->find('laravel:dispatch-shortcut');

        $ref = new ReflectionClass(LaravelDispatchShortcutCommand::class);
        $prop = $ref->getProperty('rectorExecutor');
        $prop->setAccessible(true);
        $prop->setValue($command, new class extends RectorCommandExecutor {
            public function executeRule(string $rectorClass, array $additionalArgs = [], ?callable $outputCallback = null): ProcessResult
            {
                $dryRun = in_array('--dry-run', $additionalArgs, true);
                $files = File::collectFiles(Config::getProjectRoot());

                foreach ($files as $file) {
                    $old = file_get_contents($file);
                    $new = preg_replace('/dispatch\s*\(\s*new\s+([\\\\\w]+)\(([^)]*)\)\s*\)/', '$1::dispatch($2)', $old);
                    if ($new !== null && !$dryRun) {
                        File::writeChanges($file, $old, $new);
                    }
                    if ($outputCallback) {
                        $outputCallback();
                    }
                }

                return new ProcessResult(0, '', '');
            }
        });

        $tester = new CommandTester($command);
        $input = array_merge(['path' => $this->dir], $options);
        $tester->execute($input);
    }

    public function testReplacesDispatchCall(): void
    {
        $content = "<?php\ndispatch(new MyJob('a'));";
        $expected = "<?php\nMyJob::dispatch('a');";
        $this->createFile($content);

        $this->runCommand();

        $result = file_get_contents("$this->dir/sample.php");
        $this->assertSame($expected, trim($result));
    }

    public function testDryRunDoesNotModifyFile(): void
    {
        $content = "<?php\ndispatch(new MyJob('a'));";
        $this->createFile($content);

        $this->runCommand(['--dry-run' => true]);

        $result = file_get_contents("$this->dir/sample.php");
        $this->assertSame(trim($content), trim($result));
    }
}
