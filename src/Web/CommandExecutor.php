<?php

declare(strict_types=1);

namespace Vix\Syntra\Web;

use Exception;
use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Utils\ConfigLoader;
use Vix\Syntra\Utils\ExtensionManager;
use Vix\Syntra\Utils\ProcessRunner;

class CommandExecutor
{
    public function __construct(
        private ConfigLoader $configLoader,
        private ProcessRunner $processRunner,
        private ExtensionManager $extensionManager
    ) {
        //
    }

    public function execute(string $commandClass, array $options = []): array
    {
        if (!class_exists($commandClass)) {
            throw new InvalidArgumentException("Command class not found: $commandClass");
        }

        $reflectionClass = new ReflectionClass($commandClass);

        if (!is_subclass_of($commandClass, SyntraCommand::class) || $reflectionClass->isAbstract()) {
            throw new InvalidArgumentException("Invalid command class: $commandClass");
        }

        try {
            $command = $reflectionClass->newInstance(
                $this->configLoader,
                $this->processRunner,
                $this->extensionManager
            );

            // Create input with options
            $inputArray = [];

            // Add path option if provided
            if (isset($options['path'])) {
                $inputArray['--path'] = $options['path'];
            }

            // Add dry-run option if provided
            if (isset($options['dry-run']) && $options['dry-run']) {
                $inputArray['--dry-run'] = true;
            }

            $input = new ArrayInput($inputArray);
            $output = new BufferedOutput();

            // Execute the command
            $startTime = microtime(true);

            try {
                // Create a temporary console application and add the command
                $app = new ConsoleApplication();
                $app->add($command);
                $app->setAutoExit(false);

                // Set command name in input
                $inputArray['command'] = $command->getName();
                $input = new ArrayInput($inputArray);

                // Run the command
                $exitCode = $app->run($input, $output);
            } catch (\Exception $e) {
                $exitCode = 1;
                $output->writeln('<error>Command failed: ' . $e->getMessage() . '</error>');
            }

            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds

            // Get the output content
            $outputContent = $output->fetch();

            // Parse the output to separate different types of messages
            $parsedOutput = $this->parseOutput($outputContent);

            return [
                'success' => $exitCode === 0,
                'exit_code' => $exitCode,
                'execution_time' => $executionTime,
                'output' => $parsedOutput,
                'raw_output' => $outputContent,
            ];
        } catch (Exception $e) {
            throw new RuntimeException("Failed to execute command: " . $e->getMessage(), 0, $e);
        }
    }

    private function parseOutput(string $output): array
    {
        $lines = explode("\n", $output);
        $parsed = [
            'sections' => [],
            'success' => [],
            'errors' => [],
            'warnings' => [],
            'info' => [],
            'tables' => [],
        ];

        $currentSection = null;
        $currentTable = null;
        $tableHeaders = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            // Remove ANSI color codes for parsing
            $cleanLine = $this->removeAnsiCodes($line);

            // Detect sections (lines that look like headers)
            if (preg_match('/^[A-Z].*:$/', $cleanLine) || preg_match('/^=+$/', $cleanLine)) {
                $currentSection = $cleanLine;
                $parsed['sections'][] = $currentSection;
                $currentTable = null;
                continue;
            }

            // Detect success messages
            if (preg_match('/^\[OK\]|✓|SUCCESS/i', $cleanLine)) {
                $parsed['success'][] = $cleanLine;
                continue;
            }

            // Detect error messages
            if (preg_match('/^\[ERROR\]|✗|ERROR|FAILED/i', $cleanLine)) {
                $parsed['errors'][] = $cleanLine;
                continue;
            }

            // Detect warning messages
            if (preg_match('/^\[WARNING\]|⚠|WARNING/i', $cleanLine)) {
                $parsed['warnings'][] = $cleanLine;
                continue;
            }

            // Detect table headers (lines with |)
            if (strpos($cleanLine, '|') !== false && preg_match('/^\|.*\|$/', $cleanLine)) {
                if ($currentTable === null) {
                    $currentTable = count($parsed['tables']);
                    $parsed['tables'][$currentTable] = [
                        'headers' => [],
                        'rows' => []
                    ];

                    // Parse headers
                    $headers = explode('|', trim($cleanLine, '|'));
                    $parsed['tables'][$currentTable]['headers'] = array_map('trim', $headers);
                } else {
                    // Parse table row
                    $cells = explode('|', trim($cleanLine, '|'));
                    $parsed['tables'][$currentTable]['rows'][] = array_map('trim', $cells);
                }
                continue;
            }

            // Everything else is info
            $parsed['info'][] = $cleanLine;
        }

        return $parsed;
    }

    private function removeAnsiCodes(string $text): string
    {
        return preg_replace('/\x1b\[[0-9;]*m/', '', $text);
    }
}
