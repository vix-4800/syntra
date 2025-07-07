<?php

declare(strict_types=1);

namespace Vix\Syntra\ProgressIndicators;

use Symfony\Component\Console\Style\SymfonyStyle;
use Vix\Syntra\ProgressIndicators\ProgressIndicatorInterface;

abstract class AbstractProgressIndicator implements ProgressIndicatorInterface
{
    protected string $message;

/**
 * Class constructor.
 */
    public function __construct(protected SymfonyStyle $output)
    {
        $this->message = "Processing...";
    }

/**
 * Set message.
 */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
