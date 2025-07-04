<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\General;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Vix\Syntra\Commands\SyntraCommand;
use Symfony\Component\Console\Command\Command;
use Throwable;
use Vix\Syntra\NodeVisitors\DocsVisitor;
use Vix\Syntra\Traits\ContainerAwareTrait;
use Vix\Syntra\Utils\FileHelper;

class GenerateDocsCommand extends SyntraCommand
{
    use ContainerAwareTrait;

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('general:generate-docs')
            ->setDescription('Scans Yii controllers and generates a markdown file listing all action routes with optional descriptions.')
            ->setHelp('');
    }

    public function perform(): int
    {
        $projectRoot = $this->configLoader->getProjectRoot();
        $controllerDir = "$projectRoot/backend/controllers";

        $fileHelper = $this->getService(FileHelper::class, fn(): FileHelper => new FileHelper());
        $parser = $this->getService(Parser::class, fn(): Parser => (new ParserFactory())->create(ParserFactory::PREFER_PHP7));

        $routes = [];

        $files = $fileHelper->collectFiles($controllerDir);
        foreach ($files as $file) {
            $code = file_get_contents($file);
            if ($code === false) {
                continue;
            }

            try {
                $ast = $parser->parse($code);
            } catch (Throwable $e) {
                continue;
            }

            $visitor = new DocsVisitor;

            $traverser = new NodeTraverser();
            $traverser->addVisitor($visitor);
            $traverser->traverse($ast);

            $routes = array_merge($routes, $visitor->routes);
        }

        if (empty($routes)) {
            $this->output->warning("Controllers with action methods not found.");
            return Command::SUCCESS;
        }

        $routesGrouped = [];
        foreach ($routes as $route) {
            [$controller, $action] = explode('/', $route['route']);
            $routesGrouped[$controller][] = [
                'action' => $action,
                'desc' => $route['desc'],
            ];
        }

        $mdFile = $this->writeToMarkdown("$projectRoot/docs", $routesGrouped);

        $this->output->success("Routes successfully saved to $mdFile");

        return Command::SUCCESS;
    }

    private function writeToMarkdown(string $filePath, array $routes): string
    {
        $md = "# 📘 Route documentation (Yii)\n\n";
        ksort($routes);

        foreach ($routes as $controller => $actions) {
            $md .= "## `$controller`\n\n";
            $md .= "| Method                    | Description                                        |\n";
            $md .= "|---------------------------|----------------------------------------------------|\n";

            foreach ($actions as $a) {
                $method = "`{$a['action']}`";
                $desc = $a['desc'] ?: '';
                $md .= sprintf("| %-25s | %-50s |\n", $method, $desc);
            }

            $md .= "\n";
        }

        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $mdFile = "$filePath/routes.md";
        file_put_contents($mdFile, $md);

        return $mdFile;
    }
}
