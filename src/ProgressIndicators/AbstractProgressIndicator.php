<?php

declare(strict_types=1);

namespace Vix\Syntra\ProgressIndicators;

use Symfony\Component\Console\Style\SymfonyStyle;
use Vix\Syntra\ProgressIndicators\ProgressIndicatorInterface;

abstract class AbstractProgressIndicator implements ProgressIndicatorInterface
{
    protected string $message;

    public function __construct(protected SymfonyStyle $output)
    {
        $this->message = "Processing...";
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
