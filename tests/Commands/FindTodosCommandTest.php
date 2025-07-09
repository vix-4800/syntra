<?php

namespace Vix\Syntra\Tests\Commands;

use Vix\Syntra\Tests\CommandTestCase;

class FindTodosCommandTest extends CommandTestCase
{
    public function testDetectsTodoComments(): void
    {
        file_put_contents("$this->dir/sample.php", "<?php\n// TODO: fix me\n");

        $tester = $this->runCommand('analyze:find-todos', ['path' => $this->dir]);

        $display = $tester->getDisplay();

        $this->assertStringContainsString('TODO', $display);
    }

    public function testNoProgressOptionDisablesProgressBar(): void
    {
        file_put_contents("$this->dir/sample.php", "<?php\n// TODO: fix me\n");

        $tester = $this->runCommand('analyze:find-todos', [
            'path' => $this->dir,
            '--no-progress' => true,
        ]);

        $display = $tester->getDisplay();

        $this->assertStringNotContainsString('Remaining:', $display);
        $this->assertStringContainsString('TODO', $display);
    }
}
