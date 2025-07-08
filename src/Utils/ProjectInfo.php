<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

class ProjectInfo
{
    public const TYPE_UNKNOWN = 'unknown';
    public const TYPE_YII = 'yii';
    public const TYPE_LARAVEL = 'laravel';

    private string $rootPath;

    public function __construct(?string $rootPath = null)
    {
        $this->rootPath = $rootPath ?? $this->detectRootPath();
    }

    private function detectRootPath(): string
    {
        $cwd = getcwd();
        if ($cwd === false) {
            return '';
        }

        $dir = $cwd;
        while (!file_exists($dir . '/composer.json')) {
            $parent = dirname($dir);
            if ($parent === $dir) {
                break;
            }
            $dir = $parent;
        }

        return rtrim($dir, '/');
    }

    public function setRootPath(string $path): void
    {
        $this->rootPath = rtrim($path, '/');
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    public function detect(string $rootPath = null): string
    {
        $composerPath = rtrim($rootPath ?? $this->rootPath, '/') . '/composer.json';
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

