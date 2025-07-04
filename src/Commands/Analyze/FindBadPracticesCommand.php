<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Analyze;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Vix\Syntra\Commands\SyntraCommand;
use Symfony\Component\Console\Command\Command;
use Throwable;
use Vix\Syntra\NodeVisitors\AssignmentInConditionVisitor;
use Vix\Syntra\NodeVisitors\NestedTernaryVisitor;
use Vix\Syntra\NodeVisitors\ReturnThrowVisitor;
use Vix\Syntra\Traits\ContainerAwareTrait;
use Vix\Syntra\Utils\FileHelper;

class FindBadPracticesCommand extends SyntraCommand
{
    use ContainerAwareTrait;

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('analyze:find-bad-practices')
            ->setDescription('Detects bad practices in code like magic numbers, nested ternaries, and assignments in conditions.')
            ->setHelp('');
    }

    public function perform(): int
    {
        $projectRoot = $this->configLoader->getProjectRoot();

        // Use dependency injection to get services
        $fileHelper = $this->getFileHelper();
        $parser = $this->getParser();
        $traverserFactory = $this->getTraverserFactory();

        $files = $fileHelper->collectFiles($projectRoot);

        $rows = [];
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

            // Use visitor classes through DI
            $visitorClasses = [
                NestedTernaryVisitor::class,
                AssignmentInConditionVisitor::class,
                // ReturnThrowVisitor::class,
            ];

            $traverser = $traverserFactory($visitorClasses);
            $traverser->traverse($ast);

            // Get findings from visitors
            foreach ($traverser->getVisitors() as $visitor) {
                if (property_exists($visitor, 'findings')) {
                    foreach ($visitor->findings as $finding) {
                        $rows[] = [
                            $file,
                            $finding['line'],
                            $this->snippet($finding['code'] ?? ''),
                            $finding['message'] ?? '',
                        ];
                    }
                }
            }
        }

        if (empty($rows)) {
            $this->output->success("All good. 👍");
            return Command::SUCCESS;
        }

        $this->table(
            ['File', 'Line', 'Code', 'Comment'],
            $rows
        );

        return Command::FAILURE;
    }

    /**
     * Get FileHelper from DI container or create new instance
     */
    private function getFileHelper(): FileHelper
    {
        return $this->getService(FileHelper::class, function () {
            return new FileHelper();
        });
    }

    /**
     * Get Parser from DI container or create new instance
     */
    private function getParser(): Parser
    {
        return $this->getService(Parser::class, function () {
            return (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        });
    }

    /**
     * Get Traverser Factory from DI container or create new instance
     */
    private function getTraverserFactory(): callable
    {
        return $this->getNamedService('parser.traverser_factory', function () {
            // Fallback factory
            return function (array $visitorClasses = []): NodeTraverser {
                $traverser = new NodeTraverser();

                foreach ($visitorClasses as $visitorClass) {
                    $visitor = new $visitorClass();
                    $traverser->addVisitor($visitor);
                }

                return $traverser;
            };
        });
    }

    private function snippet(string $code, int $maxLen = 60): string
    {
        $code = trim(preg_replace('/\s+/', ' ', $code));

        if (mb_strlen($code) > $maxLen) {
            return mb_substr($code, 0, $maxLen - 3) . '...';
        }

        return $code;
    }
}
