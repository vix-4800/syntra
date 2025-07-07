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
    protected static function getFacadeAccessor(): string
    {
        return PackageInstaller::class;
    }
}
