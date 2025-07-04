<?php

declare(strict_types=1);

namespace Vix\Syntra\DI;

/**
 * Service Provider Interface
 *
 * Service providers are used to register services and bindings
 * in the dependency injection container.
 */
interface ServiceProviderInterface
{
    /**
     * Register services in the container
     *
     * @param ContainerInterface $container
     */
    public function register(ContainerInterface $container): void;

    /**
     * Boot services after all providers have been registered
     *
     * This method is called after all service providers have been
     * registered, allowing for additional setup that depends on
     * other services being available.
     *
     * @param ContainerInterface $container
     */
    public function boot(ContainerInterface $container): void;
}
