<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Exceptions\CommandException;
use Vix\Syntra\Exceptions\MissingBinaryException;
use Vix\Syntra\Traits\ContainerAwareTrait;
use Vix\Syntra\Traits\RunsCheckerTrait;

class ComposerCheckCommand extends SyntraCommand
{
    use ContainerAwareTrait;
    use RunsCheckerTrait;

    protected function configure(): void
    {
        parent::configure();
        $this->setName('health:composer')
            ->setDescription('Checks Composer dependencies for updates.');
    }

    public function perform(): int
    {
        $checker = $this->getNamedService('health.composer_checker', function () {
            $root = $this->configLoader->getProjectRoot();
            return new ComposerChecker($this->processRunner, $root);
        });

        $this->output->section('Running Composer check...');

        try {
            $result = $checker->run();
        } catch (MissingBinaryException|CommandException $e) {
            $this->output->error($e->getMessage());
            return self::FAILURE;
        }

        return $this->handleCheckerResult($result, 'Composer check completed.');
    }
}
