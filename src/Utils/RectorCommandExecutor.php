<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

use Vix\Syntra\Commands\Refactor\RectorRefactorer;
use Vix\Syntra\DTO\ProcessResult;
use Vix\Syntra\Exceptions\MissingBinaryException;
use Vix\Syntra\Utils\ConfigLoader;
use Vix\Syntra\Utils\ProcessRunner;

/**
 * Utility class for executing Rector commands with specific rules
 */
class RectorCommandExecutor
{
    public function __construct(
        private readonly ConfigLoader $configLoader,
        private readonly ProcessRunner $processRunner
    ) {}

    /**
     * Execute a specific Rector rule
     *
     * @param string $rectorClass The Rector rule class name
     * @param array $additionalArgs Additional arguments to pass to Rector
     *
     * @return ProcessResult
     *
     * @throws MissingBinaryException
     */
    public function executeRule(string $rectorClass, array $additionalArgs = []): ProcessResult
    {
        $binary = $this->findRectorBinary();
        $args = $this->buildRectorArgs($rectorClass, $additionalArgs);

        return $this->processRunner->run($binary, $args);
    }

    /**
     * Execute multiple Rector rules in sequence
     *
     * @param array $rectorClasses Array of Rector rule class names
     * @param array $additionalArgs Additional arguments to pass to Rector
     *
     * @return ProcessResult The result of the last executed rule
     *
     * @throws MissingBinaryException
     */
    public function executeRules(array $rectorClasses, array $additionalArgs = []): ProcessResult
    {
        $binary = $this->findRectorBinary();
        $result = null;

        foreach ($rectorClasses as $rectorClass) {
            $args = $this->buildRectorArgs($rectorClass, $additionalArgs);
            $result = $this->processRunner->run($binary, $args);

            // Stop on first failure
            if ($result->exitCode !== 0) {
                break;
            }
        }

        return $result;
    }

    /**
     * Check if Rector is available
     */
    public function isRectorAvailable(): bool
    {
        try {
            $this->findRectorBinary();
            return true;
        } catch (MissingBinaryException) {
            return false;
        }
    }

    /**
     * Get the path to the Rector binary
     *
     * @throws MissingBinaryException
     */
    private function findRectorBinary(): string
    {
        $binary = find_composer_bin('rector', $this->configLoader->getProjectRoot());

        if (!$binary) {
            throw new MissingBinaryException("rector", "composer require --dev rector/rector");
        }

        return $binary;
    }

    /**
     * Build Rector command arguments
     */
    private function buildRectorArgs(string $rectorClass, array $additionalArgs = []): array
    {
        $args = [
            $this->configLoader->getProjectRoot(),
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
        return $this->configLoader->getCommandOption(
            'refactor',
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
