<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\Commands\Health\AbstractHealthCommand;
use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Enums\Tool;
use Vix\Syntra\Facades\Process;
use Vix\Syntra\Facades\Project;
use Vix\Syntra\Traits\HasBinaryTool;

class PhpUnitCheckCommand extends AbstractHealthCommand
{
    use HasBinaryTool;
    protected string $sectionTitle = 'Running PHPUnit tests...';

    public function __construct()
    {
        parent::__construct();
        $this->successMessage = Tool::PHPUNIT->name() . ' tests finished.';
    }

    protected function configure(): void
    {
        parent::configure();
        $this->setName('health:phpunit')
            ->setDescription('Runs the PHPUnit test suite.');
    }

    public function runCheck(): CommandResult
    {
        $this->findBinaryTool(Tool::PHPUNIT);

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
}
