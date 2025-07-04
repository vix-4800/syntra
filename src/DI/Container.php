<?php

declare(strict_types=1);

namespace Vix\Syntra\DI;

use ReflectionNamedType;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Vix\Syntra\Exceptions\ContainerException;
use Vix\Syntra\Exceptions\NotFoundException;

/**
 * Simple Dependency Injection Container
 *
 * This container implements automatic dependency injection with support for:
 * - Singleton and transient services
 * - Automatic constructor injection
 * - Interface to implementation binding
 * - Circular dependency detection
 */
class Container implements ContainerInterface
{
    /** @var array<string, mixed> */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $instances = [];

    /** @var array<string, bool> */
    private array $singletons = [];

    /** @var array<string, true> */
    private array $resolving = [];

    public function bind(string $id, callable|string|object $concrete, bool $singleton = false): void
    {
        $this->bindings[$id] = $concrete;
        $this->singletons[$id] = $singleton;

        // Remove any existing instance if rebinding
        unset($this->instances[$id]);
    }

    public function singleton(string $id, callable|string|object $concrete): void
    {
        $this->bind($id, $concrete, true);
    }

    public function instance(string $id, object $instance): void
    {
        $this->instances[$id] = $instance;
        $this->singletons[$id] = true;
    }

    public function has(string $id): bool
    {
        return $this->canResolve($id);
    }

    public function canResolve(string $id): bool
    {
        return isset($this->bindings[$id])
            || isset($this->instances[$id])
            || class_exists($id)
            || interface_exists($id);
    }

    public function get(string $id): mixed
    {
        try {
            return $this->resolve($id);
        } catch (ReflectionException $e) {
            throw new NotFoundException("Service '$id' not found in container", 0, $e);
        }
    }

    public function make(string $class): object
    {
        return $this->resolve($class);
    }

    /**
     * Resolve a service from the container
     *
     * @param string $id Service identifier
     * @return mixed
     * @throws ContainerException
     * @throws NotFoundException
     */
    private function resolve(string $id): mixed
    {
        // Check for circular dependencies
        if (isset($this->resolving[$id])) {
            throw new ContainerException("Circular dependency detected for service '$id'");
        }

        // Return existing instance if it's a singleton
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // Mark as resolving
        $this->resolving[$id] = true;

        try {
            $concrete = $this->getConcrete($id);
            $instance = $this->build($concrete);

            // Store instance if it's a singleton
            if ($this->isSingleton($id)) {
                $this->instances[$id] = $instance;
            }

            return $instance;
        } finally {
            // Always unmark as resolving
            unset($this->resolving[$id]);
        }
    }

    /**
     * Get the concrete implementation for a service
     *
     * @param string $id Service identifier
     * @return mixed
     * @throws NotFoundException
     */
    private function getConcrete(string $id): mixed
    {
        if (isset($this->bindings[$id])) {
            return $this->bindings[$id];
        }

        if (class_exists($id)) {
            return $id;
        }

        throw new NotFoundException("Unable to resolve service '$id'");
    }

    /**
     * Build an instance from concrete implementation
     *
     * @param mixed $concrete
     * @return mixed
     * @throws ContainerException
     */
    private function build(mixed $concrete): mixed
    {
        // If it's a callable, call it with the container
        if (is_callable($concrete)) {
            return $concrete($this);
        }

        // If it's already an object, return it
        if (is_object($concrete)) {
            return $concrete;
        }

        // If it's a string (class name), instantiate it
        if (is_string($concrete)) {
            return $this->instantiate($concrete);
        }

        throw new ContainerException("Cannot build service from concrete type: " . gettype($concrete));
    }

    /**
     * Instantiate a class with dependency injection
     *
     * @param string $className
     * @return object
     * @throws ContainerException
     */
    private function instantiate(string $className): object
    {
        try {
            $reflection = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new ContainerException("Class '$className' does not exist", 0, $e);
        }

        if (!$reflection->isInstantiable()) {
            throw new ContainerException("Class '$className' is not instantiable");
        }

        $constructor = $reflection->getConstructor();

        // If no constructor, just instantiate
        if ($constructor === null) {
            return new $className();
        }

        // Resolve constructor parameters
        $parameters = $constructor->getParameters();
        $dependencies = $this->resolveDependencies($parameters);

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Resolve constructor dependencies
     *
     * @param ReflectionParameter[] $parameters
     * @return array<mixed>
     * @throws ContainerException
     */
    private function resolveDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $this->resolveDependency($parameter);
            $dependencies[] = $dependency;
        }

        return $dependencies;
    }

    /**
     * Resolve a single dependency
     *
     * @param ReflectionParameter $parameter
     * @return mixed
     * @throws ContainerException
     */
    private function resolveDependency(ReflectionParameter $parameter): mixed
    {
        $type = $parameter->getType();

        // Handle union types and built-in types
        if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return $this->resolveNonClassDependency($parameter);
        }

        $className = $type->getName();

        try {
            return $this->resolve($className);
        } catch (NotFoundException $e) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }

            throw new ContainerException(
                "Unable to resolve dependency '{$parameter->getName()}' of type '$className'",
                0,
                $e
            );
        }
    }

    /**
     * Resolve non-class dependencies (primitives, defaults)
     *
     * @param ReflectionParameter $parameter
     * @return mixed
     * @throws ContainerException
     */
    private function resolveNonClassDependency(ReflectionParameter $parameter): mixed
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->isOptional()) {
            return null;
        }

        $type = $parameter->getType();
        $typeName = $type instanceof ReflectionNamedType ? $type->getName() : 'unknown';

        throw new ContainerException(
            "Cannot resolve primitive dependency '{$parameter->getName()}' of type '$typeName'"
        );
    }

    /**
     * Check if a service is configured as singleton
     *
     * @param string $id Service identifier
     * @return bool
     */
    private function isSingleton(string $id): bool
    {
        return $this->singletons[$id] ?? false;
    }
}
