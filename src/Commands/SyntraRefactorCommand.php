<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Traits\HasDangerLevel;

abstract class SyntraRefactorCommand extends SyntraCommand
{
    use HasDangerLevel;

    protected function configure(): void
    {
        parent::configure();

        $this->addForceOption();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->force = (bool) $input->getOption('force');

        parent::initialize($input, $output);
    }
}
