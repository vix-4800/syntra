<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Analyze;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use Vix\Syntra\Commands\SyntraCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Throwable;
use Vix\Syntra\Utils\FileHelper;

class FindLongMethodsCommand extends SyntraCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('analyze:find-long-methods')
            ->setDescription('Finds all methods or functions that exceed a specified number of lines, highlighting candidates for refactoring.')
            ->setHelp('')
            ->addOption('max', null, InputOption::VALUE_OPTIONAL, 'Max allowed method length (lines)', 75);
    }

    public function perform(): int
    {
        $projectRoot = $this->configLoader->getProjectRoot();
        $fileHelper = new FileHelper();
        $files = $fileHelper->collectFiles($projectRoot);

        $maxLength = (int) $this->input->getOption('max');

        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $longMethods = [];

        foreach ($files as $filePath) {
            $code = file_get_contents($filePath);
            if ($code === false) {
                continue;
            }

            try {
                $stmts = $parser->parse($code);
            } catch (Throwable) {
                continue;
            }

            $traverser = new NodeTraverser();
            $visitor = new class($filePath, $maxLength, $longMethods) extends NodeVisitorAbstract {
                private string $filePath;
                private int $maxLength;
                public array $results;

                public function __construct($filePath, $maxLength, &$results)
                {
                    $this->filePath = $filePath;
                    $this->maxLength = $maxLength;
                    $this->results = &$results;
                }

                public function enterNode(Node $node): void
                {
                    if (
                        $node instanceof ClassMethod ||
                        $node instanceof Function_
                    ) {
                        $startLine = $node->getStartLine();
                        $endLine = $node->getEndLine();
                        $length = $endLine - $startLine + 1;

                        if ($length > $this->maxLength) {
                            $class = $node instanceof ClassMethod && $node->getAttribute('parent') instanceof Class_
                                ? $node->getAttribute('parent')->name->toString()
                                : '';

                            $this->results[] = [
                                $this->filePath,
                                $class,
                                $node->name->toString(),
                                $length,
                                $startLine,
                                $endLine,
                            ];
                        }
                    }

                    // Для корректного вывода имени класса добавим ссылку на parent в дочерних узлах
                    if ($node instanceof Class_) {
                        foreach ($node->getMethods() as $method) {
                            $method->setAttribute('parent', $node);
                        }
                    }
                }
            };

            $traverser->addVisitor($visitor);
            $traverser->traverse($stmts);
        }

        if (empty($longMethods)) {
            $this->output->success("No methods/functions longer than $maxLength lines found. 👍");
            return 0;
        }

        usort($longMethods, fn($a, $b) => [$a[0], $a[1], $a[2]] <=> [$b[0], $b[1], $b[2]]);

        $this->table(
            ['File', 'Class', 'Method', 'Length', 'Start', 'End'],
            $longMethods
        );

        $this->output->warning(count($longMethods) . " method(s)/function(s) longer than $maxLength lines found.");

        return Command::SUCCESS;
    }
}
