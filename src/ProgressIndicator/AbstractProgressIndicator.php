<?php

declare(strict_types=1);

namespace Vix\Syntra\ProgressIndicator;

use Symfony\Component\Console\Style\SymfonyStyle;
use Vix\Syntra\ProgressIndicator\ProgressIndicatorInterface;

abstract class AbstractProgressIndicator implements ProgressIndicatorInterface
{
    protected SymfonyStyle $output;

    protected string $message;

    public function __construct(SymfonyStyle $output)
    {
        $this->output = $output;
        $this->message = "Processing...";
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
