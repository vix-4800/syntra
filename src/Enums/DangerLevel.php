<?php

declare(strict_types=1);

namespace Vix\Syntra\Enums;

enum DangerLevel: string
{
    case LOW = 'LOW';
    case MEDIUM = 'MEDIUM';
    case HIGH = 'HIGH';

/**
 * Get a service from the container.emoji for level.
 */
    public static function getEmojiForLevel(self $level): string
    {
        return match ($level) {
            self::LOW => '🟢',
            self::MEDIUM => '🟡',
            self::HIGH => '🔴',
        };
    }
}
