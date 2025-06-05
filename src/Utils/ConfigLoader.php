<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

use Symfony\Component\Yaml\Yaml;
use Throwable;
use Vix\Syntra\Exceptions\ConfigException;

class ConfigLoader
{
    private ?string $projectRoot = null;

    private array $config = [];

    public function __construct(?string $projectRoot = null)
    {
        $this->projectRoot = $projectRoot ?? getcwd();
    }

    public function setProjectRoot(string $path): void
    {
        $this->projectRoot = rtrim($path, '/');
    }

    public function getProjectRoot(): ?string
    {
        return $this->projectRoot;
    }

    public function get(string $path, mixed $default = null): mixed
    {
        if (empty($this->config)) {
            throw new ConfigException('Configuration not loaded yet. Call load() first.');
        }

        $parts = explode('.', $path);
        $cursor = $this->config;

        foreach ($parts as $part) {
            if (!is_array($cursor) || !array_key_exists($part, $cursor)) {
                return $default;
            }

            $cursor = $cursor[$part];
        }

        return $cursor;
    }

    /**
     * @throws ConfigException
     */
    public function load(): void
    {
        $root = $this->projectRoot;
        $ymlPath  = "$root/syntra.yml";
        $jsonPath = "$root/syntra.json";

        if (file_exists($ymlPath)) {
            try {
                $parsed = Yaml::parseFile($ymlPath);
            } catch (Throwable $e) {
                throw new ConfigException("Invalid YAML in $ymlPath: " . $e->getMessage());
            }

            if (!is_array($parsed)) {
                throw new ConfigException("$ymlPath parsed but is not an array");
            }

            $this->config = $parsed;
        } elseif (file_exists($jsonPath)) {
            $content = file_get_contents($jsonPath);
            $parsed = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($parsed)) {
                throw new ConfigException("Invalid JSON in $jsonPath: " . json_last_error_msg());
            }

            $this->config = $parsed;
        } else {
            throw new ConfigException('No syntra.yml or syntra.json found in project root.');
        }
    }
}
