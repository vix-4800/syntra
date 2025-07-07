<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Vix\Syntra\DTO\ProcessResult;
use Vix\Syntra\Exceptions\MissingBinaryException;

class ProcessRunner
{
    /**
     * @param string               $command
     * @param string[]             $args
     * @param array<string, mixed> $options
     * @param callable|null        $callback
     *
     * @return ProcessResult
     *
     * @throws MissingBinaryException
     */
    public function run(string $command, array $args = [], array $options = [], ?callable $callback = null): ProcessResult
    {
        $cmd = array_merge([$command], $args);

        $process = new Process($cmd, $options['working_dir'] ?? null);
        $process->setTimeout($options['timeout'] ?? 300);

        try {
            $process->mustRun($callback);

            return new ProcessResult(
                $process->getExitCode() ?? 0,
                $process->getOutput(),
                $process->getErrorOutput()
            );
        } catch (ProcessFailedException $e) {
            $process = $e->getProcess();

            return new ProcessResult(
                $process->getExitCode() ?? 1,
                $process->getOutput(),
                $process->getErrorOutput()
            );
        }
    }
}
