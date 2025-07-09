<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Analyze;

use PhpParser\NodeTraverser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Enums\ProgressIndicatorType;
use Vix\Syntra\NodeVisitors\LongMethodVisitor;
use Vix\Syntra\Traits\AnalyzesFilesTrait;
use Vix\Syntra\Traits\ContainerAwareTrait;
use Vix\Syntra\Traits\ParsesPhpFilesTrait;

class FindLongMethodsCommand extends SyntraCommand
{
    use ContainerAwareTrait;
    use AnalyzesFilesTrait;
    use ParsesPhpFilesTrait;

    protected ProgressIndicatorType $progressType = ProgressIndicatorType::PROGRESS_BAR;

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('analyze:find-long-methods')
            ->setDescription('Finds all methods or functions that exceed a specified number of lines, highlighting candidates for refactoring.')
            ->setHelp('Usage: vendor/bin/syntra analyze:find-long-methods [--max=LINES]')
            ->addOption('max', null, InputOption::VALUE_OPTIONAL, 'Max allowed method length (lines)', 75);
    }

    public function perform(): int
    {
        $maxLength = (int) $this->input->getOption('max');

        $longMethods = [];

        $this->analyzeFiles(function (string $filePath) use (&$longMethods, $maxLength): void {
            $this->parseFile($filePath, function (NodeTraverser $traverser) use (&$longMethods, $maxLength, $filePath): void {
                $visitor = new LongMethodVisitor($filePath, $maxLength, $longMethods);
                $traverser->addVisitor($visitor);
            });
        });

        if (empty($longMethods)) {
            $this->output->success("No methods/functions longer than $maxLength lines found. 👍");
            return Command::SUCCESS;
        }

        usort($longMethods, fn ($a, $b): int => [$a[0], $a[1], $a[2]] <=> [$b[0], $b[1], $b[2]]);

        $rows = array_map(
            static fn (array $row): array => array_map('strval', $row),
            $longMethods,
        );

        $this->table(
            ['File', 'Class', 'Method', 'Length', 'Start', 'End'],
            $rows,
        );

        $this->output->warning(count($longMethods) . " method(s)/function(s) longer than $maxLength lines found.");

        return Command::FAILURE;
    }
}
