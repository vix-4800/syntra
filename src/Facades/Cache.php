<?php

declare(strict_types=1);

namespace Vix\Syntra\Facades;

use Vix\Syntra\Utils\CacheStore;

/**
 * @method static void  setEnabled(bool $enabled)
 * @method static bool  isEnabled()
 * @method static bool  has(string $key)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static void  set(string $key, mixed $value)
 * @method static void  clear(string $key)
 * @method static void  clearAll()
 */
class Cache extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CacheStore::class;
    }
}
