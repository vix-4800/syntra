<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Vix\Syntra\Exceptions\CommandException;
use Vix\Syntra\Exceptions\MissingBinaryException;
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

        $this->setName('project:check')
            ->setDescription('Run basic health checks: composer, phpstan, phpunit, etc.');
    }

    public function perform(): int
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

            try {
                $result = $item['checker']->run();
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
                        shell_exec($e->suggestedInstall);
                        $this->output->success('phpstan installed! Please re-run the command.');
                    }
                }

                continue;
            } catch (CommandException $e) {
                $this->output->error($e->getMessage());
                continue;
            }

            if ($result->isOk()) {
                $this->output->success("$name: OK");
            } elseif ($result->hasWarnings()) {
                $hasWarnings = true;
                $this->output->warning("$name: warning(s)");
                foreach ($result->messages as $msg) {
                    $this->output->writeln("  - $msg");
                }
            } else {
                $hasErrors = true;
                $this->output->error("$name: ERROR");
                foreach ($result->messages as $msg) {
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
