<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

use Symfony\Component\Console\Command\Command;
use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Enums\CommandStatus;

/**
 * Helper trait for displaying command results consistently.
 */
trait HandlesResultTrait
{
    protected function handleResult(CommandResult $result, string $successMessage, bool $failOnWarning = false): int
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

        if ($result->status === CommandStatus::WARNING && !$failOnWarning) {
            return Command::SUCCESS;
        }

        return $result->status === CommandStatus::OK ? Command::SUCCESS : Command::FAILURE;
    }
}
