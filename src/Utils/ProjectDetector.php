<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

class ProjectDetector
{
    public const TYPE_UNKNOWN = 'unknown';
    public const TYPE_YII = 'yii';
    public const TYPE_LARAVEL = 'laravel';

/**
 * Detect the project type.
 */
    public function detect(string $projectRoot): string
    {
        $composerPath = rtrim($projectRoot, '/').'/composer.json';
        if (!is_file($composerPath)) {
            return self::TYPE_UNKNOWN;
        }

        $data = json_decode((string) file_get_contents($composerPath), true);
        if (!is_array($data)) {
            return self::TYPE_UNKNOWN;
        }

        $requires = array_merge(
            array_keys($data['require'] ?? []),
            array_keys($data['require-dev'] ?? [])
        );

        foreach ($requires as $package) {
            if (preg_match('#^yiisoft/yii2(-.*)?$#', (string) $package)) {
                return self::TYPE_YII;
            }
            if (preg_match('#^laravel/#', (string) $package)) {
                return self::TYPE_LARAVEL;
            }
        }

        return self::TYPE_UNKNOWN;
    }
}
