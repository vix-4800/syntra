<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\DTO\CommandResult;

interface HealthCheckCommandInterface
{
/**
 * Run check.
 */
    public function runCheck(): CommandResult;
}
