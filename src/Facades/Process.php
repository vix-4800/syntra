<?php

declare(strict_types=1);

namespace Vix\Syntra\Facades;

use Vix\Syntra\DTO\ProcessResult;
use Vix\Syntra\Utils\ProcessRunner;

/**
 * @method static ProcessResult run(string $binary, array $args = [], ?callable $callback = null)
 */
class Process extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ProcessRunner::class;
    }
}
