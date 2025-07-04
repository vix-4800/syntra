<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

use Symfony\Component\Console\Command\Command;
use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Enums\CommandStatus;

/**
 * Helper trait for health check commands.
 */
trait RunsCheckerTrait
{
    protected function handleCheckerResult(CommandResult $result, string $successMessage): int
    {
        if ($result->status === CommandStatus::OK) {
            $this->output->success($successMessage);
            return Command::SUCCESS;
        }

        if ($result->status === CommandStatus::WARNING) {
            $this->output->warning($successMessage);
        } else {
            $this->output->error($successMessage);
        }

        foreach ($result->messages as $msg) {
            $this->output->writeln('  - ' . $msg);
        }

        return $result->status === CommandStatus::WARNING ? Command::SUCCESS : Command::FAILURE;
    }
}
