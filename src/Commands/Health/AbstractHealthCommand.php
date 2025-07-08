<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\DTO\CommandResult;

abstract class AbstractHealthCommand extends SyntraCommand
{
    protected string $sectionTitle = '';
    protected string $successMessage = '';

    abstract public function runCheck(): CommandResult;

    public function perform(): int
    {
        $this->output->section($this->sectionTitle);

        $result = $this->runCheck();

        return $this->afterCheck($result);
    }

    protected function afterCheck(CommandResult $result): int
    {
        return $this->handleResult($result, $this->successMessage, $this->failOnWarning);
    }
}
