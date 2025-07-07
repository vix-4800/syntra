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

        $mdFile = $this->writeToMarkdown("$projectRoot/docs", $routesGrouped, 'Yii');

        $this->output->success("Routes successfully saved to $mdFile");

        return Command::SUCCESS;
    }

    private function writeToMarkdown(string $filePath, array $routes, string $suffix = ''): string
    {
        $md = '# 📘 Route documentation' . ($suffix ? " ($suffix)" : '') . "\n\n";
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
