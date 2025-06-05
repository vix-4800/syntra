<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ProcessRunner
{
    /**
     * @param string $command
     * @param string[] $args
     * @param array<string, mixed> $options
     *
     * @return array{exitCode:int, output:string, stderr:string}
     */
    public function run(string $command, array $args = [], array $options = []): array
    {
        $cmd = array_merge([$command], $args);

        $process = new Process($cmd, $options['working_dir'] ?? null);
        $process->setTimeout($options['timeout'] ?? 300);

        try {
            $process->mustRun();

            return [
                'exitCode' => $process->getExitCode() ?? 0,
                'output' => $process->getOutput(),
                'stderr' => $process->getErrorOutput(),
            ];
        } catch (ProcessFailedException $e) {
            $proc = $e->getProcess();

            return [
                'exitCode' => $proc->getExitCode() ?? 1,
                'output' => $proc->getOutput(),
                'stderr' => $proc->getErrorOutput(),
            ];
        }
    }
}
