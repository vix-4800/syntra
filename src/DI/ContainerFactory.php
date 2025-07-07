<?php

declare(strict_types=1);

namespace Vix\Syntra\DI;

use Vix\Syntra\DI\Providers\ApplicationServiceProvider;
use Vix\Syntra\DI\Providers\HealthServiceProvider;
use Vix\Syntra\DI\Providers\ParserServiceProvider;

class ContainerFactory
{
/**
 * Create a new instance.
 */
    public static function create(): ContainerInterface
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
}
