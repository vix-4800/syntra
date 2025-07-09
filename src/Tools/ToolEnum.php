<?php

declare(strict_types=1);

namespace Vix\Syntra\Tools;

/**
 * List of built-in tools with their metadata.
 */
enum ToolEnum: string implements ToolInterface
{
    case RECTOR = 'rector';
    case PHP_CS_FIXER = 'php-cs-fixer';
    case PHP_STAN = 'phpstan';
    case PHPUNIT = 'phpunit';

    private const CONFIG = [
        'rector' => [
            'name' => 'Rector',
            'package' => 'rector/rector',
            'description' => 'Rector (code refactoring)',
            'dev' => true,
        ],
        'php-cs-fixer' => [
            'name' => 'PHP CS Fixer',
            'package' => 'friendsofphp/php-cs-fixer',
            'description' => 'PHP CS Fixer (code style fixes)',
            'dev' => true,
        ],
        'phpstan' => [
            'name' => 'PHPStan',
            'package' => 'phpstan/phpstan',
            'description' => 'PHPStan (static analysis)',
            'dev' => true,
        ],
        'phpunit' => [
            'name' => 'PHPUnit',
            'package' => 'phpunit/phpunit',
            'description' => 'PHPUnit (running tests)',
            'dev' => true,
        ],
    ];

    private function config(string $key): mixed
    {
        return self::CONFIG[$this->value][$key];
    }

    public function name(): string
    {
        return $this->config('name');
    }

    public function binaryName(): string
    {
        return $this->value;
    }

    public function packageName(): string
    {
        return $this->config('package');
    }

    public function description(): string
    {
        return $this->config('description');
    }

    public function isDev(): bool
    {
        return $this->config('dev');
    }

    public function installCommand(): string
    {
        $devFlag = $this->isDev() ? '--dev ' : '';
        return sprintf('composer require %s%s', $devFlag, $this->packageName());
    }
}
