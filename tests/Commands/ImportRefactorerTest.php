<?php

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Application;
use Vix\Syntra\Utils\ConfigLoader;

class ImportRefactorerTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/syntra_test_' . uniqid();
        mkdir($this->dir);
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

        $command = $app->find('refactor:imports');
        $tester = new CommandTester($command);
        $tester->execute(array_merge(['--path' => $this->dir], $options));
    }

    public function testReordersHeaderBlocks(): void
    {
        $content = <<<'PHP'
<?php
use Foo\Bar\B;

/** @var Baz $baz */

namespace Foo\Bar;

use Foo\Bar\A;

/**
 * File doc
 */

echo 'done';
PHP;
        $expected = <<<'PHP'
<?php

/**
 * File doc
 */

namespace Foo\Bar;

use Foo\Bar\B;
use Foo\Bar\A;

/** @var Baz $baz */

echo 'done';
PHP;
        $this->createFile($content);

        $this->runCommand();

        $result = file_get_contents("$this->dir/sample.php");

        $this->assertSame(trim($expected), trim($result));
    }

    public function testDryRunDoesNotModifyFile(): void
    {
        $content = <<<'PHP'
<?php
use Foo\Bar\B;

/** @var Baz $baz */

namespace Foo\Bar;

use Foo\Bar\A;

/**
 * File doc
 */

echo 'done';
PHP;
        $this->createFile($content);

        $this->runCommand(['--dry-run' => true]);

        $result = file_get_contents("$this->dir/sample.php");

        $this->assertSame(trim($content), trim($result));
    }
}
