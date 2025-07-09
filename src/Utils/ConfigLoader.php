<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

use Vix\Syntra\Enums\CommandGroup;
use Vix\Syntra\Utils\ProjectInfo;

class ConfigLoader
{
    /**
     * List of built-in command groups.
     *
     * @var string[]
     */
    private array $coreGroups = [
        CommandGroup::REFACTOR->value,
        CommandGroup::HEALTH->value,
        CommandGroup::ANALYZE->value,
        CommandGroup::GENERAL->value,
    ];

    private readonly array $commands;

    public function __construct()
    {
        $projectRoot = (new ProjectInfo())->getRootPath();
        $projectConfig = rtrim($projectRoot, '/') . '/syntra.php';

        $this->commands = is_readable($projectConfig)
            ? require $projectConfig
            : require PACKAGE_ROOT . '/syntra.php';
    }

    public function getCommandConfig(string $group, string $commandClass): array|bool
    {
        return $this->commands[$group][$commandClass] ?? false;
    }

    public function isCommandEnabled(string $group, string $commandClass): bool
    {
        $cfg = $this->getCommandConfig($group, $commandClass);

        if (is_array($cfg)) {
            return $cfg['enabled'] ?? true;
        }

        return (bool)$cfg;
    }

    public function getCommandOption(string $group, string $commandClass, string $option, $default = null)
    {
        $cfg = $this->getCommandConfig($group, $commandClass);

        if (is_array($cfg) && array_key_exists($option, $cfg)) {
            return $cfg[$option];
        }

        return $default;
    }

    public function getEnabledCommands(): array
    {
        return $this->filterEnabledCommands(true);
    }

    public function getEnabledCommandsByGroup(string $group): array
    {
        if (!isset($this->commands[$group])) {
            return [];
        }

        $result = [];
        foreach ($this->commands[$group] as $class => $cfg) {
            if ($this->isCommandEnabled($group, $class)) {
                $result[] = $class;
            }
        }

        return $result;
    }

    public function getEnabledExtensionCommands(): array
    {
        return $this->filterEnabledCommands(false);
    }

    private function filterEnabledCommands(bool $core): array
    {
        $result = [];

        foreach ($this->commands as $group => $commands) {
            $isCoreGroup = in_array($group, $this->coreGroups, true);
            if ($core !== $isCoreGroup) {
                continue;
            }

            foreach ($commands as $class => $cfg) {
                if ($this->isCommandEnabled($group, $class)) {
                    $result[] = $class;
                }
            }
        }

        return $result;
    }
}
