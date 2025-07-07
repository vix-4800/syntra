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
        /** @var CommandStatus $status */
        $status = CommandStatus::from($result->status->value);

        if ($status === CommandStatus::OK) {
            $this->output->success($successMessage);
            return Command::SUCCESS;
        }

        if ($status === CommandStatus::WARNING) {
            $this->output->warning($successMessage);
        } else {
            $this->output->error($successMessage);
        }

        foreach ($result->messages as $msg) {
            $this->output->writeln('  - ' . $msg);
        }

        if ($status === CommandStatus::WARNING && !$failOnWarning) {
            return Command::SUCCESS;
        }

        // OK status handled above, but keep explicit check for clarity
        /** @phpstan-ignore-next-line */
        return $status === CommandStatus::OK ? Command::SUCCESS : Command::FAILURE;
    }
}
