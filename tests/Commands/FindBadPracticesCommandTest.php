<?php

declare(strict_types=1);

namespace Vix\Syntra\Tests\Commands;

use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Tests\CommandTestCase;

class FindBadPracticesCommandTest extends CommandTestCase
{
    public function testDetectsBadPractices(): void
    {
        $code = <<<'PHP'
        <?php
        function foo($a, $b) {
            return $a ? ($b ? 1 : 2) : 3;
        }
        PHP;

        file_put_contents("$this->dir/test.php", $code);

        $tester = $this->runCommand('analyze:find-bad-practices', [
            'path' => $this->dir,
            '--no-progress' => true,
        ]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('Nested ternary operator', $tester->getDisplay());
    }
}
