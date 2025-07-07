<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\Commands\Health\ComposerCheckCommand;
use Vix\Syntra\Commands\Health\EditorConfigCheckCommand;
use Vix\Syntra\Commands\Health\PhpStanCheckCommand;
use Vix\Syntra\Commands\Health\PhpUnitCheckCommand;
use Vix\Syntra\Commands\Health\PhpVersionCheckCommand;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Traits\CommandRunnerTrait;

class ProjectCheckCommand extends SyntraCommand
{
    use CommandRunnerTrait;

    protected function configure(): void
    {
        parent::configure();

        $this->setName('health:project')
            ->setDescription('Run basic health checks: composer, phpstan, phpunit, etc.')
            ->setHelp('Usage: vendor/bin/syntra health:project');
    }

    public function perform(): int
    {
        $this->output->section('Starting project health check...');

        $checks = [
            ['name' => 'PHP Version', 'class' => PhpVersionCheckCommand::class],
            ['name' => 'EditorConfig', 'class' => EditorConfigCheckCommand::class],
            ['name' => 'Composer', 'class' => ComposerCheckCommand::class],
            ['name' => 'PHPStan', 'class' => PhpStanCheckCommand::class],
            ['name' => 'PHPUnit', 'class' => PhpUnitCheckCommand::class],
        ];

        $hasErrors = false;
        foreach ($checks as $item) {
            $exitCode = $this->runCommand($item['class']);

            if ($exitCode !== self::SUCCESS) {
                $hasErrors = true;
            }
        }

        if ($hasErrors) {
            return self::FAILURE;
        }

        $this->output->success('Project check completed without critical errors.');
        return self::SUCCESS;
    }
}
