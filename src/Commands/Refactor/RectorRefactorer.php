<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Refactor;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $result = $this->processRunner->run(
            find_composer_bin('rector', $this->configLoader->getProjectRoot()),
            [
                'process',
                $this->configLoader->getProjectRoot(),
                "--config=" . $this->configLoader->get('refactor.rector.config'),
            ],
        );

        $this->output->success('Rector refactoring completed.');

        return $result['exitCode'];
    }
}
