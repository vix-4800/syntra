<?php

declare(strict_types=1);

namespace Vix\Syntra\DI;

use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Dependency Injection Container Interface
 *
 * This interface extends PSR-11 Container Interface and adds
 * additional methods for binding services and managing the container.
 */
interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Bind a service to the container
     *
     * @param string                 $id        Service identifier
     * @param callable|string|object $concrete  Service implementation
     * @param bool                   $singleton Whether the service should be a singleton
     */
    public function bind(string $id, callable|string|object $concrete, bool $singleton = false): void;

    /**
     * Bind a singleton service to the container
     *
     * @param string                 $id       Service identifier
     * @param callable|string|object $concrete Service implementation
     */
    public function singleton(string $id, callable|string|object $concrete): void;

    /**
     * Register an instance as a singleton
     *
     * @param string $id       Service identifier
     * @param object $instance Service instance
     */
    public function instance(string $id, object $instance): void;

    /**
     * Check if a service can be resolved
     *
     * @param  string $id Service identifier
     * @return bool
     */
    public function canResolve(string $id): bool;

    /**
     * Resolve a service with automatic dependency injection
     *
     * @template T
     * @param  class-string<T> $class
     * @return T
     */
    public function make(string $class): object;
}
