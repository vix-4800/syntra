<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

use RuntimeException;
use Vix\Syntra\Application;
use Vix\Syntra\Commands\Health\HealthCheckCommandInterface;
use Vix\Syntra\DTO\CommandResult;

trait CommandRunnerTrait
{
    /**
     * Run another health command and return its result.
     *
     * @param class-string<HealthCheckCommandInterface> $class
     */
    protected function runHealthCommand(string $class): CommandResult
    {
        if (!is_a($class, HealthCheckCommandInterface::class, true)) {
            throw new RuntimeException("$class must implement HealthCheckCommandInterface");
        }

        if (method_exists($this, 'getApplication') && $this->getApplication() instanceof Application) {
            $container = $this->getApplication()->getContainer();
            /** @var HealthCheckCommandInterface $command */
            $command = $container->make($class);
        } else {
            throw new RuntimeException('Application container not available');
        }

        return $command->runCheck();
    }
}
