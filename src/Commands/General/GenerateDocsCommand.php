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
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\File;
use Vix\Syntra\Facades\Project;
use Vix\Syntra\NodeVisitors\DocsVisitor;
use Vix\Syntra\Traits\ContainerAwareTrait;
use Vix\Syntra\Utils\ProjectDetector;

class GenerateDocsCommand extends SyntraCommand
{
    use ContainerAwareTrait;

    protected ProgressIndicatorType $progressType = ProgressIndicatorType::PROGRESS_BAR;

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('general:generate-docs')
            ->setDescription('Scans project controllers and generates a markdown file listing all action routes (currently only Yii is supported).')
            ->setHelp('Usage: vendor/bin/syntra general:generate-docs [--controllerDir=controllerDir]')
            ->addOption('controllerDir', null, InputOption::VALUE_OPTIONAL, 'Relative path to controllers directory', 'backend/controllers');
    }

    public function perform(): int
    {
        $projectRoot = Config::getProjectRoot();

        $type = Project::detect($projectRoot);

        if ($type === ProjectDetector::TYPE_YII) {
            return $this->generateForYii($projectRoot);
        }

        $this->output->warning('Unsupported project type or framework not detected.');

        return Command::SUCCESS;
    }

    private function generateForYii(string $projectRoot): int
    {
        $controllerDirOption = $this->input->getOption('controllerDir');
        $controllerDir = $projectRoot . '/' . ltrim((string) ($controllerDirOption ?? 'backend/controllers'), '/');

        $parser = $this->getService(Parser::class, fn (): Parser => (new ParserFactory())->create(ParserFactory::PREFER_PHP7));

        $routes = [];

        $files = File::collectFiles($controllerDir);

        $this->setProgressMax(count($files));
        $this->startProgress();

        foreach ($files as $file) {
            $code = file_get_contents($file);
            if ($code === false) {
                continue;
            }

            try {
                $ast = $parser->parse($code);
            } catch (Throwable) {
                continue;
            }

            $visitor = new DocsVisitor();

            $traverser = new NodeTraverser();
            $traverser->addVisitor($visitor);
            $traverser->traverse($ast);

            $routes = array_merge($routes, $visitor->getResults());

            $this->advanceProgress();
        }

        $this->finishProgress();

        if (empty($routes)) {
            $this->output->warning('Controllers with action methods not found.');
            return Command::SUCCESS;
        }

        $routesGrouped = [];
        foreach ($routes as $route) {
            [$controller, $action] = explode('/', (string) $route['route']);
            $routesGrouped[$controller][] = [
                'action' => $action,
                'desc' => $route['desc'],
            ];
        }
        // Count references to each route across controllers and view files
        $searchFiles = File::collectFiles(
            $projectRoot,
            ['php', 'phtml', 'twig']
        );
        $searchFiles = array_filter(
            $searchFiles,
            static fn(string $f): bool => str_contains($f, 'controllers') || str_contains($f, 'views')
        );

        $refCounts = [];
        foreach ($routes as $route) {
            $count = 0;
            foreach ($searchFiles as $f) {
                $content = file_get_contents($f);
                if ($content === false) {
                    continue;
                }
                $count += substr_count($content, (string) $route['route']);
            }
            $refCounts[(string) $route['route']] = $count;
        }
        $mdFile = $this->writeToMarkdown("$projectRoot/docs", $routesGrouped, $refCounts, 'Yii');

        $rows = array_map(
            static fn(array $r): array => [strval($r['route']), (string) $refCounts[$r['route']]],
            $routes
        );
        $this->table(['Route', 'Refs'], $rows);

        $totalRefs = array_sum($refCounts);

        $this->output->success(
            sprintf(
                'Routes successfully saved to %s. Found %d references.',
                $mdFile,
                $totalRefs
            )
        );

        return Command::SUCCESS;
    }

    private function writeToMarkdown(string $filePath, array $routes, array $refCounts = [], string $suffix = ''): string
    {
        $md = '# 📘 Route documentation' . ($suffix ? " ($suffix)" : '') . "\n\n";
        ksort($routes);

        foreach ($routes as $controller => $actions) {
            $md .= "## `$controller`\n\n";
            $md .= "| Method                    | Refs | Description                     |\n";
            $md .= "|---------------------------|------|----------------------------------------------|\n";

            foreach ($actions as $a) {
                $method = "`{$a['action']}`";
                $desc = $a['desc'] ?: '';
                $routeKey = $controller . '/' . $a['action'];
                $refs = $refCounts[$routeKey] ?? 0;
                $md .= sprintf("| %-25s | %4d | %-50s |\n", $method, $refs, $desc);
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
