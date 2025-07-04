<?php

declare(strict_types=1);

namespace Vix\Syntra\DI\Providers;

use Vix\Syntra\Commands\Health\ComposerChecker;
use Vix\Syntra\Commands\Health\PhpStanChecker;
use Vix\Syntra\DI\ContainerInterface;
use Vix\Syntra\DI\ServiceProviderInterface;
use Vix\Syntra\Utils\ConfigLoader;
use Vix\Syntra\Utils\ProcessRunner;

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
        // Register ComposerChecker factory
        $container->bind('health.composer_checker', function (ContainerInterface $container): ComposerChecker {
            $processRunner = $container->get(ProcessRunner::class);
            $configLoader = $container->get(ConfigLoader::class);
            $projectRoot = $configLoader->getProjectRoot();

            return new ComposerChecker($processRunner, $projectRoot);
        });

        // Register PhpStanChecker factory
        $container->bind('health.phpstan_checker', function (ContainerInterface $container): PhpStanChecker {
            $processRunner = $container->get(ProcessRunner::class);
            $configLoader = $container->get(ConfigLoader::class);
            $projectRoot = $configLoader->getProjectRoot();

            $level = (int) $configLoader->getCommandOption('health', PhpStanChecker::class, 'level', 0);
            $config = $configLoader->getCommandOption('health', PhpStanChecker::class, 'config');

            return new PhpStanChecker($processRunner, $projectRoot, $level, $config);
        });

        // Register PhpUnitChecker factory
        $container->bind('health.phpunit_checker', function (ContainerInterface $container): void {
            $processRunner = $container->get(ProcessRunner::class);
            $configLoader = $container->get(ConfigLoader::class);
            $projectRoot = $configLoader->getProjectRoot();

            // return new PhpUnitChecker($processRunner, $projectRoot);
        });

        // Register SecurityChecker factory
        $container->bind('health.security_checker', function (ContainerInterface $container): void {
            $processRunner = $container->get(ProcessRunner::class);
            $configLoader = $container->get(ConfigLoader::class);
            $projectRoot = $configLoader->getProjectRoot();

            // return new SecurityChecker($processRunner, $projectRoot);
        });
    }

    public function boot(ContainerInterface $container): void
    {
        // No additional booting needed for health checkers
    }
}
