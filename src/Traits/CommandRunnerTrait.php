<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Vix\Syntra\Commands\SyntraCommand;
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

    /**
     * Run any other Syntra command by class name.
     *
     * @param class-string<SyntraCommand> $class
     * @param array<string, mixed> $input
     */
    protected function runCommand(string $class, array $input = []): int
    {
        if (!is_a($class, SyntraCommand::class, true)) {
            throw new RuntimeException("$class must extend " . SyntraCommand::class);
        }

        if (method_exists($this, 'getApplication') && $this->getApplication() instanceof Application) {
            $container = $this->getApplication()->getContainer();
            /** @var SyntraCommand $command */
            $command = $container->make($class);
            $command->setApplication($this->getApplication());
        } else {
            throw new RuntimeException('Application container not available');
        }

        return $command->run(new ArrayInput($input), $this->output);
    }
}
