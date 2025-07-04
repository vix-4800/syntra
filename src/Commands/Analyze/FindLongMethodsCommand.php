<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Analyze;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Vix\Syntra\Commands\SyntraCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Throwable;
use Vix\Syntra\NodeVisitors\LongMethodVisitor;
use Vix\Syntra\Traits\ContainerAwareTrait;
use Vix\Syntra\Utils\FileHelper;

class FindLongMethodsCommand extends SyntraCommand
{
    use ContainerAwareTrait;

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

        $fileHelper = $this->getService(FileHelper::class, fn(): FileHelper => new FileHelper());
        $parser = $this->getService(Parser::class, fn(): Parser => (new ParserFactory())->create(ParserFactory::PREFER_PHP7));

        $files = $fileHelper->collectFiles($projectRoot);

        $maxLength = (int) $this->input->getOption('max');

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
            $visitor = new LongMethodVisitor($filePath, $maxLength, $longMethods);

            $traverser->addVisitor($visitor);
            $traverser->traverse($stmts);
        }

        if (empty($longMethods)) {
            $this->output->success("No methods/functions longer than $maxLength lines found. 👍");
            return Command::SUCCESS;
        }

        usort($longMethods, fn($a, $b): int => [$a[0], $a[1], $a[2]] <=> [$b[0], $b[1], $b[2]]);

        $this->table(
            ['File', 'Class', 'Method', 'Length', 'Start', 'End'],
            $longMethods
        );

        $this->output->warning(count($longMethods) . " method(s)/function(s) longer than $maxLength lines found.");

        return Command::FAILURE;
    }
}
