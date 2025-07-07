<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Vix\Syntra\Commands\Health\HealthCheckCommandInterface;
use Vix\Syntra\Commands\SyntraCommand;
use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Enums\CommandStatus;
use Vix\Syntra\Facades\Config;
use Vix\Syntra\Traits\HandlesResultTrait;

class EditorConfigCheckCommand extends SyntraCommand implements HealthCheckCommandInterface
{
    use HandlesResultTrait;

    private const DEFAULT_CONFIG = <<<'CFG'
root = true

[*]
charset = utf-8
end_of_line = lf
indent_size = 4
indent_style = space
insert_final_newline = true
trim_trailing_whitespace = true

[*.md]
trim_trailing_whitespace = false

[*.{yml,yaml}]
indent_size = 2

[docker-compose.yml]
indent_size = 2

[*.php]
indent_style = space
indent_size = 4
insert_final_newline = true
trim_trailing_whitespace = true
CFG;

/**
 * Configure the command options.
 */
    protected function configure(): void
    {
        parent::configure();
        $this->setName('health:editorconfig')
            ->setDescription('Checks for the existence of a .editorconfig file.')
            ->addOption('generate', 'g', InputOption::VALUE_NONE, 'Generate a default .editorconfig file if missing');
    }

/**
 * Run check.
 */
    public function runCheck(): CommandResult
    {
        $path = Config::getProjectRoot() . '/.editorconfig';

        if (is_file($path)) {
            return CommandResult::ok(['.editorconfig file exists.']);
        }

        return CommandResult::warning([
            '.editorconfig file not found. Use --generate to create a default one.',
        ]);
    }

/**
 * Perform the command actions.
 */
    public function perform(): int
    {
        $this->output->section('Checking for .editorconfig...');

        $result = $this->runCheck();
        if ($result->status === CommandStatus::WARNING && $this->input->getOption('generate')) {
            $path = Config::getProjectRoot() . '/.editorconfig';
            file_put_contents($path, self::DEFAULT_CONFIG . "\n");
            $this->output->success('.editorconfig file generated.');
            return Command::SUCCESS;
        }

        return $this->handleResult($result, '.editorconfig check completed.');
    }
}
