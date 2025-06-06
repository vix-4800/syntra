<?php

declare(strict_types=1);

namespace Vix\Syntra;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use Vix\Syntra\Contracts\AvailabilityCheckerInterface;
use Vix\Syntra\Traits\HasStyledOutput;
use Vix\Syntra\Utils\ConfigLoader;
use Vix\Syntra\Utils\ExtensionManager;
use Vix\Syntra\Utils\ProcessRunner;

abstract class SyntraCommand extends Command implements AvailabilityCheckerInterface
{
    use HasStyledOutput;

    protected bool $dryRun = false;

    public function __construct(
        protected ConfigLoader $configLoader,
        protected ProcessRunner $processRunner,
        protected ExtensionManager $extensionManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Root path of the project', null)
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Do not apply changes, only show what would be done');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->output = new SymfonyStyle($input, $output);

        $this->dryRun = (bool) $input->getOption('dry-run');

        if ($input->getOption('path')) {
            $this->configLoader->setProjectRoot((string) $input->getOption('path'));
        }

        // $this->extensionManager->registerFromConfig($this->configLoader->get('extensions', []));
    }
}
