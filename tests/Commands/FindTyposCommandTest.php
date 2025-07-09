<?php

declare(strict_types=1);

namespace Vix\Syntra\Tests\Commands;

use Symfony\Component\Process\ExecutableFinder;
use Vix\Syntra\Tests\CommandTestCase;

class FindTyposCommandTest extends CommandTestCase
{
    public function testDetectsTypos(): void
    {
        $finder = new ExecutableFinder();
        if ($finder->find('aspell') === null) {
            $this->markTestSkipped('Aspell not installed.');
        }

        file_put_contents("$this->dir/teh_file.php", "<?php\n");

        $tester = $this->runCommand('analyze:find-typos', [
            'path' => $this->dir,
            '--no-progress' => true,
        ]);

        $display = $tester->getDisplay();

        $this->assertStringContainsString('teh', $display);
    }
}
