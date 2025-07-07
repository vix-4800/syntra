<?php

declare(strict_types=1);

namespace Vix\Syntra\ProgressIndicators;

class NullProgressIndicator implements ProgressIndicatorInterface
{
/**
 * Start the progress indicator.
 */
    public function start(): void
    {
        //
    }

/**
 * Advance the progress indicator.
 */
    public function advance(int $step = 1): void
    {
        //
    }

/**
 * Finish the progress indicator.
 */
    public function finish(): void
    {
        //
    }

/**
 * Set message.
 */
    public function setMessage(string $message): void
    {
        //
    }
}
