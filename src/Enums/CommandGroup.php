<?php

declare(strict_types=1);

namespace Vix\Syntra\Enums;

/**
 * Defines the default command groups used by Syntra.
 */
enum CommandGroup: string
{
    case REFACTOR = 'refactor';
    case HEALTH = 'health';
    case ANALYZE = 'analyze';
    case GENERAL = 'general';
    case YII = 'yii';
    case LARAVEL = 'laravel';
}
