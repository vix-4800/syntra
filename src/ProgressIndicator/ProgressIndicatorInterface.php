<?php

declare(strict_types=1);

namespace Vix\Syntra\ProgressIndicator;

interface ProgressIndicatorInterface
{
    public function start(): void;

    public function advance(): void;

    public function finish(): void;

    public function setMessage(string $message): void;
}
