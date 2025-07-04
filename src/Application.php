<?php

declare(strict_types=1);

namespace Vix\Syntra;

use ReflectionClass;
use Symfony\Component\Console\Application as SymfonyApplication;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\DI\ContainerInterface;
use Vix\Syntra\DI\ContainerFactory;
use Vix\Syntra\Utils\ConfigLoader;

class Application extends SymfonyApplication
{
    private readonly ContainerInterface $container;

    private static ?string $packageVersion = null;

    public static function getPackageVersion(): string
    {
        if (self::$packageVersion === null) {
            $composerFile = dirname(__DIR__) . '/composer.json';
            if (is_readable($composerFile)) {
                $data = json_decode((string) file_get_contents($composerFile), true);
                self::$packageVersion = $data['version'] ?? '0.0.0';
            } else {
                self::$packageVersion = '0.0.0';
            }
        }

        return self::$packageVersion;
    }

    public function __construct(string $name = 'Syntra', string $version = '')
    {
        parent::__construct($name, $version ?: self::getPackageVersion());

        $this->container = ContainerFactory::create();
        $this->registerCommands();
        $this->registerExtensionCommands();
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
