<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

use Vix\Syntra\Enums\CommandGroup;

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

    private ?string $projectRoot = null;

    private readonly array $commands;

/**
 * Class constructor.
 */
    public function __construct(?string $projectRoot = null)
    {
        $this->projectRoot = $projectRoot ?? getcwd();
        $this->commands = require PACKAGE_ROOT . '/config.php';
    }

/**
 * Set project root.
 */
    public function setProjectRoot(string $path): void
    {
        $this->projectRoot = rtrim($path, '/');
    }

/**
 * Get project root.
 */
    public function getProjectRoot(): ?string
    {
        return $this->projectRoot;
    }

/**
 * Get command config.
 */
    public function getCommandConfig(string $group, string $commandClass): array|bool
    {
        return $this->commands[$group][$commandClass] ?? false;
    }

/**
 * Is command enabled.
 */
    public function isCommandEnabled(string $group, string $commandClass): bool
    {
        $cfg = $this->getCommandConfig($group, $commandClass);

        if (is_array($cfg)) {
            return $cfg['enabled'] ?? true;
        }

        return (bool)$cfg;
    }

/**
 * Get command option.
 */
    public function getCommandOption(string $group, string $commandClass, string $option, $default = null)
    {
        $cfg = $this->getCommandConfig($group, $commandClass);

        if (is_array($cfg) && array_key_exists($option, $cfg)) {
            return $cfg[$option];
        }

        return $default;
    }

/**
 * Get enabled commands.
 */
    public function getEnabledCommands(): array
    {
        return $this->filterEnabledCommands(true);
    }

/**
 * Get enabled extension commands.
 */
    public function getEnabledExtensionCommands(): array
    {
        return $this->filterEnabledCommands(false);
    }

/**
 * Filter enabled commands.
 */
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
