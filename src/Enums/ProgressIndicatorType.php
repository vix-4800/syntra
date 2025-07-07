<?php

declare(strict_types=1);

namespace Vix\Syntra\Enums;

enum ProgressIndicatorType: string
{
    case SPINNER = 'spinner';
    case PROGRESS_BAR = 'progress_bar';
    case BOUNCING = 'bouncing';
    case NONE = 'none';
}
