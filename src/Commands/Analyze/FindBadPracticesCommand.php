<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Analyze;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Vix\Syntra\Commands\SyntraCommand;
use Symfony\Component\Console\Command\Command;
use Throwable;
use Vix\Syntra\Commands\Analyze\BadPractice\AssignmentInConditionVisitor;
use Vix\Syntra\Commands\Analyze\BadPractice\NestedTernaryVisitor;
use Vix\Syntra\Commands\Analyze\BadPractice\ReturnThrowVisitor;
use Vix\Syntra\Utils\FileHelper;

class FindBadPracticesCommand extends SyntraCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('analyze:find-bad-practices')
            ->setDescription('')
            ->setHelp('');
    }

    public function perform(): int
    {
        $projectRoot = $this->configLoader->getProjectRoot();
        $fileHelper = new FileHelper();
        $files = $fileHelper->collectFiles($projectRoot);

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

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

            $visitors = [
                new NestedTernaryVisitor(),
                new AssignmentInConditionVisitor(),
                // new ReturnThrowVisitor(),
            ];

            $traverser = new NodeTraverser();
            foreach ($visitors as $v) {
                $traverser->addVisitor($v);
            }
            $traverser->traverse($ast);

            foreach ($visitors as $v) {
                foreach ($v->findings as $finding) {
                    $rows[] = [
                        $file,
                        $finding['line'],
                        $this->snippet($finding['code'] ?? ''),
                        $finding['message'] ?? '',
                    ];
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

    private function snippet(string $code, int $maxLen = 60): string
    {
        $code = trim(preg_replace('/\s+/', ' ', $code));

        if (mb_strlen($code) > $maxLen) {
            return mb_substr($code, 0, $maxLen - 3) . '...';
        }

        return $code;
    }
}
