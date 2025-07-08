<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Vix\Syntra\Enums\ProgressIndicatorType;
use Vix\Syntra\Exceptions\CommandException;
use Vix\Syntra\Exceptions\MissingBinaryException;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Facades\Project;
use Vix\Syntra\Facades\File;
use Vix\Syntra\Facades\Installer;
use Vix\Syntra\ProgressIndicators\ProgressIndicatorFactory;
use Vix\Syntra\ProgressIndicators\ProgressIndicatorInterface;
use Vix\Syntra\Traits\HandlesResultTrait;
use Vix\Syntra\Traits\HasStyledOutput;
use Vix\Syntra\Utils\TeeOutput;

abstract class SyntraCommand extends Command
{
    use HasStyledOutput;
    use HandlesResultTrait;

    protected InputInterface $input;

    protected bool $dryRun = false;
    protected bool $noProgress = false;
    protected bool $noCache = false;
    protected bool $failOnWarning = false;
    protected bool $ciMode = false;

    protected ?string $outputFile = null;

    protected ProgressIndicatorInterface $progressIndicator;

    protected ProgressIndicatorType $progressType = ProgressIndicatorType::SPINNER;

    protected int $progressMax = 0;

    protected string $path;

    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to operate on', Project::getRootPath())
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Do not apply changes, only show what would be done')
            ->addOption('no-progress', null, InputOption::VALUE_NONE, 'Disable progress output')
            ->addOption('no-cache', null, InputOption::VALUE_NONE, 'Disable file caching')
            ->addOption('fail-on-warning', null, InputOption::VALUE_NONE, 'Return exit code 1 if warnings were found')
            ->addOption('ci', null, InputOption::VALUE_NONE, 'CI mode (implies --no-progress and --fail-on-warning)')
            ->addOption('output-file', null, InputOption::VALUE_OPTIONAL, 'Write command output to the given file');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->input = $input;

        $this->outputFile = $input->getOption('output-file');
        if ($this->outputFile !== null) {
            $stream = fopen($this->outputFile, 'w');
            if ($stream !== false) {
                $fileOutput = new StreamOutput($stream);
                $output = new TeeOutput($output, $fileOutput);
            }
        }

        $this->output = new SymfonyStyle($input, $output);

        $this->dryRun = (bool) $input->getOption('dry-run');
        $this->noProgress = (bool) $input->getOption('no-progress');
        $this->noCache = (bool) $input->getOption('no-cache');
        $this->failOnWarning = (bool) $input->getOption('fail-on-warning');
        $this->ciMode = (bool) $input->getOption('ci') || getenv('CI') !== false;

        if ($this->ciMode) {
            $this->noProgress = true;
            $this->failOnWarning = true;
        }

        File::setCacheEnabled(!$this->noCache);

        $argPath = $input->getArgument('path');
        $this->path = $argPath !== null ? (string) $argPath : Project::getRootPath();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            return $this->perform();
        } catch (MissingBinaryException $e) {
            return $this->handleMissingBinary($e);
        } catch (CommandException $e) {
            $this->output->error($e->getMessage());

            return self::FAILURE;
        }
    }

    abstract public function perform(): int;

    protected function startProgress(): void
    {
        $type = $this->noProgress ? ProgressIndicatorType::NONE : $this->progressType;

        $this->progressIndicator = ProgressIndicatorFactory::create(
            $type,
            $this->output,
            $this->progressMax,
        );

        $this->progressIndicator->start();
    }

    protected function advanceProgress(int $step = 1): void
    {
        $this->progressIndicator->advance($step);
    }

    protected function finishProgress(): void
    {
        $this->progressIndicator->finish();
    }

    protected function setProgressMax(int $max): void
    {
        $this->progressMax = $max;
    }

    /**
     * Handle MissingBinaryException uniformly across commands.
     */
    protected function handleMissingBinary(MissingBinaryException $e): int
    {
        $this->output->error($e->getMessage());

        if ($e->suggestedInstall && $this->input->isInteractive()) {
            $helper = $this->getHelper('question');
            if ($helper instanceof QuestionHelper) {
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
        }

        return self::FAILURE;
    }
}
