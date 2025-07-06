<?php

declare(strict_types=1);

namespace Vix\Syntra\ProgressIndicators;

use Symfony\Component\Console\Style\SymfonyStyle;
use Vix\Syntra\ProgressIndicators\NullProgressIndicator;
use Vix\Syntra\ProgressIndicators\ProgressIndicatorInterface;

class ProgressIndicatorFactory
{
    public const TYPE_SPINNER = 'spinner';
    public const TYPE_PROGRESS_BAR = 'progress_bar';
    public const TYPE_NONE = 'none';

    public static function create(
        string $type,
        SymfonyStyle $output,
        int $maxSteps = 0
    ): ProgressIndicatorInterface {
        return match ($type) {
            self::TYPE_PROGRESS_BAR => new ProgressBarIndicator($output, $maxSteps),
            self::TYPE_SPINNER => new SpinnerIndicator($output),
            self::TYPE_NONE => new NullProgressIndicator(),
            default => new SpinnerIndicator($output),
        };
    }
}
