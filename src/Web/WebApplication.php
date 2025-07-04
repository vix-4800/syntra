<?php

declare(strict_types=1);

namespace Vix\Syntra\Web;

use Exception;
use ReflectionClass;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\DI\ContainerInterface;
use Vix\Syntra\DI\ContainerFactory;
use Vix\Syntra\Utils\ConfigLoader;

class WebApplication
{
    private readonly ContainerInterface $container;
    private array $commands = [];

    public function __construct()
    {
        $this->container = ContainerFactory::create();
        $this->loadCommands();
    }

    public function run(): void
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Remove query string from URI
        $path = parse_url($requestUri, PHP_URL_PATH);

        // Simple routing
        switch ($path) {
            case '/':
                $this->handleHome();
                break;
            case '/api/commands':
                $this->handleApiCommands();
                break;
            case '/api/execute':
                if ($requestMethod === 'POST') {
                    $this->handleApiExecute();
                } else {
                    $this->sendError(405, 'Method Not Allowed');
                }
                break;
            case '/assets/style.css':
                $this->handleAssets('style.css', 'text/css');
                break;
            case '/assets/script.js':
                $this->handleAssets('script.js', 'application/javascript');
                break;
            default:
                $this->sendError(404, 'Not Found');
        }
    }

    private function handleHome(): void
    {
        $html = $this->renderTemplate('index.html', [
            'title' => 'Syntra Web Interface',
            'version' => '1.5.0'
        ]);

        header('Content-Type: text/html');
        echo $html;
    }

    private function handleApiCommands(): void
    {
        $webConfig = $this->getWebConfig();
        $commands = [];

        foreach ($this->commands as $commandInfo) {
            $class = $commandInfo['class'];
            $group = $commandInfo['group'];

            // Check if web access is enabled for this command
            if (!$this->isWebEnabled($group, $class, $webConfig)) {
                continue;
            }

            $commands[] = [
                'name' => $commandInfo['name'],
                'description' => $commandInfo['description'],
                'group' => $group,
                'class' => $class,
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($commands);
    }

    private function handleApiExecute(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['command'])) {
            $this->sendError(400, 'Command is required');
            return;
        }

        $commandClass = $input['command'];
        $options = $input['options'] ?? [];

        // Find the command
        $commandInfo = null;
        foreach ($this->commands as $info) {
            if ($info['class'] === $commandClass) {
                $commandInfo = $info;
                break;
            }
        }

        if (!$commandInfo) {
            $this->sendError(404, 'Command not found');
            return;
        }

        // Check if web access is enabled
        $webConfig = $this->getWebConfig();
        if (!$this->isWebEnabled($commandInfo['group'], $commandClass, $webConfig)) {
            $this->sendError(403, 'Web access disabled for this command');
            return;
        }

        try {
            $executor = $this->container->make(CommandExecutor::class);
            $result = $executor->execute($commandClass, $options);

            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (Exception $e) {
            $this->sendError(500, 'Command execution failed: ' . $e->getMessage());
        }
    }

    private function handleAssets(string $file, string $contentType): void
    {
        $filePath = __DIR__ . '/assets/' . $file;

        if (!file_exists($filePath)) {
            $this->sendError(404, 'Asset not found');
            return;
        }

        header('Content-Type: ' . $contentType);
        readfile($filePath);
    }

    private function loadCommands(): void
    {
        $configLoader = $this->container->get(ConfigLoader::class);

        $allCommands = array_merge(
            $configLoader->getEnabledCommands(),
            $configLoader->getEnabledExtensionCommands()
        );

        foreach ($allCommands as $class) {
            if (!class_exists($class)) {
                continue;
            }

            $reflectionClass = new ReflectionClass($class);

            if (!is_subclass_of($class, SyntraCommand::class) || $reflectionClass->isAbstract()) {
                continue;
            }

            try {
                $instance = $this->container->make($class);

                $this->commands[] = [
                    'class' => $class,
                    'name' => $instance->getName(),
                    'description' => $instance->getDescription(),
                    'group' => $this->getCommandGroup($class),
                ];
            } catch (Exception $e) {
                // Skip commands that can't be instantiated
                continue;
            }
        }
    }

    private function getCommandGroup(string $class): string
    {
        $config = require PACKAGE_ROOT . '/config.php';

        foreach ($config as $group => $commands) {
            if (isset($commands[$class])) {
                return $group;
            }
        }

        return 'unknown';
    }

    private function getWebConfig(): array
    {
        $config = require PACKAGE_ROOT . '/config.php';

        // Return the web configuration section
        return $config['web'] ?? [];
    }

    private function isWebEnabled(string $group, string $class, array $webConfig): bool
    {
        // Check if web access is globally disabled
        if (isset($webConfig['enabled']) && !$webConfig['enabled']) {
            return false;
        }

        // Get the command config from the main config
        $configLoader = $this->container->get(ConfigLoader::class);
        $commandConfig = $configLoader->getCommandConfig($group, $class);

        if (!$commandConfig) {
            return false;
        }

        if (is_array($commandConfig)) {
            return $commandConfig['web_enabled'] ?? true;
        }

        // If it's just a boolean, default to enabled for web
        return true;
    }

    private function renderTemplate(string $template, array $vars = []): string
    {
        $templatePath = __DIR__ . '/templates/' . $template;

        if (!file_exists($templatePath)) {
            throw new Exception("Template not found: $template");
        }

        extract($vars);

        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    private function sendError(int $code, string $message): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
    }
}
