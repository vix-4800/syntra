<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Health;

use Vix\Syntra\DTO\CommandResult;
use Vix\Syntra\Utils\ProcessRunner;

class ComposerChecker
{
    public function __construct(
        private readonly ProcessRunner $processRunner,
        private readonly string $projectRoot
    ) {
        //
    }

    public function run(): CommandResult
    {
        $result = $this->processRunner->run(
            'composer',
            ['outdated', '--direct', '--format=json'],
            ['working_dir' => $this->projectRoot]
        );

        if ($result->exitCode !== 0) {
            return CommandResult::error(["Composer errored out:\n$result->stderr"]);
        }

        $json = json_decode($result->output, true);
        if (!isset($json['installed']) || count($json['installed']) === 0) {
            return CommandResult::ok(["All packages are up to date."]);
        }

        $packages = array_map(fn($pkg): string => "{$pkg['name']} ({$pkg['version']} → {$pkg['latest']})", $json['installed']);

        return CommandResult::warning($packages);
    }
}
