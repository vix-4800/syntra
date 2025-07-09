<?php

declare(strict_types=1);

namespace Vix\Syntra\ProgressIndicators;

class NullProgressIndicator extends AbstractProgressIndicator
{
    public function start(): void
    {
        //
    }

    public function advance(int $step = 1): void
    {
        //
    }

    public function finish(): void
    {
        //
    }
}
