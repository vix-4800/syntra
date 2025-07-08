<?php

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\ExecutableFinder;
use Vix\Syntra\Application;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\File;
use Vix\Syntra\Facades\Project;

class FindTyposCommandTest extends TestCase
{
    public function testDetectsTypos(): void
    {
        $finder = new ExecutableFinder();
        if ($finder->find('aspell') === null) {
            $this->markTestSkipped('Aspell not installed.');
        }

        $dir = sys_get_temp_dir() . '/syntra_typo_' . uniqid();
        mkdir($dir);
        file_put_contents("$dir/teh_file.php", "<?php\n");

        $app = new Application();
        File::clearCache();
        Config::setContainer($app->getContainer());
        Project::setRootPath($dir);

        $command = $app->find('analyze:find-typos');
        $tester = new CommandTester($command);
        $tester->execute(['path' => $dir, '--no-progress' => true]);

        $display = $tester->getDisplay();

        $this->assertStringContainsString('teh', $display);

        unlink("$dir/teh_file.php");
        rmdir($dir);
    }
}
