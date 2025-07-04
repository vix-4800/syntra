<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\Exceptions\CommandException;
use Vix\Syntra\Exceptions\MissingBinaryException;
use Vix\Syntra\Traits\ContainerAwareTrait;
use Vix\Syntra\Traits\RunsCheckerTrait;

class PhpStanCheckCommand extends SyntraCommand
{
    use ContainerAwareTrait;
    use RunsCheckerTrait;

    protected function configure(): void
    {
        parent::configure();
        $this->setName('health:phpstan')
            ->setDescription('Runs PHPStan static analysis.');
    }

    public function perform(): int
    {
        $checker = $this->getNamedService('health.phpstan_checker', function () {
            $root = $this->configLoader->getProjectRoot();
            $level = (int) $this->configLoader->getCommandOption('health', PhpStanChecker::class, 'level', 0);
            $config = $this->configLoader->getCommandOption('health', PhpStanChecker::class, 'config');
            return new PhpStanChecker($this->processRunner, $root, $level, $config);
        });

        $this->output->section('Running PHPStan...');

        try {
            $result = $checker->run();
        } catch (MissingBinaryException|CommandException $e) {
            $this->output->error($e->getMessage());
            return self::FAILURE;
        }

        return $this->handleCheckerResult($result, 'PHPStan analysis completed.');
    }
}
