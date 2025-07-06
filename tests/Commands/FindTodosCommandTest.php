<?php

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Application;
use Vix\Syntra\Utils\ConfigLoader;
use Vix\Syntra\Utils\FileHelper;

class FindTodosCommandTest extends TestCase
{
    public function testDetectsTodoComments(): void
    {
        $dir = sys_get_temp_dir() . '/syntra_test_' . uniqid();
        mkdir($dir);
        file_put_contents("$dir/sample.php", "<?php\n// TODO: fix me\n");

        FileHelper::clearCache();

        $app = new Application();
        $container = $app->getContainer();
        $container->get(ConfigLoader::class)->setProjectRoot($dir);

        $command = $app->find('analyze:find-todos');
        $tester = new CommandTester($command);
        $tester->execute(['--path' => $dir]);

        $display = $tester->getDisplay();

        $this->assertStringContainsString('TODO', $display);

        unlink("$dir/sample.php");
        rmdir($dir);
    }

    public function testNoProgressOptionDisablesProgressBar(): void
    {
        $dir = sys_get_temp_dir() . '/syntra_test_' . uniqid();
        mkdir($dir);
        file_put_contents("$dir/sample.php", "<?php\n// TODO: fix me\n");

        FileHelper::clearCache();

        $app = new Application();
        $container = $app->getContainer();
        $container->get(ConfigLoader::class)->setProjectRoot($dir);

        $command = $app->find('analyze:find-todos');
        $tester = new CommandTester($command);
        $tester->execute(['--path' => $dir, '--no-progress' => true]);

        $display = $tester->getDisplay();

        $this->assertStringNotContainsString('Remaining:', $display);
        $this->assertStringContainsString('TODO', $display);

        unlink("$dir/sample.php");
        rmdir($dir);
    }
}
