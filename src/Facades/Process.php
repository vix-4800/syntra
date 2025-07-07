<?php

declare(strict_types=1);

namespace Vix\Syntra\Facades;

use Vix\Syntra\DTO\ProcessResult;
use Vix\Syntra\Utils\ProcessRunner;

/**
 * @method static ProcessResult run(string $command, array $args = [], array $options = [], ?callable $callback = null)
 */
class Process extends Facade
{
/**
 * Get the facade accessor class name.
 */
    protected static function getFacadeAccessor(): string
    {
        return ProcessRunner::class;
    }
}
