<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands;

use Exception;
use Vix\Syntra\Enums\Tool;
use Vix\Syntra\Facades\Rector;

/**
 * Generic command base for running specific Rector rules.
 */
abstract class RectorRunnerCommand extends SyntraRefactorCommand
{
    /**
     * Return Rector rule class(es) to execute.
     *
     * @return string[] Array of Rector rule class names
     */
    abstract protected function getRectorRules(): array;

    /**
     * Build additional Rector CLI arguments.
     */
    protected function getAdditionalArgs(): array
    {
        $args = [];

        if ($this->dryRun) {
            $args[] = '--dry-run';
        }

        return $args;
    }

    /**
     * Message shown when Rector succeeds.
     */
    protected function getSuccessMessage(): string
    {
        return Tool::RECTOR->name() . ' refactoring completed.';
    }

    /**
     * Message shown when Rector fails.
     */
    protected function getErrorMessage(): string
    {
        return Tool::RECTOR->name() . ' refactoring crashed.';
    }

    /**
     * Execute the Rector rule(s).
     */
    public function perform(): int
    {
        $rules = $this->getRectorRules();
        $additionalArgs = $this->getAdditionalArgs();

        $this->startProgress();

        $outputCallback = function (): void {
            $this->advanceProgress();
        };

        try {
            $result = Rector::executeRules($this->path, $rules, $additionalArgs, $outputCallback);

            $this->progressIndicator->setMessage(
                $result->exitCode === 0 ? $this->getSuccessMessage() : $this->getErrorMessage()
            );

            return $result->exitCode;
        } catch (Exception $e) {
            $this->progressIndicator->setMessage('Error!');
            $this->output->error('Failed to execute Rector: ' . $e->getMessage());
            return self::FAILURE;
        } finally {
            $this->finishProgress();
        }
    }
}
