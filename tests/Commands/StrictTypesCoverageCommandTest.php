<?php

namespace Vix\Syntra\Tests\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Vix\Syntra\Tests\CommandTestCase;

class StrictTypesCoverageCommandTest extends CommandTestCase
{

    public function testSuccessWhenAllFilesDeclareStrict(): void
    {
        file_put_contents("$this->dir/a.php", "<?php\ndeclare(strict_types=1);\n");
        file_put_contents("$this->dir/b.php", "<?php\ndeclare(strict_types=1);\n");

        $tester = $this->runCommand('analyze:strict-types', [
            'path' => $this->dir,
            '--no-progress' => true,
        ]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('100.0%', $tester->getDisplay());
    }

    public function testFailureWhenMissingDeclarations(): void
    {
        file_put_contents("$this->dir/a.php", "<?php\ndeclare(strict_types=1);\n");
        file_put_contents("$this->dir/b.php", "<?php echo 'no';\n");

        $tester = $this->runCommand('analyze:strict-types', [
            'path' => $this->dir,
            '--no-progress' => true,
        ]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('50.0%', $tester->getDisplay());
    }
}
