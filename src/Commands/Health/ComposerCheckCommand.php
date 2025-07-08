<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\Commands\Health\AbstractHealthCommand;
use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Facades\Process;
use Vix\Syntra\Facades\Project;

class ComposerCheckCommand extends AbstractHealthCommand
{
    protected string $sectionTitle = 'Running Composer check...';
    protected string $successMessage = 'Composer check completed.';

    protected function configure(): void
    {
        parent::configure();
        $this->setName('health:composer')
            ->setDescription('Checks Composer dependencies for updates.');
    }

    public function runCheck(): CommandResult
    {
        $result = Process::run(
            'composer',
            ['outdated', '--direct', '--format=json'],
            ['working_dir' => Project::getRootPath()]
        );

        if ($result->exitCode !== 0) {
            return CommandResult::error(["Composer errored out:\n$result->stderr"]);
        }

        $json = json_decode($result->output, true);
        if (!isset($json['installed']) || count($json['installed']) === 0) {
            return CommandResult::ok(['All packages are up to date.']);
        }

        $packages = array_map(
            fn ($pkg): string => "{$pkg['name']} ({$pkg['version']} → {$pkg['latest']})",
            $json['installed']
        );

        return CommandResult::warning($packages);
    }
}
