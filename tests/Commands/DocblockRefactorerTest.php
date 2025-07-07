<?php

namespace Vix\Syntra\Tests\Commands;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Application;
use Vix\Syntra\Utils\ConfigLoader;
use Vix\Syntra\Utils\FileHelper;

class DocblockRefactorerTest extends TestCase
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

    private function runCommand(): string
    {
        FileHelper::clearCache();

        $app = new Application();
        $container = $app->getContainer();
        $container->get(ConfigLoader::class)->setProjectRoot($this->dir);

        $command = $app->find('refactor:docblocks');
        $tester = new CommandTester($command);
        $tester->execute([
            'path' => $this->dir,
            '--force' => true,
            '--no-progress' => true,
        ]);

        return file_get_contents("$this->dir/sample.php");
    }

    public function testDocblockInsertedBeforeFinalClass(): void
    {
        $content = "<?php\nfinal class Foo {}\n";
        $this->createFile($content);

        $result = $this->runCommand();

        $this->assertMatchesRegularExpression('/\/\*\*.*Class.*\*\/\s*final class/s', $result);
        $this->assertDoesNotMatchRegularExpression('/final\s*\n\s*\/\*/', $result);
    }

    public function testDocblockInsertedBeforeAbstractClass(): void
    {
        $content = "<?php\nabstract class Bar {}\n";
        $this->createFile($content);

        $result = $this->runCommand();

        $this->assertMatchesRegularExpression('/\/\*\*.*Class.*\*\/\s*abstract class/s', $result);
        $this->assertDoesNotMatchRegularExpression('/abstract\s*\n\s*\/\*/', $result);
    }

    public function testDocblockInsertedBeforeReadonlyClass(): void
    {
        $content = "<?php\nreadonly class Baz {}\n";
        $this->createFile($content);

        $result = $this->runCommand();

        $this->assertMatchesRegularExpression('/\/\*\*.*Class.*\*\/\s*readonly class/s', $result);
        $this->assertDoesNotMatchRegularExpression('/readonly\s*\n\s*\/\*/', $result);
    }

    public function testDocblockInsertedBeforeFinalReadonlyClass(): void
    {
        $content = "<?php\nfinal readonly class Qux {}\n";
        $this->createFile($content);

        $result = $this->runCommand();

        $this->assertMatchesRegularExpression('/\/\*\*.*Class.*\*\/\s*final readonly class/s', $result);
        $this->assertDoesNotMatchRegularExpression('/readonly\s*\n\s*\/\*/', $result);
    }
}
