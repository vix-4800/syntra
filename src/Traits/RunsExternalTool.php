<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

use Vix\Syntra\Facades\Process;
use Vix\Syntra\Tools\ToolInterface;

/**
 * Helper trait for executing external CLI tools with progress feedback.
 */
trait RunsExternalTool
{
    use HasBinaryTool;

    /**
     * Run the given tool and display progress.
     *
     * @param ToolInterface $tool           Tool used for binary lookup
     * @param string[]      $args           Arguments passed to the binary
     * @param string        $successMessage Message shown on success
     * @param string        $errorMessage   Message shown on failure
     */
    protected function runTool(ToolInterface $tool, array $args, string $successMessage, string $errorMessage): int
    {
        $this->findBinaryTool($tool);

        $this->startProgress();

        $outputCallback = function (): void {
            $this->advanceProgress();
        };

        $result = Process::run($this->binary, $args, callback: $outputCallback);

        $this->progressIndicator->setMessage($result->exitCode === 0 ? 'Success!' : 'Error!');

        $this->finishProgress();

        if ($result->exitCode === 0) {
            $this->output->success($successMessage);
        } else {
            $this->output->error($errorMessage);
        }

        return $result->exitCode;
    }
}
