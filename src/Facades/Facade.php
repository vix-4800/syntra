<?php

declare(strict_types=1);

namespace Vix\Syntra\Facades;

use RuntimeException;
use Vix\Syntra\DI\ContainerInterface;

/**
 * Base facade implementation similar to Laravel facades.
 *
 * Facades provide a static interface to services resolved
 * from Syntra's dependency injection container.
 */
abstract class Facade
{
    private static ?ContainerInterface $container = null;

    /** @var array<string, object> */
    private static array $resolved = [];

    /**
     * Set the container instance used by facades.
     */
    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    /**
     * Get the container instance.
     */
    public static function getContainer(): ?ContainerInterface
    {
        return self::$container;
    }

    /**
     * Resolve the underlying service from the container.
     */
    protected static function getFacadeRoot(): object
    {
        $accessor = static::getFacadeAccessor();

        if (!isset(self::$resolved[$accessor])) {
            if (self::$container === null || !self::$container->has($accessor)) {
                throw new RuntimeException("Service '$accessor' not available for facade");
            }
            self::$resolved[$accessor] = self::$container->get($accessor);
        }

        return self::$resolved[$accessor];
    }

    /**
     * Get the service identifier to resolve from the container.
     */
    abstract protected static function getFacadeAccessor(): string;

    public static function __callStatic(string $method, array $args): mixed
    {
        return static::getFacadeRoot()->$method(...$args);
    }
}
