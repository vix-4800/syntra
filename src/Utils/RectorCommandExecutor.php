<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

use Vix\Syntra\Commands\Refactor\RectorRefactorer;
use Vix\Syntra\DTO\ProcessResult;
use Vix\Syntra\Enums\CommandGroup;
use Vix\Syntra\Exceptions\MissingBinaryException;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\Process;
use Vix\Syntra\Tools\RectorTool;
use Vix\Syntra\Traits\HasBinaryTool;

/**
 * Utility class for executing Rector commands with specific rules
 */
class RectorCommandExecutor
{
    use HasBinaryTool;

    /**
     * Execute Rector rule(s)
     *
     * @param string[] $rectorClasses  Rector rule class name(s)
     * @param array    $additionalArgs Additional arguments to pass to Rector
     *
     * @return ProcessResult The result of the last executed rule. If no rules
     *                        are provided, a successful empty result is returned.
     *
     * @throws MissingBinaryException
     */
    public function executeRules(string $path, array $rectorClasses, array $additionalArgs = [], ?callable $outputCallback = null): ProcessResult
    {
        if ($rectorClasses === []) {
            // No rules to execute, return a successful default result
            return new ProcessResult(0, '', '');
        }

        $this->findBinaryTool(new RectorTool());
        $result = null;

        foreach ($rectorClasses as $rectorClass) {
            $args = $this->buildRectorArgs($path, $rectorClass, $additionalArgs);
            $result = Process::run($this->binary, $args, callback: $outputCallback);

            // Stop on first failure
            if ($result->exitCode !== 0) {
                break;
            }
        }

        return $result;
    }

    /**
     * Build Rector command arguments
     */
    private function buildRectorArgs(string $path, string $rectorClass, array $additionalArgs = []): array
    {
        $args = [
            $path,
            "--config=" . $this->getRectorConfig(),
            "--only=" . $this->normalizeRectorClassName($rectorClass),
            "--clear-cache",
        ];

        return array_merge($args, $additionalArgs);
    }

    /**
     * Get the Rector configuration file path
     */
    private function getRectorConfig(): string
    {
        return Config::getCommandOption(
            CommandGroup::REFACTOR->value,
            RectorRefactorer::class,
            'commands_config'
        );
    }

    /**
     * Normalize Rector class name by removing ::class suffix
     */
    private function normalizeRectorClassName(string $className): string
    {
        return str_replace("::class", "", $className);
    }
}
