<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

class ConfigLoader
{
    private array $coreGroups = ['refactor', 'health', 'analyze', 'general'];

    private ?string $projectRoot = null;

    private readonly array $commands;

    public function __construct(?string $projectRoot = null)
    {
        $this->projectRoot = $projectRoot ?? getcwd();
        $this->commands = require_once(PACKAGE_ROOT . '/config.php');
    }

    public function setProjectRoot(string $path): void
    {
        $this->projectRoot = rtrim($path, '/');
    }

    public function getProjectRoot(): ?string
    {
        return $this->projectRoot;
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

    public function getEnabledExtensionCommands(): array
    {
        return $this->filterEnabledCommands(false);
    }

    private function filterEnabledCommands(bool $core): array
    {
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
