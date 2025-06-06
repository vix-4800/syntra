<?php

declare(strict_types=1);

namespace Vix\Syntra\Contracts;

interface AvailabilityCheckerInterface
{
    public function isAvailable(): bool;
}
