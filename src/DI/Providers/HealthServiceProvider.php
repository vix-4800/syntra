<?php

declare(strict_types=1);

namespace Vix\Syntra\DI\Providers;

use Vix\Syntra\DI\ContainerInterface;
use Vix\Syntra\DI\ServiceProviderInterface;

/**
 * Health Service Provider
 *
 * Registers health checker services that can be used
 * for project health validation.
 */
class HealthServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        // Register SecurityChecker factory (placeholder for future use)
    }

    public function boot(ContainerInterface $container): void
    {
        // No additional booting needed for health checkers
    }
}
