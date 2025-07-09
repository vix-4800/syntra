<?php

declare(strict_types=1);

namespace Vix\Syntra\ProgressIndicators;

use Symfony\Component\Console\Style\SymfonyStyle;
use Vix\Syntra\Enums\ProgressIndicatorType;
use Vix\Syntra\ProgressIndicators\NullProgressIndicator;
use Vix\Syntra\ProgressIndicators\ProgressIndicatorInterface;

class ProgressIndicatorFactory
{
    public static function create(
        ProgressIndicatorType $type,
        SymfonyStyle $output,
        int $maxSteps = 0
    ): ProgressIndicatorInterface {
        return match ($type) {
            ProgressIndicatorType::PROGRESS_BAR => new ProgressBarIndicator($output, $maxSteps),
            ProgressIndicatorType::SPINNER => new SpinnerIndicator($output),
            ProgressIndicatorType::BOUNCING => new BouncingIndicator($output),
            ProgressIndicatorType::NONE => new NullProgressIndicator($output),
        };
    }
}
