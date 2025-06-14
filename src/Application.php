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
    private readonly ConfigLoader $configLoader;
    private readonly ProcessRunner $processRunner;
    private readonly ExtensionManager $extensionManager;

    public function __construct(string $name = 'Syntra', string $version = '1.0.0')
    {
        parent::__construct($name, $version);

        $this->configLoader = new ConfigLoader();
        $this->processRunner = new ProcessRunner();
        $this->extensionManager = new ExtensionManager($this->configLoader);

        $this->configLoader->load();

        $this->registerCommandGroup([
            'directory' => PACKAGE_ROOT . '/src/Commands/Refactor',
            'namespace' => 'Vix\\Syntra\\Commands\\Refactor',
            'enabled' => true
        ]);

        $this->registerCommandGroup([
            'directory' => PACKAGE_ROOT . '/src/Commands/Rector',
            'namespace' => 'Vix\\Syntra\\Commands\\Rector\\',
            'enabled' => $this->configLoader->get('tools.rector.enabled', false)
        ]);

        $this->registerExtensionCommands();
    }

    /**
     * @param array{enabled:bool, directory:string, namespace:string} $options
     */
    private function registerCommandGroup(array $options): void
    {
        if (!$options['enabled']) {
            return;
        }

        $commands = [];
        $phpFiles = (new FileHelper())->collectFiles($options['directory']);

        foreach ($phpFiles as $filePath) {
            // Derive the class name from the file path
            $relativePath = substr($filePath, strlen($options['directory']) + 1, -4);
            $class = $options['namespace'] . str_replace(['/', '\\'], '\\', $relativePath);

            if (!class_exists($class)) {
                continue;
            }

            // Ensure it's a concrete subclass of Symfony Command
            $reflectionClass = new ReflectionClass($class);

            if (
                is_subclass_of($class, SyntraCommand::class)
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
