<?php

declare(strict_types=1);

namespace Vix\Syntra\Traits;

use Vix\Syntra\Exceptions\NotFoundException;
use Vix\Syntra\Application;
use Vix\Syntra\DI\ContainerInterface;

/**
 * Provides container access methods to avoid code duplication
 * across commands and other classes that need container access.
 */
trait ContainerAwareTrait
{
    /**
     * Get the DI container if available
     *
     * @return ContainerInterface|null
     */
    protected function getContainer(): ?ContainerInterface
    {
        $app = $this->getApplication();
        if ($app instanceof Application) {
            return $app->getContainer();
        }

        return null;
    }

    /**
     * Resolve a service from the container with an optional fallback
     *
     * @template T of object
     * @param  class-string<T> $id       Service identifier
     * @param  callable|null   $fallback Function to create service if container unavailable
     * @return T|mixed
     */
    protected function resolveService(string $id, ?callable $fallback = null): mixed
    {
        $container = $this->getContainer();

        if ($container !== null && $container->has($id)) {
            return $container->get($id);
        }

        if ($fallback !== null) {
            return $fallback();
        }

        throw new NotFoundException("Service '$id' not available and no fallback provided");
    }
}
