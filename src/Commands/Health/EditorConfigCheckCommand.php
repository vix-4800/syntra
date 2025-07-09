<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Vix\Syntra\Commands\Health\AbstractHealthCommand;
use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Enums\CommandStatus;
use Vix\Syntra\Facades\Project;

class EditorConfigCheckCommand extends AbstractHealthCommand
{
    protected string $sectionTitle = 'Checking for .editorconfig...';
    protected string $successMessage = '.editorconfig check completed.';

    protected function configure(): void
    {
        parent::configure();
        $this->setName('health:editorconfig')
            ->setDescription('Checks for the existence of a .editorconfig file.')
            ->addOption('generate', 'g', InputOption::VALUE_NONE, 'Generate a default .editorconfig file if missing');
    }

    public function runCheck(): CommandResult
    {
        $path = Project::getRootPath() . '/.editorconfig';

        if (is_file($path)) {
            return CommandResult::ok(['.editorconfig file exists.']);
        }

        return CommandResult::warning([
            '.editorconfig file not found. Use --generate to create a default one.',
        ]);
    }

    protected function afterCheck(CommandResult $result): int
    {
        if ($result->status === CommandStatus::WARNING && $this->input->getOption('generate')) {
            $path = Project::getRootPath() . '/.editorconfig';
            $stubPath = PACKAGE_ROOT . '/stubs/editorconfig.stub';
            if (is_file($stubPath)) {
                $content = file_get_contents($stubPath);
                if ($content !== false) {
                    file_put_contents($path, $content);
                }
            }
            $this->output->success('.editorconfig file generated.');
            return Command::SUCCESS;
        }

        return parent::afterCheck($result);
    }
}
