<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Vix\Syntra\ProgressIndicators\ProgressIndicatorFactory;
use Vix\Syntra\ProgressIndicators\ProgressIndicatorInterface;
use Vix\Syntra\Traits\HasStyledOutput;
use Vix\Syntra\Utils\ConfigLoader;
use Vix\Syntra\Utils\ProcessRunner;

abstract class SyntraCommand extends Command
{
    use HasStyledOutput;

    protected InputInterface $input;

    protected bool $dryRun = false;
    protected bool $noProgress = false;

    protected ProgressIndicatorInterface $progressIndicator;

    protected string $progressType = ProgressIndicatorFactory::TYPE_SPINNER;

    protected int $progressMax = 0;

    public function __construct(
        protected ConfigLoader $configLoader,
        protected ProcessRunner $processRunner
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::OPTIONAL, 'Root path of the project', $this->configLoader->getProjectRoot())
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Do not apply changes, only show what would be done')
            ->addOption('no-progress', null, InputOption::VALUE_NONE, 'Disable progress output');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->input = $input;
        $this->output = new SymfonyStyle($input, $output);

        $this->dryRun = (bool) $input->getOption('dry-run');
        $this->noProgress = (bool) $input->getOption('no-progress');

        $argPath = $input->getArgument('path');
        if ($argPath !== null) {
            $this->configLoader->setProjectRoot((string) $argPath);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->perform();
    }

    abstract public function perform(): int;

    protected function startProgress(): void
    {
        $type = $this->noProgress ? ProgressIndicatorFactory::TYPE_NONE : $this->progressType;

        $this->progressIndicator = ProgressIndicatorFactory::create(
            $type,
            $this->output,
            $this->progressMax,
        );

        $this->progressIndicator->start();
    }

    protected function advanceProgress(): void
    {
        $this->progressIndicator->advance();
    }

    protected function finishProgress(): void
    {
        $this->progressIndicator->finish();
    }

    protected function setProgressMax(int $max): void
    {
        $this->progressMax = $max;
    }
}
