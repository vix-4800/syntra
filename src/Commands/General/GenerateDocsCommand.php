<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\General;

use ReflectionClass;
use ReflectionMethod;
use Vix\Syntra\Commands\SyntraCommand;
use Symfony\Component\Console\Command\Command;
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

        $files = (new FileHelper())->collectFiles($controllerDir);

        $routes = [];

        foreach ($files as $file) {
            require_once $file;

            $className = $this->findClassNameInFile($file);
            if (!$className) {
                continue;
            }

            $reflector = new ReflectionClass($className);

            if ($reflector->isAbstract() || !preg_match('/Controller$/', $className)) {
                continue;
            }

            $controllerName = strtolower(preg_replace('/Controller$/', '', $reflector->getShortName()));

            foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (strpos($method->name, 'action') !== 0) {
                    continue;
                }

                $actionRaw = substr($method->name, 6);
                $action = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $actionRaw));

                $route = "$controllerName/$action";

                $description = '';
                if ($doc = $method->getDocComment()) {
                    $lines = explode("\n", $doc);
                    foreach ($lines as $line) {
                        $line = trim($line, "/* \t");
                        if ($line && strpos($line, '@') !== 0) {
                            $description = $line;
                            break;
                        }
                    }
                }

                $routes[] = [
                    'route' => $route,
                    'desc' => $description,
                ];
            }
        }

        if (empty($routes)) {
            $this->output->warning("Controllers with action methods not found.");
            return Command::SUCCESS;
        }

        $md = "# Route documentation (Yii)\n\n";
        foreach ($routes as $r) {
            $desc = $r['desc'] ? " — {$r['desc']}" : '';
            $md .= "- **`{$r['route']}`**{$desc}\n";
        }

        $this->output->writeln($md);

        return Command::SUCCESS;
    }

    private function findClassNameInFile(string $file)
    {
        $src = file_get_contents($file);

        $ns = preg_match('/namespace\s+(.+?);/', $src, $mns)
            ? $mns[1]
            : '';

        if (preg_match('/class\s+([^\s]+)/', $src, $mc)) {
            $cls = $mc[1];
        } else {
            return null;
        }

        return $ns ? "$ns\\$cls" : $cls;
    }
}
