<?php

declare(strict_types=1);

namespace Vix\Syntra;

use Composer\InstalledVersions;
use ReflectionClass;
use Symfony\Component\Console\Application as SymfonyApplication;
use Throwable;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\DI\ContainerFactory;
use Vix\Syntra\DI\ContainerInterface;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\Facade;

class Application extends SymfonyApplication
{
    private readonly ContainerInterface $container;

    private static ?string $packageVersion = null;

    public static function getPackageVersion(): string
    {
        if (self::$packageVersion === null) {
            // Prefer Composer installed version if available
            if (class_exists(InstalledVersions::class)) {
                try {
                    /** @psalm-suppress UndefinedClass */
                    self::$packageVersion = InstalledVersions::getPrettyVersion('vix/syntra');
                } catch (Throwable) {
                    // Ignore and fallback to git
                }
            }

            if (!self::$packageVersion) {
                $tag = trim((string) shell_exec('git describe --tags --abbrev=0 2>/dev/null'));
                self::$packageVersion = $tag !== '' ? $tag : '0.0.0';
            }
        }

        return self::$packageVersion;
    }

    public function __construct(string $name = 'Syntra', string $version = '')
    {
        parent::__construct($name, $version ?: self::getPackageVersion());

        $this->container = ContainerFactory::create();

        // Make the container available to facades
        if (class_exists(Facade::class)) {
            Facade::setContainer($this->container);
        }

        $this->registerFromConfig(Config::getEnabledCommands());
        $this->registerFromConfig(Config::getEnabledExtensionCommands());
    }

    /**
     * Get the DI container
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Register commands from configuration
     *
     * @param string[] $classes List of command class names
     */
    private function registerFromConfig(array $classes): void
    {
        foreach ($classes as $class) {
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
}
