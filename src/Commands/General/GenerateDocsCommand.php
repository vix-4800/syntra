<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\General;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Throwable;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Enums\ProgressIndicatorType;
use Vix\Syntra\Facades\File;
use Vix\Syntra\Facades\Project;
use Vix\Syntra\NodeVisitors\DocsVisitor;
use Vix\Syntra\Traits\ContainerAwareTrait;
use Vix\Syntra\Traits\AnalyzesFilesTrait;
use Vix\Syntra\Utils\ProjectInfo;

class GenerateDocsCommand extends SyntraCommand
{
    use ContainerAwareTrait;
    use AnalyzesFilesTrait;

    protected ProgressIndicatorType $progressType = ProgressIndicatorType::PROGRESS_BAR;

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('general:generate-docs')
            ->setDescription('Scans project controllers and generates a markdown file listing all action routes (currently only Yii is supported).')
            ->setHelp('Usage: vendor/bin/syntra general:generate-docs [--controller-dir=controllerDir] [--count-refs]')
            ->addOption('controller-dir', null, InputOption::VALUE_OPTIONAL, 'Relative path to controllers directory', 'backend/controllers')
            ->addOption('count-refs', null, InputOption::VALUE_NONE, 'Count references to each route in controllers and views');
    }

    public function perform(): int
    {
        $rootPath = Project::getRootPath();
        $type = Project::detect($rootPath);

        if ($type === ProjectInfo::TYPE_YII) {
            return $this->generateForYii($rootPath);
        }

        $this->output->warning('Unsupported project type or framework not detected.');

        return Command::SUCCESS;
    }

    private function generateForYii(string $rootPath): int
    {
        $controllerDirOption = $this->input->getOption('controller-dir');
        $controllerDir = $rootPath . '/' . ltrim((string) ($controllerDirOption ?? 'backend/controllers'), '/');

        $parser = $this->resolveService(Parser::class, fn (): Parser => (new ParserFactory())->create(ParserFactory::PREFER_PHP7));

        $routes = [];

        $prevPath = $this->path;
        $this->path = $controllerDir;
        $files = $this->collectFiles();
        $this->path = $prevPath;

        $this->iterateFiles($files, function (string $file) use (&$routes, $parser): void {
            $code = file_get_contents($file);
            if ($code === false) {
                return;
            }

            try {
                $ast = $parser->parse($code);
            } catch (Throwable) {
                return;
            }

            $visitor = new DocsVisitor();

            $traverser = new NodeTraverser();
            $traverser->addVisitor($visitor);
            $traverser->traverse($ast);

            $routes = array_merge($routes, $visitor->getResults());
        });

        if (empty($routes)) {
            $this->output->warning('Controllers with action methods not found.');
            return Command::SUCCESS;
        }

        $routesGrouped = [];
        foreach ($routes as $route) {
            [$controller, $action] = explode('/', (string) $route['route']);
            $routesGrouped[$controller][] = [
                'action' => $action,
                'params' => $route['params'],
                'desc' => $route['desc'],
            ];
        }
        $mdFile = '';
        $refCounts = [];
        if ($this->input->getOption('count-refs')) {
            // Count references to each route across controllers and view files
            $prevPath = $this->path;
            $this->path = $rootPath;
            $searchFiles = $this->collectFiles();
            $this->path = $prevPath;
            $searchFiles = array_merge(
                $searchFiles,
                File::collectFiles(
                    $rootPath,
                    ['phtml', 'twig']
                )
            );
            $searchFiles = array_filter(
                $searchFiles,
                static fn (string $f): bool => str_contains($f, 'controllers') || str_contains($f, 'views')
            );

            $this->iterateFiles($routes, function (array $route) use ($searchFiles, &$refCounts): void {
                $count = 0;
                foreach ($searchFiles as $f) {
                    $content = file_get_contents($f);
                    if ($content === false) {
                        continue;
                    }
                    $count += substr_count($content, (string) $route['route']);
                }
                $refCounts[(string) $route['route']] = $count;
            });
            $mdFile = $this->writeToMarkdown("$rootPath/docs", $routesGrouped, $refCounts, 'Yii');

            $this->output->success(
                sprintf('Routes successfully saved to %s.', $mdFile)
            );
        } else {
            $mdFile = $this->writeToMarkdown("$rootPath/docs", $routesGrouped, [], 'Yii');

            $this->output->success("Routes successfully saved to $mdFile");
        }

        return Command::SUCCESS;
    }

    private function writeToMarkdown(string $filePath, array $routes, array $refCounts = [], string $suffix = ''): string
    {
        $md = '# 📘 Route documentation' . ($suffix ? " ($suffix)" : '') . "\n\n";
        ksort($routes);

        foreach ($routes as $controller => $actions) {
            $md .= "## `$controller`\n\n";
            $md .= "| Method                    | Refs | Params                   | Description                                        |\n";
            $md .= "|---------------------------|------|--------------------------|----------------------------------------------------|\n";

            foreach ($actions as $a) {
                $method = "`{$a['action']}`";
                $params = implode(", ", $a["params"]);
                $desc = $a['desc'] ?: '';

                $routeKey = $controller . '/' . $a['action'];
                $refs = $refCounts[$routeKey] ?? 0;

                $md .= sprintf("| %-25s | %4d | %-23s | %-50s |\n", $method, $refs, $params, $desc);
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
