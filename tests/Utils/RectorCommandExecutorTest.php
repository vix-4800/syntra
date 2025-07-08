<?php

namespace Vix\Syntra\Tests\Utils;

use PHPUnit\Framework\TestCase;
use Vix\Syntra\Application;
use Vix\Syntra\DTO\ProcessResult;
use Vix\Syntra\Utils\RectorCommandExecutor;

class RectorCommandExecutorTest extends TestCase
{
    public function testReturnsDefaultResultWhenNoRulesProvided(): void
    {
        new Application();
        $executor = new RectorCommandExecutor();

        $result = $executor->executeRules(sys_get_temp_dir(), []);

        $this->assertInstanceOf(ProcessResult::class, $result);
        $this->assertSame(0, $result->exitCode);
        $this->assertSame('', $result->output);
        $this->assertSame('', $result->stderr);
    }
}

