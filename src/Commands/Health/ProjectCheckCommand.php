<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vix\Syntra\SyntraCommand;

class ProjectCheckCommand extends SyntraCommand
{
    public function isAvailable(): bool
    {
        return true;
    }

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

        $checks = [];

        $checks[] = [
            'name' => 'Composer',
            'checker' => new ComposerChecker($this->processRunner, $projectRoot),
        ];

        $checks[] = [
            'name' => 'PHPStan',
            'checker' => new PhpStanChecker(
                $this->processRunner,
                $projectRoot,
                (int)($this->configLoader->get('tools.phpstan.level', 5)),
                $this->configLoader->get('tools.phpstan.config', 'phpstan.neon'),
            ),
        ];

        $hasErrors = false;
        $hasWarnings = false;

        foreach ($checks as $item) {
            $name = $item['name'];
            $result = $item['checker']->run();

            if ($result['status'] === 'ok') {
                $this->output->success("$name: OK");
            } elseif ($result['status'] === 'warning') {
                $hasWarnings = true;
                $this->output->warning("$name: warning(s)");
                foreach ($result['messages'] as $msg) {
                    $this->output->writeln("  - $msg");
                }
            } else {
                $hasErrors = true;
                $this->output->error("$name: ERROR");
                foreach ($result['messages'] as $msg) {
                    $this->output->writeln("  - $msg");
                }
            }
        }

        if ($hasErrors) {
            return self::FAILURE;
        }

        $this->output->success('Project check completed without critical errors.');
        return self::SUCCESS;
    }
}
