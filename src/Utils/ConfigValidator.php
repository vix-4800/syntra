<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

use Vix\Syntra\Exceptions\ConfigException;

class ConfigValidator
{
    /**
     * Validate Syntra configuration array structure.
     *
     * @throws ConfigException if the configuration is invalid
     */
    public static function validate(array $config): void
    {
        foreach ($config as $group => $commands) {
            if (!is_array($commands)) {
                throw new ConfigException("Config group '$group' must be an array");
            }
            foreach ($commands as $class => $cfg) {
                if (!is_bool($cfg) && !is_array($cfg)) {
                    throw new ConfigException("Config for command '$class' in group '$group' must be bool or array");
                }
                if (is_array($cfg) && array_key_exists('enabled', $cfg) && !is_bool($cfg['enabled'])) {
                    throw new ConfigException("'enabled' option for command '$class' in group '$group' must be bool");
                }
            }
        }
    }
}
