<?php

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Application;
use Vix\Syntra\Facades\File;
use Vix\Syntra\Facades\Project;

class FindBadPracticesCommandTest extends TestCase
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

    public function testDetectsBadPractices(): void
    {
        $code = <<<'PHP'
<?php
function foo($a, $b) {
    return $a ? ($b ? 1 : 2) : 3;
}
PHP;
        file_put_contents("$this->dir/test.php", $code);

        $app = new Application();
        File::clearCache();
        Project::setRootPath($this->dir);

        $command = $app->find('analyze:find-bad-practices');
        $tester = new CommandTester($command);
        $tester->execute([
            'path' => $this->dir,
            '--no-progress' => true,
        ]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('Nested ternary operator', $tester->getDisplay());
    }
}
