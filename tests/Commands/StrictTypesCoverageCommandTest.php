<?php

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Application;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\File;

class StrictTypesCoverageCommandTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/syntra_' . uniqid();
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob("$this->dir/*.php"));
        rmdir($this->dir);
    }

    private function runCommand(): CommandTester
    {
        $app = new Application();
        File::clearCache();
        Config::setProjectRoot($this->dir);

        $command = $app->find('analyze:strict-types');
        $tester = new CommandTester($command);
        $tester->execute([
            'path' => $this->dir,
            '--no-progress' => true,
        ]);

        return $tester;
    }

    public function testSuccessWhenAllFilesDeclareStrict(): void
    {
        file_put_contents("$this->dir/a.php", "<?php\ndeclare(strict_types=1);\n");
        file_put_contents("$this->dir/b.php", "<?php\ndeclare(strict_types=1);\n");

        $tester = $this->runCommand();

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('100.0%', $tester->getDisplay());
    }

    public function testFailureWhenMissingDeclarations(): void
    {
        file_put_contents("$this->dir/a.php", "<?php\ndeclare(strict_types=1);\n");
        file_put_contents("$this->dir/b.php", "<?php echo 'no';\n");

        $tester = $this->runCommand();

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('50.0%', $tester->getDisplay());
    }
}
