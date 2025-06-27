<?php

declare(strict_types=1);

namespace Vix\Syntra;

use ReflectionClass;
use Symfony\Component\Console\Application as SymfonyApplication;
use Vix\Syntra\Utils\ConfigLoader;
use Vix\Syntra\Utils\ExtensionManager;
use Vix\Syntra\Utils\ProcessRunner;

class Application extends SymfonyApplication
{
    private readonly ConfigLoader $configLoader;
    private readonly ProcessRunner $processRunner;
    private readonly ExtensionManager $extensionManager;

    public function __construct(string $name = 'Syntra', string $version = '1.0.0')
    {
        parent::__construct($name, $version);

        $this->configLoader = new ConfigLoader();
        $this->processRunner = new ProcessRunner();
        $this->extensionManager = new ExtensionManager($this->configLoader);

        $this->registerCommands();

        $this->registerExtensionCommands();
    }

    private function registerCommands(): void
    {
        foreach ($this->configLoader->getEnabledCommands() as $commandClass) {
            if (!class_exists($commandClass)) {
                continue;
            }

            // Ensure it's a concrete subclass of Syntra Command
            $reflectionClass = new ReflectionClass($commandClass);

            if (
                is_subclass_of($commandClass, SyntraCommand::class)
                && !$reflectionClass->isAbstract()
            ) {
                $instance = $reflectionClass->newInstance(
                    $this->configLoader,
                    $this->processRunner,
                    $this->extensionManager
                );

                $this->add($instance);
            }
        }
    }

    private function registerExtensionCommands(): void
    {
        //
    }
}
