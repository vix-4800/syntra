<?php

declare(strict_types=1);

namespace Vix\Syntra\DI\Providers;

use Vix\Syntra\DI\ContainerInterface;
use Vix\Syntra\DI\ServiceProviderInterface;
use Vix\Syntra\Utils\ConfigLoader;
use Vix\Syntra\Utils\FileHelper;
use Vix\Syntra\Utils\PackageInstaller;
use Vix\Syntra\Utils\ProcessRunner;
use Vix\Syntra\Utils\ProjectDetector;
use Vix\Syntra\Utils\RectorCommandExecutor;

/**
 * Application Service Provider
 *
 * Registers core application services as singletons.
 * These services are shared across the entire application lifecycle.
 */
class ApplicationServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        // Register ConfigLoader as singleton
        $container->singleton(ConfigLoader::class, fn (): ConfigLoader => new ConfigLoader());

        // Register ProcessRunner as singleton
        $container->singleton(ProcessRunner::class, fn (): ProcessRunner => new ProcessRunner());

        // Register FileHelper as singleton
        $container->singleton(FileHelper::class, fn (): FileHelper => new FileHelper());

        // Register PackageInstaller as singleton
        $container->singleton(PackageInstaller::class, fn (): PackageInstaller => new PackageInstaller());

        // Register ProjectDetector as singleton
        $container->singleton(ProjectDetector::class, fn (): ProjectDetector => new ProjectDetector());

        // Register RectorCommandExecutor as singleton
        $container->singleton(RectorCommandExecutor::class, fn (): RectorCommandExecutor => new RectorCommandExecutor());
    }

    public function boot(ContainerInterface $container): void
    {
        // No additional booting needed for core services
    }
}
