<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\General;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use Vix\Syntra\Commands\SyntraCommand;
use Symfony\Component\Console\Command\Command;
use Throwable;
use Vix\Syntra\Utils\FileHelper;

class GenerateDocsCommand extends SyntraCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('general:generate-docs')
            ->setDescription('')
            ->setHelp('');
    }

    public function perform(): int
    {
        $projectRoot = $this->configLoader->getProjectRoot();
        $controllerDir = "$projectRoot/backend/controllers";

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

        $routes = [];

        $files = (new FileHelper())->collectFiles($controllerDir);
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

            $visitor = new class extends NodeVisitorAbstract {
                public array $routes = [];

                public function enterNode(Node $node): void
                {
                    if (
                        $node instanceof Class_
                        && $node->name !== null
                        && str_ends_with($node->name->name, 'Controller')
                    ) {
                        $short = $node->name->name;
                        $ctrl = preg_replace('/Controller$/', '', $short);
                        $ctrl = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $ctrl));

                        foreach ($node->getMethods() as $method) {
                            if (! $method->isPublic()) {
                                continue;
                            }

                            $mName = $method->name->name;
                            if (str_starts_with($mName, 'action')) {
                                $actionRaw = substr($mName, 6);
                                $action  = strtolower(
                                    preg_replace('/([a-z])([A-Z])/', '$1-$2', $actionRaw)
                                );
                                $route = "$ctrl/$action";

                                $desc = '';
                                if ($doc = $method->getDocComment()) {
                                    $lines = explode("\n", $doc->getText());
                                    foreach ($lines as $line) {
                                        $txt = trim($line, "/* \t");
                                        if ($txt !== '' && $txt[0] !== '@') {
                                            $desc = $txt;
                                            break;
                                        }
                                    }
                                }

                                $this->routes[] = [
                                    'route' => $route,
                                    'desc' => $desc,
                                ];
                            }
                        }
                    }
                }
            };

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
