<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Vix\Syntra\Application;
use Vix\Syntra\Commands\SyntraCommand;

trait CommandRunnerTrait
{
    /**
     * Run any other Syntra command by class name.
     *
     * @param class-string $class
     * @param array<string, mixed>        $input
     */
    protected function runCommand(string $class, array $input = []): int
    {
        if (!is_a($class, SyntraCommand::class, true)) {
            throw new RuntimeException("$class must extend " . SyntraCommand::class);
        }

        $app = $this->getApplication();
        if (!$app instanceof Application) {
            throw new RuntimeException('Application container not available');
        }

        $container = $app->getContainer();
        /** @var SyntraCommand $command */
        $command = $container->make($class);
        $command->setApplication($app);

        return $command->run(new ArrayInput($input), $this->output);
    }
}
