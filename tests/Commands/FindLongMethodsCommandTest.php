<?php

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Application;
use Vix\Syntra\Facades\File;
use Vix\Syntra\Facades\Project;

class FindLongMethodsCommandTest extends TestCase
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

    public function testDetectsLongMethod(): void
    {
        $body = str_repeat("    \$a = 1;\n", 4);
        $code = "<?php\nfunction longOne() {\n$body}\n";
        file_put_contents("$this->dir/test.php", $code);

        $app = new Application();
        File::clearCache();
        Project::setRootPath($this->dir);

        $command = $app->find('analyze:find-long-methods');
        $tester = new CommandTester($command);
        $tester->execute([
            'path' => $this->dir,
            '--no-progress' => true,
            '--max' => 3,
        ]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('longOne', $tester->getDisplay());
    }
}
