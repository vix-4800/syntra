<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vix\Syntra\Exceptions\CommandException;

class RectorRefactorer extends SyntraRefactorCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('refactor:rector')
            ->setDescription('')
            ->addForceOption();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $binary = find_composer_bin('rector', $this->configLoader->getProjectRoot());

        if (!$binary) {
            throw new CommandException("rector not installed.");
        }

        $result = $this->processRunner->run(
            $binary,
            [
                'process',
                $this->configLoader->getProjectRoot(),
                "--config=" . $this->configLoader->get('refactor.rector.config'),
            ],
        );

        $this->output->success('Rector refactoring completed.');

        return $result->exitCode;
    }
}
