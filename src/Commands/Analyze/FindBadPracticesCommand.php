<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Analyze;

use PhpParser\NodeTraverser;
use Symfony\Component\Console\Command\Command;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Enums\ProgressIndicatorType;
use Vix\Syntra\NodeVisitors\AssignmentInConditionVisitor;
use Vix\Syntra\NodeVisitors\NestedTernaryVisitor;
use Vix\Syntra\Traits\AnalyzesFilesTrait;
use Vix\Syntra\Traits\ParsesPhpFilesTrait;

class FindBadPracticesCommand extends SyntraCommand
{
    use AnalyzesFilesTrait;
    use ParsesPhpFilesTrait;

    protected ProgressIndicatorType $progressType = ProgressIndicatorType::PROGRESS_BAR;

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('analyze:find-bad-practices')
            ->setDescription('Detects bad practices in code like magic numbers, nested ternaries, and assignments in conditions.')
            ->setHelp('Usage: vendor/bin/syntra analyze:find-bad-practices');
    }

    public function perform(): int
    {
        $rows = [];
        $this->analyzeFiles(function (string $file) use (&$rows): void {
            $visitors = [];

            $this->parseFile($file, function (NodeTraverser $traverser) use (&$visitors): void {
                $visitorClasses = [
                    NestedTernaryVisitor::class,
                    AssignmentInConditionVisitor::class,
                    // ReturnThrowVisitor::class,
                ];

                foreach ($visitorClasses as $visitorClass) {
                    $visitor = new $visitorClass();
                    $visitors[] = $visitor;
                    $traverser->addVisitor($visitor);
                }
            });

            foreach ($visitors as $visitor) {
                foreach ($visitor->getResults() as $finding) {
                    $rows[] = [
                        $file,
                        $finding['line'],
                        $this->snippet($finding['code'] ?? ''),
                        $finding['message'] ?? '',
                    ];
                }
            }
        });

        if (empty($rows)) {
            $this->output->success("All good. 👍");
            return Command::SUCCESS;
        }

        $rows = array_map(
            static fn (array $row): array => array_map('strval', $row),
            $rows,
        );

        $this->table(
            ['File', 'Line', 'Code', 'Comment'],
            $rows,
        );

        return Command::FAILURE;
    }

    private function snippet(string $code, int $maxLen = 60): string
    {
        $code = trim((string) preg_replace('/\s+/', ' ', $code));

        if (mb_strlen($code) > $maxLen) {
            return mb_substr($code, 0, $maxLen - 3) . '...';
        }

        return $code;
    }
}
