<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\Commands\Health\HealthCheckCommandInterface;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Facades\Process;
use Vix\Syntra\Facades\Project;
use Vix\Syntra\Tools\PhpUnitTool;
use Vix\Syntra\Traits\HandlesResultTrait;
use Vix\Syntra\Traits\HasBinaryTool;

class PhpUnitCheckCommand extends SyntraCommand implements HealthCheckCommandInterface
{
    use HandlesResultTrait;
    use HasBinaryTool;

    protected function configure(): void
    {
        parent::configure();
        $this->setName('health:phpunit')
            ->setDescription('Runs the PHPUnit test suite.');
    }

    public function runCheck(): CommandResult
    {
        $result = Process::run(
            $this->binary,
            options: ['working_dir' => Project::getRootPath()]
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

        $this->findBinaryTool(new PhpUnitTool());
        $result = $this->runCheck();

        return $this->handleResult($result, 'PHPUnit tests finished.', $this->failOnWarning);
    }
}
