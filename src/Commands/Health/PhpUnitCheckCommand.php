<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\Commands\Health\HealthCheckCommandInterface;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Exceptions\MissingBinaryException;
use Vix\Syntra\Facades\Process;
use Vix\Syntra\Facades\Project;
use Vix\Syntra\Traits\HandlesResultTrait;

class PhpUnitCheckCommand extends SyntraCommand implements HealthCheckCommandInterface
{
    use HandlesResultTrait;

    protected function configure(): void
    {
        parent::configure();
        $this->setName('health:phpunit')
            ->setDescription('Runs the PHPUnit test suite.');
    }

    public function runCheck(): CommandResult
    {
        $binary = find_composer_bin('phpunit', Project::getRootPath());

        if (!$binary) {
            throw new MissingBinaryException('phpunit', 'composer require --dev phpunit/phpunit');
        }

        $result = Process::run(
            $binary,
            [],
            ['working_dir' => Project::getRootPath()]
        );

        if ($result->exitCode === 0) {
            return CommandResult::ok(['PHPUnit tests passed.']);
        }

        $output = trim($result->output ?: $result->stderr);
        $messages = $output === '' ? ["PHPUnit failed with exit code $result->exitCode."] : preg_split('/\r?\n/', $output);

        return CommandResult::error($messages);
    }

    public function perform(): int
    {
        $this->output->section('Running PHPUnit tests...');

        $result = $this->runCheck();

        return $this->handleResult($result, 'PHPUnit tests finished.', $this->failOnWarning);
    }
}
