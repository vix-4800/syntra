<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Vix\Syntra\Commands\Health\ComposerCheckCommand;
use Vix\Syntra\Commands\Health\EditorConfigCheckCommand;
use Vix\Syntra\Commands\Health\PhpStanCheckCommand;
use Vix\Syntra\Commands\Health\PhpUnitCheckCommand;
use Vix\Syntra\Commands\Health\PhpVersionCheckCommand;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Exceptions\CommandException;
use Vix\Syntra\Exceptions\MissingBinaryException;
use Vix\Syntra\Facades\Installer;
use Vix\Syntra\Traits\CommandRunnerTrait;
use Vix\Syntra\Traits\HandlesResultTrait;

class ProjectCheckCommand extends SyntraCommand
{
    use CommandRunnerTrait;
    use HandlesResultTrait;

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
            try {
                $exitCode = $this->runCommand($item['class']);
            } catch (MissingBinaryException $e) {
                $this->output->error($e->getMessage());

                if ($e->suggestedInstall) {
                    /** @var QuestionHelper $helper */
                    $helper = $this->getHelper('question');

                    $question = new ConfirmationQuestion(
                        '<fg=yellow>Do you want to install it now? (y/N): </>',
                        false,
                        '/^(y|yes)$/i'
                    );

                    if ($helper->ask($this->input, $this->output, $question)) {
                        $this->output->writeln("Running: $e->suggestedInstall");
                        $commandResult = Installer::install($e->suggestedInstall);

                        $this->handleResult($commandResult, 'Installation finished.');
                    }
                }

                continue;
            } catch (CommandException $e) {
                $this->output->error($e->getMessage());
                continue;
            }

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
