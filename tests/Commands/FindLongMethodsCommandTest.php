<?php

namespace Vix\Syntra\Tests\Commands;

use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Tests\CommandTestCase;

class FindLongMethodsCommandTest extends CommandTestCase
{
    public function testDetectsLongMethod(): void
    {
        $body = str_repeat("    \$a = 1;\n", 4);
        $code = "<?php\nfunction longOne() {\n$body}\n";
        file_put_contents("$this->dir/test.php", $code);

        $tester = $this->runCommand('analyze:find-long-methods', [
            'path' => $this->dir,
            '--no-progress' => true,
            '--max' => 3,
        ]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('longOne', $tester->getDisplay());
    }
}
