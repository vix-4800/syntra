<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands;

use Exception;
use Vix\Syntra\Utils\RectorCommandExecutor;

/**
 * Generic command base for running specific Rector rules.
 */
abstract class RectorRunnerCommand extends SyntraRefactorCommand
{
    protected RectorCommandExecutor $rectorExecutor;

    public function __construct()
    {
        parent::__construct();
        $this->rectorExecutor = new RectorCommandExecutor();
    }

    /**
     * Return Rector rule class(es) to execute.
     *
     * @return string|array
     */
    abstract protected function getRectorRules(): string|array;

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
        return 'Rector refactoring completed.';
    }

    /**
     * Message shown when Rector fails.
     */
    protected function getErrorMessage(): string
    {
        return 'Rector refactoring crashed.';
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
            $result = is_array($rules)
                ? $this->rectorExecutor->executeRules($rules, $additionalArgs, $outputCallback)
                : $this->rectorExecutor->executeRule($rules, $additionalArgs, $outputCallback);

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
