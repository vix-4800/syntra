<?php

declare(strict_types=1);

namespace Vix\Syntra\Facades;

use Vix\Syntra\Utils\ProcessRunner;

/**
 * @method static \Vix\Syntra\DTO\ProcessResult run(string $binary, array $args = [], ?callable $callback = null)
 */
class Process extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ProcessRunner::class;
    }
}
