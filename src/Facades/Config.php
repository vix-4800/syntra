<?php

declare(strict_types=1);

namespace Vix\Syntra\Facades;

use Vix\Syntra\Utils\ConfigLoader;

/**
 * @method static array|bool  getCommandConfig(string $group, string $commandClass)
 * @method static bool        isCommandEnabled(string $group, string $commandClass)
 * @method static mixed       getCommandOption(string $group, string $commandClass, string $option, $default = null)
 * @method static array       getEnabledCommands()
 * @method static array       getEnabledExtensionCommands()
 */
class Config extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ConfigLoader::class;
    }
}
