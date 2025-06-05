<?php

declare(strict_types=1);

namespace Vix\Syntra;

use ReflectionClass;
use Symfony\Component\Console\Application as SymfonyApplication;
use Vix\Syntra\Utils\ConfigLoader;
use Vix\Syntra\Utils\ExtensionManager;
use Vix\Syntra\Utils\FileHelper;
use Vix\Syntra\Utils\ProcessRunner;

class Application extends SymfonyApplication
{
    private ConfigLoader $configLoader;
    private ProcessRunner $processRunner;
    private ExtensionManager $extensionManager;

    public function __construct(string $name = 'Syntra', string $version = '1.0.0')
    {
        parent::__construct($name, $version);

        $this->configLoader = new ConfigLoader();
        $this->processRunner = new ProcessRunner();
        $this->extensionManager = new ExtensionManager($this->configLoader);

        $this->registerCoreCommands();
        $this->registerExtensionCommands();
    }

    private function registerCoreCommands(): void
    {
        $directory = PACKAGE_ROOT . '/src/Commands';
        $namespacePrefix = 'Vix\\Syntra\\Commands\\';

        $commands = [];

        $phpFiles = (new FileHelper())->collectFiles($directory);

        foreach ($phpFiles as $filePath) {
            // Derive the class name from the file path
            $relativePath = substr($filePath, strlen($directory) + 1, -4);
            $class = $namespacePrefix . str_replace(['/', '\\'], '\\', $relativePath);

            // Ensure it's a concrete subclass of Symfony Command
            $reflectionClass = new ReflectionClass($class);

            if (
                class_exists($class)
                && is_subclass_of($class, SyntraCommand::class)
                && !$reflectionClass->isAbstract()
            ) {
                $constructor = $reflectionClass->getConstructor();

                if ($constructor === null || $constructor->getNumberOfRequiredParameters() === 0) {
                    $commands[] = $reflectionClass->newInstance();
                } elseif ($constructor && $constructor->getNumberOfRequiredParameters() === 3) {
                    $commands[] = $reflectionClass->newInstance(
                        $this->configLoader,
                        $this->processRunner,
                        $this->extensionManager
                    );
                } else {
                    continue;
                }
            }
        }

        $this->addCommands($commands);
    }

    private function registerExtensionCommands(): void
    {
        //
    }
}
