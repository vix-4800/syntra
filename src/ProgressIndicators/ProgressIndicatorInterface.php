<?php

declare(strict_types=1);

namespace Vix\Syntra\ProgressIndicators;

interface ProgressIndicatorInterface
{
    public function start(): void;

    public function advance(int $step = 1): void;

    public function finish(): void;

    public function setMessage(string $message): void;
}
