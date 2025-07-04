<?php

declare(strict_types=1);

namespace Vix\Syntra;

use ReflectionClass;
use Symfony\Component\Console\Application as SymfonyApplication;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\DI\Container;
use Vix\Syntra\DI\ContainerInterface;
use Vix\Syntra\DI\Providers\ApplicationServiceProvider;
use Vix\Syntra\DI\Providers\HealthServiceProvider;
use Vix\Syntra\DI\Providers\ParserServiceProvider;
use Vix\Syntra\Utils\ConfigLoader;

class Application extends SymfonyApplication
{
    private readonly ContainerInterface $container;

    public function __construct(string $name = 'Syntra', string $version = '1.0.0')
    {
        parent::__construct($name, $version);

        $this->container = $this->setupContainer();
        $this->registerCommands();
        $this->registerExtensionCommands();
    }

    /**
     * Set up the dependency injection container
     */
    private function setupContainer(): ContainerInterface
    {
        $container = new Container();

        // Register service providers
        $providers = [
            new ApplicationServiceProvider(),
            new HealthServiceProvider(),
            new ParserServiceProvider(),
        ];

        // Register all services
        foreach ($providers as $provider) {
            $provider->register($container);
        }

        // Boot all services
        foreach ($providers as $provider) {
            $provider->boot($container);
        }

        return $container;
    }

    /**
     * Get the DI container
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    private function registerCommands(): void
    {
        $configLoader = $this->container->get(ConfigLoader::class);

        foreach ($configLoader->getEnabledCommands() as $class) {
            if (!class_exists($class)) {
                continue;
            }

            // Ensure it's a concrete subclass of Syntra Command
            $reflectionClass = new ReflectionClass($class);

            if (
                is_subclass_of($class, SyntraCommand::class)
                && !$reflectionClass->isAbstract()
            ) {
                $instance = $this->container->make($class);
                $this->add($instance);
            }
        }
    }

    private function registerExtensionCommands(): void
    {
        $configLoader = $this->container->get(ConfigLoader::class);

        foreach ($configLoader->getEnabledExtensionCommands() as $class) {
            if (!class_exists($class)) {
                continue;
            }

            $reflectionClass = new ReflectionClass($class);

            if (
                is_subclass_of($class, SyntraCommand::class)
                && !$reflectionClass->isAbstract()
            ) {
                $instance = $this->container->make($class);
                $this->add($instance);
            }
        }
    }
}
