<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

use Vix\Syntra\SyntraConfig;

class ConfigLoader
{
    private ?string $projectRoot = null;

    private readonly SyntraConfig $config;

    public function __construct(?string $projectRoot = null)
    {
        $this->projectRoot = $projectRoot ?? getcwd();
        $this->config = new SyntraConfig();
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
        $all = $this->config->commands();

        return $all[$group][$commandClass] ?? false;
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
        $result = [];
        $all = $this->config->commands();

        foreach ($all as $group => $commands) {
            foreach ($commands as $class => $cfg) {
                if (!$this->isCommandEnabled($group, $class)) {
                    continue;
                }

                $result[] = $class;
            }
        }

        return $result;
    }
}
