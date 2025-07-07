<?php

declare(strict_types=1);

namespace Vix\Syntra\Facades;

use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Utils\PackageInstaller;

/**
 * @method static CommandResult install(string $command)
 */
class Installer extends Facade
{
/**
 * Get the facade accessor class name.
 */
    protected static function getFacadeAccessor(): string
    {
        return PackageInstaller::class;
    }
}
