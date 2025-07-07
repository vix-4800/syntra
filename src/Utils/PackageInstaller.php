<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Facades\Process;

/**
 * Simple helper for installing external packages.
 */
class PackageInstaller
{
    /**
     * Run installation command and return standardized result.
     */
    public function install(string $command): CommandResult
    {
        $processResult = Process::run('bash', ['-c', $command]);

        $output = trim($processResult->output ?: $processResult->stderr);
        $messages = $output === '' ? [] : preg_split('/\r?\n/', $output);

        return $processResult->exitCode === 0
            ? CommandResult::ok($messages)
            : CommandResult::error($messages ?: ["Command failed with exit code {$processResult->exitCode}."]);
    }
}
