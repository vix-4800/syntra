<?php

declare(strict_types=1);

namespace Vix\Syntra\Enums;

enum DangerLevel: string
{
    case LOW = 'LOW';
    case MEDIUM = 'MEDIUM';
    case HIGH = 'HIGH';

    public static function getEmojiForLevel(self $level): string
    {
        return match ($level) {
            self::LOW => '🟢',
            self::MEDIUM => '🟡',
            self::HIGH => '🔴',
        };
    }
}
