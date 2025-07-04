<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Exception;
use Vix\Syntra\Commands\SyntraRefactorCommand;
use Vix\Syntra\Utils\ConfigLoader;
use Vix\Syntra\Utils\ExtensionManager;
use Vix\Syntra\Utils\ProcessRunner;
use Vix\Syntra\Utils\RectorCommandExecutor;

/**
 * Base class for Yii commands that use Rector rules
 */
abstract class YiiRectorCommand extends SyntraRefactorCommand
{
    protected RectorCommandExecutor $rectorExecutor;

    public function __construct(
        ConfigLoader $configLoader,
        ProcessRunner $processRunner,
        ExtensionManager $extensionManager
    ) {
        parent::__construct($configLoader, $processRunner, $extensionManager);
        $this->rectorExecutor = new RectorCommandExecutor($configLoader, $processRunner);
    }

    /**
     * Get the Rector rule class(es) to execute
     *
     * @return string|array Single Rector class or array of classes
     */
    abstract protected function getRectorRules(): string|array;

    /**
     * Get additional Rector arguments (optional override)
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
     * Get success message (optional override)
     */
    protected function getSuccessMessage(): string
    {
        return 'Rector refactoring completed.';
    }

    /**
     * Get error message (optional override)
     */
    protected function getErrorMessage(): string
    {
        return 'Rector refactoring crashed.';
    }

    /**
     * Perform the Rector refactoring
     */
    public function perform(): int
    {
        $rules = $this->getRectorRules();
        $additionalArgs = $this->getAdditionalArgs();

        try {
            $result = is_array($rules)
                ? $this->rectorExecutor->executeRules($rules, $additionalArgs)
                : $this->rectorExecutor->executeRule($rules, $additionalArgs);

            if ($result->exitCode === 0) {
                $this->output->success($this->getSuccessMessage());
            } else {
                $this->output->error($this->getErrorMessage());
            }

            return $result->exitCode;
        } catch (Exception $e) {
            $this->output->error("Failed to execute Rector: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
