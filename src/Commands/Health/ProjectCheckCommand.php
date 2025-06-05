<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vix\Syntra\SyntraCommand;

class ProjectCheckCommand extends SyntraCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('project:check')
            ->setDescription('Run basic health checks: composer, phpstan, phpunit, etc.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output->section('Starting project health check...');

        $projectRoot = $this->configLoader->getProjectRoot();

        // 1) Composer
        $this->output->text('→ Checking Composer dependencies...');
        $composerChecker = new ComposerChecker($this->processRunner, $projectRoot);
        $result = $composerChecker->run();

        if ($result['status'] === 'ok') {
            $this->output->success('Composer: OK');
        } elseif ($result['status'] === 'warning') {
            $this->output->warning('Composer: outdated packages found');

            foreach ($result['messages'] as $msg) {
                $this->output->writeln("  - $msg");
            }
        } else {
            $this->output->error('Composer: ERROR');

            foreach ($result['messages'] as $msg) {
                $this->output->writeln("  - $msg");
            }

            return self::FAILURE;
        }

        // TODO: сюда позже добавим PHPStan, CS-Fixer, PHPUnit, SecurityChecker и т.д.

        $this->output->success('Project check completed without critical errors.');
        return self::SUCCESS;
    }
}
