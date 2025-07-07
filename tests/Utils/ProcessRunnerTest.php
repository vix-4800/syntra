<?php

namespace Vix\Syntra\Tests\Utils;

use PHPUnit\Framework\TestCase;
use Vix\Syntra\Facades\Process;
use Vix\Syntra\Application;

class ProcessRunnerTest extends TestCase
{
    public function testRunSuccessfulCommand(): void
    {
        new Application();
        $result = Process::run('php', ['-r', 'echo "hello";']);

        $this->assertSame(0, $result->exitCode);
        $this->assertSame('hello', trim($result->output));
    }

    public function testRunFails(): void
    {
        new Application();
        $result = Process::run('php', ['-r', 'fwrite(STDERR, "err"); exit(1);']);

        $this->assertSame(1, $result->exitCode);
        $this->assertSame('err', trim($result->stderr));
    }
}
