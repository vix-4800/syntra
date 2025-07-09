<?php

declare(strict_types=1);

namespace Vix\Syntra\Utils;

/**
 * Simple in-memory cache store.
 */
class CacheStore
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    private bool $enabled = true;

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;

        if (!$enabled) {
            $this->data = [];
        }
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function has(string $key): bool
    {
        return $this->enabled && array_key_exists($key, $this->data);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    public function set(string $key, mixed $value): void
    {
        if ($this->enabled) {
            $this->data[$key] = $value;
        }
    }

    public function clear(string $key): void
    {
        unset($this->data[$key]);
    }

    public function clearAll(): void
    {
        $this->data = [];
    }
}
